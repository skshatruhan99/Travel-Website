<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendBookingEmail;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function show(Request $request)
    {
        $booking = Booking::with('items')->where('booking_reference', $request->string('reference'))
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('seat_hold_expires_at')
                    ->orWhere('seat_hold_expires_at', '>', now());
            })
            ->firstOrFail();
        $pendingPayment = $booking->payments()->where('status', 'pending')->latest('id')->first();

        return view('pages.payment', compact('booking', 'pendingPayment'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'exists:bookings,booking_reference'],
            'method' => ['required', 'in:cash'],
        ]);

        $booking = Booking::where('booking_reference', $data['reference'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $existingCashPayment = $booking->payments()
            ->where('method', 'cash')
            ->whereIn('status', ['pending', 'success'])
            ->first();

        if (in_array($booking->status, ['confirmed', 'completed'], true) && $existingCashPayment) {
            return redirect()->route('invoice', ['reference' => $booking->booking_reference]);
        }

        abort_unless(
            $booking->status === 'pending'
                && (! $booking->seat_hold_expires_at || $booking->seat_hold_expires_at->isFuture()),
            404
        );

        [$booking, $invoice, $created] = DB::transaction(function () use ($booking) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            $cashPayment = Payment::where('booking_id', $booking->id)
                ->where('method', 'cash')
                ->whereIn('status', ['pending', 'success'])
                ->lockForUpdate()
                ->first();

            if ($cashPayment) {
                if ($booking->status === 'pending') {
                    $booking->update(['status' => 'confirmed', 'seat_hold_expires_at' => null]);
                }

                return [$booking, Invoice::ensureForBooking($booking), false];
            }

            abort_if($booking->status !== 'pending', 409, 'This booking is no longer awaiting confirmation.');

            Payment::where('booking_id', $booking->id)
                ->where('gateway', 'eSewa')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->update(['status' => 'failed']);

            $cashPayment = Payment::create([
                'booking_id' => $booking->id,
                'payment_reference' => 'PAY'.strtoupper(Str::random(9)),
                'gateway' => 'Cash on Delivery',
                'method' => 'cash',
                'amount' => $booking->total_amount,
                'currency' => 'NPR',
                'status' => 'pending',
            ]);

            $booking->update(['status' => 'confirmed', 'seat_hold_expires_at' => null]);

            return [$booking, Invoice::ensureForBooking($booking), true];
        });

        if (! $created) {
            return redirect()->route('invoice', ['reference' => $booking->booking_reference]);
        }

        GenerateInvoicePdf::dispatch($invoice->id)->afterCommit();
        SendBookingEmail::dispatch($booking->id)->afterCommit();

        return redirect()->route('invoice', ['reference' => $booking->booking_reference]);
    }

    public function esewaSuccess(Request $request)
    {
        $encodedData = $request->string('data');
        abort_if(blank($encodedData), 400, 'Invalid eSewa response.');

        $decodedData = base64_decode((string) $encodedData, true);
        abort_if($decodedData === false, 400, 'Invalid eSewa response.');

        $payload = json_decode($decodedData, true);
        abort_if(! is_array($payload), 400, 'Invalid eSewa response.');

        abort_unless(
            ($payload['signed_field_names'] ?? null) === 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names',
            400,
            'Invalid eSewa response fields.'
        );

        abort_unless(strtoupper((string) ($payload['status'] ?? '')) === 'COMPLETE', 400, 'Payment was not successful.');

        $transactionUuid = (string) ($payload['transaction_uuid'] ?? '');
        abort_if(blank($transactionUuid), 400, 'Missing transaction reference.');

        $secretKey = config('services.esewa.secret_key');
        abort_if(blank($secretKey), 503, 'eSewa is not configured.');

        $signaturePayload = collect(explode(',', $payload['signed_field_names']))
            ->map(fn (string $field) => $field.'='.($payload[$field] ?? ''))
            ->implode(',');
        $expectedSignature = base64_encode(hash_hmac('sha256', $signaturePayload, $secretKey, true));

        abort_unless(
            hash_equals($expectedSignature, (string) ($payload['signature'] ?? '')),
            400,
            'Invalid eSewa signature.'
        );

        $payment = Payment::with('booking')->where('payment_reference', $transactionUuid)->first();
        abort_if(! $payment, 404, 'Payment record not found.');
        abort_unless($payment->booking->user_id === $request->user()->id, 403);
        abort_unless($payment->currency === 'NPR', 400, 'Invalid payment currency.');
        if (in_array($payment->status, ['success', 'refunded'], true)) {
            return redirect()->route('invoice', ['reference' => $payment->booking->booking_reference]);
        }
        abort_unless(in_array($payment->status, ['pending', 'failed'], true), 409, 'This payment cannot be processed.');

        abort_unless((string) ($payload['product_code'] ?? '') === (string) config('services.esewa.product_code'), 400, 'Invalid product code.');
        abort_unless(
            number_format((float) ($payload['total_amount'] ?? 0), 2, '.', '') === number_format((float) $payment->amount, 2, '.', ''),
            400,
            'Payment amount mismatch.'
        );

        $verifiedTransaction = $this->verifyEsewaStatus($payment);
        abort_unless(strtoupper((string) ($verifiedTransaction['status'] ?? '')) === 'COMPLETE', 400, 'eSewa transaction verification failed.');

        return $this->completeVerifiedEsewaPayment($payment);
    }

    public function esewaFailure(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'exists:bookings,booking_reference'],
        ]);

        $booking = Booking::where('booking_reference', $data['reference'])
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'cancelled'])
            ->firstOrFail();
        $payment = Payment::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'failed'])
            ->latest()
            ->first();

        if (! $payment) {
            if ($booking->status === 'cancelled') {
                return redirect()->route('account')
                    ->withErrors(['payment' => 'No active online payment was found.']);
            }

            return redirect()->route('payment', ['reference' => $booking->booking_reference])
                ->withErrors(['payment' => 'No active online payment was found.']);
        }

        $verifiedTransaction = $this->verifyEsewaStatus($payment);
        $providerStatus = strtoupper((string) ($verifiedTransaction['status'] ?? ''));

        if ($providerStatus === 'COMPLETE') {
            return $this->completeVerifiedEsewaPayment($payment);
        }

        abort_unless($providerStatus === 'PENDING', 400, 'eSewa returned an unexpected payment status.');

        $bookingStatus = DB::transaction(function () use ($payment) {
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status === 'pending') {
                $lockedPayment->update(['status' => 'failed']);
            }

            if (
                $booking->status === 'pending'
                && $booking->seat_hold_expires_at
                && $booking->seat_hold_expires_at->isPast()
            ) {
                $booking->update(['status' => 'cancelled', 'seat_hold_expires_at' => null]);
            }

            return $booking->status;
        });

        if ($bookingStatus === 'cancelled') {
            return redirect()->route('account')
                ->withErrors(['payment' => 'The previous online payment is pending. If it completes, our team will process a refund.']);
        }

        return redirect()->route('payment', ['reference' => $booking->booking_reference])
            ->withErrors(['payment' => 'The previous online payment was not completed. You can confirm with cash on delivery.']);
    }

    private function verifyEsewaStatus(Payment $payment): array
    {
        try {
            $statusResponse = Http::acceptJson()
                ->timeout(10)
                ->get(config('services.esewa.status_url'), [
                    'product_code' => config('services.esewa.product_code'),
                    'total_amount' => $payment->amount,
                    'transaction_uuid' => $payment->payment_reference,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Could not connect to the eSewa transaction-status service.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            abort(503, 'Could not reach eSewa to verify the transaction. Please retry shortly.');
        }

        abort_unless($statusResponse->successful(), 502, 'Could not verify the transaction with eSewa.');

        $verifiedTransaction = $statusResponse->json();
        abort_unless(
            is_array($verifiedTransaction)
                && (string) ($verifiedTransaction['product_code'] ?? '') === (string) config('services.esewa.product_code')
                && (string) ($verifiedTransaction['transaction_uuid'] ?? '') === $payment->payment_reference
                && number_format((float) ($verifiedTransaction['total_amount'] ?? 0), 2, '.', '') === number_format((float) $payment->amount, 2, '.', ''),
            400,
            'eSewa transaction verification failed.'
        );

        return $verifiedTransaction;
    }

    private function completeVerifiedEsewaPayment(Payment $payment)
    {
        [$booking, $invoice, $sendConfirmationEmail] = DB::transaction(function () use ($payment) {
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedPayment->status, ['success', 'refunded'], true)) {
                return [$booking, Invoice::ensureForBooking($booking), false];
            }
            abort_unless(in_array($lockedPayment->status, ['pending', 'failed'], true), 409, 'This payment has already been processed.');

            abort_unless(
                in_array($booking->status, ['pending', 'confirmed', 'completed', 'cancelled', 'refunded'], true),
                409,
                'This booking cannot receive a payment.'
            );

            $holdExpired = $booking->seat_hold_expires_at && $booking->seat_hold_expires_at->isPast();
            $alreadyPaid = Payment::where('booking_id', $booking->id)
                ->where('status', 'success')
                ->exists();
            $cashOnDeliverySelected = Payment::where('booking_id', $booking->id)
                ->where('method', 'cash')
                ->whereIn('status', ['pending', 'success'])
                ->where('id', '!=', $lockedPayment->id)
                ->exists();
            $sendConfirmationEmail = false;

            $lockedPayment->update(['status' => 'success', 'paid_at' => now()]);

            if ($booking->status === 'pending' && ! $holdExpired) {
                $booking->update(['status' => 'confirmed', 'seat_hold_expires_at' => null]);
                $sendConfirmationEmail = ! $alreadyPaid;
            } else {
                if ($booking->status === 'pending') {
                    $booking->update(['status' => 'cancelled', 'seat_hold_expires_at' => null]);
                }

                if (
                    in_array($booking->status, ['cancelled', 'completed', 'refunded'], true)
                    || $holdExpired
                    || $alreadyPaid
                    || $cashOnDeliverySelected
                ) {
                    Refund::firstOrCreate(
                        ['payment_id' => $lockedPayment->id],
                        [
                            'booking_id' => $booking->id,
                            'amount' => $lockedPayment->amount,
                            'currency' => $lockedPayment->currency,
                            'status' => 'requested',
                            'reason' => $holdExpired
                                ? 'Payment completed after the seat reservation expired.'
                                : ($alreadyPaid
                                    ? 'Duplicate successful payment.'
                                    : ($cashOnDeliverySelected
                                        ? 'eSewa payment completed after cash on delivery was selected.'
                                        : ($booking->status === 'cancelled'
                                            ? 'Booking was cancelled before payment completed.'
                                            : 'Payment completed after the booking was closed.'))),
                            'requested_at' => now(),
                        ]
                    );
                } elseif ($booking->status === 'confirmed') {
                    $sendConfirmationEmail = ! $alreadyPaid && ! $cashOnDeliverySelected;
                }
            }

            return [$booking, Invoice::ensureForBooking($booking), $sendConfirmationEmail];
        });

        GenerateInvoicePdf::dispatch($invoice->id)->afterCommit();
        if ($sendConfirmationEmail) {
            SendBookingEmail::dispatch($booking->id)->afterCommit();
        }

        return redirect()->route('invoice', ['reference' => $booking->booking_reference]);
    }
}

<?php

namespace App\Jobs;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SendBookingEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::with(['user', 'tour.destination', 'items', 'travelers', 'payments', 'invoice'])
            ->findOrFail($this->bookingId);

        $hasConfirmationPayment = $booking->payments->contains(fn ($payment) => $payment->status === 'success'
            || ($payment->method === 'cash' && $payment->status === 'pending'));

        if ($booking->status !== 'confirmed' || ! $hasConfirmationPayment) {
            Log::info('Skipping confirmation email for a booking that is not confirmed with a valid payment record.', [
                'booking_id' => $booking->id,
            ]);

            return;
        }

        $recipient = $booking->travelers->first()?->email ?: $booking->user?->email;

        if (blank($recipient)) {
            throw new RuntimeException('No email address is available for this booking.');
        }

        $invoice = $booking->invoice ?? Invoice::ensureForBooking($booking);

        if (! $invoice->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            GenerateInvoicePdf::dispatchSync($invoice->id);
            $booking->load('invoice');
        }

        Mail::to($recipient)->send(new BookingConfirmedMail($booking));
    }
}

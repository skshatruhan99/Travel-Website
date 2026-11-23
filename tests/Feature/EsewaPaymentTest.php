<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EsewaPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_is_only_available_to_the_booking_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = $this->bookingFor($owner);

        $this->actingAs($otherUser)
            ->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertNotFound();
    }

    public function test_another_user_cannot_submit_payment_for_the_booking(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = $this->bookingFor($owner);

        $this->actingAs($otherUser)
            ->post(route('payment.store'), [
                'reference' => $booking->booking_reference,
                'method' => 'cash',
            ])
            ->assertNotFound();
    }

    public function test_esewa_success_is_confirmed_after_server_status_verification(): void
    {
        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-secret',
            'services.esewa.status_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
        ]);

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $payment = $this->paymentFor($booking);

        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::response([
                'product_code' => 'EPAYTEST',
                'transaction_uuid' => $payment->payment_reference,
                'total_amount' => 105,
                'status' => 'COMPLETE',
                'ref_id' => '0001TS9',
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('esewa.success', ['data' => $this->signedCallback($payment->payment_reference)]))
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'success',
            'currency' => 'NPR',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);

        Http::assertSent(function ($request) use ($payment) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_starts_with($request->url(), 'https://rc.esewa.com.np/api/epay/transaction/status/')
                && ($query['product_code'] ?? null) === 'EPAYTEST'
                && ($query['transaction_uuid'] ?? null) === $payment->payment_reference
                && number_format((float) ($query['total_amount'] ?? 0), 2, '.', '') === '105.00';
        });
    }

    public function test_esewa_success_is_rejected_when_server_status_is_not_complete(): void
    {
        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-secret',
            'services.esewa.status_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
        ]);

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $payment = $this->paymentFor($booking);

        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::response([
                'product_code' => 'EPAYTEST',
                'transaction_uuid' => $payment->payment_reference,
                'total_amount' => 105,
                'status' => 'PENDING',
                'ref_id' => null,
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('esewa.success', ['data' => $this->signedCallback($payment->payment_reference)]))
            ->assertBadRequest();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
            'paid_at' => null,
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }

    public function test_failure_return_checks_esewa_and_allows_a_late_verified_success(): void
    {
        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-secret',
            'services.esewa.status_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
        ]);

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $payment = $this->paymentFor($booking);

        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::sequence()
                ->push([
                    'product_code' => 'EPAYTEST',
                    'transaction_uuid' => $payment->payment_reference,
                    'total_amount' => 105,
                    'status' => 'PENDING',
                ])
                ->push([
                    'product_code' => 'EPAYTEST',
                    'transaction_uuid' => $payment->payment_reference,
                    'total_amount' => 105,
                    'status' => 'COMPLETE',
                    'ref_id' => '0001TS9',
                ]),
        ]);

        $this->actingAs($user)
            ->get(route('esewa.failure', ['reference' => $booking->booking_reference]))
            ->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'failed']);

        $this->get(route('esewa.success', ['data' => $this->signedCallback($payment->payment_reference)]))
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'success']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_late_esewa_success_after_switching_to_cash_creates_a_refund_request(): void
    {
        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-secret',
            'services.esewa.status_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
        ]);

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $oldPayment = $this->paymentFor($booking);
        $oldPayment->update(['status' => 'failed']);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'CASH-'.$booking->booking_reference,
            'gateway' => 'Cash on Delivery',
            'method' => 'cash',
            'amount' => 105,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);
        $booking->update(['status' => 'confirmed']);

        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::response([
                'product_code' => 'EPAYTEST',
                'transaction_uuid' => $oldPayment->payment_reference,
                'total_amount' => 105,
                'status' => 'COMPLETE',
                'ref_id' => '0001TS9',
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('esewa.success', ['data' => $this->signedCallback($oldPayment->payment_reference)]))
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));

        $this->assertDatabaseHas('payments', ['id' => $oldPayment->id, 'status' => 'success']);
        $this->assertDatabaseHas('refunds', [
            'booking_id' => $booking->id,
            'payment_id' => $oldPayment->id,
            'status' => 'requested',
        ]);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);
    }

    public function test_verified_payment_after_seat_hold_expiry_creates_a_refund_request(): void
    {
        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-secret',
            'services.esewa.status_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
        ]);

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $booking->update(['seat_hold_expires_at' => now()->subMinute()]);
        $payment = $this->paymentFor($booking);

        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::response([
                'product_code' => 'EPAYTEST',
                'transaction_uuid' => $payment->payment_reference,
                'total_amount' => 105,
                'status' => 'COMPLETE',
                'ref_id' => '0001TS9',
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('esewa.success', ['data' => $this->signedCallback($payment->payment_reference)]))
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'success']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('refunds', [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'status' => 'requested',
        ]);
    }

    private function bookingFor(User $user): Booking
    {
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara-'.$user->id,
            'country' => 'Nepal',
            'description' => 'A mountain destination.',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Mountain Tour',
            'slug' => 'mountain-tour-'.$user->id,
            'price' => 100,
            'is_published' => true,
        ]);

        return Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NP'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
            'adults' => 1,
            'children' => 0,
            'subtotal' => 100,
            'tax' => 5,
            'total_amount' => 105,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);
    }

    private function paymentFor(Booking $booking): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => $booking->booking_reference.'-ABC123',
            'gateway' => 'eSewa',
            'method' => 'wallet',
            'amount' => 105,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);
    }

    private function signedCallback(string $transactionUuid): string
    {
        $payload = [
            'transaction_code' => '000AWEO',
            'status' => 'COMPLETE',
            'total_amount' => 105,
            'transaction_uuid' => $transactionUuid,
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names',
        ];
        $signaturePayload = collect(explode(',', $payload['signed_field_names']))
            ->map(fn (string $field) => $field.'='.($payload[$field] ?? ''))
            ->implode(',');
        $payload['signature'] = base64_encode(hash_hmac('sha256', $signaturePayload, 'test-secret', true));

        return base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));
    }
}

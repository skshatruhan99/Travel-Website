<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_booking_cancellation_requests_a_refund_and_admin_records_manual_processing(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_reference' => 'NPREFUND001',
            'adults' => 2,
            'children' => 0,
            'subtotal' => 500,
            'tax' => 25,
            'total_amount' => 525,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYREFUND001',
            'gateway' => 'eSewa',
            'method' => 'wallet',
            'amount' => 525,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('booking.cancel', $booking), ['reason' => 'Schedule changed'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $refund = Refund::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame('requested', $refund->status);
        $this->assertSame('525.00', number_format((float) $refund->amount, 2, '.', ''));
        $this->assertSame('success', $payment->fresh()->status);
        $this->assertSame('cancelled', $booking->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.refunds.process', [$booking, $refund]), [
                'provider_reference' => 'ESEWA-REF-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'status' => 'processed',
            'provider_reference' => 'ESEWA-REF-123',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'refunded',
        ]);
    }

    public function test_cancelling_an_unpaid_booking_does_not_create_a_refund(): void
    {
        $user = User::factory()->create();
        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_reference' => 'NPREFUND002',
            'adults' => 1,
            'children' => 0,
            'total_amount' => 100,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('booking.cancel', $booking))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseMissing('refunds', [
            'booking_id' => $booking->id,
        ]);
    }
}

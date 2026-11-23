<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashOnDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_offers_only_cash_on_delivery(): void
    {
        $user = User::factory()->create();
        $booking = $this->bookingFor($user);

        $this->actingAs($user)
            ->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Cash on Delivery')
            ->assertSee('Pay in cash on arrival')
            ->assertDontSee('eSewa')
            ->assertDontSee('Debit / Credit Card')
            ->assertDontSee('Digital Wallet');

        $this->assertFalse(Route::has('esewa.start'));
    }

    public function test_cash_on_delivery_confirms_booking_and_handles_an_older_pending_payment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $booking = $this->bookingFor($user);
        $oldPayment = Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'OLD-'.$booking->booking_reference,
            'gateway' => 'eSewa',
            'method' => 'wallet',
            'amount' => $booking->total_amount,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('payment.store'), [
                'reference' => $booking->booking_reference,
                'method' => 'cash',
            ])
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));

        $cashPayment = Payment::where('booking_id', $booking->id)
            ->where('method', 'cash')
            ->firstOrFail();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('payments', [
            'id' => $oldPayment->id,
            'status' => 'failed',
        ]);
        $this->assertSame('pending', $cashPayment->status);
        $this->assertNull($cashPayment->paid_at);

        $this->actingAs($user)
            ->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Payment due in cash on arrival');

        $this->actingAs($user)
            ->post(route('payment.store'), [
                'reference' => $booking->booking_reference,
                'method' => 'cash',
            ])
            ->assertRedirect(route('invoice', ['reference' => $booking->booking_reference]));

        $this->assertDatabaseCount('payments', 2);
    }

    public function test_online_payment_methods_are_rejected(): void
    {
        $user = User::factory()->create();
        $booking = $this->bookingFor($user);

        $this->actingAs($user)
            ->post(route('payment.store'), [
                'reference' => $booking->booking_reference,
                'method' => 'card',
            ])
            ->assertSessionHasErrors('method');

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending']);
    }

    private function bookingFor(User $user): Booking
    {
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'cash-pokhara-'.$user->id,
            'country' => 'Nepal',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Cash Tour',
            'slug' => 'cash-tour-'.$user->id,
            'price' => 100,
            'is_published' => true,
        ]);

        return Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NPCASH'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'adults' => 1,
            'children' => 0,
            'subtotal' => 100,
            'tax' => 5,
            'total_amount' => 105,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);
    }
}

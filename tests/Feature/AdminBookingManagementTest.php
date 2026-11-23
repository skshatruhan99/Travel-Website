<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_booking_traveler_and_payment_details(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $customer = User::factory()->create();
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara',
            'country' => 'Nepal',
            'description' => 'A mountain destination.',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Lakeside Escape',
            'slug' => 'lakeside-escape',
            'price' => 500,
            'min_group_size' => 1,
            'max_group_size' => 10,
            'is_published' => true,
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NPDETAIL01',
            'travel_start_date' => '2026-11-10',
            'adults' => 1,
            'children' => 0,
            'subtotal' => 500,
            'tax' => 25,
            'total_amount' => 525,
            'currency' => 'NPR',
            'status' => 'pending',
            'special_request' => 'Vegetarian meals, please.',
        ]);
        BookingTraveler::create([
            'booking_id' => $booking->id,
            'first_name' => 'Asha',
            'last_name' => 'Rai',
            'email' => 'asha@example.test',
            'phone' => '+977123456789',
            'nationality' => 'Nepali',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYDETAIL01',
            'gateway' => 'Test gateway',
            'method' => 'cash',
            'amount' => 525,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('NPDETAIL01')
            ->assertSee('Lakeside Escape')
            ->assertSee('asha@example.test')
            ->assertSee('PAYDETAIL01')
            ->assertSee('Vegetarian meals, please.');

        $this->actingAs($admin)
            ->get(route('admin.bookings'))
            ->assertOk()
            ->assertSee(route('admin.bookings.show', $booking));
    }

    public function test_admin_cannot_transition_a_completed_booking(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $booking = Booking::create([
            'booking_reference' => 'NPCOMPLETE01',
            'adults' => 1,
            'children' => 0,
            'total_amount' => 0,
            'currency' => 'NPR',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\ProductionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_catalog_and_detail_page_allow_booking_and_wishlist(): void
    {
        $activity = $this->activity();

        $this->get(route('activities'))
            ->assertOk()
            ->assertSee($activity->name)
            ->assertSee(route('activities.show', $activity->slug))
            ->assertSee('Save activity');

        $this->get(route('activities.show', $activity->slug))
            ->assertOk()
            ->assertSee('Book this activity')
            ->assertSee('Activity date');

        $this->actingAs(User::factory()->create())
            ->post(route('wishlist.store'), [
                'item_type' => 'activity',
                'item_id' => $activity->id,
            ])->assertRedirect();

        $this->get(route('wishlist'))
            ->assertOk()
            ->assertSee($activity->name);
        $this->assertDatabaseHas('wishlists', [
            'item_type' => 'activity',
            'item_id' => $activity->id,
        ]);
    }

    public function test_activities_for_destinations_outside_nepal_are_hidden(): void
    {
        $destination = Destination::create([
            'name' => 'Bali',
            'slug' => 'bali',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
        $activity = Activity::create([
            'destination_id' => $destination->id,
            'name' => 'Bali Beach Activity',
            'slug' => 'bali-beach-activity',
            'price' => 100,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $this->get(route('activities'))->assertOk()->assertDontSee('Bali Beach Activity');
        $this->get(route('activities.show', $activity->slug))->assertNotFound();
    }

    public function test_activity_checkout_persists_booking_and_redirects_to_payment(): void
    {
        $activity = $this->activity(['price' => 1000]);
        $user = User::factory()->create();
        $date = today()->addDays(7)->toDateString();

        $this->get(route('activities.booking', [
            'activity' => $activity->slug,
            'activity_date' => $date,
            'participants' => 2,
        ]))
            ->assertOk()
            ->assertSee('Complete Your Activity Booking')
            ->assertSee('NPR 2,100.00');

        $response = $this->actingAs($user)->post(route('activities.booking.store', $activity->slug), [
            'activity_date' => $date,
            'participants' => 2,
            'first_name' => 'Asha',
            'last_name' => 'Rai',
            'email' => 'asha@example.test',
            'phone' => '+977123456789',
        ]);
        $booking = Booking::firstOrFail();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => $user->id,
            'tour_id' => null,
            'travel_start_date' => $date,
            'adults' => 2,
            'subtotal' => 2000,
            'tax' => 100,
            'total_amount' => 2100,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'item_type' => 'activity',
            'item_id' => $activity->id,
            'quantity' => 2,
            'total_price' => 2000,
        ]);
        $this->assertDatabaseHas('booking_travelers', [
            'booking_id' => $booking->id,
            'first_name' => 'Asha',
        ]);
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));
    }

    public function test_activity_capacity_counts_active_bookings_for_the_same_date(): void
    {
        $activity = $this->activity(['capacity' => 2]);
        $date = today()->addDays(7)->toDateString();
        $booking = Booking::create([
            'booking_reference' => 'NPACTIVITY01',
            'travel_start_date' => $date,
            'adults' => 2,
            'total_amount' => 2000,
            'currency' => 'NPR',
            'status' => 'pending',
            'seat_hold_expires_at' => now()->addMinutes(15),
        ]);
        BookingItem::create([
            'booking_id' => $booking->id,
            'item_type' => 'activity',
            'item_id' => $activity->id,
            'name' => $activity->name,
            'quantity' => 2,
            'unit_price' => $activity->price,
            'total_price' => 2000,
        ]);

        $this->from(route('activities.show', $activity->slug))
            ->get(route('activities.booking', [
                'activity' => $activity->slug,
                'activity_date' => $date,
                'participants' => 1,
            ]))
            ->assertSessionHasErrors('participants');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_activity_booking_continues_through_payment_and_invoice(): void
    {
        Storage::fake('local');
        $activity = $this->activity();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('activities.booking.store', $activity->slug), [
            'activity_date' => today()->addDays(7)->toDateString(),
            'participants' => 1,
            'first_name' => 'Asha',
            'email' => 'asha@example.test',
            'phone' => '+977123456789',
        ]);
        $booking = Booking::firstOrFail();

        $this->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee($activity->name);

        $this->get(route('account'))
            ->assertOk()
            ->assertSee($activity->name);

        $booking->update(['status' => 'confirmed']);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYACTIVITY01',
            'gateway' => 'Test gateway',
            'method' => 'wallet',
            'amount' => $booking->total_amount,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee($activity->name)
            ->assertSee('Pokhara')
            ->assertSee('1 participant')
            ->assertSee('NPR 105.00');
    }

    public function test_demo_activities_are_seeded_from_the_existing_activity_examples(): void
    {
        $this->seed(ProductionDemoSeeder::class);

        $this->assertDatabaseHas('activities', [
            'slug' => 'mountain-hiking',
            'name' => 'Mountain Hiking',
            'capacity' => 12,
            'is_active' => true,
        ]);
    }

    private function activity(array $overrides = []): Activity
    {
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'activity-pokhara',
            'country' => 'Nepal',
        ]);

        return Activity::create(array_merge([
            'destination_id' => $destination->id,
            'name' => 'Lake Boating',
            'slug' => 'lake-boating',
            'description' => 'Enjoy peaceful lake views.',
            'category' => 'Water activity',
            'price' => 100,
            'capacity' => 10,
            'is_active' => true,
        ], $overrides));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Database\Seeders\ProductionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tour_catalog_only_shows_published_database_tours(): void
    {
        $destination = $this->destination();
        $this->tour($destination, ['title' => 'Published Adventure', 'slug' => 'published-adventure']);
        $this->tour($destination, ['title' => 'Hidden Adventure', 'slug' => 'hidden-adventure', 'is_published' => false]);

        $this->get(route('tour'))
            ->assertOk()
            ->assertSee('Published Adventure')
            ->assertDontSee('Hidden Adventure');
    }

    public function test_tours_for_destinations_outside_nepal_are_hidden(): void
    {
        $destination = Destination::create([
            'name' => 'Bali',
            'slug' => 'bali',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
        $tour = $this->tour($destination, [
            'title' => 'Bali Island Tour',
            'slug' => 'bali-island-tour',
        ]);

        $this->get(route('tour'))->assertOk()->assertDontSee('Bali Island Tour');
        $this->get(route('tour.show', $tour->slug))->assertNotFound();
    }

    public function test_public_tour_detail_uses_a_calendar_and_scheduled_booking_still_works(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination, ['title' => 'Published Adventure', 'slug' => 'published-adventure']);
        TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'capacity' => 3,
        ]);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('Published Adventure')
            ->assertSee('name="travel_start_date"', false)
            ->assertDontSee('Available departure');

        $this->get(route('booking', [
            'tour_id' => $tour->id,
            'tour_date_id' => TourDate::where('tour_id', $tour->id)->value('id'),
            'adults' => 1,
        ]))
            ->assertOk()
            ->assertSee('Complete Your Booking')
            ->assertSee('Continue to Payment');
    }

    public function test_tour_detail_allows_a_client_to_choose_a_date_without_a_scheduled_departure(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination, ['title' => 'Flexible Adventure']);
        $travelDate = now()->addDays(30)->toDateString();

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertSee('Choose travel date')
            ->assertSee('Availability will be confirmed after booking.')
            ->assertSee('>Book now</button>', false)
            ->assertDontSee('<button class="btn btn-primary" type="submit" style="width:100%" disabled', false);

        $this->get(route('booking', [
            'tour_id' => $tour->id,
            'travel_start_date' => $travelDate,
            'adults' => 1,
            'children' => 0,
        ]))
            ->assertOk()
            ->assertSee(now()->addDays(30)->format('M j, Y'));
    }

    public function test_tour_detail_shows_only_approved_reviews(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        $user = User::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'reviewable_type' => Tour::class,
            'reviewable_id' => $tour->id,
            'rating' => 5,
            'comment' => 'Approved review text.',
            'status' => 'approved',
        ]);
        Review::create([
            'user_id' => $user->id,
            'reviewable_type' => Tour::class,
            'reviewable_id' => $tour->id,
            'rating' => 1,
            'comment' => 'Pending review text.',
            'status' => 'pending',
        ]);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('Approved review text.')
            ->assertDontSee('Pending review text.');
    }

    public function test_booking_can_be_created_for_a_client_selected_date_without_a_scheduled_departure(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        $user = User::factory()->create();
        $travelDate = now()->addDays(30)->toDateString();

        $response = $this->actingAs($user)->post(route('booking.store'), [
            'tour_id' => $tour->id,
            'travel_start_date' => $travelDate,
            'adults' => 1,
            'children' => 0,
            'first_name' => 'Test',
            'last_name' => 'Traveler',
            'email' => 'traveler@example.test',
            'phone' => '+977123456789',
        ]);

        $booking = Booking::firstOrFail();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'tour_id' => $tour->id,
            'tour_date_id' => null,
            'travel_start_date' => $travelDate,
            'travel_end_date' => null,
        ]);
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));
    }

    public function test_booking_requires_a_calendar_date_when_no_scheduled_departure_is_selected(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);

        $this->from(route('tour.show', $tour->slug))
            ->get(route('booking', ['tour_id' => $tour->id, 'adults' => 1]))
            ->assertRedirect(route('tour.show', $tour->slug))
            ->assertSessionHasErrors('travel_start_date');
    }

    public function test_demo_tours_are_seeded_with_an_upcoming_departure(): void
    {
        $this->seed(ProductionDemoSeeder::class);

        $tour = Tour::where('slug', 'pokhara-lake-adventure')->firstOrFail();

        $this->assertTrue($tour->dates()
            ->where('is_available', true)
            ->whereDate('departure_date', '>=', today())
            ->exists());
    }

    public function test_booking_uses_selected_tour_price_and_saves_tour_reference(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination, ['price' => 100, 'child_price' => 50]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'return_date' => now()->addDays(37)->toDateString(),
            'capacity' => 10,
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('booking.store'), [
            'tour_id' => $tour->getKey(),
            'travel_start_date' => $tourDate->departure_date->toDateString(),
            'adults' => 2,
            'children' => 1,
            'first_name' => 'Test',
            'last_name' => 'Traveler',
            'email' => 'traveler@example.test',
            'phone' => '+977123456789',
        ]);

        $booking = Booking::firstOrFail();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->getKey(),
            'tour_id' => $tour->getKey(),
            'tour_date_id' => $tourDate->id,
            'adults' => 2,
            'children' => 1,
            'subtotal' => 250,
            'tax' => 12.5,
            'total_amount' => 262.5,
        ]);
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));

        $this->actingAs($user)
            ->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Choose Payment Method');
    }

    public function test_invalid_coupon_explains_why_checkout_did_not_continue(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'capacity' => 10,
        ]);
        $user = User::factory()->create();
        $bookingUrl = route('booking', [
            'tour_id' => $tour->id,
            'tour_date_id' => $tourDate->id,
            'adults' => 1,
        ]);

        $this->actingAs($user)
            ->from($bookingUrl)
            ->post(route('booking.store'), [
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'adults' => 1,
                'children' => 0,
                'first_name' => 'Test',
                'email' => 'traveler@example.test',
                'phone' => '+977123456789',
                'coupon_code' => '123456',
            ])
            ->assertRedirect($bookingUrl);

        $this->get($bookingUrl)
            ->assertOk()
            ->assertSee('This coupon code is invalid or expired.');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_cannot_exceed_departure_capacity(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'capacity' => 2,
        ]);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)->post(route('booking.store'), [
            'tour_id' => $tour->id,
            'travel_start_date' => $tourDate->departure_date->toDateString(),
            'adults' => 2,
            'children' => 0,
            'first_name' => 'First',
            'email' => 'first@example.test',
            'phone' => '123456789',
        ])->assertRedirect();

        $this->actingAs($secondUser)->from(route('booking', [
            'tour_id' => $tour->id,
            'travel_start_date' => $tourDate->departure_date->toDateString(),
            'adults' => 1,
        ]))->post(route('booking.store'), [
            'tour_id' => $tour->id,
            'travel_start_date' => $tourDate->departure_date->toDateString(),
            'adults' => 1,
            'children' => 0,
            'first_name' => 'Second',
            'email' => 'second@example.test',
            'phone' => '123456789',
        ])->assertSessionHasErrors('travelers');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_expired_pending_seat_holds_do_not_block_new_bookings(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'capacity' => 1,
        ]);
        Booking::create([
            'tour_id' => $tour->id,
            'tour_date_id' => $tourDate->id,
            'booking_reference' => 'NPEXPIRED01',
            'adults' => 1,
            'children' => 0,
            'total_amount' => 100,
            'currency' => 'NPR',
            'status' => 'pending',
            'seat_hold_expires_at' => now()->subMinute(),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->post(route('booking.store'), [
            'tour_id' => $tour->id,
            'tour_date_id' => $tourDate->id,
            'adults' => 1,
            'children' => 0,
            'first_name' => 'New',
            'email' => 'new@example.test',
            'phone' => '123456789',
        ])->assertRedirect();

        $this->assertDatabaseCount('bookings', 2);
    }

    private function destination(): Destination
    {
        return Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara',
            'country' => 'Nepal',
            'description' => 'A mountain destination.',
        ]);
    }

    private function tour(Destination $destination, array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'destination_id' => $destination->getKey(),
            'title' => 'Mountain Tour',
            'slug' => 'mountain-tour',
            'price' => 100,
            'min_group_size' => 1,
            'max_group_size' => 10,
            'is_published' => true,
            'is_best_seller' => false,
        ], $overrides));
    }
}

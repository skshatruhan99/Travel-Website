<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_real_matching_destinations_tours_hotels_activities_and_flights(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination);
        TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => today()->addDays(10)->toDateString(),
            'capacity' => 4,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'name' => 'Pokhara Lakeside Lodge',
            'slug' => 'pokhara-lakeside-lodge',
            'price_per_night' => 100,
            'is_active' => true,
        ]);
        $hotel->rooms()->create([
            'name' => 'Lake View Room',
            'price_per_night' => 100,
            'max_guests' => 2,
            'rooms_available' => 2,
            'is_active' => true,
        ]);
        Activity::create([
            'destination_id' => $destination->id,
            'name' => 'Phewa Lake Boating',
            'slug' => 'phewa-lake-boating',
            'category' => 'Water activity',
            'price' => 25,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $flight = $this->flight([
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_at' => today()->addDays(3)->setTime(8, 0),
        ]);
        Destination::create([
            'name' => 'Hidden Valley',
            'slug' => 'hidden-valley',
            'country' => 'Nepal',
            'is_active' => false,
        ]);

        $this->get(route('search', [
            'destination' => 'Pokhara',
            'check_in' => today()->addDays(3)->toDateString(),
            'travelers' => 2,
        ]))
            ->assertOk()
            ->assertSee('Pokhara')
            ->assertSee($tour->title)
            ->assertSee($hotel->name)
            ->assertSee('Phewa Lake Boating')
            ->assertSee($flight->flight_number)
            ->assertSee(route('activities.booking', [
                'activity' => 'phewa-lake-boating',
                'activity_date' => today()->addDays(3)->toDateString(),
                'participants' => 2,
            ]))
            ->assertDontSee('Hidden Valley');
    }

    public function test_search_matches_multiword_keywords_and_excludes_unavailable_inventory(): void
    {
        $destination = $this->destination();
        $tour = $this->tour($destination, [
            'title' => 'Pokhara Sunrise Trek',
            'slug' => 'pokhara-sunrise-trek',
            'max_group_size' => 8,
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => today()->addDays(5)->toDateString(),
            'capacity' => 2,
        ]);
        $booking = Booking::create([
            'booking_reference' => 'NPSEARCH01',
            'tour_id' => $tour->id,
            'tour_date_id' => $tourDate->id,
            'travel_start_date' => $tourDate->departure_date->toDateString(),
            'adults' => 2,
            'total_amount' => 200,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);

        $this->get(route('search', [
            'q' => 'Pokhara Sunrise',
            'check_in' => today()->addDays(3)->toDateString(),
            'travelers' => 1,
        ]))
            ->assertOk()
            ->assertDontSee($tour->title)
            ->assertSee('No matching options found');

        $flight = $this->flight([
            'flight_number' => 'RA505',
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_at' => today()->addDays(3)->setTime(9, 0),
            'available_seats' => 1,
        ]);
        $flightBooking = Booking::create([
            'booking_reference' => 'NPSEARCH02',
            'travel_start_date' => $flight->departure_at->toDateString(),
            'adults' => 1,
            'total_amount' => 100,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        BookingItem::create([
            'booking_id' => $flightBooking->id,
            'item_type' => 'flight',
            'item_id' => $flight->id,
            'name' => 'RA505',
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
        ]);

        $this->get(route('search', [
            'destination' => 'Pokhara',
            'check_in' => today()->addDays(3)->toDateString(),
            'travelers' => 1,
        ]))
            ->assertOk()
            ->assertSee('Pokhara')
            ->assertDontSee('RA505')
            ->assertDontSee('Select flight');
    }

    public function test_search_requires_a_term_future_date_and_valid_traveler_count(): void
    {
        $this->get(route('search'))->assertSessionHasErrors(['destination', 'q', 'check_in', 'travelers']);
        $this->get(route('search', [
            'destination' => 'Pokhara',
            'check_in' => today()->subDay()->toDateString(),
            'travelers' => 21,
        ]))->assertSessionHasErrors(['check_in', 'travelers']);
    }

    public function test_search_excludes_foreign_destinations_and_catalog_items(): void
    {
        $destination = Destination::create([
            'name' => 'Bali',
            'slug' => 'bali',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
        $this->tour($destination, [
            'title' => 'Bali Adventure',
            'slug' => 'bali-adventure',
        ]);
        Hotel::create([
            'destination_id' => $destination->id,
            'name' => 'Bali Stay',
            'slug' => 'bali-stay',
            'price_per_night' => 100,
            'is_active' => true,
        ]);
        Activity::create([
            'destination_id' => $destination->id,
            'name' => 'Bali Activity',
            'slug' => 'bali-activity',
            'price' => 100,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $this->get(route('search', [
            'destination' => 'Bali',
            'check_in' => today()->addDays(3)->toDateString(),
            'travelers' => 1,
        ]))
            ->assertOk()
            ->assertDontSee('Bali Adventure')
            ->assertDontSee('Bali Stay')
            ->assertDontSee('Bali Activity');
    }

    private function destination(): Destination
    {
        return Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara',
            'country' => 'Nepal',
            'location' => 'Western Nepal',
            'description' => 'A lakeside mountain destination.',
            'is_active' => true,
        ]);
    }

    private function tour(Destination $destination, array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'destination_id' => $destination->id,
            'title' => 'Pokhara Lake Adventure',
            'slug' => 'pokhara-lake-adventure',
            'short_description' => 'Explore the lakeside.',
            'price' => 100,
            'min_group_size' => 1,
            'max_group_size' => 10,
            'is_published' => true,
        ], $overrides));
    }

    private function flight(array $overrides = []): Flight
    {
        return Flight::create(array_merge([
            'airline' => 'Nepal Airlines',
            'flight_number' => 'RA101',
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_at' => today()->addDays(3)->setTime(8, 0),
            'arrival_at' => today()->addDays(3)->setTime(8, 45),
            'price' => 100,
            'available_seats' => 5,
            'class' => 'Economy',
            'is_active' => true,
        ], $overrides));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_destination_leads_through_places_tour_details_and_booking(): void
    {
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara',
            'country' => 'Nepal',
            'location' => 'Western Nepal',
            'short_description' => 'Lakeside serenity and Himalayan views.',
            'description' => 'A scenic lakeside destination.',
            'hero_image' => 'https://example.test/pokhara.jpg',
            'nearby_places' => [
                ['name' => 'Phewa Lake', 'distance' => '2 km', 'image' => 'https://example.test/lake.jpg', 'description' => 'A lakeside escape.'],
                ['name' => 'World Peace Pagoda', 'distance' => '7 km', 'image' => 'https://example.test/pagoda.jpg', 'description' => 'A hilltop viewpoint.'],
            ],
            'is_featured' => true,
            'is_active' => true,
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->getKey(),
            'title' => 'Pokhara Lake & Adventure',
            'slug' => 'pokhara-lake-adventure',
            'short_description' => 'A lakeside getaway with sunrise views.',
            'description' => 'Boat on Phewa Lake and visit Sarangkot.',
            'duration' => '5 days',
            'min_group_size' => 1,
            'max_group_size' => 10,
            'price' => 32000,
            'is_published' => true,
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => now()->addDays(30)->toDateString(),
            'capacity' => 10,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('NpAdventure')
            ->assertSee(asset('npadventure-mark.svg'))
            ->assertSee('aria-label="Mobile navigation"', false)
            ->assertSee(route('destination'))
            ->assertSee(route('tour'))
            ->assertSee(route('hotels'))
            ->assertSee('Pokhara')
            ->assertSee(route('destination.show', $destination->slug));

        $this->get(route('destination'))
            ->assertOk()
            ->assertSee('href="'.route('destination.show', $destination->slug).'" aria-label="View Pokhara destination details"', false);

        $this->get(route('destination.show', $destination->slug))
            ->assertOk()
            ->assertSee('Phewa Lake')
            ->assertSee('World Peace Pagoda')
            ->assertSee('Popular tours in Pokhara')
            ->assertSee('Pokhara Lake & Adventure')
            ->assertSee('Book this tour')
            ->assertSee(route('tour.show', $tour->slug));

        $this->get(route('tour'))
            ->assertOk()
            ->assertSee('href="'.route('tour.show', $tour->slug).'" aria-label="View Pokhara Lake &amp; Adventure tour details"', false);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('Book now')
            ->assertSee(route('booking'));

        $this->get(route('booking', [
            'tour_id' => $tour->getKey(),
            'tour_date_id' => $tourDate->id,
            'adults' => 1,
            'children' => 0,
        ]))
            ->assertOk()
            ->assertSee('Complete Your Booking')
            ->assertSee('Pokhara Lake & Adventure');
    }

    public function test_inactive_destination_is_not_publicly_accessible(): void
    {
        $destination = Destination::create([
            'name' => 'Hidden Place',
            'slug' => 'hidden-place',
            'country' => 'Nepal',
            'is_active' => false,
        ]);

        $this->get(route('destination.show', $destination->slug))->assertNotFound();
    }

    public function test_non_nepal_destinations_are_not_publicly_listed_or_accessible(): void
    {
        $destination = Destination::create([
        'name' => 'Bali',
        'slug' => 'bali',
        'country' => 'Indonesia',
        'is_active' => true,
        'is_featured' => true,
        ]);

        $this->get(route('home'))->assertOk()->assertDontSee('Bali');
        $this->get(route('destination'))->assertOk()->assertDontSee('Bali');
        $this->get(route('destination.show', $destination->slug))->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\Setting;
use App\Models\Tour;
use Database\Seeders\ProductionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_inventory_has_bookable_tour_departures_flights_and_activities(): void
    {
        $this->seed(ProductionDemoSeeder::class);

        $publishedTours = Tour::where('is_published', true)->with('dates')->get();

        $this->assertNotEmpty($publishedTours);
        foreach ($publishedTours as $tour) {
            $this->assertTrue(
                $tour->dates->contains(fn ($date) => $date->is_available && $date->departure_date->isFuture()),
                "The demo tour [{$tour->slug}] needs a future departure."
            );
        }

        $this->assertTrue(Flight::where('flight_number', 'RA101')->where('is_active', true)->exists());
        $this->assertDatabaseCount('activities', 3);
    }

    public function test_demo_catalog_prices_stay_affordable_when_seeded_again(): void
    {
        $this->seed(ProductionDemoSeeder::class);

        $this->assertLessThanOrEqual(2000, Tour::query()->max('price'));
        $this->assertLessThanOrEqual(1000, Tour::query()->max('child_price'));
        $this->assertLessThanOrEqual(2000, Hotel::query()->max('price_per_night'));
        $this->assertLessThanOrEqual(2000, HotelRoom::query()->max('price_per_night'));
        $this->assertLessThanOrEqual(2000, Activity::query()->max('price'));
        $this->assertGreaterThanOrEqual(500, Activity::query()->min('price'));
        $this->assertLessThanOrEqual(2000, Flight::query()->max('price'));

        $hotel = Hotel::query()->where('slug', 'himalayan-courtyard')->firstOrFail();
        $room = $hotel->rooms()->where('name', 'Standard Room')->firstOrFail();
        $room->update(['price_per_night' => 9000]);

        $this->seed(ProductionDemoSeeder::class);

        $this->assertEquals(
            (float) $hotel->fresh()->price_per_night,
            (float) $room->fresh()->price_per_night,
        );
    }

    public function test_demo_places_use_distinct_photos_and_real_nepal_property_names(): void
    {
        $this->seed(ProductionDemoSeeder::class);

        $destinations = \App\Models\Destination::query()
            ->whereIn('slug', ['butwal', 'kathmandu', 'pokhara', 'annapurna'])
            ->pluck('hero_image', 'slug');

        $this->assertSame('images/nepal/butwal.jpg', $destinations['butwal']);
        $this->assertSame('images/nepal/phewa-lake.jpg', $destinations['pokhara']);
        $this->assertNotSame($destinations['butwal'], $destinations['pokhara']);
        $this->assertDatabaseHas('tours', [
            'title' => 'Butwal City Tour',
            'slug' => 'butwal-city-tour',
            'destination_id' => \App\Models\Destination::where('slug', 'butwal')->value('id'),
            'featured_image' => 'images/nepal/butwal.jpg',
            'price' => 1000,
        ]);
        $this->assertDatabaseHas('hotels', [
            'name' => 'Hotel Yak & Yeti',
            'featured_image' => 'images/nepal/hotel-yak-yeti.jpg',
        ]);
        $this->assertDatabaseHas('hotels', [
            'name' => 'Hotel Barahi',
            'featured_image' => 'images/nepal/hotel-barahi.jpg',
        ]);
        $this->assertDatabaseHas('hotels', [
            'name' => 'The Old Inn, Bandipur',
            'featured_image' => 'images/nepal/old-inn-bandipur.jpg',
        ]);
        $this->assertDatabaseHas('hotels', [
            'name' => 'Hotel Yak & Yeti',
            'address' => 'Durbar Marg, Kathmandu, Nepal',
        ]);
        $this->assertDatabaseHas('hotels', [
            'name' => 'Hotel Barahi',
            'address' => 'Lakeside Road, Pokhara, Nepal',
        ]);
        $this->assertDatabaseHas('tours', [
            'slug' => 'pokhara-sunrise-tour',
            'featured_image' => 'images/nepal/sarangkot-sunrise.jpg',
        ]);
        $this->assertDatabaseHas('activities', [
            'slug' => 'paragliding',
            'image' => 'images/nepal/paragliding-pokhara.jpg',
        ]);
    }

    public function test_legacy_misnamed_pokhara_tour_is_corrected_to_butwal(): void
    {
        $this->seed(ProductionDemoSeeder::class);
        $butwal = \App\Models\Destination::where('slug', 'butwal')->firstOrFail();
        $seededButwalTour = Tour::query()->where('slug', 'butwal-city-tour')->firstOrFail();
        $seededButwalTour->dates()->delete();
        $seededButwalTour->delete();
        $tour = Tour::query()->where('slug', 'pokhara-lake-adventure')->firstOrFail();
        $tour->update([
            'title' => 'pokhara',
            'slug' => 'pokhara',
            'destination_id' => $butwal->id,
            'featured_image' => 'https://images.unsplash.com/example',
        ]);

        $migration = require database_path('migrations/2026_10_28_000002_curate_remaining_nepal_catalog.php');
        $migration->up();

        $this->assertDatabaseHas('tours', [
            'title' => 'Butwal City Tour',
            'slug' => 'butwal-city-tour',
            'destination_id' => $butwal->id,
            'featured_image' => 'images/nepal/butwal.jpg',
            'price' => 1000,
        ]);
        $this->assertDatabaseMissing('tours', ['slug' => 'pokhara']);
    }

    public function test_photo_credits_page_lists_image_authors_and_licenses(): void
    {
        $this->get(route('photo-credits'))
            ->assertOk()
            ->assertSee('Nepal photo credits')
            ->assertSee('Phewa Lake, Pokhara')
            ->assertSee('Jmhullot')
            ->assertSee('CC BY-SA 3.0')
            ->assertSee('Hotel Barahi, Pokhara');
    }

    public function test_existing_catalog_prices_are_lowered_by_the_price_migration(): void
    {
        $this->seed(ProductionDemoSeeder::class);
        Tour::query()->update(['price' => 24000, 'child_price' => 18000]);
        Hotel::query()->update(['price_per_night' => 12400]);
        HotelRoom::query()->update(['price_per_night' => 12400]);
        Activity::query()->update(['price' => 4500]);
        Flight::query()->update(['price' => 4500]);
        Setting::setValue('site_name', 'NpAdvanture');

        $migration = require database_path('migrations/2026_10_28_000000_lower_catalog_prices_to_affordable_range.php');
        $migration->up();

        $this->assertLessThanOrEqual(2000, Tour::query()->max('price'));
        $this->assertLessThanOrEqual(1000, Tour::query()->max('child_price'));
        $this->assertLessThanOrEqual(2000, Hotel::query()->max('price_per_night'));
        $this->assertLessThanOrEqual(2000, HotelRoom::query()->max('price_per_night'));
        $this->assertLessThanOrEqual(2000, Activity::query()->max('price'));
        $this->assertLessThanOrEqual(2000, Flight::query()->max('price'));
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'NpAdventure']);
    }
}

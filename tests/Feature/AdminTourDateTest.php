<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTourDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_manage_tour_departures(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $tour = $this->tour();

        $this->actingAs($admin)
            ->post(route('admin.tours.dates.store', $tour), [
                'departure_date' => '2026-11-15',
                'return_date' => '2026-11-22',
                'capacity' => 12,
            ])
            ->assertRedirect();

        $date = TourDate::where('tour_id', $tour->id)->firstOrFail();
        $this->assertDatabaseHas('tour_dates', [
            'id' => $date->id,
            'is_available' => true,
            'capacity' => 12,
        ]);
        $this->actingAs($admin)
            ->get(route('admin.tours.dates.index', $tour))
            ->assertOk()
            ->assertSee('Departure dates')
            ->assertSee('12 seats left');

        $this->actingAs($admin)
            ->patch(route('admin.tours.dates.update', [$tour, $date]), [
                'return_date' => '2026-11-23',
                'capacity' => 15,
                'is_available' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('2026-11-23', $date->fresh()->return_date->toDateString());
        $this->assertSame(15, $date->fresh()->capacity);
    }

    public function test_admin_cannot_reduce_capacity_below_reserved_seats(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $tour = $this->tour();
        $date = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => '2026-11-15',
            'capacity' => 5,
        ]);
        Booking::create([
            'tour_id' => $tour->id,
            'tour_date_id' => $date->id,
            'booking_reference' => 'NPADMINSEAT1',
            'adults' => 3,
            'children' => 0,
            'total_amount' => 300,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.tours.dates.update', [$tour, $date]), [
                'return_date' => null,
                'capacity' => 2,
                'is_available' => '1',
            ])
            ->assertSessionHasErrors('capacity');

        $this->assertDatabaseHas('tour_dates', [
            'id' => $date->id,
            'capacity' => 5,
        ]);
    }

    private function tour(): Tour
    {
        $destination = Destination::create([
            'name' => 'Kathmandu',
            'slug' => 'admin-dates-kathmandu',
            'country' => 'Nepal',
        ]);

        return Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Admin Test Tour',
            'slug' => 'admin-test-tour',
            'price' => 100,
            'is_published' => true,
        ]);
    }
}

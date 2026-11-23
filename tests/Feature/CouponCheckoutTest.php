<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_coupon_reduces_total_and_records_usage(): void
    {
        $user = User::factory()->create();
        $destination = Destination::create([
            'name' => 'Kathmandu',
            'country' => 'Nepal',
            'slug' => 'kathmandu',
            'is_featured' => true,
            'is_active' => true,
        ]);

        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Everest Base Camp',
            'slug' => 'everest-base-camp',
            'duration' => '10 days',
            'min_group_size' => 1,
            'max_group_size' => 12,
            'price' => 1000,
            'child_price' => 700,
            'is_best_seller' => false,
            'is_published' => true,
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'departure_date' => today()->addDays(30)->toDateString(),
            'return_date' => today()->addDays(36)->toDateString(),
            'capacity' => 12,
        ]);

        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'description' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'minimum_amount' => 500,
            'usage_limit' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('booking.store'), [
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'adults' => 1,
                'children' => 0,
                'first_name' => 'Amit',
                'last_name' => 'Sharma',
                'email' => 'amit@example.com',
                'phone' => '9800000000',
                'coupon_code' => 'SAVE10',
            ])
            ->assertRedirect();

        $booking = Booking::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('100.00', number_format($booking->discount, 2, '.', ''));
        $this->assertSame('945.00', number_format($booking->total_amount, 2, '.', ''));
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'used_count' => 1,
        ]);
    }
}

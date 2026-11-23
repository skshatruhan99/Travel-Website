<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_detail_and_checkout_offer_room_booking(): void
    {
        [$hotel, $room] = $this->hotelAndRoom();
        $checkIn = today()->addDays(10)->toDateString();
        $checkOut = today()->addDays(12)->toDateString();

        $this->get(route('hotel.show', $hotel->slug))
            ->assertOk()
            ->assertSee('Room type')
            ->assertSee('Standard Room')
            ->assertSee('Check availability');

        $this->from(route('hotel.show', $hotel->slug))->get(route('hotel.booking', [
            'hotel' => $hotel->slug,
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
        ]))
            ->assertOk()
            ->assertSee('Complete Your Stay Booking')
            ->assertSee('NPR 210.00');
    }

    public function test_hotels_for_destinations_outside_nepal_are_hidden(): void
    {
        $destination = Destination::create([
            'name' => 'Bali',
            'slug' => 'bali',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'name' => 'Bali Beach Hotel',
            'slug' => 'bali-beach-hotel',
            'price_per_night' => 100,
            'is_active' => true,
        ]);

        $this->get(route('hotels'))->assertOk()->assertDontSee('Bali Beach Hotel');
        $this->get(route('hotel.show', $hotel->slug))->assertNotFound();
    }

    public function test_hotel_booking_persists_stay_details_and_redirects_to_payment(): void
    {
        [$hotel, $room] = $this->hotelAndRoom();
        $user = User::factory()->create();
        $checkIn = today()->addDays(10)->toDateString();
        $checkOut = today()->addDays(12)->toDateString();

        $response = $this->actingAs($user)->post(route('hotel.booking.store', $hotel->slug), [
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
            'first_name' => 'Hotel',
            'last_name' => 'Guest',
            'email' => 'guest@example.test',
            'phone' => '+977123456789',
        ]);

        $booking = Booking::firstOrFail();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => $user->id,
            'tour_id' => null,
            'travel_start_date' => $checkIn,
            'travel_end_date' => $checkOut,
            'adults' => 2,
            'subtotal' => 200,
            'tax' => 10,
            'total_amount' => 210,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'item_type' => 'hotel_room',
            'item_id' => $room->id,
            'quantity' => 1,
            'total_price' => 200,
        ]);
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));
    }

    public function test_overlapping_stays_cannot_book_more_rooms_than_available(): void
    {
        [$hotel, $room] = $this->hotelAndRoom(['rooms_available' => 1]);
        $existing = Booking::create([
            'booking_reference' => 'NPHOTEL001',
            'travel_start_date' => today()->addDays(10)->toDateString(),
            'travel_end_date' => today()->addDays(12)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 200,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        BookingItem::create([
            'booking_id' => $existing->id,
            'item_type' => 'hotel_room',
            'item_id' => $room->id,
            'name' => $hotel->name.' — '.$room->name,
            'quantity' => 1,
            'unit_price' => 200,
            'total_price' => 200,
        ]);

        $this->from(route('hotel.show', $hotel->slug))->get(route('hotel.booking', [
            'hotel' => $hotel->slug,
            'room_id' => $room->id,
            'check_in' => today()->addDays(11)->toDateString(),
            'check_out' => today()->addDays(13)->toDateString(),
            'adults' => 2,
            'rooms' => 1,
        ]))->assertRedirect(route('hotel.show', $hotel->slug))
            ->assertSessionHasErrors('rooms');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_room_capacity_is_validated_before_checkout(): void
    {
        [$hotel, $room] = $this->hotelAndRoom(['max_guests' => 2]);

        $this->from(route('hotel.show', $hotel->slug))->get(route('hotel.booking', [
            'hotel' => $hotel->slug,
            'room_id' => $room->id,
            'check_in' => today()->addDays(10)->toDateString(),
            'check_out' => today()->addDays(12)->toDateString(),
            'adults' => 3,
            'rooms' => 1,
        ]))->assertRedirect(route('hotel.show', $hotel->slug))
            ->assertSessionHasErrors('adults');
    }

    public function test_admin_can_manage_hotel_room_inventory(): void
    {
        [$hotel] = $this->hotelAndRoom();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.hotels.rooms.index', $hotel))
            ->assertOk()
            ->assertSee('Room types')
            ->assertSee('Standard Room');

        $this->post(route('admin.hotels.rooms.store', $hotel), [
            'name' => 'Family Suite',
            'price_per_night' => 180,
            'max_guests' => 4,
            'rooms_available' => 3,
            'includes_breakfast' => 1,
        ])->assertRedirect();

        $room = HotelRoom::where('hotel_id', $hotel->id)->where('name', 'Family Suite')->firstOrFail();
        $this->assertDatabaseHas('hotel_rooms', [
            'id' => $room->id,
            'rooms_available' => 3,
            'max_guests' => 4,
            'includes_breakfast' => true,
        ]);

        $this->put(route('admin.hotels.rooms.update', [$hotel, $room]), [
            'name' => 'Family Suite',
            'price_per_night' => 200,
            'max_guests' => 4,
            'rooms_available' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('hotel_rooms', [
            'id' => $room->id,
            'price_per_night' => 200,
            'rooms_available' => 2,
            'is_active' => false,
        ]);
    }

    public function test_hotel_booking_is_available_through_payment_and_invoice(): void
    {
        Storage::fake('local');
        [$hotel, $room] = $this->hotelAndRoom();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('hotel.booking.store', $hotel->slug), [
            'room_id' => $room->id,
            'check_in' => today()->addDays(10)->toDateString(),
            'check_out' => today()->addDays(12)->toDateString(),
            'adults' => 2,
            'rooms' => 1,
            'first_name' => 'Hotel',
            'email' => 'guest@example.test',
            'phone' => '+977123456789',
        ]);
        $booking = Booking::firstOrFail();
        $this->assertSame($room->id, $booking->items()->firstOrFail()->room?->id);
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));

        $this->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee($hotel->name)
            ->assertSee($room->name);

        $booking->update(['status' => 'confirmed']);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYHOTEL001',
            'gateway' => 'Test gateway',
            'method' => 'wallet',
            'amount' => $booking->total_amount,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee($hotel->name)
            ->assertSee($room->name)
            ->assertSee('Pokhara')
            ->assertSee('NPR 210.00');

        $this->get(route('account'))
            ->assertOk()
            ->assertSee($room->name);

        $this->post(route('booking.cancel', $booking), ['reason' => 'Travel plans changed'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('refunds', [
            'booking_id' => $booking->id,
            'status' => 'requested',
        ]);
    }

    private function hotelAndRoom(array $roomOverrides = []): array
    {
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'pokhara',
            'country' => 'Nepal',
            'description' => 'A mountain destination.',
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'name' => 'Lake Stay',
            'slug' => 'lake-stay',
            'price_per_night' => 100,
            'is_active' => true,
        ]);
        $room = $hotel->rooms()->create(array_merge([
            'name' => 'Standard Room',
            'price_per_night' => 100,
            'max_guests' => 2,
            'rooms_available' => 2,
            'is_active' => true,
        ], $roomOverrides));

        return [$hotel, $room];
    }
}

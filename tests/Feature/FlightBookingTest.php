<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingTraveler;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlightBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_results_allow_selecting_a_flight_and_entering_passenger_details(): void
    {
        $flight = $this->flight();

        $this->get(route('flights.search', [
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_date' => $flight->departure_at->toDateString(),
            'passengers' => 2,
        ]))
            ->assertOk()
            ->assertSee('Select flight')
            ->assertSee('5 seats left');

        $this->get(route('flight.booking', ['flight' => $flight, 'passengers' => 2]))
            ->assertOk()
            ->assertSee('Passenger 1')
            ->assertSee('Passenger 2')
            ->assertSee('NPR 210.00');
    }

    public function test_flight_booking_saves_each_passenger_and_redirects_to_payment(): void
    {
        $flight = $this->flight(['available_seats' => 4, 'price' => 200]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('flight.booking.store', $flight), [
            'passenger_count' => 2,
            'passengers' => [
                [
                    'first_name' => 'Asha',
                    'last_name' => 'Rai',
                    'email' => 'asha@example.test',
                    'phone' => '+977123456789',
                    'date_of_birth' => '1992-04-10',
                    'passport_number' => 'NP12345',
                    'nationality' => 'Nepali',
                ],
                [
                    'first_name' => 'Kiran',
                    'last_name' => 'Rai',
                    'date_of_birth' => '1990-02-20',
                ],
            ],
        ]);

        $booking = Booking::firstOrFail();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => $user->id,
            'tour_id' => null,
            'adults' => 2,
            'children' => 0,
            'subtotal' => 400,
            'tax' => 20,
            'total_amount' => 420,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'item_type' => 'flight',
            'item_id' => $flight->id,
            'quantity' => 2,
            'unit_price' => 200,
            'total_price' => 400,
        ]);
        $this->assertDatabaseHas('booking_travelers', [
            'booking_id' => $booking->id,
            'first_name' => 'Asha',
            'passport_number' => 'NP12345',
        ]);
        $this->assertDatabaseHas('booking_travelers', [
            'booking_id' => $booking->id,
            'first_name' => 'Kiran',
        ]);
        $this->assertSame(2, BookingTraveler::where('booking_id', $booking->id)->count());
        $response->assertRedirect(route('payment', ['reference' => $booking->booking_reference]));
    }

    public function test_flight_booking_enforces_remaining_seats_including_pending_holds(): void
    {
        $flight = $this->flight(['available_seats' => 2]);
        $existingBooking = Booking::create([
            'booking_reference' => 'NPFLIGHT01',
            'travel_start_date' => $flight->departure_at->toDateString(),
            'adults' => 2,
            'total_amount' => 400,
            'currency' => 'NPR',
            'status' => 'pending',
            'seat_hold_expires_at' => now()->addMinutes(10),
        ]);
        BookingItem::create([
            'booking_id' => $existingBooking->id,
            'item_type' => 'flight',
            'item_id' => $flight->id,
            'name' => 'Nepal Airlines RA101',
            'quantity' => 2,
            'unit_price' => 200,
            'total_price' => 400,
        ]);

        $this->from(route('flights'))->get(route('flight.booking', [
            'flight' => $flight,
            'passengers' => 1,
        ]))->assertSessionHasErrors('passengers');

        $this->get(route('flights.search', [
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_date' => $flight->departure_at->toDateString(),
            'passengers' => 1,
        ]))->assertOk()
            ->assertSee('No flights found');
    }

    public function test_flight_booking_is_included_in_payment_and_invoice(): void
    {
        Storage::fake('local');
        $flight = $this->flight();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('flight.booking.store', $flight), [
            'passenger_count' => 1,
            'passengers' => [[
                'first_name' => 'Asha',
                'last_name' => 'Rai',
                'email' => 'asha@example.test',
                'phone' => '+977123456789',
            ]],
        ]);
        $booking = Booking::firstOrFail();

        $this->get(route('payment', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Kathmandu to Pokhara');

        $booking->update(['status' => 'confirmed']);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYFLIGHT01',
            'gateway' => 'Test gateway',
            'method' => 'wallet',
            'amount' => $booking->total_amount,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $this->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Kathmandu to Pokhara')
            ->assertSee('1 passenger')
            ->assertSee('NPR 105.00');

        $this->get(route('account'))
            ->assertOk()
            ->assertSee('Nepal Airlines RA101');

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

    private function flight(array $overrides = []): Flight
    {
        return Flight::create(array_merge([
            'airline' => 'Nepal Airlines',
            'flight_number' => 'RA101',
            'departure_city' => 'Kathmandu',
            'arrival_city' => 'Pokhara',
            'departure_at' => now()->addDays(10)->setTime(8, 0),
            'arrival_at' => now()->addDays(10)->setTime(8, 45),
            'price' => 100,
            'available_seats' => 5,
            'class' => 'Economy',
            'is_active' => true,
        ], $overrides));
    }
}

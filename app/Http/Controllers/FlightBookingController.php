<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingTraveler;
use App\Models\Flight;
use App\Services\FlightSeatAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FlightBookingController extends Controller
{
    public function create(Request $request, Flight $flight, FlightSeatAvailability $availability)
    {
        abort_unless($flight->is_active && $flight->departure_at->isFuture(), 404);
        $data = $request->validate([
            'passengers' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $passengerCount = (int) $data['passengers'];
        $this->ensureAvailability($availability, $flight, $passengerCount);

        $subtotal = round($passengerCount * (float) $flight->price, 2);
        $tax = round($subtotal * 0.05, 2);

        return view('pages.flight-booking', [
            'flight' => $flight,
            'passengerCount' => $passengerCount,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
        ]);
    }

    public function store(Request $request, Flight $flight, FlightSeatAvailability $availability)
    {
        abort_unless($flight->is_active && $flight->departure_at->isFuture(), 404);
        $countData = $request->validate([
            'passenger_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
        $passengerCount = (int) $countData['passenger_count'];

        $data = $request->validate([
            'passengers' => ['required', 'array', 'size:'.$passengerCount],
            'passengers.*.first_name' => ['required', 'string', 'max:100'],
            'passengers.*.last_name' => ['required', 'string', 'max:100'],
            'passengers.*.email' => ['nullable', 'email', 'max:255'],
            'passengers.*.phone' => ['nullable', 'string', 'max:30'],
            'passengers.*.date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'passengers.*.passport_number' => ['nullable', 'string', 'max:50'],
            'passengers.*.nationality' => ['nullable', 'string', 'max:100'],
            'special_request' => ['nullable', 'string', 'max:2000'],
        ]);
        $request->validate([
            'passengers.0.email' => ['required', 'email', 'max:255'],
            'passengers.0.phone' => ['required', 'string', 'max:30'],
        ]);

        $booking = DB::transaction(function () use (
            $request, $flight, $passengerCount, $data, $availability
        ) {
            $lockedFlight = Flight::whereKey($flight->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();
            abort_if(! $lockedFlight->departure_at->isFuture(), 404);
            $this->ensureAvailability($availability, $lockedFlight, $passengerCount);

            $subtotal = round($passengerCount * (float) $lockedFlight->price, 2);
            $tax = round($subtotal * 0.05, 2);
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'booking_reference' => 'NP'.strtoupper(Str::random(8)),
                'travel_start_date' => $lockedFlight->departure_at->toDateString(),
                'adults' => $passengerCount,
                'children' => 0,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => round($subtotal + $tax, 2),
                'currency' => 'NPR',
                'status' => 'pending',
                'seat_hold_expires_at' => now()->addMinutes(20),
                'special_request' => $data['special_request'] ?? null,
            ]);

            BookingItem::create([
                'booking_id' => $booking->id,
                'item_type' => 'flight',
                'item_id' => $lockedFlight->id,
                'name' => $this->flightDescription($lockedFlight),
                'quantity' => $passengerCount,
                'unit_price' => $lockedFlight->price,
                'total_price' => $subtotal,
            ]);

            foreach ($data['passengers'] as $passenger) {
                BookingTraveler::create([
                    'booking_id' => $booking->id,
                    'first_name' => $passenger['first_name'],
                    'last_name' => $passenger['last_name'],
                    'email' => $passenger['email'] ?? null,
                    'phone' => $passenger['phone'] ?? null,
                    'date_of_birth' => $passenger['date_of_birth'] ?? null,
                    'passport_number' => $passenger['passport_number'] ?? null,
                    'nationality' => $passenger['nationality'] ?? null,
                ]);
            }

            return $booking;
        });

        return redirect()->route('payment', ['reference' => $booking->booking_reference]);
    }

    private function ensureAvailability(
        FlightSeatAvailability $availability,
        Flight $flight,
        int $passengerCount
    ): void {
        $remaining = $availability->availableSeats($flight);

        if ($passengerCount > $remaining) {
            throw ValidationException::withMessages([
                'passengers' => 'Only '.$remaining.' seat(s) remain on this flight.',
            ]);
        }
    }

    private function flightDescription(Flight $flight): string
    {
        return trim(($flight->airline ? $flight->airline.' ' : '')
            .($flight->flight_number ?: 'Flight')
            .' · '.$flight->departure_city.' to '.$flight->arrival_city
            .' · '.$flight->departure_at->format('M j, Y H:i')
            .' · '.$flight->class);
    }
}

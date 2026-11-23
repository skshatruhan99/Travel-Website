<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingTraveler;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Services\HotelRoomAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HotelBookingController extends Controller
{
    public function create(Request $request, Hotel $hotel, HotelRoomAvailability $availability)
    {
        abort_unless($hotel->is_active, 404);

        $data = $this->validatedBookingDetails($request);
        $room = $this->roomForHotel($hotel, (int) $data['room_id']);
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guests = (int) $data['adults'] + (int) ($data['children'] ?? 0);
        $rooms = (int) $data['rooms'];

        $this->ensureRoomCapacity($room, $guests, $rooms);
        $this->ensureAvailability($availability, $room, $checkIn, $checkOut, $rooms);

        $subtotal = round($nights * $rooms * (float) $room->price_per_night, 2);
        $tax = round($subtotal * 0.05, 2);

        return view('pages.hotel-booking', [
            'hotel' => $hotel,
            'room' => $room,
            'data' => $data,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'nights' => $nights,
            'adults' => (int) $data['adults'],
            'children' => (int) ($data['children'] ?? 0),
            'rooms' => $rooms,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }

    public function store(Request $request, Hotel $hotel, HotelRoomAvailability $availability)
    {
        abort_unless($hotel->is_active, 404);

        $data = $this->validatedBookingDetails($request, true);
        $room = $this->roomForHotel($hotel, (int) $data['room_id']);
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $rooms = (int) $data['rooms'];
        $this->ensureRoomCapacity($room, $adults + $children, $rooms);

        $booking = DB::transaction(function () use (
            $request, $data, $hotel, $room, $checkIn, $checkOut,
            $nights, $adults, $children, $rooms, $availability
        ) {
            $lockedRoom = HotelRoom::whereKey($room->id)
                ->where('hotel_id', $hotel->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureAvailability($availability, $lockedRoom, $checkIn, $checkOut, $rooms);

            $subtotal = round($nights * $rooms * (float) $lockedRoom->price_per_night, 2);
            $tax = round($subtotal * 0.05, 2);
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'booking_reference' => 'NP'.strtoupper(Str::random(8)),
                'travel_start_date' => $checkIn->toDateString(),
                'travel_end_date' => $checkOut->toDateString(),
                'adults' => $adults,
                'children' => $children,
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
                'item_type' => 'hotel_room',
                'item_id' => $lockedRoom->id,
                'name' => $hotel->name.' — '.$lockedRoom->name,
                'quantity' => $rooms,
                'unit_price' => (float) $lockedRoom->price_per_night * $nights,
                'total_price' => $subtotal,
            ]);

            BookingTraveler::create([
                'booking_id' => $booking->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            return $booking;
        });

        return redirect()->route('payment', ['reference' => $booking->booking_reference]);
    }

    private function validatedBookingDetails(Request $request, bool $includeGuestDetails = false): array
    {
        $rules = [
            'room_id' => ['required', 'integer', 'exists:hotel_rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rooms' => ['required', 'integer', 'min:1', 'max:10'],
        ];

        if ($includeGuestDetails) {
            $rules += [
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['nullable', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
                'special_request' => ['nullable', 'string', 'max:2000'],
            ];
        }

        return $request->validate($rules);
    }

    private function roomForHotel(Hotel $hotel, int $roomId): HotelRoom
    {
        return $hotel->rooms()
            ->where('is_active', true)
            ->whereKey($roomId)
            ->firstOrFail();
    }

    private function ensureRoomCapacity(HotelRoom $room, int $guests, int $rooms): void
    {
        if ($guests > $room->max_guests * $rooms) {
            throw ValidationException::withMessages([
                'adults' => 'The selected room count cannot accommodate all guests.',
            ]);
        }
    }

    private function ensureAvailability(
        HotelRoomAvailability $availability,
        HotelRoom $room,
        Carbon $checkIn,
        Carbon $checkOut,
        int $rooms
    ): void {
        $available = $availability->availableRooms($room, $checkIn, $checkOut);

        if ($rooms > $available) {
            throw ValidationException::withMessages([
                'rooms' => 'Only '.$available.' room(s) are available for those dates.',
            ]);
        }
    }
}

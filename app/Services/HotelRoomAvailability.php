<?php

namespace App\Services;

use App\Models\BookingItem;
use App\Models\HotelRoom;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HotelRoomAvailability
{
    public function availableRooms(HotelRoom $room, Carbon $checkIn, Carbon $checkOut): int
    {
        $reserved = BookingItem::query()
            ->where('item_type', 'hotel_room')
            ->where('item_id', $room->id)
            ->whereHas('booking', function ($query) use ($checkIn, $checkOut) {
                $query->where('travel_start_date', '<', $checkOut->toDateString())
                    ->where('travel_end_date', '>', $checkIn->toDateString())
                    ->where(function ($query) {
                        $query->whereIn('status', ['confirmed', 'completed'])
                            ->orWhere(function ($query) {
                                $query->where('status', 'pending')
                                    ->where('seat_hold_expires_at', '>', now());
                            });
                    });
            })
            ->sum('quantity');

        return max(0, $room->rooms_available - (int) $reserved);
    }
}

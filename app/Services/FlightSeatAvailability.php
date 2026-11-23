<?php

namespace App\Services;

use App\Models\BookingItem;
use App\Models\Flight;
use Illuminate\Support\Collection;

class FlightSeatAvailability
{
    public function availableSeats(Flight $flight): int
    {
        $reserved = BookingItem::query()
            ->where('item_type', 'flight')
            ->where('item_id', $flight->id)
            ->whereHas('booking', function ($query) use ($flight) {
                $query->whereDate('travel_start_date', $flight->departure_at->toDateString())
                    ->where(function ($query) {
                        $query->whereIn('status', ['confirmed', 'completed'])
                            ->orWhere(function ($query) {
                                $query->where('status', 'pending')
                                    ->where('seat_hold_expires_at', '>', now());
                            });
                    });
            })
            ->sum('quantity');

        return max(0, $flight->available_seats - (int) $reserved);
    }

    public function addAvailability(Collection $flights): Collection
    {
        $reservedByFlight = BookingItem::query()
            ->selectRaw('booking_items.item_id, SUM(booking_items.quantity) as reserved_seats')
            ->where('booking_items.item_type', 'flight')
            ->whereIn('booking_items.item_id', $flights->modelKeys())
            ->whereHas('booking', function ($query) {
                $query->where(function ($query) {
                    $query->whereIn('status', ['confirmed', 'completed'])
                        ->orWhere(function ($query) {
                            $query->where('status', 'pending')
                                ->where('seat_hold_expires_at', '>', now());
                        });
                });
            })
            ->groupBy('booking_items.item_id')
            ->pluck('reserved_seats', 'item_id');

        return $flights->each(function (Flight $flight) use ($reservedByFlight) {
            $flight->setAttribute(
                'remaining_seats',
                max(0, $flight->available_seats - (int) $reservedByFlight->get($flight->id, 0))
            );
        });
    }
}

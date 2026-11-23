<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\BookingItem;
use Illuminate\Support\Carbon;

class ActivityAvailability
{
    public function availablePlaces(Activity $activity, Carbon $date): int
    {
        $reserved = BookingItem::query()
            ->where('item_type', 'activity')
            ->where('item_id', $activity->id)
            ->whereHas('booking', function ($query) use ($date) {
                $query->whereDate('travel_start_date', $date->toDateString())
                    ->where(function ($query) {
                        $query->whereIn('status', ['confirmed', 'completed'])
                            ->orWhere(function ($query) {
                                $query->where('status', 'pending')
                                    ->where('seat_hold_expires_at', '>', now());
                            });
                    });
            })
            ->sum('quantity');

        return max(0, $activity->capacity - (int) $reserved);
    }
}

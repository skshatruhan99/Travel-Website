<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingTraveler;
use App\Services\ActivityAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivityBookingController extends Controller
{
    public function create(Request $request, Activity $activity, ActivityAvailability $availability)
    {
        abort_unless($activity->is_active, 404);
        $data = $request->validate([
            'activity_date' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
        $date = Carbon::parse($data['activity_date']);
        $participants = (int) $data['participants'];
        $this->ensureAvailability($availability, $activity, $date, $participants);

        $subtotal = round($participants * (float) $activity->price, 2);
        $tax = round($subtotal * 0.05, 2);

        return view('pages.activity-booking', [
            'activity' => $activity->load('destination'),
            'date' => $date,
            'participants' => $participants,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
        ]);
    }

    public function store(Request $request, Activity $activity, ActivityAvailability $availability)
    {
        abort_unless($activity->is_active, 404);
        $data = $request->validate([
            'activity_date' => ['required', 'date', 'after_or_equal:today'],
            'participants' => ['required', 'integer', 'min:1', 'max:20'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'special_request' => ['nullable', 'string', 'max:2000'],
        ]);
        $date = Carbon::parse($data['activity_date']);
        $participants = (int) $data['participants'];

        $booking = DB::transaction(function () use ($request, $activity, $availability, $data, $date, $participants) {
            $lockedActivity = Activity::whereKey($activity->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();
            $this->ensureAvailability($availability, $lockedActivity, $date, $participants);

            $subtotal = round($participants * (float) $lockedActivity->price, 2);
            $tax = round($subtotal * 0.05, 2);
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'booking_reference' => 'NP'.strtoupper(Str::random(8)),
                'travel_start_date' => $date->toDateString(),
                'adults' => $participants,
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
                'item_type' => 'activity',
                'item_id' => $lockedActivity->id,
                'name' => $lockedActivity->name,
                'quantity' => $participants,
                'unit_price' => $lockedActivity->price,
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

    private function ensureAvailability(
        ActivityAvailability $availability,
        Activity $activity,
        Carbon $date,
        int $participants
    ): void {
        $available = $availability->availablePlaces($activity, $date);

        if ($participants > $available) {
            throw ValidationException::withMessages([
                'participants' => 'Only '.$available.' places remain for this activity on that date.',
            ]);
        }
    }
}

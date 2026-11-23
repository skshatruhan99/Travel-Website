<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Booking::with('tour')->whereKey($data['booking_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->firstOrFail();

        abort_if(Review::where('booking_id', $booking->id)->exists(), 409, 'A review has already been submitted for this booking.');
        abort_unless($booking->tour, 422, 'The tour for this booking is no longer available.');

        Review::create([
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'reviewable_type' => Tour::class,
            'reviewable_id' => $booking->tour_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thanks for your review. It will appear after approval.');
    }
}
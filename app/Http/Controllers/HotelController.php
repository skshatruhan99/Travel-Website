<?php

namespace App\Http\Controllers;

use App\Models\Hotel;

class HotelController extends Controller
{
    public function index()
    {
        return view('pages.hotels', [
            'hotels' => Hotel::with('destination')
                ->where('is_active', true)
                ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))
                ->latest()
                ->paginate(12),
        ]);
    }

    public function show(Hotel $hotel)
    {
        abort_unless($hotel->is_active && $hotel->destination()->where('country', 'Nepal')->exists(), 404);

        $hotel->load(['destination', 'rooms' => fn ($query) => $query->where('is_active', true)->orderBy('price_per_night')]);

        return view('pages.hotel-detail', compact('hotel'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tour;

class TourController extends Controller
{
    public function index()
    {
        return view('pages.tour', [
            'tours' => Tour::with('destination')
                ->where('is_published', true)
                ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))
                ->latest()
                ->paginate(12),
        ]);
    }

    public function show(Tour $tour)
    {
        abort_unless($tour->is_published && $tour->destination()->where('country', 'Nepal')->exists(), 404);

        $tour->load([
            'reviews' => fn ($query) => $query->where('status', 'approved')->with('user'),
        ]);

        return view('pages.tour-detail', compact('tour'));
    }
}

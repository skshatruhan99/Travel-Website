<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::query()
            ->where('is_active', true)
            ->where('country', 'Nepal')
            ->withCount(['tours' => fn ($query) => $query->where('is_published', true)])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(12);

        return view('pages.destinations', compact('destinations'));
    }

    public function show(Destination $destination)
    {
        abort_unless($destination->is_active && $destination->country === 'Nepal', 404);

        $tours = $destination->tours()
            ->where('is_published', true)
            ->orderByDesc('is_best_seller')
            ->orderBy('price')
            ->paginate(8);

        return view('pages.destination-detail', compact('destination', 'tours'));
    }
}

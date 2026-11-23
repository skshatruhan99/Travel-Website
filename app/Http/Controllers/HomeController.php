<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $destinations = Schema::hasTable('destinations') && Schema::hasTable('tours')
            ? Destination::query()
                ->where('is_active', true)
                ->where('country', 'Nepal')
                ->where('is_featured', true)
                ->withCount(['tours' => fn ($query) => $query->where('is_published', true)])
                ->orderBy('name')
                ->take(6)
                ->get()
            : collect();

        return view('pages.home', compact('destinations'));
    }
}

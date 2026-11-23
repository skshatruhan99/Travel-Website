<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        return view('pages.activities', [
            'activities' => Activity::with('destination')
                ->where('is_active', true)
                ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Activity $activity)
    {
        abort_unless($activity->is_active && $activity->destination()->where('country', 'Nepal')->exists(), 404);

        return view('pages.activity-detail', [
            'activity' => $activity->load('destination'),
        ]);
    }
}

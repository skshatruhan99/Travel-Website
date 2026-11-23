<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TourDateController extends Controller
{
    public function index(Tour $tour): View
    {
        $dates = $tour->dates()->orderBy('departure_date')->get();

        foreach ($dates as $date) {
            $date->reserved_seats = $date->bookings()->reservingSeats()->sum(DB::raw('adults + children'));
            $date->available_seats = max(0, $date->capacity - $date->reserved_seats);
        }

        return view('admin.tours.dates', compact('tour', 'dates'));
    }

    public function store(Request $request, Tour $tour): RedirectResponse
    {
        $data = $request->validate([
            'departure_date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('tour_dates', 'departure_date')->where('tour_id', $tour->id),
            ],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $tour->dates()->create([
            ...$data,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return back()->with('success', 'Departure date added.');
    }

    public function update(Request $request, Tour $tour, TourDate $tourDate): RedirectResponse
    {
        abort_unless($tourDate->tour_id === $tour->id, 404);

        $data = $request->validate([
            'return_date' => ['nullable', 'date', 'after_or_equal:'.$tourDate->departure_date->toDateString()],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $tour, $tourDate, $data) {
            $lockedDate = TourDate::whereKey($tourDate->id)
                ->where('tour_id', $tour->id)
                ->lockForUpdate()
                ->firstOrFail();
            $reservedSeats = $lockedDate->bookings()->reservingSeats()->sum(DB::raw('adults + children'));
            if ($data['capacity'] < $reservedSeats) {
                throw ValidationException::withMessages([
                    'capacity' => 'Capacity cannot be lower than seats already reserved for this departure.',
                ]);
            }

            $lockedDate->update([
                'return_date' => $data['return_date'] ?? null,
                'capacity' => $data['capacity'],
                'is_available' => $request->boolean('is_available'),
            ]);
        });

        return back()->with('success', 'Departure date updated.');
    }
}

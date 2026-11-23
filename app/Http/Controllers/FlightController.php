<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Services\FlightSeatAvailability;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    public function search(Request $request, FlightSeatAvailability $availability)
    {
        $data = $request->validate([
            'departure_city' => ['required', 'string', 'max:100'],
            'arrival_city' => ['required', 'string', 'max:100'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'passengers' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $flights = Flight::where('is_active', true)
            ->where('departure_city', 'like', '%' . $data['departure_city'] . '%')
            ->where('arrival_city', 'like', '%' . $data['arrival_city'] . '%')
            ->whereDate('departure_at', $data['departure_date'])
            ->where('departure_at', '>', now())
            ->where('available_seats', '>=', $data['passengers'])
            ->orderBy('price')
            ->get();
        $availability->addAvailability($flights);
        $flights = $flights->filter(fn (Flight $flight) => $flight->remaining_seats >= (int) $data['passengers']);

        return view('pages.flight-results', compact('flights', 'data'));
    }
}

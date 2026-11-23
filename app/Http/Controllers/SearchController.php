<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Tour;
use App\Services\ActivityAvailability;
use App\Services\FlightSeatAvailability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    public function __invoke(
        Request $request,
        FlightSeatAvailability $flightAvailability,
        ActivityAvailability $activityAvailability
    ) {
        $data = $request->validate([
            'destination' => ['nullable', 'required_without:q', 'string', 'max:100'],
            'q' => ['nullable', 'required_without:destination', 'string', 'max:100'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'travelers' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
        $term = trim((string) ($data['destination'] ?? $data['q']));
        $terms = array_values(array_unique(preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: []));
        if ($terms === []) {
            throw ValidationException::withMessages([
                'destination' => 'Enter a destination or search keyword.',
            ]);
        }
        $date = Carbon::parse($data['check_in']);
        $travelers = (int) $data['travelers'];

        $destinations = $this->matching(Destination::query()->where('is_active', true)->where('country', 'Nepal'), $terms, [
            'name', 'country', 'location', 'short_description', 'description',
        ])->orderByDesc('is_featured')->orderBy('name')->limit(12)->get();

        $tours = $this->matching(Tour::query()
            ->with(['destination', 'dates' => fn ($query) => $query
                ->where('is_available', true)
                ->whereDate('departure_date', '>=', $date->toDateString())
                ->orderBy('departure_date')])
            ->where('is_published', true)
            ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal')), $terms, [
                'title', 'short_description', 'description',
            ], 'destination')->orderByDesc('is_best_seller')->orderBy('price')->limit(40)->get();

        $tourDateIds = $tours->flatMap(fn (Tour $tour) => $tour->dates->modelKeys());
        $reservedTourSeats = $tourDateIds->isEmpty()
            ? collect()
            : Booking::query()->reservingSeats()
                ->whereIn('tour_date_id', $tourDateIds)
                ->select('tour_date_id')
                ->selectRaw('SUM(adults + children) as reserved_seats')
                ->groupBy('tour_date_id')
                ->pluck('reserved_seats', 'tour_date_id');

        $tours = $tours->map(function (Tour $tour) use ($reservedTourSeats, $travelers) {
            $tour->available_departures = $tour->dates
                ->map(function ($departure) use ($reservedTourSeats) {
                    $departure->available_seats = max(
                        0,
                        $departure->capacity - (int) ($reservedTourSeats[$departure->id] ?? 0)
                    );

                    return $departure;
                })
                ->filter(fn ($departure) => $departure->available_seats > 0)
                ->values();

            return $tour;
        })->filter(function (Tour $tour) use ($travelers) {
            return $tour->available_departures->isNotEmpty()
                && $travelers >= $tour->min_group_size
                && (! $tour->max_group_size || $travelers <= $tour->max_group_size)
                && $tour->available_departures->contains(fn ($departure) => $departure->available_seats >= $travelers);
        })->values();

        $hotels = $this->matching(Hotel::query()
            ->with(['destination', 'rooms' => fn ($query) => $query->where('is_active', true)->orderBy('price_per_night')])
            ->where('is_active', true)
            ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal')), $terms, [
                'name', 'address', 'short_description', 'description',
            ], 'destination')
            ->whereHas('rooms', fn ($query) => $query->where('is_active', true))
            ->orderBy('price_per_night')
            ->limit(12)
            ->get();

        $activities = $this->matching(Activity::query()
            ->with('destination')
            ->where('is_active', true)
            ->whereHas('destination', fn ($query) => $query->where('country', 'Nepal')), $terms, [
                'name', 'description', 'category', 'difficulty',
            ], 'destination')
            ->orderByDesc('is_featured')
            ->orderBy('price')
            ->limit(12)
            ->get();
        $activities = $activities->filter(
            fn (Activity $activity) => $activityAvailability->availablePlaces($activity, $date) >= $travelers
        )->values();

        $flights = $this->matching(Flight::query()
            ->where('is_active', true)
            ->where('departure_at', '>', now())
            ->whereDate('departure_at', $date->toDateString()), $terms, [
                'departure_city', 'arrival_city', 'airline', 'flight_number', 'class',
            ])->orderBy('price')->limit(20)->get();
        $flightAvailability->addAvailability($flights);
        $flights = $flights->filter(fn (Flight $flight) => $flight->remaining_seats >= $travelers)->values();

        return view('pages.search-results', [
            'search' => [
                'term' => $term,
                'check_in' => $date->toDateString(),
                'travelers' => $travelers,
            ],
            'destinations' => $destinations,
            'tours' => $tours,
            'hotels' => $hotels,
            'activities' => $activities,
            'flights' => $flights,
        ]);
    }

    private function matching(
        Builder $query,
        array $terms,
        array $columns,
        ?string $destinationRelation = null
    ): Builder {
        return $query->where(function (Builder $query) use ($terms, $columns, $destinationRelation) {
            foreach ($terms as $term) {
                $query->where(function (Builder $query) use ($term, $columns, $destinationRelation) {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', '%'.$term.'%');
                    }

                    if ($destinationRelation) {
                        $query->orWhereHas($destinationRelation, function (Builder $query) use ($term) {
                            $query->where('name', 'like', '%'.$term.'%')
                                ->orWhere('country', 'like', '%'.$term.'%')
                                ->orWhere('location', 'like', '%'.$term.'%');
                        });
                    }
                });
            }
        });
    }
}

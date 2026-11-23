<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TripPlan;
use App\Models\TripPlanDay;
use App\Models\TripPlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripPlannerController extends Controller
{
    public function index(Request $request)
    {
        $destinations = Destination::where('is_active', true)->where('country', 'Nepal')->orderBy('name')->get();
        $plans = $request->user()
            ? $request->user()->tripPlans()->withCount('items')->latest()->get()
            : collect();

        return view('pages.planner', compact('destinations', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
        ]);
        $destination = Destination::where('is_active', true)
            ->where('country', 'Nepal')
            ->findOrFail($data['destination_id']);
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $dayCount = (int) $start->diffInDays($end) + 1;

        if ($dayCount > 31) {
            throw ValidationException::withMessages([
                'end_date' => 'A trip plan can include up to 31 days.',
            ]);
        }

        if ((int) $data['adults'] + (int) ($data['children'] ?? 0) > 20) {
            throw ValidationException::withMessages([
                'children' => 'A trip plan can include up to 20 travelers.',
            ]);
        }

        $plan = DB::transaction(function () use ($request, $data, $destination, $start, $dayCount) {
            $plan = TripPlan::create([
                'user_id' => $request->user()->id,
                'title' => $data['title'] ?: $destination->name.' trip',
                'destination' => $destination->name,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
                'budget' => $data['budget'] ?? null,
                'status' => 'saved',
            ]);

            for ($dayNumber = 1; $dayNumber <= $dayCount; $dayNumber++) {
                $plan->days()->create([
                    'day_number' => $dayNumber,
                    'date' => $start->copy()->addDays($dayNumber - 1)->toDateString(),
                    'location' => $destination->name,
                ]);
            }

            return $plan;
        });

        return redirect()->route('planner.show', $plan)->with('success', 'Trip plan saved. Add destinations, tours, hotels, and activities to each day.');
    }

    public function show(Request $request, TripPlan $tripPlan)
    {
        $this->authorizePlan($request, $tripPlan);
        $tripPlan->load([
            'days.items.destination',
            'days.items.tour.destination',
            'days.items.hotel.destination',
            'days.items.activity.destination',
        ]);

        return view('pages.trip-summary', [
            'plan' => $tripPlan,
            'choices' => $this->catalogChoices(),
        ]);
    }

    public function updateDay(Request $request, TripPlan $tripPlan, TripPlanDay $day)
    {
        $this->authorizePlan($request, $tripPlan);
        $day = $tripPlan->days()->whereKey($day->id)->firstOrFail();
        $data = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $day->update($data);

        return back()->with('success', 'Day itinerary updated.');
    }

    public function addItem(Request $request, TripPlan $tripPlan, TripPlanDay $day)
    {
        $this->authorizePlan($request, $tripPlan);
        $day = $tripPlan->days()->whereKey($day->id)->firstOrFail();
        $data = $request->validate([
            'catalog_choice' => ['required', 'regex:/^(destination|tour|hotel|activity):[1-9][0-9]*$/'],
        ]);
        [$type, $itemId] = explode(':', $data['catalog_choice'], 2);
        $item = $this->findCatalogItem($type, (int) $itemId);
        $estimatedPrice = $this->estimateItemPrice($tripPlan, $item, $type);

        DB::transaction(function () use ($tripPlan, $day, $item, $type, $estimatedPrice) {
            $day->items()->create([
                'item_type' => $type,
                'item_id' => $item->id,
                'name' => $item->name ?? $item->title,
                'price' => $estimatedPrice,
                'description' => $this->itemDescription($item, $type),
                'sort_order' => (int) $day->items()->max('sort_order') + 1,
            ]);
            $this->updateEstimatedTotal($tripPlan);
        });

        return back()->with('success', 'Item added to Day '.$day->day_number.'.');
    }

    public function removeItem(Request $request, TripPlan $tripPlan, TripPlanItem $item)
    {
        $this->authorizePlan($request, $tripPlan);
        $day = $tripPlan->days()->whereKey($item->trip_plan_day_id)->firstOrFail();

        DB::transaction(function () use ($tripPlan, $item, $day) {
            $item->delete();
            $remaining = $day->items()->orderBy('sort_order')->orderBy('id')->get();
            foreach ($remaining as $index => $remainingItem) {
                $remainingItem->update(['sort_order' => $index + 1]);
            }
            $this->updateEstimatedTotal($tripPlan);
        });

        return back()->with('success', 'Itinerary item removed.');
    }

    public function moveItem(Request $request, TripPlan $tripPlan, TripPlanItem $item)
    {
        $this->authorizePlan($request, $tripPlan);
        $data = $request->validate(['direction' => ['required', 'in:up,down']]);
        $day = $tripPlan->days()->whereKey($item->trip_plan_day_id)->firstOrFail();
        $item = $day->items()->whereKey($item->id)->firstOrFail();
        $neighborQuery = $day->items();

        $neighbor = $data['direction'] === 'up'
            ? $neighborQuery->where(function ($query) use ($item) {
                $query->where('sort_order', '<', $item->sort_order)
                    ->orWhere(function ($query) use ($item) {
                        $query->where('sort_order', $item->sort_order)->where('id', '<', $item->id);
                    });
            })->orderByDesc('sort_order')->orderByDesc('id')->first()
            : $neighborQuery->where(function ($query) use ($item) {
                $query->where('sort_order', '>', $item->sort_order)
                    ->orWhere(function ($query) use ($item) {
                        $query->where('sort_order', $item->sort_order)->where('id', '>', $item->id);
                    });
            })->orderBy('sort_order')->orderBy('id')->first();

        if (! $neighbor) {
            return back()->withErrors([
                'itinerary' => $data['direction'] === 'up'
                    ? 'This item is already first for the day.'
                    : 'This item is already last for the day.',
            ]);
        }

        DB::transaction(function () use ($item, $neighbor) {
            $itemOrder = $item->sort_order;
            $item->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $itemOrder]);
        });

        return back()->with('success', 'Itinerary order updated.');
    }

    public function destroy(Request $request, TripPlan $tripPlan)
    {
        $this->authorizePlan($request, $tripPlan);
        $tripPlan->delete();

        return redirect()->route('planner')->with('success', 'Trip plan deleted.');
    }

    private function authorizePlan(Request $request, TripPlan $tripPlan): void
    {
        abort_unless($tripPlan->user_id === $request->user()->id, 403);
    }

    private function catalogChoices(): array
    {
        return [
            'destination' => Destination::where('is_active', true)->where('country', 'Nepal')->orderBy('name')->get(['id', 'name']),
            'tour' => Tour::where('is_published', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->orderBy('title')->get(['id', 'title as name']),
            'hotel' => Hotel::where('is_active', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->orderBy('name')->get(['id', 'name']),
            'activity' => Activity::where('is_active', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function findCatalogItem(string $type, int $id): Destination|Tour|Hotel|Activity
    {
        return match ($type) {
            'destination' => Destination::where('is_active', true)->where('country', 'Nepal')->findOrFail($id),
            'tour' => Tour::where('is_published', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->findOrFail($id),
            'hotel' => Hotel::where('is_active', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->findOrFail($id),
            'activity' => Activity::where('is_active', true)->whereHas('destination', fn ($query) => $query->where('country', 'Nepal'))->findOrFail($id),
        };
    }

    private function estimateItemPrice(TripPlan $plan, Destination|Tour|Hotel|Activity $item, string $type): float
    {
        $travelers = $plan->adults + $plan->children;

        return match ($type) {
            'tour' => round(
                $plan->adults * (float) $item->price
                + $plan->children * (float) ($item->child_price ?? $item->price),
                2
            ),
            'hotel' => round(
                max(1, $plan->start_date->diffInDays($plan->end_date)) * (float) $item->price_per_night,
                2
            ),
            'activity' => round($travelers * (float) $item->price, 2),
            default => 0,
        };
    }

    private function itemDescription(Destination|Tour|Hotel|Activity $item, string $type): ?string
    {
        return match ($type) {
            'destination' => $item->short_description ?: $item->description,
            'tour' => $item->short_description ?: $item->description,
            'hotel' => $item->short_description ?: $item->description,
            'activity' => $item->description,
        };
    }

    private function updateEstimatedTotal(TripPlan $plan): void
    {
        $plan->update(['estimated_total' => $plan->items()->sum('price')]);
    }
}

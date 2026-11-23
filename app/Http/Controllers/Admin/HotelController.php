<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HotelController extends Controller
{
    public function index()
    {
        return view('admin.hotels.index', ['hotels' => Hotel::with('destination')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.hotels.create', ['destinations' => Destination::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        foreach (['free_cancellation', 'breakfast_included', 'mountain_view', 'pool', 'spa', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $hotel = Hotel::create($data);
        $hotel->rooms()->create([
            'name' => 'Standard Room',
            'description' => 'Comfortable room for up to two guests.',
            'price_per_night' => $hotel->price_per_night,
            'max_guests' => 2,
            'rooms_available' => 1,
            'includes_breakfast' => $hotel->breakfast_included,
        ]);

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel created successfully.');
    }

    public function edit(Hotel $hotel)
    {
        return view('admin.hotels.edit', ['hotel' => $hotel, 'destinations' => Destination::orderBy('name')->get()]);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $hotel);
        foreach (['free_cancellation', 'breakfast_included', 'mountain_view', 'pool', 'spa', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $hotel->update($data);
        $standardRoom = $hotel->rooms()->where('name', 'Standard Room')->first();
        if ($standardRoom) {
            $standardRoom->update([
                'price_per_night' => $hotel->price_per_night,
                'includes_breakfast' => $hotel->breakfast_included,
            ]);
        }

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel updated successfully.');
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'address' => ['nullable', 'string', 'max:255'],
            'stars' => ['nullable', 'integer', 'between:1,5'],
            'price_per_night' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'featured_image' => ['nullable', 'url', 'max:2048'],
            'free_cancellation' => ['sometimes', 'boolean'],
            'breakfast_included' => ['sometimes', 'boolean'],
            'mountain_view' => ['sometimes', 'boolean'],
            'pool' => ['sometimes', 'boolean'],
            'spa' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?Hotel $hotel = null): string
    {
        $base = Str::slug($name) ?: 'hotel';
        $slug = $base;
        $suffix = 2;

        while (Hotel::where('slug', $slug)->when($hotel, fn ($query) => $query->whereKeyNot($hotel->id))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}

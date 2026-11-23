<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    public function index()
    {
        return view('admin.tours.index', [
            'tours' => Tour::with('destination')->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.tours.create', [
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_published'] = $request->boolean('is_published');

        Tour::create($data);

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully.');
    }

    public function edit(Tour $tour)
    {
        return view('admin.tours.edit', [
            'tour' => $tour,
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Tour $tour)
    {
        $data = $this->validatedData($request, $tour);
        $data['slug'] = $this->uniqueSlug($data['title'], $tour);
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_published'] = $request->boolean('is_published');
        $tour->update($data);

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    public function destroy(Tour $tour)
    {
        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    private function validatedData(Request $request, ?Tour $tour = null): array
    {
        return $request->validate([
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'duration' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:100'],
            'min_group_size' => ['required', 'integer', 'min:1', 'max:100'],
            'max_group_size' => ['nullable', 'integer', 'gte:min_group_size', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'child_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'transport' => ['nullable', 'string', 'max:255'],
            'max_altitude' => ['nullable', 'string', 'max:100'],
            'featured_image' => ['nullable', 'url', 'max:2048'],
            'is_best_seller' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $title, ?Tour $tour = null): string
    {
        $base = Str::slug($title) ?: 'tour';
        $slug = $base;
        $suffix = 2;

        while (Tour::where('slug', $slug)->when($tour, fn ($query) => $query->whereKeyNot($tour->id))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}

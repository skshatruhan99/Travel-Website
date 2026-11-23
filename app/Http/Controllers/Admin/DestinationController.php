<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::latest()->paginate(20);

        return view('admin.destinations.index', compact('destinations'));
    }

    public function create()
    {
        return view('admin.destinations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        Destination::create($validated);

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', 'Destination added successfully.');
    }

    public function edit(Destination $destination)
    {
        return view('admin.destinations.edit', compact('destination'));
    }

    public function update(Request $request, Destination $destination)
    {
        $validated = $this->validatedData($request, $destination);
        $validated['slug'] = $this->uniqueSlug($validated['name'], $destination);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');
        $destination->update($validated);

        return redirect()->route('admin.destinations.index')->with('success', 'Destination updated successfully.');
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();

        return redirect()->route('admin.destinations.index')->with('success', 'Destination deleted successfully.');
    }

    private function validatedData(Request $request, ?Destination $destination = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'hero_image' => ['nullable', 'url', 'max:2048'],
            'best_time' => ['nullable', 'string', 'max:255'],
            'weather' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:100'],
            'languages' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?Destination $destination = null): string
    {
        $base = Str::slug($name) ?: 'destination';
        $slug = $base;
        $suffix = 2;

        while (Destination::where('slug', $slug)->when($destination, fn ($query) => $query->whereKeyNot($destination->id))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
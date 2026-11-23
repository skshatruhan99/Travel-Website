<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelRoom;
use Illuminate\Http\Request;

class HotelRoomController extends Controller
{
    public function index(Hotel $hotel)
    {
        return view('admin.hotels.rooms', [
            'hotel' => $hotel,
            'rooms' => $hotel->rooms()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Hotel $hotel)
    {
        $hotel->rooms()->create($this->validatedData($request));

        return back()->with('success', 'Room type added.');
    }

    public function update(Request $request, Hotel $hotel, HotelRoom $room)
    {
        abort_unless($room->hotel_id === $hotel->id, 404);
        $room->update($this->validatedData($request, false));

        return back()->with('success', 'Room type updated.');
    }

    public function destroy(Hotel $hotel, HotelRoom $room)
    {
        abort_unless($room->hotel_id === $hotel->id, 404);
        $room->update(['is_active' => false]);

        return back()->with('success', 'Room type is no longer available for booking.');
    }

    private function validatedData(Request $request, bool $defaultActive = true): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_per_night' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'rooms_available' => ['required', 'integer', 'min:0', 'max:1000'],
            'includes_breakfast' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['includes_breakfast'] = $request->boolean('includes_breakfast');
        $data['is_active'] = $request->boolean('is_active', $defaultActive);

        return $data;
    }
}

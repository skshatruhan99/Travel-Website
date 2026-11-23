@extends('layouts.app')

@section('title', $hotel->name . ' - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ $hotel->name }}</h1>
        <p>{{ $hotel->destination?->name }}{{ $hotel->destination?->country ? ', ' . $hotel->destination->country : '' }} · Comfortable stays for your journey.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="detail-grid">
            <div class="detail-image">
                @if($photo = \App\Support\NepalPhotos::hotel($hotel->slug))<img src="{{ $photo }}" alt="{{ $hotel->name }}, {{ $hotel->destination?->name }}, Nepal">@endif
            </div>
            <div class="booking-card">
                @if($hotel->stars)<span class="rating">{{ str_repeat('★', $hotel->stars) }}</span>@endif
                <h2 style="margin-top:10px">{{ $hotel->name }}</h2>
                <p class="muted">{{ $hotel->address }}</p>
                <p style="margin-top:20px">{{ $hotel->description ?: $hotel->short_description }}</p>
                <div class="tour-meta" style="margin-top:14px">@foreach(['free_cancellation' => 'Free cancellation', 'breakfast_included' => 'Breakfast included', 'mountain_view' => 'Mountain view', 'pool' => 'Pool', 'spa' => 'Spa'] as $field => $label) @if($hotel->$field)<span>{{ $label }}</span>@endif @endforeach</div>
                <div class="invoice-total" style="margin-top:25px">
                    <span>From</span>
                    <strong class="price">NPR {{ number_format($hotel->rooms->min('price_per_night') ?? $hotel->price_per_night, 2) }}/night</strong>
                </div>
                @if($hotel->rooms->isNotEmpty())
                    <form action="{{ route('hotel.booking', $hotel->slug) }}" method="GET" style="margin-top:20px">
                        <div class="form-group">
                            <label for="room_id">Room type</label>
                            <select id="room_id" name="room_id" class="form-control" required>
                                <option value="">Select a room</option>
                                @foreach($hotel->rooms as $room)
                                    <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                        {{ $room->name }} · NPR {{ number_format($room->price_per_night, 2) }}/night · up to {{ $room->max_guests }} guests{{ $room->includes_breakfast ? ' · breakfast included' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-grid">
                            <div class="form-group"><label for="check_in">Check-in</label><input id="check_in" name="check_in" type="date" min="{{ today()->toDateString() }}" value="{{ old('check_in', request('check_in')) }}" class="form-control" required></div>
                            <div class="form-group"><label for="check_out">Check-out</label><input id="check_out" name="check_out" type="date" min="{{ today()->addDay()->toDateString() }}" value="{{ old('check_out', request('check_out')) }}" class="form-control" required></div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group"><label for="adults">Adults</label><input id="adults" name="adults" type="number" min="1" max="20" value="{{ old('adults', request('adults', 2)) }}" class="form-control" required></div>
                            <div class="form-group"><label for="children">Children</label><input id="children" name="children" type="number" min="0" max="20" value="{{ old('children', 0) }}" class="form-control"></div>
                        </div>
                        <div class="form-group"><label for="rooms">Rooms</label><input id="rooms" name="rooms" type="number" min="1" max="10" value="{{ old('rooms', 1) }}" class="form-control" required></div>
                        @if($errors->any())<p class="badge badge-red" role="alert">{{ $errors->first() }}</p>@endif
                        <button type="submit" class="btn btn-primary" style="width:100%">Check availability <i class="fa-solid fa-arrow-right"></i></button>
                    </form>
                @else
                    <p class="muted" style="margin-top:20px">Room options are not currently available for this stay.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@extends('admin.layout')

@section('title', 'Room types | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">STAY INVENTORY</span><h1>Room types</h1><p>Manage rooms and nightly rates for {{ $hotel->name }}.</p></div>
        <a class="admin-role-button" href="{{ route('admin.hotels.index') }}">Back to hotels</a>
    </div>
    @if(session('success'))<div class="admin-form-errors" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    @foreach($rooms as $room)
        <section class="admin-panel" style="margin-bottom:20px">
            <form action="{{ route('admin.hotels.rooms.update', [$hotel, $room]) }}" method="POST" class="admin-form">
                @csrf @method('PUT')
                <h2>{{ $room->name }} @unless($room->is_active)<span class="admin-status">Inactive</span>@endunless</h2>
                <div class="admin-form-grid">
                    <div class="form-group"><label for="room-name-{{ $room->id }}">Room type</label><input id="room-name-{{ $room->id }}" name="name" class="form-control" value="{{ $room->name }}" required></div>
                    <div class="form-group"><label for="room-price-{{ $room->id }}">Price per night (NPR)</label><input id="room-price-{{ $room->id }}" name="price_per_night" type="number" min="0" step="0.01" class="form-control" value="{{ $room->price_per_night }}" required></div>
                    <div class="form-group"><label for="room-capacity-{{ $room->id }}">Max guests per room</label><input id="room-capacity-{{ $room->id }}" name="max_guests" type="number" min="1" max="20" class="form-control" value="{{ $room->max_guests }}" required></div>
                    <div class="form-group"><label for="room-stock-{{ $room->id }}">Rooms available</label><input id="room-stock-{{ $room->id }}" name="rooms_available" type="number" min="0" max="1000" class="form-control" value="{{ $room->rooms_available }}" required></div>
                    <div class="form-group admin-form-wide"><label for="room-description-{{ $room->id }}">Description</label><textarea id="room-description-{{ $room->id }}" name="description" class="form-control" rows="2">{{ $room->description }}</textarea></div>
                </div>
                <div class="admin-form-toggles"><label><input type="checkbox" name="includes_breakfast" value="1" @checked($room->includes_breakfast)> Breakfast included</label><label><input type="checkbox" name="is_active" value="1" @checked($room->is_active)> Available for booking</label></div>
                <div class="admin-form-actions"><button class="admin-primary-action" type="submit">Save room type</button></div>
            </form>
            @if($room->is_active)
                <form action="{{ route('admin.hotels.rooms.destroy', [$hotel, $room]) }}" method="POST" class="admin-form-actions" style="margin-top:12px">
                    @csrf @method('DELETE')
                    <button class="admin-role-button" type="submit">Deactivate room type</button>
                </form>
            @endif
        </section>
    @endforeach
    <section class="admin-panel">
        <form action="{{ route('admin.hotels.rooms.store', $hotel) }}" method="POST" class="admin-form">
            @csrf
            <h2>Add room type</h2>
            <div class="admin-form-grid">
                <div class="form-group"><label for="new-room-name">Room type</label><input id="new-room-name" name="name" class="form-control" required></div>
                <div class="form-group"><label for="new-room-price">Price per night (NPR)</label><input id="new-room-price" name="price_per_night" type="number" min="0" step="0.01" class="form-control" required></div>
                <div class="form-group"><label for="new-room-capacity">Max guests per room</label><input id="new-room-capacity" name="max_guests" type="number" min="1" max="20" value="2" class="form-control" required></div>
                <div class="form-group"><label for="new-room-stock">Rooms available</label><input id="new-room-stock" name="rooms_available" type="number" min="0" max="1000" value="1" class="form-control" required></div>
                <div class="form-group admin-form-wide"><label for="new-room-description">Description</label><textarea id="new-room-description" name="description" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="admin-form-toggles"><label><input type="checkbox" name="includes_breakfast" value="1"> Breakfast included</label></div>
            <div class="admin-form-actions"><button class="admin-primary-action" type="submit">Add room type</button></div>
        </form>
    </section>
</section>
@endsection

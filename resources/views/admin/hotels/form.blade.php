<form action="{{ $hotel ? route('admin.hotels.update', $hotel) : route('admin.hotels.store') }}" method="POST" class="admin-form">
    @csrf
    @if($hotel) @method('PUT') @endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-form-grid">
        <div class="form-group"><label for="name">Hotel name</label><input id="name" name="name" class="form-control" value="{{ old('name', $hotel?->name) }}" required></div>
        <div class="form-group"><label for="destination_id">Destination</label><select id="destination_id" name="destination_id" class="form-control" required><option value="">Select destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(old('destination_id', $hotel?->destination_id) == $destination->id)>{{ $destination->name }}, {{ $destination->country }}</option>@endforeach</select></div>
        <div class="form-group"><label for="price_per_night">Price per night (NPR)</label><input id="price_per_night" name="price_per_night" type="number" min="0" step="0.01" class="form-control" value="{{ old('price_per_night', $hotel?->price_per_night) }}" required></div>
        <div class="form-group"><label for="stars">Stars</label><select id="stars" name="stars" class="form-control"><option value="">Not rated</option>@foreach(range(1, 5) as $star)<option value="{{ $star }}" @selected(old('stars', $hotel?->stars) == $star)>{{ $star }} star{{ $star > 1 ? 's' : '' }}</option>@endforeach</select></div>
        <div class="form-group admin-form-wide"><label for="address">Address</label><input id="address" name="address" class="form-control" value="{{ old('address', $hotel?->address) }}"></div>
        <div class="form-group admin-form-wide"><label for="short_description">Short description</label><textarea id="short_description" name="short_description" class="form-control" rows="2">{{ old('short_description', $hotel?->short_description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="description">Description</label><textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $hotel?->description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="featured_image">Image URL</label><input id="featured_image" name="featured_image" type="url" class="form-control" value="{{ old('featured_image', $hotel?->featured_image) }}"></div>
    </div>
    <div class="admin-form-toggles">
        @foreach(['free_cancellation' => 'Free cancellation', 'breakfast_included' => 'Breakfast included', 'mountain_view' => 'Mountain view', 'pool' => 'Pool', 'spa' => 'Spa', 'is_active' => 'Show on website'] as $field => $label)
            <label><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $hotel?->$field ?? ($field === 'is_active')))>{{ $label }}</label>
        @endforeach
    </div>
    <div class="admin-form-actions"><button class="admin-primary-action" type="submit">{{ $hotel ? 'Save changes' : 'Create hotel' }}</button><a class="admin-role-button" href="{{ route('admin.hotels.index') }}">Cancel</a></div>
</form>
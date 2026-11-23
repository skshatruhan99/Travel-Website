<form action="{{ $tour ? route('admin.tours.update', $tour) : route('admin.tours.store') }}" method="POST" class="admin-form">
    @csrf
    @if($tour) @method('PUT') @endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-form-grid">
        <div class="form-group"><label for="title">Tour name</label><input id="title" name="title" class="form-control" value="{{ old('title', $tour?->title) }}" required maxlength="255"></div>
        <div class="form-group"><label for="destination_id">Destination</label><select id="destination_id" name="destination_id" class="form-control" required><option value="">Select destination</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(old('destination_id', $tour?->destination_id) == $destination->id)>{{ $destination->name }}, {{ $destination->country }}</option>@endforeach</select></div>
        <div class="form-group"><label for="price">Adult price (NPR)</label><input id="price" name="price" type="number" min="0" step="0.01" class="form-control" value="{{ old('price', $tour?->price) }}" required></div>
        <div class="form-group"><label for="child_price">Child price (NPR)</label><input id="child_price" name="child_price" type="number" min="0" step="0.01" class="form-control" value="{{ old('child_price', $tour?->child_price) }}"></div>
        <div class="form-group"><label for="duration">Duration</label><input id="duration" name="duration" class="form-control" placeholder="7 days" value="{{ old('duration', $tour?->duration) }}" maxlength="100"></div>
        <div class="form-group"><label for="grade">Difficulty / grade</label><input id="grade" name="grade" class="form-control" value="{{ old('grade', $tour?->grade) }}" maxlength="100"></div>
        <div class="form-group"><label for="min_group_size">Minimum group size</label><input id="min_group_size" name="min_group_size" type="number" min="1" max="100" class="form-control" value="{{ old('min_group_size', $tour?->min_group_size ?? 1) }}" required></div>
        <div class="form-group"><label for="max_group_size">Maximum group size</label><input id="max_group_size" name="max_group_size" type="number" min="1" max="100" class="form-control" value="{{ old('max_group_size', $tour?->max_group_size) }}"></div>
        <div class="form-group"><label for="transport">Transport</label><input id="transport" name="transport" class="form-control" value="{{ old('transport', $tour?->transport) }}" maxlength="255"></div>
        <div class="form-group"><label for="max_altitude">Maximum altitude</label><input id="max_altitude" name="max_altitude" class="form-control" value="{{ old('max_altitude', $tour?->max_altitude) }}" maxlength="100"></div>
        <div class="form-group admin-form-wide"><label for="short_description">Short description</label><textarea id="short_description" name="short_description" class="form-control" rows="2" maxlength="1000">{{ old('short_description', $tour?->short_description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="description">Full description</label><textarea id="description" name="description" class="form-control" rows="5" maxlength="10000">{{ old('description', $tour?->description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="featured_image">Featured image URL</label><input id="featured_image" name="featured_image" type="url" class="form-control" value="{{ old('featured_image', $tour?->featured_image) }}" placeholder="https://example.com/tour.jpg"></div>
    </div>
    <div class="admin-form-toggles">
        <label><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $tour?->is_published ?? true))> Publish on website</label>
        <label><input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $tour?->is_best_seller ?? false))> Mark as best seller</label>
    </div>
    <div class="admin-form-actions"><button class="admin-primary-action" type="submit">{{ $tour ? 'Save changes' : 'Create tour' }}</button><a class="admin-role-button" href="{{ route('admin.tours.index') }}">Cancel</a></div>
</form>
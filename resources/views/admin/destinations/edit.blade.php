@extends('admin.layout')

@section('title', 'Edit Destination | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">PLACE DIRECTORY</span><h1>Edit destination</h1><p>Update destination information.</p></div></div>
<section class="admin-panel admin-form-panel"><form action="{{ route('admin.destinations.update', $destination) }}" method="POST" class="admin-form">
    @csrf
    @method('PUT')
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-form-grid">
        <div class="form-group"><label for="name">Name</label><input id="name" name="name" class="form-control" value="{{ old('name', $destination->name) }}" required></div>
        <div class="form-group"><label for="country">Country</label><input id="country" name="country" class="form-control" value="{{ old('country', $destination->country) }}" required></div>
        <div class="form-group"><label for="location">Region / location</label><input id="location" name="location" class="form-control" value="{{ old('location', $destination->location) }}"></div>
        <div class="form-group"><label for="best_time">Best time to visit</label><input id="best_time" name="best_time" class="form-control" value="{{ old('best_time', $destination->best_time) }}"></div>
        <div class="form-group admin-form-wide"><label for="short_description">Short description</label><textarea id="short_description" name="short_description" class="form-control" rows="2">{{ old('short_description', $destination->short_description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="description">Description</label><textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $destination->description) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="hero_image">Image URL</label><input id="hero_image" name="hero_image" type="url" class="form-control" value="{{ old('hero_image', $destination->hero_image) }}"></div>
    </div>
    <div class="admin-form-toggles"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $destination->is_active))> Active</label><label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured))> Featured</label></div>
    <div class="admin-form-actions"><button class="admin-primary-action" type="submit">Save changes</button><a class="admin-role-button" href="{{ route('admin.destinations.index') }}">Cancel</a></div>
</form></section></section>
@endsection
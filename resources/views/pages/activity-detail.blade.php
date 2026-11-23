@extends('layouts.app')

@section('title', $activity->name.' | Activities')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ $activity->name }}</h1>
        <p>{{ $activity->destination?->name }}{{ $activity->destination?->country ? ', '.$activity->destination->country : '' }} · {{ $activity->category ?: 'Experience' }}</p>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <div>
            @if($photo = \App\Support\NepalPhotos::activity($activity->slug))<div class="detail-image"><img src="{{ $photo }}" alt="{{ $activity->name }} in {{ $activity->destination?->name }}, Nepal"></div>@endif
            <h2 style="margin-top:20px">About this experience</h2>
            <p class="muted" style="margin-top:10px;white-space:pre-line">{{ $activity->description }}</p>
            <div class="tour-meta" style="margin-top:20px">
                @if($activity->duration)<span><i class="fa-regular fa-clock"></i> {{ $activity->duration }}</span>@endif
                @if($activity->difficulty)<span>{{ $activity->difficulty }}</span>@endif
                <span>{{ $activity->capacity }} places per day</span>
            </div>
        </div>
        <aside class="booking-card sticky-card">
            <span class="muted">Price per participant</span>
            <div class="price">NPR {{ number_format($activity->price, 2) }}</div>
            <form action="{{ route('activities.booking', $activity->slug) }}" method="GET" style="margin-top:20px">
                <div class="form-group">
                    <label for="activity_date">Activity date</label>
                    <input id="activity_date" name="activity_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('activity_date') }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="participants">Participants</label>
                    <input id="participants" name="participants" type="number" min="1" max="20" value="{{ old('participants', 1) }}" class="form-control" required>
                </div>
                @if($errors->any())<p class="badge badge-red" role="alert">{{ $errors->first() }}</p>@endif
                <button class="btn btn-primary" type="submit" style="width:100%">Book this activity <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <form action="{{ route('wishlist.store') }}" method="POST" style="margin-top:12px">
                @csrf
                <input type="hidden" name="item_type" value="activity">
                <input type="hidden" name="item_id" value="{{ $activity->id }}">
                <button class="btn btn-outline" type="submit" style="width:100%"><i class="fa-regular fa-heart"></i> Save activity</button>
            </form>
        </aside>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', $tour->title . ' | NpAdvanture')

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="{{ route('destination.show', $tour->destination?->slug ?? 'pokhara') }}">{{ $tour->destination?->name ?? 'Destination' }}</a> / <a href="{{ route('tour') }}">Tours</a> / {{ $tour->title }}</div><h1>{{ $tour->title }}</h1><p>{{ $tour->short_description }}</p></div></section>
<section class="section">
    <div class="container detail-grid">
        <div>
            @if($photo = \App\Support\NepalPhotos::tour($tour->slug, $tour->destination?->slug))<div class="detail-image"><img src="{{ $photo }}" alt="{{ $tour->title }} in {{ $tour->destination?->name }}, Nepal"></div>@endif
            <div style="margin-top:24px"><span class="muted">{{ $tour->destination?->name }}{{ $tour->destination?->country ? ', ' . $tour->destination->country : '' }}</span><h2 style="margin-top:10px">About this tour</h2><p class="muted" style="margin-top:10px;white-space:pre-line">{{ $tour->description ?: $tour->short_description }}</p></div>
            <div class="tour-meta" style="margin-top:20px"><span><i class="fa-regular fa-clock"></i> {{ $tour->duration ?: 'Duration varies' }}</span><span><i class="fa-solid fa-users"></i> {{ $tour->min_group_size }}{{ $tour->max_group_size ? '-' . $tour->max_group_size : '+' }} travelers</span>@if($tour->grade)<span>{{ $tour->grade }}</span>@endif</div>
        </div>
        <aside class="booking-card sticky-card">
            <span class="muted">Starting from</span><div class="price">NPR {{ number_format($tour->price, 2) }}</div>
            <form action="{{ route('booking') }}" method="GET" style="margin-top:20px">
                <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                <div class="form-group">
                    <label for="travel_start_date">Choose travel date</label>
                    <input id="travel_start_date"
                           name="travel_start_date"
                           type="date"
                           class="form-control"
                           min="{{ today()->toDateString() }}"
                           value="{{ old('travel_start_date') }}"
                           required>
                    <small class="muted">Choose your preferred date. Availability will be confirmed after booking.</small>
                    @error('travel_start_date')
                        <p class="badge badge-red" role="alert" style="margin-top:8px">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-grid">
                    <div class="form-group"><label for="adults">Adults</label><input id="adults" name="adults" type="number" class="form-control" min="1" max="{{ $tour->max_group_size ?: 20 }}" value="{{ max(1, $tour->min_group_size) }}" required></div>
                    <div class="form-group"><label for="children">Children</label><input id="children" name="children" type="number" class="form-control" min="0" max="20" value="0"></div>
                </div>
                <button class="btn btn-primary" type="submit" style="width:100%">Book now</button>
            </form>
        </aside>
    </div>
</section>
@if($tour->reviews->isNotEmpty())
<section class="section bg-light"><div class="container"><div class="section-heading"><div><h2>Traveler reviews</h2><p>Feedback from guests whose reviews have been approved.</p></div></div><div class="cards-grid">@foreach($tour->reviews as $review)<article class="booking-card"><strong>{{ str_repeat('★', $review->rating) }}</strong><p style="margin-top:10px">{{ $review->comment }}</p><small class="muted">{{ $review->user?->name ?? 'Traveler' }}</small></article>@endforeach</div></div></section>
@endif
@endsection
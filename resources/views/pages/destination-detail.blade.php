@extends('layouts.app')

@section('title', $destination->name . ', ' . $destination->country . ' | NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('destination') }}">Destinations</a> / {{ $destination->name }}</div>
        <h1>{{ $destination->name }}, {{ $destination->country }}</h1>
        <p>{{ $destination->short_description ?: $destination->description }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="detail-grid destination-overview">
            <div>
                @if($photo = \App\Support\NepalPhotos::destination($destination->slug))
                    <div class="detail-image"><img src="{{ $photo }}" alt="{{ $destination->name }}, Nepal"></div>
                @endif
                <div class="destination-copy">
                    <h2>Discover {{ $destination->name }}</h2>
                    <p class="muted">{{ $destination->description ?: $destination->short_description }}</p>
                </div>
            </div>
            <aside class="booking-card destination-facts">
                <span class="badge badge-red">Destination guide</span>
                <h2>{{ $destination->name }}</h2>
                @if($destination->location)<p class="muted">{{ $destination->location }}</p>@endif
                <div class="destination-fact-list">
                    @if($destination->best_time)<p><strong>Best time</strong><span>{{ $destination->best_time }}</span></p>@endif
                    @if($destination->weather)<p><strong>Weather</strong><span>{{ $destination->weather }}</span></p>@endif
                    @if($destination->languages)<p><strong>Languages</strong><span>{{ $destination->languages }}</span></p>@endif
                    @if($destination->currency)<p><strong>Currency</strong><span>{{ $destination->currency }}</span></p>@endif
                </div>
                <a href="#popular-tours" class="btn btn-primary destination-fact-action">Browse {{ $destination->name }} tours</a>
            </aside>
        </div>
    </div>
</section>

<section class="section bg-light" id="nearby-places">
    <div class="container">
        <div class="section-heading">
            <div><h2>Popular places to visit</h2><p>Nearby highlights around {{ $destination->name }}.</p></div>
        </div>
        <div class="cards-grid nearby-place-grid">
            @forelse($destination->nearby_places ?? [] as $place)
                @php($photo = \App\Support\NepalPhotos::place($place['name']))
                @if($photo)
                <article class="card nearby-place-card">
                    <div class="card-img"><img src="{{ $photo }}" alt="{{ $place['name'] }}, Nepal" loading="lazy"></div>
                    <div class="card-body">
                        <div class="nearby-place-meta"><span>{{ $destination->name }}</span><span><i class="fa-solid fa-location-dot"></i> {{ $place['distance'] }}</span></div>
                        <h3>{{ $place['name'] }}</h3>
                        <p class="muted">{{ $place['description'] }}</p>
                        <div class="card-footer"><span class="muted">Nearby place</span><a class="text-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($place['name'] . ', ' . $destination->name) }}" target="_blank" rel="noopener noreferrer">Open map <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
                    </div>
                </article>
                @endif
            @empty
                <p class="muted">Nearby places for this destination are being added.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="section" id="popular-tours">
    <div class="container">
        <div class="section-heading">
            <div><h2>Popular tours in {{ $destination->name }}</h2><p>Choose a trip and continue to its full itinerary and booking.</p></div>
        </div>
        <div class="cards-grid compact-mobile-grid destination-tour-grid">
            @forelse($tours as $tour)
                <article class="card tour-card">
                    @if($photo = \App\Support\NepalPhotos::tour($tour->slug, $destination->slug))
                        <div class="card-img"><img src="{{ $photo }}" alt="{{ $tour->title }} in {{ $destination->name }}, Nepal" loading="lazy"></div>
                    @endif
                    <div class="card-body">
                        <span class="muted">{{ $destination->name }}, {{ $destination->country }}</span>
                        <h3>{{ $tour->title }}</h3>
                        <p class="muted">{{ $tour->short_description }}</p>
                        <div class="tour-meta"><span><i class="fa-regular fa-clock"></i> {{ $tour->duration ?: 'Duration varies' }}</span>@if($tour->rating)<span><i class="fa-solid fa-star"></i> {{ number_format($tour->rating, 1) }}</span>@endif</div>
                        <div class="card-footer"><strong class="price">NPR {{ number_format($tour->price, 0) }}</strong><a class="text-link" href="{{ route('tour.show', $tour->slug) }}">View tour</a><a class="btn btn-primary" href="{{ route('tour.show', $tour->slug) }}">Book this tour</a></div>
                    </div>
                </article>
            @empty
                <p class="muted">Tours for {{ $destination->name }} are being prepared.</p>
            @endforelse
        </div>
        <div class="admin-pagination">{{ $tours->links() }}</div>
    </div>
</section>
@endsection
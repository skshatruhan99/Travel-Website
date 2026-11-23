@extends('layouts.app')

@section('title', 'Tours | NpAdvanture')

@section('content')

<section class="page-hero"><div class="container"><h1>Find your next adventure</h1><p>Explore tours created by our team and book your place.</p></div></section>

<section class="section">
    <div class="container">
        <div class="cards-grid compact-mobile-grid">
            @forelse($tours as $tour)
                <article class="card tour-card">
                    <div class="card-img"><a href="{{ route('tour.show', $tour->slug) }}" aria-label="View {{ $tour->title }} tour details">@if($photo = \App\Support\NepalPhotos::tour($tour->slug, $tour->destination?->slug))<img src="{{ $photo }}" alt="{{ $tour->title }} in {{ $tour->destination?->name }}, Nepal">@endif</a></div>
                    <div class="card-body">
                        <span class="muted">{{ $tour->destination?->name }}{{ $tour->destination?->country ? ', ' . $tour->destination->country : '' }}</span>
                        <h2>{{ $tour->title }}</h2>
                        <p class="muted">{{ $tour->short_description ?: $tour->duration }}</p>
                        <div class="card-footer"><strong class="price">NPR {{ number_format($tour->price, 2) }}</strong><a class="btn btn-primary" href="{{ route('tour.show', $tour->slug) }}">View tour</a></div>
                    </div>
                </article>
            @empty
                <div class="admin-empty" style="grid-column:1/-1">Tours are being prepared. Please check back soon.</div>
            @endforelse
        </div>
        <div class="admin-pagination">{{ $tours->links() }}</div>
    </div>
</section>

@endsection
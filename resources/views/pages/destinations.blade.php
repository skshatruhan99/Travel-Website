@extends('layouts.app')

@section('title', 'Destinations | NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Destinations</div>
        <h1>Explore Nepal</h1>
        <p>Choose a destination to discover nearby places and locally guided tours.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cards-grid destination-card-grid">
            @forelse($destinations as $destination)
                <article class="card destination-card">
                    <div class="card-img"><a href="{{ route('destination.show', $destination->slug) }}" aria-label="View {{ $destination->name }} destination details">@if($photo = \App\Support\NepalPhotos::destination($destination->slug))<img src="{{ $photo }}" alt="{{ $destination->name }}, Nepal">@endif</a></div>
                    <div class="card-body">
                        <span class="muted">{{ $destination->country }}</span>
                        <h2>{{ $destination->name }}</h2>
                        <p class="muted">{{ $destination->short_description }}</p>
                        <div class="card-footer">
                            <span class="rating">{{ $destination->tours_count }} {{ $destination->tours_count === 1 ? 'tour' : 'tours' }}</span>
                            <a href="{{ route('destination.show', $destination->slug) }}" class="text-link">Explore <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="muted">Destinations are being prepared. Please check back soon.</p>
            @endforelse
        </div>
        <div class="admin-pagination">{{ $destinations->links() }}</div>
    </div>
</section>
@endsection
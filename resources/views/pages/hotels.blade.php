@extends('layouts.app')

@section('title', 'Hotels & Stays')

@section('content')

<section class="page-hero">

    <div class="container">

        <h1>Hotels & Stays</h1>

        <p>
            Find comfortable places to stay during your journey.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="cards-grid compact-mobile-grid">

            @forelse($hotels as $hotel)
                <article class="card hotel-card">
                    <div class="card-img">
                        @if($photo = \App\Support\NepalPhotos::hotel($hotel->slug))<img src="{{ $photo }}" alt="{{ $hotel->name }}, {{ $hotel->destination?->name }}, Nepal">@endif
                    </div>
                    <div class="card-body">
                        @if($hotel->stars)<span class="rating">{{ str_repeat('★', $hotel->stars) }}</span>@endif
                        <h3>{{ $hotel->name }}</h3>
                        <p class="muted">{{ $hotel->destination?->name }}{{ $hotel->destination?->country ? ', ' . $hotel->destination->country : '' }}</p>
                        <div class="card-footer">
                            <span class="price">NPR {{ number_format($hotel->price_per_night, 2) }}/night</span>
                            <a href="{{ route('hotel.show', $hotel->slug) }}" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="muted" style="grid-column:1/-1">No hotel stays are currently listed.</p>
            @endforelse

        </div>

    </div>
    <div class="container admin-pagination">{{ $hotels->links() }}</div>

</section>

@endsection
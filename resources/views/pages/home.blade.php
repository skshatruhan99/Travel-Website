@extends('layouts.app')

@section('title', 'NpAdvanture - Explore The World')

@section('content')

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <span class="badge badge-red">
                Your Journey Starts Here
            </span>

            <h1>
                Explore the world.<br>
                Create memories.
            </h1>

            <p>
                Discover breathtaking destinations, exciting adventures
                and unforgettable travel experiences.
            </p>

            <div class="hero-actions">

                <a href="{{ route('destination') }}" class="btn btn-primary">
                    Explore Destinations
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="{{ route('tour') }}" class="btn btn-light">
                    View Tours
                </a>

            </div>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="section-heading">

            <div>
                <h2>Find Your Perfect Journey</h2>
                <p>Search destinations, hotels and experiences.</p>
            </div>

        </div>

        <form class="search-box" method="GET" action="{{ route('search') }}">

            <div class="search-field">
                <label for="search_destination">Destination</label>
                <input id="search_destination" name="destination" type="text" value="{{ request('destination') }}" placeholder="Where do you want to go?" required>
            </div>

            <div class="search-field">
                <label for="search_check_in">Check In</label>
                <input id="search_check_in" name="check_in" type="date" min="{{ today()->toDateString() }}" value="{{ request('check_in', today()->addDay()->toDateString()) }}" required>
            </div>

            <div class="search-field">
                <label for="search_travelers">Travelers</label>
                <input id="search_travelers" name="travelers" type="number" value="{{ request('travelers', 2) }}" min="1" max="20" required>
            </div>

            <button type="submit" class="btn btn-primary">
                Search
            </button>

        </form>

    </div>

</section>


<section class="section bg-light">

    <div class="container">

        <div class="section-heading">

            <div>
                <h2>Popular Destinations</h2>
                <p>Places travelers love to explore.</p>
            </div>

            <a href="{{ route('destination') }}" class="text-link">
                View All →
            </a>

        </div>


        <div class="cards-grid destination-card-grid">

            @forelse($destinations as $destination)

                <article class="card destination-card">

                    <div class="card-img">
                        <a href="{{ route('destination.show', $destination->slug) }}"
                           aria-label="View {{ $destination->name }} destination details">
                            @if($photo = \App\Support\NepalPhotos::destination($destination->slug))
                                <img src="{{ $photo }}" alt="{{ $destination->name }}, Nepal">
                            @endif
                        </a>
                    </div>

                    <div class="card-body">

                        <span class="muted">
                            {{ $destination->country }}
                        </span>

                        <h3>{{ $destination->name }}</h3>
                        <p class="muted">{{ $destination->short_description }}</p>

                        <div class="card-footer">

                            <span class="rating">
                                {{ $destination->tours_count }} {{ $destination->tours_count === 1 ? 'tour' : 'tours' }}
                            </span>

                            <a href="{{ route('destination.show', $destination->slug) }}"
                               class="text-link">
                                View destination
                            </a>

                        </div>

                    </div>

                </article>

            @empty
                <p class="muted">New destinations are being added soon.</p>
            @endforelse

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="section-heading">

            <div>
                <h2>Why Travel With Us?</h2>
                <p>Everything you need for a smooth journey.</p>
            </div>

        </div>


        <div class="features-grid">

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3>Trusted Travel</h3>
                <p class="muted">
                    Reliable partners and secure booking.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h3>Best Value</h3>
                <p class="muted">
                    Great experiences at competitive prices.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p class="muted">
                    We're here whenever you need us.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-earth-americas"></i>
                </div>
                <h3>Unique Experiences</h3>
                <p class="muted">
                    Discover places beyond ordinary tourism.
                </p>
            </div>

        </div>

    </div>

</section>

@endsection
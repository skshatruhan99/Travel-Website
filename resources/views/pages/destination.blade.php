@extends('layouts.app')

@section('title', 'Pokhara Destination')

@section('content')

<section class="page-hero">

    <div class="container">

        <div class="breadcrumb">
            <span>Home</span>
            /
            <span>Destinations</span>
            /
            <span>Pokhara</span>
        </div>

        <h1>Pokhara, Nepal</h1>

        <p>
            Discover lakes, mountains and unforgettable adventures.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="detail-grid">

            <div>

                <div class="detail-image">

                    <img src="{{ asset(config('nepal_photos.photos.phewa-lake.path')) }}"
                         alt="Phewa Lake, Pokhara, Nepal">

                </div>

                <div style="margin-top:30px">

                    <h2>Discover Pokhara</h2>

                    <p class="muted" style="margin-top:12px">
                        Pokhara is one of Nepal's most beautiful destinations,
                        famous for peaceful lakes, spectacular mountain views
                        and outdoor adventures.
                    </p>

                </div>

            </div>


            <div>

                <div class="booking-card sticky-card">

                    <h3>Pokhara</h3>

                    <p class="muted">
                        Nepal's adventure capital
                    </p>

                    <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">

                    <div class="tour-meta">
                        <span>
                            <i class="fa-solid fa-location-dot"></i>
                            Nepal
                        </span>
                    </div>

                    <a href="{{ route('tour') }}"
                       class="btn btn-primary"
                       style="width:100%">
                        Explore Tours
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="section bg-light">

    <div class="container">

        <div class="section-heading">

            <div>
                <h2>Things To Do</h2>
                <p>Experiences you shouldn't miss in Pokhara.</p>
            </div>

        </div>


        <div class="cards-grid">

            <div class="card">
                <div class="card-body">
                    <div class="feature-icon">
                        <i class="fa-solid fa-mountain"></i>
                    </div>
                    <h3>Mountain Views</h3>
                    <p class="muted">
                        Enjoy panoramic Himalayan landscapes.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="feature-icon">
                        <i class="fa-solid fa-water"></i>
                    </div>
                    <h3>Lake Adventures</h3>
                    <p class="muted">
                        Explore beautiful Phewa Lake.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="feature-icon">
                        <i class="fa-solid fa-person-hiking"></i>
                    </div>
                    <h3>Trekking</h3>
                    <p class="muted">
                        Discover amazing Himalayan trails.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>

@endsection
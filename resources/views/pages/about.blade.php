@extends('layouts.app')

@section('title', 'About NpAdvanture')

@section('content')

<section class="page-hero">

    <div class="container">

        <h1>About NpAdvanture</h1>

        <p>
            Making travel easier, more inspiring and more memorable.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="detail-grid">

            <div>

                <h2>We Believe Travel Changes Everything</h2>

                <p class="muted" style="margin-top:15px">

                    NpAdvanture is designed to help travelers discover
                    destinations, plan adventures and create unforgettable
                    memories.

                </p>

                <p class="muted" style="margin-top:15px">

                    From finding the perfect destination to booking
                    experiences, our platform brings the entire journey
                    together in one place.

                </p>

            </div>


            <div class="detail-image">

                <img src="{{ asset(config('nepal_photos.photos.annapurna-base-camp.path')) }}"
                     alt="Annapurna Base Camp, Nepal">

            </div>

        </div>

    </div>

</section>


<section class="section bg-light">

    <div class="container">

        <div class="features-grid">

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <h3>Passion</h3>
                <p class="muted">
                    We love discovering new places.
                </p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Community</h3>
                <p class="muted">
                    Travelers are at the heart of everything.
                </p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-earth-americas"></i>
                </div>
                <h3>Discovery</h3>
                <p class="muted">
                    There is always somewhere new to explore.
                </p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <h3>Responsible Travel</h3>
                <p class="muted">
                    Travel with respect for places and people.
                </p>
            </div>

        </div>

    </div>

</section>

@endsection
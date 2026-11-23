@extends('layouts.app')

@section('title', 'Flights & Transport')

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Flights & Transport</h1>
        <p>Plan your journey from departure to destination.</p>
    </div>
</section>


<section class="section bg-light">

    <div class="container">

        <div class="booking-card">

            <h2>Search Flights</h2>

            <form action="{{ route('flights.search') }}" method="GET">
            <div class="form-grid" style="margin-top:25px">

                <div class="form-group">
                    <label for="departure_city">From</label>

                    <input id="departure_city" name="departure_city" type="text"
                           class="form-control"
                           placeholder="Departure city"
                           value="{{ request('departure_city') }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="arrival_city">To</label>

                    <input id="arrival_city" name="arrival_city" type="text"
                           class="form-control"
                           placeholder="Destination"
                           value="{{ request('arrival_city') }}"
                           required>
                </div>

            </div>


            <div class="form-grid">

                <div class="form-group">
                    <label for="departure_date">Departure</label>
                    <input id="departure_date" name="departure_date" type="date" min="{{ today()->toDateString() }}" value="{{ request('departure_date') }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="passengers">Passengers</label>
                    <input id="passengers" name="passengers" type="number"
                           class="form-control"
                           min="1"
                           max="20"
                           value="{{ request('passengers', 1) }}"
                           required>
                </div>

            </div>

            <button type="submit" class="btn btn-primary">
                Search Flights
            </button>

            </form>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="section-heading">

            <div>
                <h2>Popular Transport Options</h2>
            </div>

        </div>


        <div class="features-grid">

            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-plane"></i>
                </div>

                <h3>Flights</h3>

                <p class="muted">
                    Domestic and international connections.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-bus"></i>
                </div>

                <h3>Bus</h3>

                <p class="muted">
                    Comfortable intercity transportation.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-car"></i>
                </div>

                <h3>Private Car</h3>

                <p class="muted">
                    Flexible private transfers.
                </p>
            </div>


            <div class="feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-train"></i>
                </div>

                <h3>Train</h3>

                <p class="muted">
                    Explore routes by rail.
                </p>
            </div>

        </div>

    </div>

</section>

@endsection
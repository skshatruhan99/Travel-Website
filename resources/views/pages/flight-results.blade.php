@extends('layouts.app')

@section('title', 'Flight Results - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Available Flights</h1>
        <p>{{ $data['departure_city'] }} to {{ $data['arrival_city'] }} · {{ $data['departure_date'] }} · {{ $data['passengers'] }} passengers</p>
    </div>
</section>
<section class="section bg-light">
    <div class="container">
        @forelse($flights as $flight)
            <div class="booking-card" style="margin-bottom:15px">
                <div class="card-footer">
                    <div>
                        <h3>{{ $flight->airline ?: 'Nepal Airlines' }} · {{ $flight->flight_number }}</h3>
                        <p class="muted">{{ $flight->departure_city }} to {{ $flight->arrival_city }} · {{ $flight->class }}</p>
                        <p class="muted">Depart {{ $flight->departure_at->format('M j, Y · H:i') }} · Arrive {{ $flight->arrival_at->format('M j, Y · H:i') }}</p>
                    </div>
                    <div style="text-align:right">
                        <strong class="price">NPR {{ number_format($flight->price) }}</strong>
                        <p class="muted">{{ $flight->remaining_seats }} seats left</p>
                        <a class="btn btn-primary" style="margin-top:10px" href="{{ route('flight.booking', ['flight' => $flight, 'passengers' => $data['passengers']]) }}">Select flight</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="booking-card">
                <h2>No flights found</h2>
                <p class="muted" style="margin-top:10px">Try another date or route.</p>
                <a href="{{ route('flights') }}" class="btn btn-primary" style="margin-top:20px">Search Again</a>
            </div>
        @endforelse
    </div>
</section>
@endsection

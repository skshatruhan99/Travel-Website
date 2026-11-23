@extends('layouts.app')

@section('title', 'Search Results - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Search Results</h1>
        <p>Matching destinations and experiences for {{ $search['term'] }} · {{ \Illuminate\Support\Carbon::parse($search['check_in'])->format('d M Y') }} · {{ $search['travelers'] }} {{ \Illuminate\Support\Str::plural('traveler', $search['travelers']) }}</p>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        <form class="search-box" method="GET" action="{{ route('search') }}">
            <div class="search-field"><label for="search_destination">Destination or keyword</label><input id="search_destination" name="destination" value="{{ $search['term'] }}" required maxlength="100"></div>
            <div class="search-field"><label for="search_check_in">Travel date</label><input id="search_check_in" name="check_in" type="date" min="{{ today()->toDateString() }}" value="{{ $search['check_in'] }}" required></div>
            <div class="search-field"><label for="search_travelers">Travelers</label><input id="search_travelers" name="travelers" type="number" min="1" max="20" value="{{ $search['travelers'] }}" required></div>
            <button type="submit" class="btn btn-primary">Search again</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        @php($resultCount = $destinations->count() + $tours->count() + $hotels->count() + $activities->count() + $flights->count())
        @if($resultCount === 0)
            <div class="booking-card">
                <h2>No matching options found</h2>
                <p class="muted" style="margin-top:10px">Try another destination or date. Search checks available destinations, tours, stays, activities, and flights.</p>
            </div>
        @endif

        @if($destinations->isNotEmpty())
            <div class="section-heading"><div><h2>Destinations</h2><p>{{ $destinations->count() }} matching places</p></div></div>
            <div class="cards-grid destination-card-grid">
                @foreach($destinations as $destination)
                    <article class="card destination-card">
                        <div class="card-img">@if($photo = \App\Support\NepalPhotos::destination($destination->slug))<a href="{{ route('destination.show', $destination->slug) }}" aria-label="View {{ $destination->name }} destination details"><img src="{{ $photo }}" alt="{{ $destination->name }}, Nepal"></a>@endif</div>
                        <div class="card-body">
                            <span class="muted">{{ $destination->country }}</span>
                            <h3>{{ $destination->name }}</h3>
                            <p class="muted">{{ $destination->short_description ?: $destination->description }}</p>
                            <a href="{{ route('destination.show', $destination->slug) }}" class="btn btn-primary" style="margin-top:15px">Explore destination</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($tours->isNotEmpty())
            <div class="section-heading" style="margin-top:50px"><div><h2>Tours</h2><p>Published tours with departures and seats available for your group.</p></div></div>
            <div class="cards-grid compact-mobile-grid">
                @foreach($tours as $tour)
                    @php($departure = $tour->available_departures->first(fn ($option) => $option->available_seats >= $search['travelers']))
                    <article class="card tour-card">
                        <div class="card-img">@if($photo = \App\Support\NepalPhotos::tour($tour->slug, $tour->destination?->slug))<a href="{{ route('tour.show', $tour->slug) }}" aria-label="View {{ $tour->title }} tour details"><img src="{{ $photo }}" alt="{{ $tour->title }} in {{ $tour->destination?->name }}, Nepal"></a>@endif</div>
                        <div class="card-body">
                            <p class="muted">{{ $tour->destination?->name }} · {{ $departure->departure_date->format('M j, Y') }} · {{ $departure->available_seats }} seats</p>
                            <h3>{{ $tour->title }}</h3>
                            <p class="muted">{{ $tour->short_description }}</p>
                            <div class="card-footer"><strong class="price">NPR {{ number_format($tour->price, 2) }}</strong><a class="btn btn-primary" href="{{ route('booking', ['tour_id' => $tour->id, 'tour_date_id' => $departure->id, 'adults' => $search['travelers'], 'children' => 0]) }}">Book tour</a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($hotels->isNotEmpty())
            <div class="section-heading" style="margin-top:50px"><div><h2>Hotels & stays</h2><p>Active properties with bookable room types.</p></div></div>
            <div class="cards-grid compact-mobile-grid">
                @foreach($hotels as $hotel)
                    <article class="card hotel-card">
                        <div class="card-img">@if($photo = \App\Support\NepalPhotos::hotel($hotel->slug))<img src="{{ $photo }}" alt="{{ $hotel->name }}, {{ $hotel->destination?->name }}, Nepal">@endif</div>
                        <div class="card-body">
                            @if($hotel->stars)<span class="rating">{{ str_repeat('★', $hotel->stars) }}</span>@endif
                            <h3>{{ $hotel->name }}</h3>
                            <p class="muted">{{ $hotel->destination?->name }}{{ $hotel->destination?->country ? ', '.$hotel->destination->country : '' }}</p>
                            <div class="card-footer"><strong class="price">From NPR {{ number_format($hotel->rooms->min('price_per_night'), 2) }}/night</strong><a href="{{ route('hotel.show', ['hotel' => $hotel->slug, 'check_in' => $search['check_in'], 'check_out' => \Illuminate\Support\Carbon::parse($search['check_in'])->addDay()->toDateString(), 'adults' => $search['travelers']]) }}" class="btn btn-primary">View stay</a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($activities->isNotEmpty())
            <div class="section-heading" style="margin-top:50px"><div><h2>Activities</h2><p>Experiences with places available on your selected date.</p></div></div>
            <div class="cards-grid">
                @foreach($activities as $activity)
                    <article class="card">
                        @if($photo = \App\Support\NepalPhotos::activity($activity->slug))
                            <div class="card-img"><img src="{{ $photo }}" alt="{{ $activity->name }} in {{ $activity->destination?->name }}, Nepal"></div>
                        @endif
                        <div class="card-body">
                            <span class="muted">{{ $activity->destination?->name }} · {{ $activity->category }}</span>
                            <h3>{{ $activity->name }}</h3>
                            <p class="muted">{{ $activity->description }}</p>
                            <div class="card-footer"><strong class="price">NPR {{ number_format($activity->price, 2) }}/person</strong><a href="{{ route('activities.booking', ['activity' => $activity->slug, 'activity_date' => $search['check_in'], 'participants' => $search['travelers']]) }}" class="btn btn-primary">Book activity</a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($flights->isNotEmpty())
            <div class="section-heading" style="margin-top:50px"><div><h2>Flights</h2><p>Flights on your selected date with seats available for your group.</p></div></div>
            <div class="cards-grid">
                @foreach($flights as $flight)
                    <article class="booking-card">
                        <h3>{{ $flight->airline ?: 'Airline' }} · {{ $flight->flight_number }}</h3>
                        <p class="muted">{{ $flight->departure_city }} to {{ $flight->arrival_city }} · {{ $flight->class }}</p>
                        <p class="muted">Depart {{ $flight->departure_at->format('H:i') }} · Arrive {{ $flight->arrival_at->format('H:i') }} · {{ $flight->remaining_seats }} seats left</p>
                        <div class="card-footer"><strong class="price">NPR {{ number_format($flight->price, 2) }}/person</strong><a href="{{ route('flight.booking', ['flight' => $flight->id, 'passengers' => $search['travelers']]) }}" class="btn btn-primary">Select flight</a></div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection

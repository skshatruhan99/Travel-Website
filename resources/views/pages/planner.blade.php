@extends('layouts.app')

@section('title', 'Trip Planner')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Trip Planner</h1>
        <p>Save a trip and build an itinerary from real destinations, tours, hotels, and activities.</p>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        @if(session('success'))<p class="badge badge-green" role="status" style="margin-bottom:20px">{{ session('success') }}</p>@endif
        <div class="planner-grid">
            <div class="planner-box">
                <h2>Create a trip plan</h2>
                @if($errors->any())<p class="badge badge-red" role="alert" style="margin-top:15px">{{ $errors->first() }}</p>@endif
                @guest
                    <p class="muted" style="margin:12px 0">Log in to save and manage your itinerary.</p>
                @endguest
                <form method="POST" action="{{ route('planner.store') }}" style="margin-top:25px">
                    @csrf
                    <div class="form-group">
                        <label for="title">Trip name (optional)</label>
                        <input id="title" name="title" value="{{ old('title') }}" class="form-control" maxlength="255" placeholder="e.g. Summer in Nepal">
                    </div>
                    <div class="form-group">
                        <label for="destination_id">Starting destination</label>
                        <select id="destination_id" name="destination_id" class="form-control" required>
                            <option value="">Select a destination</option>
                            @foreach($destinations as $destination)
                                <option value="{{ $destination->id }}" @selected(old('destination_id') == $destination->id)>{{ $destination->name }}, {{ $destination->country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}" class="form-control" required></div>
                        <div class="form-group"><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}" class="form-control" required></div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label for="adults">Adults</label><input id="adults" name="adults" type="number" min="1" max="20" value="{{ old('adults', 2) }}" class="form-control" required></div>
                        <div class="form-group"><label for="children">Children</label><input id="children" name="children" type="number" min="0" max="19" value="{{ old('children', 0) }}" class="form-control"></div>
                    </div>
                    <div class="form-group"><label for="budget">Budget (NPR, optional)</label><input id="budget" name="budget" type="number" min="0" step="0.01" value="{{ old('budget') }}" class="form-control"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Save trip and build itinerary</button>
                </form>
            </div>

            <div class="planner-box">
                <div class="section-heading"><div><h2>Your saved trips</h2><p>Open a plan to manage its day-by-day itinerary.</p></div></div>
                @auth
                    @forelse($plans as $plan)
                        <div class="plan-item">
                            <div class="plan-number"><i class="fa-solid fa-map"></i></div>
                            <div style="flex:1">
                                <a href="{{ route('planner.show', $plan) }}"><strong>{{ $plan->title }}</strong></a>
                                <p class="muted">{{ $plan->destination }} · {{ $plan->start_date->format('d M Y') }} – {{ $plan->end_date->format('d M Y') }}</p>
                                <small class="muted">{{ $plan->items_count }} itinerary item(s) · Estimate NPR {{ number_format($plan->estimated_total, 2) }}</small>
                            </div>
                            <a href="{{ route('planner.show', $plan) }}" class="text-link">Manage</a>
                        </div>
                    @empty
                        <p class="muted">No saved trips yet. Create your first plan to get started.</p>
                    @endforelse
                @else
                    <p class="muted">Your saved trip plans will appear here after you log in.</p>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection

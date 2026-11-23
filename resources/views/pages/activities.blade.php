@extends('layouts.app')

@section('title', 'Activities & Experiences')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Activities & Experiences</h1>
        <p>Make your journey more exciting with unforgettable experiences.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cards-grid">
            @forelse($activities as $activity)
                <article class="card">
                    <div class="card-img">
                        @if($photo = \App\Support\NepalPhotos::activity($activity->slug))
                            <img src="{{ $photo }}" alt="{{ $activity->name }} in {{ $activity->destination?->name }}, Nepal">
                        @else
                            <div class="feature-icon"><i class="fa-solid fa-person-hiking"></i></div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($activity->is_featured)<span class="badge badge-red">Featured</span>@endif
                        <h3>{{ $activity->name }}</h3>
                        <p class="muted">{{ $activity->destination?->name }}{{ $activity->destination?->country ? ', '.$activity->destination->country : '' }}</p>
                        <p class="muted" style="margin-top:10px">{{ \Illuminate\Support\Str::limit($activity->description, 120) }}</p>
                        <div class="card-footer">
                            <strong class="price">NPR {{ number_format($activity->price, 2) }} / person</strong>
                            <a href="{{ route('activities.show', $activity->slug) }}" class="btn btn-primary">View & book</a>
                        </div>
                        <form action="{{ route('wishlist.store') }}" method="POST" style="margin-top:12px">
                            @csrf
                            <input type="hidden" name="item_type" value="activity">
                            <input type="hidden" name="item_id" value="{{ $activity->id }}">
                            <button class="btn btn-outline" type="submit"><i class="fa-regular fa-heart"></i> Save activity</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="muted" style="grid-column:1/-1">No activities are currently available.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Saved Places - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Saved Places</h1>
        <p>Keep your favorite Nepal stays and experiences close.</p>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        <div class="booking-card" style="max-width:760px;margin:auto">
            @if(session('success'))
                <p class="badge badge-green">{{ session('success') }}</p>
            @endif
            <div class="section-heading">
                <div>
                    <h2>Your Wishlist</h2>
                    <p>Save a hotel or destination while you plan your next trip.</p>
                </div>
            </div>
            @if($items->isEmpty())
            <div style="text-align:center;padding:35px 10px">
                <i class="fa-regular fa-heart" style="font-size:42px;color:var(--red)"></i>
                <h3 style="margin-top:15px">No saved places yet</h3>
                <p class="muted" style="margin-top:8px">Explore Nepal and save the places you want to visit.</p>
                <a href="{{ route('hotels') }}" class="btn btn-primary" style="margin-top:20px">Explore Hotels</a>
            </div>
            @else
                @foreach($items as $item)
                    <div class="plan-item">
                        <div class="plan-number"><i class="fa-solid fa-heart"></i></div>
                        <div style="flex:1">
                            <strong>
                                @if($item->item_type === 'activity' && $item->activity)
                                    <a href="{{ route('activities.show', $item->activity->slug) }}">{{ $item->activity->name }}</a>
                                @else
                                    {{ ucfirst($item->item_type) }} #{{ $item->item_id }}
                                @endif
                            </strong>
                            <p class="muted">Saved {{ $item->created_at->format('d M Y') }}</p>
                        </div>
                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-link" style="border:0;background:none;cursor:pointer">Remove</button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
@endsection

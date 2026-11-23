@extends('layouts.app')

@section('title', 'Nepal Photo Credits')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Photo credits</div>
        <h1>Nepal photo credits</h1>
        <p>These location photographs are from Wikimedia Commons and are used under the licenses shown below.</p>
    </div>
</section>

<section class="section">
    <div class="container cards-grid">
        @foreach(config('nepal_photos.photos') as $photo)
            <article class="card">
                <div class="card-img">
                    <img src="{{ asset($photo['path']) }}" alt="{{ $photo['place'] }}" loading="lazy">
                </div>
                <div class="card-body">
                    <h2>{{ $photo['place'] }}</h2>
                    <p class="muted">Photo by {{ $photo['author'] }}</p>
                    <p class="muted">Resized for website display.</p>
                    <p class="muted">
                        <a class="text-link" href="{{ $photo['source'] }}" target="_blank" rel="noopener noreferrer">Original photo</a>
                        ·
                        <a class="text-link" href="{{ $photo['license_url'] }}" target="_blank" rel="noopener noreferrer">{{ $photo['license'] }}</a>
                    </p>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection

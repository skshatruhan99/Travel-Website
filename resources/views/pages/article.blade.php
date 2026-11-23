@extends('layouts.app')

@section('title', $article->title . ' - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="article-category">{{ $article->category }}</span>
        <h1>{{ $article->title }}</h1>
        <p>{{ $article->excerpt }}</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:900px">
        <article class="booking-card">
            @if($photo = \App\Support\NepalPhotos::place($article->title))<img src="{{ $photo }}" alt="{{ $article->title }}, Nepal" style="max-height:420px;border-radius:8px">@endif
            <div style="margin-top:25px;white-space:pre-line">{{ $article->content }}</div>
            <a href="{{ route('planner') }}" class="btn btn-primary" style="margin-top:25px">Plan Your Trip <i class="fa-solid fa-arrow-right"></i></a>
        </article>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Travel Journal')

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Travel Journal</h1>
        <p>Stories, guides and inspiration for your next adventure.</p>
    </div>
</section>


<section class="section">

    <div class="container">

        <div class="cards-grid">
            @forelse($articles as $article)
                <article class="card article-card">
                    @if($photo = \App\Support\NepalPhotos::place($article->title))<div class="card-img"><img src="{{ $photo }}" alt="{{ $article->title }}, Nepal"></div>@endif
                    <div class="card-body">
                        <span class="article-category">{{ $article->category }}</span>
                        <h3>{{ $article->title }}</h3>
                        <p class="muted">{{ $article->excerpt }}</p>
                        <a href="{{ route('journal.article', $article->slug) }}" class="text-link" style="display:inline-block;margin-top:15px">Read Article →</a>
                    </div>
                </article>
            @empty
                <p class="muted" style="grid-column:1/-1">New travel stories are coming soon.</p>
            @endforelse
        </div>
        <div class="admin-pagination">{{ $articles->links() }}</div>

    </div>

</section>

@endsection
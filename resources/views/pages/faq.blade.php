@extends('layouts.app')

@section('title', 'FAQ & Policies')

@section('content')

<section class="page-hero">

    <div class="container">

        <h1>FAQ & Policies</h1>

        <p>
            Find answers to frequently asked questions.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="faq-list">

            @forelse($faqs as $faq)
                <article class="faq-item">
                    <div class="faq-question"><span>{{ $faq->question }}</span><i class="fa-solid fa-plus"></i></div>
                    <div class="faq-answer">{{ $faq->answer }}</div>
                </article>
            @empty
                <p class="muted">There are no published FAQs yet.</p>
            @endforelse

        </div>

    </div>

</section>

@endsection
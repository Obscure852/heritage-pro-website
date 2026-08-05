@extends('layouts.website-editorial')

@section('meta-description', $journal['lead'])

@push('page-styles')
    <style>
        @include('website.partials.editorial.home-styles')
        @include('website.partials.editorial.article-styles')
    </style>
@endpush

@section('content')
    <div class="hp-page">
        @include('website.partials.editorial.nav')
        <main id="main">
            <section class="hp-section hp-band">
                <div class="hp-intro">
                    <p class="hp-eyebrow">{{ $journal['eyebrow'] }}</p>
                    <h1 class="hp-h2">{{ $journal['heading'] }}</h1>
                    <p class="hp-lead">{{ $journal['lead'] }}</p>
                </div>
                <div class="hp-grid-3 hp-journal hp-journal--index">
                    @foreach ($articles as $article)
                        @include('website.partials.editorial.post-card', [
                            'article' => $article,
                            'showStandfirst' => true,
                        ])
                    @endforeach
                </div>
            </section>
            @include('website.partials.editorial.demo')
        </main>
        @include('website.partials.editorial.footer')
    </div>
@endsection

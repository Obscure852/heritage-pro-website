@extends('layouts.website-editorial')

@section('meta-description', $article['standfirst'])

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
            <article class="hp-article hp-band">
                <header class="hp-article__head">
                    <p class="hp-label">{{ $article['kind'] }} · {{ $article['reading_time'] }}</p>
                    <h1 class="hp-article__title">{{ $article['title'] }}</h1>
                    <p class="hp-article__standfirst">{{ $article['standfirst'] }}</p>
                    <p class="hp-article__byline">
                        {{ $article['author'] }}
                        <span aria-hidden="true">·</span>
                        <time datetime="{{ $article['published_at'] }}">{{ \Illuminate\Support\Carbon::parse($article['published_at'])->format('j F Y') }}</time>
                    </p>
                </header>

                @if ($article['image'])
                    <figure class="hp-article__figure">
                        <img src="{{ asset($article['image']) }}" alt="{{ $article['image_alt'] }}" width="1200" height="600">
                    </figure>
                @endif

                @include('website.partials.editorial.article-body', ['blocks' => $article['body']])

                <footer class="hp-article__foot">
                    <p>Written by the team who build and implement Heritage&nbsp;Pro.</p>
                    <a href="#demo" class="hp-btn hp-btn--solid">Book a 30-minute demo</a>
                </footer>
            </article>

            @if (! empty($moreArticles))
                <section class="hp-section hp-band">
                    <div class="hp-headrow">
                        <h2 class="hp-h2 hp-h2--sm">More from the journal</h2>
                        <a href="{{ route('website.journal') }}" class="hp-link">All writing</a>
                    </div>
                    <div class="hp-grid-2 hp-journal">
                        @foreach ($moreArticles as $more)
                            @include('website.partials.editorial.post-card', ['article' => $more])
                        @endforeach
                    </div>
                </section>
            @endif

            @include('website.partials.editorial.demo')
        </main>
        @include('website.partials.editorial.footer')
    </div>
@endsection

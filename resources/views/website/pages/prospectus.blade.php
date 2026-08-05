@extends('layouts.website-editorial')

@section('meta-description', $meta['standfirst'])

@push('page-styles')
    <style>
        @include('website.partials.editorial.home-styles')
        @include('website.partials.editorial.article-styles')
    </style>
@endpush

@section('content')
    @php
        $sections = collect($body)->where('type', 'heading')->pluck('text');
    @endphp
    <div class="hp-page">
        @include('website.partials.editorial.nav')
        <main id="main">
            <article class="hp-article hp-band">
                <header class="hp-article__head">
                    <p class="hp-label">{{ $meta['eyebrow'] }}</p>
                    <h1 class="hp-article__title">{{ $meta['heading'] }}</h1>
                    <p class="hp-article__standfirst">{{ $meta['standfirst'] }}</p>
                    <p class="hp-article__byline">{{ $meta['edition_note'] }}</p>
                </header>

                <nav class="hp-contents" aria-label="{{ $meta['contents_label'] }}">
                    <p class="hp-contents__label">{{ $meta['contents_label'] }}</p>
                    <ol class="hp-contents__list">
                        @foreach ($sections as $section)
                            <li><a href="#{{ Str::slug($section) }}">{{ $section }}</a></li>
                        @endforeach
                    </ol>
                </nav>

                @include('website.partials.editorial.article-body', [
                    'blocks' => $body,
                    'anchors' => true,
                ])

                <footer class="hp-article__foot">
                    <p>Prepared by the team who build and implement Heritage&nbsp;Pro.</p>
                    <a href="#demo" class="hp-btn hp-btn--solid">Book a 30-minute demo</a>
                </footer>
            </article>

            @include('website.partials.editorial.demo')
        </main>
        @include('website.partials.editorial.footer')
    </div>
@endsection

@extends('layouts.website-editorial')

@push('page-styles')
    <style>
        @include('website.partials.editorial.home-styles')
    </style>
@endpush

@section('content')
    <div class="hp-page">
        @include('website.partials.editorial.nav')
        <main id="main">
            @include('website.partials.editorial.hero', ['hero' => $pageConfig['hero']])
            @include('website.partials.editorial.logos')
            @include('website.partials.editorial.editions')
            @include('website.partials.editorial.stats')
            @include('website.partials.editorial.capabilities')
            @include('website.partials.editorial.modules')
            @include('website.partials.editorial.testimonials')
            @include('website.partials.editorial.deployments')
            @include('website.partials.editorial.pricing')
            @include('website.partials.editorial.faq')
            @include('website.partials.editorial.journal')
            @include('website.partials.editorial.resellers')
            @include('website.partials.editorial.team')
            @include('website.partials.editorial.demo')
        </main>
        @include('website.partials.editorial.footer')
    </div>
@endsection

@push('page-scripts')
    <script>
        // Bring a returning submission into view when the form reports back.
        (() => {
            if (window.location.hash || !document.querySelector('.hp-alert, .hp-field__error')) {
                return;
            }

            const demo = document.getElementById('demo');

            if (demo) {
                window.setTimeout(() => demo.scrollIntoView({ behavior: 'smooth', block: 'start' }), 120);
            }
        })();
    </script>
@endpush

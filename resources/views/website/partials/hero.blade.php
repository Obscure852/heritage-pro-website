@php
    $secondaryHref = $hero['secondary_href'] ?? (isset($hero['secondary_route']) ? route($hero['secondary_route']) : '#');
    $hasEyebrow = !empty($hero['eyebrow']);
@endphp
<section class="hero">
    <div class="container hero-inner">
        <div>
            @if ($hasEyebrow)
                <span class="eyebrow">{{ $hero['eyebrow'] }}</span>
            @endif
            <h1 style="margin-top: {{ $hasEyebrow ? '16px' : '0' }};">{{ $hero['title'] }}</h1>
            <p class="lead">{{ $hero['lead'] }}</p>
            <div class="hero-cta">
                <a href="#contact" class="btn btn-primary btn-lg">Book a demo @include('website.partials.icon', ['name' => 'arrow', 'size' => 16])</a>
                <a href="{{ $secondaryHref }}" class="btn btn-secondary btn-lg">
                    @if (!empty($hero['show_play_icon']))
                        @include('website.partials.icon', ['name' => 'play', 'size' => 14])
                    @endif
                    {{ $hero['secondary_label'] }}
                </a>
            </div>
            <div class="hero-trust">
                @foreach ($site['hero_trust'] as $trust)
                    <div><b>{{ $trust['value'] }}</b> {{ $trust['label'] }}</div>
                @endforeach
            </div>
        </div>
        <div class="hero-media">
            @include('website.partials.window-chrome')
            @include('website.partials.hero-mini-dash')
        </div>
    </div>
</section>

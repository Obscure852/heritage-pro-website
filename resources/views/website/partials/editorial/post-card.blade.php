@php
    $eyebrow = $article['kind'] . ' · ' . $article['reading_time'];
@endphp
<a href="{{ route('website.journal.article', $article['slug']) }}" class="hp-post">
    @if ($article['image'])
        <div class="hp-post__media">
            <img src="{{ asset($article['image']) }}" alt="{{ $article['image_alt'] }}" width="520" height="340" loading="lazy">
        </div>
    @else
        <div class="hp-post__media hp-post__media--mark" aria-hidden="true"><span>Heritage&nbsp;Pro</span></div>
    @endif
    <p class="hp-label hp-post__meta">{{ $eyebrow }}</p>
    <h3 class="hp-post__title">{{ $article['title'] }}</h3>
    @if ($showStandfirst ?? false)
        <p class="hp-post__standfirst">{{ $article['standfirst'] }}</p>
    @endif
</a>

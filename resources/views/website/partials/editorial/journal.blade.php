@php
    $latestArticles = array_slice(config('heritage_journal.articles'), 0, 3);
@endphp
<section id="journal" class="hp-section hp-band">
    <div class="hp-headrow">
        <div>
            <p class="hp-eyebrow">VIII. The journal</p>
            <h2 class="hp-h2 hp-h2--sm">Ideas and playbooks for school leaders.</h2>
        </div>
        <a href="{{ route('website.journal') }}" class="hp-link">All writing</a>
    </div>
    <div class="hp-grid-3 hp-journal">
        @foreach ($latestArticles as $article)
            @include('website.partials.editorial.post-card', ['article' => $article])
        @endforeach
    </div>
</section>

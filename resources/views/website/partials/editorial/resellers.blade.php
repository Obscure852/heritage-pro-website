@php
    $resellers = [
        [
            'name' => 'Image Life',
            'body' => 'Sales, implementation and first-line support for junior and senior schools.',
            'items' => ['Licensing & renewals', 'Onboarding & staff training', 'First-line support'],
        ],
        [
            'name' => 'Platinum Identity',
            'body' => 'Sales, implementation and first-line support for school groups and multi-campus clients.',
            'items' => ['Licensing & renewals', 'Group rollout planning', 'First-line support'],
        ],
        [
            'name' => 'Dedicated Origins',
            'body' => 'Sales, implementation and first-line support for colleges and tertiary institutions.',
            'items' => ['Licensing & renewals', 'Data migration support', 'First-line support'],
        ],
    ];
@endphp
<section id="resellers" class="hp-section hp-band">
    <div class="hp-intro">
        <p class="hp-eyebrow">IX. Resellers</p>
        <h2 class="hp-h2 hp-h2--sm">Authorised Heritage Pro resellers.</h2>
        <p class="hp-lead">Accredited partners licensed to sell, implement and support Heritage Pro. Each holds current product certification and works to the same implementation standard as our own team.</p>
    </div>
    <div class="hp-grid-3 hp-resellers__grid">
        @foreach ($resellers as $reseller)
            <article class="hp-card hp-reseller">
                <p class="hp-label">Authorised reseller</p>
                <h3 class="hp-reseller__name">{{ $reseller['name'] }}</h3>
                <p class="hp-body">{{ $reseller['body'] }}</p>
                <div class="hp-ruled">
                    @foreach ($reseller['items'] as $item)
                        <div>{{ $item }}</div>
                    @endforeach
                </div>
                <a href="#demo" class="hp-link">Contact {{ $reseller['name'] }}</a>
            </article>
        @endforeach
    </div>
    <div class="hp-callout">
        <p>Interested in reselling Heritage Pro in your market?</p>
        <a href="#demo" class="hp-btn hp-btn--solid">Apply to become a reseller</a>
    </div>
</section>

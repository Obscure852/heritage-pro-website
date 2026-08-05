@php
    $stats = [
        ['value' => '98+', 'label' => 'Institutions running Heritage Pro'],
        ['value' => '84,000+', 'label' => 'Learner records under management'],
        ['value' => '99.95%', 'label' => 'Platform uptime, trailing 12 months'],
        ['value' => '12×', 'label' => 'Faster end-of-term reporting'],
    ];
@endphp
<section class="hp-stats hp-band hp-band--tint" aria-label="Heritage Pro at a glance">
    <dl class="hp-stats__grid">
        @foreach ($stats as $stat)
            <div class="hp-stats__item">
                <dd class="hp-stats__value">{{ $stat['value'] }}</dd>
                <dt class="hp-stats__label">{{ $stat['label'] }}</dt>
            </div>
        @endforeach
    </dl>
</section>

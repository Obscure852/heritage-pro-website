@php
    $extraClass = $extraClass ?? '';
@endphp
<div class="cases-grid{{ $extraClass !== '' ? ' ' . $extraClass : '' }}">
    @foreach ($site['customer_cards'] as $card)
        <div class="case-card">
            <div class="case-cover {{ $card['theme'] }}">
                <span class="case-tag">{{ $card['tag'] }}</span>
                <div class="case-kicker">{{ $card['kicker'] }}</div>
            </div>
            <div class="case-body">
                <h4>{{ $card['title'] }}</h4>
                <p>{{ $card['description'] }}</p>
                <div class="case-metrics">
                    @foreach ($card['metrics'] as $metric)
                        <div class="case-metric"><b>{{ $metric['value'] }}</b><span>{{ $metric['label'] }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

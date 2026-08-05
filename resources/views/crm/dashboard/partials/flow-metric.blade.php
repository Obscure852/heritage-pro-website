@php
    $direction = $metric['direction'];
    $delta = $metric['delta'];
    $icon = match ($direction) {
        'up' => 'bx-trending-up',
        'down' => 'bx-trending-down',
        default => 'bx-minus',
    };
@endphp

<div class="crm-kpi-card">
    <p class="crm-kicker">{{ $metric['label'] }}</p>
    <strong class="crm-kpi-value">{{ $metric['display'] ?? number_format($metric['value']) }}</strong>

    <div class="crm-kpi-delta">
        @if ($delta === null)
            @unless ($hideEmptyDelta ?? false)
                <span class="crm-kpi-chip is-flat">
                    <i class="bx bx-minus"></i> No prior activity
                </span>
            @endunless
        @else
            <span class="crm-kpi-chip is-{{ $direction }}">
                <i class="bx {{ $icon }}"></i>
                {{ $delta > 0 ? '+' : '' }}{{ $delta }}%
            </span>
            <span class="crm-kpi-note">vs {{ number_format($metric['previous']) }} before</span>
        @endif
    </div>

    @if ($metric['url'])
        <a class="crm-kpi-link" href="{{ $metric['url'] }}">
            Open <i class="bx bx-right-arrow-alt"></i>
        </a>
    @endif
</div>

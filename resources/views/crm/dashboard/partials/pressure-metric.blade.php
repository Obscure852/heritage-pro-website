@php
    $isAlert = ($alert ?? false) && $metric['value'] > 0;
@endphp

<div class="crm-pressure-card{{ $isAlert ? ' is-alert' : '' }}">
    <div class="crm-pressure-copy">
        <p class="crm-kicker">{{ $metric['label'] }}</p>
        <strong>{{ number_format($metric['value']) }}</strong>
    </div>

    @if ($metric['url'])
        <a class="crm-pressure-link" href="{{ $metric['url'] }}" aria-label="Open {{ $metric['label'] }}">
            <i class="bx bx-right-arrow-alt"></i>
        </a>
    @endif
</div>

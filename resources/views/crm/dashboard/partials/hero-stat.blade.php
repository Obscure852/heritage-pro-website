@php
    $statUrl = $url ?? null;
@endphp

@if ($statUrl)
    <a class="crm-summary-hero-stat crm-summary-hero-stat-link" href="{{ $statUrl }}">
        <strong>{{ $value }}</strong>
        <span>{{ $label }}</span>
    </a>
@else
    <div class="crm-summary-hero-stat">
        <strong>{{ $value }}</strong>
        <span>{{ $label }}</span>
    </div>
@endif

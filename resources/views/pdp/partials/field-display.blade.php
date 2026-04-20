@php
    $displayValue = $viewService->displayValue($field, $value);
@endphp

<div class="display-card">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
        <div class="display-label">{{ $field->label }}</div>
        @if ($field->period_scope)
            <span class="badge-soft badge-soft-dark">{{ $viewService->periodLabel($field->period_scope) }}</span>
        @endif
    </div>

    @if (is_array($value))
        <pre class="display-value">{{ $displayValue }}</pre>
    @else
        <div class="display-value">{{ $displayValue }}</div>
    @endif
</div>

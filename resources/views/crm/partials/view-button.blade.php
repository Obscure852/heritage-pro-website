@php
    $buttonClass = $class ?? 'btn crm-icon-action';
    $buttonLabel = $label ?? 'View record';
    $buttonIcon = $icon ?? 'fas fa-eye';
@endphp

<a href="{{ $url }}" class="{{ $buttonClass }}" title="{{ $buttonLabel }}" aria-label="{{ $buttonLabel }}">
    <i class="{{ $buttonIcon }}"></i>
</a>

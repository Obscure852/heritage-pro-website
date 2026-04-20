@php
    $type = $type ?? 'submit';
    $variant = $variant ?? 'btn-primary';
    $icon = $icon ?? 'fas fa-save';
    $label = $label ?? 'Save';
    $loadingText = $loadingText ?? 'Saving...';
    $className = trim(($class ?? '') . ' btn-loading');
@endphp

<button type="{{ $type }}" class="btn {{ $variant }} {{ $className }}" @disabled($disabled ?? false)>
    <span class="btn-text"><i class="{{ $icon }}"></i> {{ $label }}</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        {{ $loadingText }}
    </span>
</button>

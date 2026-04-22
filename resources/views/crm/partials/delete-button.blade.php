@php
    $resolvedLabel = $label ?? 'Delete';
    $resolvedIcon = $icon ?? 'fas fa-trash-alt';
    $usesIconOnly = $iconOnly ?? false;
    $buttonClass = $class ?? ($usesIconOnly ? 'btn crm-icon-action crm-icon-danger' : 'btn btn-danger crm-btn-danger');
@endphp

<form method="POST"
    action="{{ $action }}"
    class="{{ $formClass ?? 'crm-inline-form' }}"
    onsubmit="return confirm('{{ $message ?? 'Are you sure you want to permanently delete this record?' }}')">
    @csrf
    @method('DELETE')

    <button type="submit" class="{{ $buttonClass }}" title="{{ $resolvedLabel }}" aria-label="{{ $resolvedLabel }}">
        <i class="{{ $resolvedIcon }}"></i>
        @if ($usesIconOnly)
            <span class="visually-hidden">{{ $resolvedLabel }}</span>
        @else
            {{ $resolvedLabel }}
        @endif
    </button>
</form>

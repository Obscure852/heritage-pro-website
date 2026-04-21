<form method="POST"
    action="{{ $action }}"
    class="{{ $formClass ?? 'crm-inline-form' }}"
    onsubmit="return confirm('{{ $message ?? 'Are you sure you want to permanently delete this record?' }}')">
    @csrf
    @method('DELETE')

    <button type="submit" class="{{ $class ?? 'btn btn-danger crm-btn-danger' }}">
        <i class="{{ $icon ?? 'fas fa-trash-alt' }}"></i> {{ $label ?? 'Delete' }}
    </button>
</form>

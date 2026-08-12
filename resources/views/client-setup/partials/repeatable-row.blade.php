<div class="crm-wizard-repeatable-row" data-repeatable-row>
    @foreach ($fields as $field)
        <div class="crm-field">
            <label for="{{ $field['id'] }}">{{ $field['label'] }}@if ($field['required'] ?? false) <span aria-hidden="true">*</span>@endif</label>
            <input
                id="{{ $field['id'] }}"
                name="{{ $field['name'] }}"
                type="{{ $field['type'] ?? 'text' }}"
                value="{{ $field['value'] ?? '' }}"
                class="form-control"
                @if ($field['required'] ?? false) required @endif
            >
        </div>
    @endforeach
    <button type="button" class="btn btn-light crm-btn-light crm-wizard-remove-row" data-repeatable-remove>
        <i class="bx bx-trash"></i><span class="visually-hidden">Remove row</span>
    </button>
</div>

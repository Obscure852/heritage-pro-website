@php
    $selectedIds = collect($selectedIds ?? [])->map(fn ($value) => (string) $value)->all();
    $inputName = $inputName ?? 'custom_filter_ids[]';
    $inputIdPrefix = $inputIdPrefix ?? 'crm-custom-filter';
@endphp

@if (collect($filters ?? [])->isEmpty())
    <div class="crm-empty-inline">No custom filters are available yet.</div>
@else
    <div class="crm-pill-selector">
        @foreach ($filters as $filter)
            @php($inputId = $inputIdPrefix . '-' . $filter->id)
            <label class="crm-select-pill" for="{{ $inputId }}">
                <input
                    id="{{ $inputId }}"
                    class="crm-select-pill-input"
                    type="checkbox"
                    name="{{ $inputName }}"
                    value="{{ $filter->id }}"
                    @checked(in_array((string) $filter->id, $selectedIds, true))
                >
                <span class="crm-select-pill-face">{{ $filter->name }}</span>
            </label>
        @endforeach
    </div>
@endif

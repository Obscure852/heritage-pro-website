@php
    $depth = $depth ?? 0;
    $prefix = $prefix ?? '';
    $formData = $data ?? [];
    $errorDetails = $errorDetails ?? [];
@endphp

@foreach ($fields as $field)
    @php
        $fieldPath = $prefix === '' ? $field['key'] : $prefix . '.' . $field['key'];
        $inputName = 'data[' . str_replace('.', '][', $fieldPath) . ']';
        $value = data_get($formData, $fieldPath, $field['default'] ?? null);
        $fieldId = 'client_setup_' . str_replace(['.', '[', ']', '__'], '_', $fieldPath);
        $isRequired = ($field['requirement'] ?? 'O') === 'R';
        $help = $field['help'] ?? null;
        $helpId = $help ? $fieldId . '_help' : null;
        $condition = $field['required_when'] ?? null;
        $conditionFieldPath = $condition && isset($condition['field'])
            ? ($prefix === '' ? $condition['field'] : $prefix . '.' . $condition['field'])
            : null;
        $conditionFieldId = $conditionFieldPath
            ? 'client_setup_' . str_replace(['.', '[', ']', '__'], '_', $conditionFieldPath)
            : null;
        $conditionOperator = $condition && array_key_exists('equals', $condition) ? 'equals' : 'not_equals';
        $conditionValue = $condition
            ? ($condition['equals'] ?? $condition['not_equals'] ?? null)
            : null;
        $fieldErrors = array_values(array_filter($errorDetails, static fn (array $detail): bool => ($detail['path'] ?? null) === $fieldPath));
        $fieldErrorId = $fieldId . '_error';
        $describedBy = implode(' ', array_filter([$helpId, $fieldErrors !== [] ? $fieldErrorId : null]));
    @endphp

    @if ($field['type'] === 'repeatable')
        @php
            $rows = is_array($value) && $value !== [] ? $value : [[]];
            $placeholder = '__INDEX_' . $depth . '__';
        @endphp
        <fieldset class="crm-wizard-repeatable" data-repeatable-collection data-repeatable-label="{{ $field['label'] }}" data-repeatable-required="{{ $isRequired ? 'true' : 'false' }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
            <legend>{{ $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@endif</legend>
            @if ($help)
                <small id="{{ $helpId }}" class="crm-muted crm-wizard-field-help">{{ $help }}</small>
            @endif
            @if ($fieldErrors !== [])
                <div id="{{ $fieldErrorId }}" class="crm-field-error" role="alert" tabindex="-1">
                    @foreach ($fieldErrors as $fieldError)
                        <div>{{ $fieldError['message'] ?? 'Review this section.' }}</div>
                    @endforeach
                </div>
            @endif

            <div data-repeatable-rows>
                @foreach ($rows as $index => $row)
                    <div class="crm-wizard-repeatable-card" data-repeatable-row>
                        <div class="crm-wizard-repeatable-card-heading">
                            <strong data-repeatable-row-heading>{{ $field['label'] }} {{ $index + 1 }}</strong>
                            <button type="button" class="btn btn-light crm-btn-light crm-wizard-remove-row" data-repeatable-remove aria-label="Remove {{ strtolower($field['label']) }} {{ $index + 1 }}" @if (count($rows) === 1 && $isRequired) disabled @endif>
                                <i class="bx bx-trash" aria-hidden="true"></i><span class="visually-hidden" data-repeatable-remove-label>Remove {{ strtolower($field['label']) }} {{ $index + 1 }}</span>
                            </button>
                        </div>
                        <div class="crm-wizard-repeatable-grid">
                            @include('client-setup.partials.structured-fields', [
                                'fields' => $field['fields'] ?? [],
                            'data' => $formData,
                            'prefix' => $fieldPath . '.' . $index,
                            'depth' => $depth + 1,
                            'errorDetails' => $errorDetails,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>

            <template data-repeatable-template>
                <div class="crm-wizard-repeatable-card" data-repeatable-row>
                    <div class="crm-wizard-repeatable-card-heading">
                        <strong data-repeatable-row-heading>{{ $field['label'] }}</strong>
                        <button type="button" class="btn btn-light crm-btn-light crm-wizard-remove-row" data-repeatable-remove aria-label="Remove {{ strtolower($field['label']) }}">
                            <i class="bx bx-trash" aria-hidden="true"></i><span class="visually-hidden" data-repeatable-remove-label>Remove {{ strtolower($field['label']) }}</span>
                        </button>
                    </div>
                    <div class="crm-wizard-repeatable-grid">
                        @include('client-setup.partials.structured-fields', [
                            'fields' => $field['fields'] ?? [],
                            'data' => $formData,
                            'prefix' => $fieldPath . '.' . $placeholder,
                            'depth' => $depth + 1,
                            'errorDetails' => $errorDetails,
                        ])
                    </div>
                </div>
            </template>

            <button type="button" class="btn btn-light crm-btn-light" data-repeatable-add>
                <i class="bx bx-plus"></i> Add {{ strtolower($field['label']) }}
            </button>
        </fieldset>
        @continue
    @endif

    <div
        class="crm-field crm-wizard-field crm-wizard-field-{{ $field['type'] }}"
        data-wizard-field-path="{{ $fieldPath }}"
        @if ($fieldErrors !== []) aria-invalid="true" @endif
        @if ($condition && $conditionFieldId)
            data-conditional-field
            data-condition-source="{{ $conditionFieldId }}"
            data-condition-operator="{{ $conditionOperator }}"
            data-condition-value="{{ base64_encode(json_encode($conditionValue)) }}"
        @endif
    >
        @if ($field['type'] === 'boolean')
            <input type="hidden" name="{{ $inputName }}" value="0">
            <div class="crm-wizard-checkbox-row">
                <input id="{{ $fieldId }}" name="{{ $inputName }}" type="checkbox" value="1" class="form-check-input" @checked((bool) $value) @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>
                <label for="{{ $fieldId }}">{{ $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@endif</label>
            </div>
        @else
            <label for="{{ $fieldId }}">{{ $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@endif</label>

            @if ($field['type'] === 'textarea')
                <textarea id="{{ $fieldId }}" name="{{ $inputName }}" rows="4" class="form-control" @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>{{ is_scalar($value) ? $value : '' }}</textarea>
            @elseif ($field['type'] === 'select')
                <select id="{{ $fieldId }}" name="{{ $inputName }}" class="form-select" @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>
                    <option value="">Select an option</option>
                    @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @elseif ($field['type'] === 'multiselect')
                @php $selectedValues = is_array($value) ? $value : []; @endphp
                <select id="{{ $fieldId }}" name="{{ $inputName }}[]" class="form-select" multiple @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>
                    @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, array_map('strval', $selectedValues), true))>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @else
                <input
                    id="{{ $fieldId }}"
                    name="{{ $inputName }}"
                    type="{{ $field['type'] }}"
                    value="{{ is_scalar($value) ? $value : '' }}"
                    class="form-control"
                    @if (isset($field['min'])) min="{{ $field['min'] }}" @endif
                    @if (isset($field['max'])) max="{{ $field['max'] }}" @endif
                    @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                    @if ($isRequired) required aria-required="true" @endif
                    @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                    @if ($fieldErrors !== []) aria-invalid="true" @endif
                    @if ($condition) data-conditional-required="true" @endif
                >
            @endif
        @endif

        @if ($help && $field['type'] !== 'repeatable')
            <small id="{{ $helpId }}" class="crm-muted crm-wizard-field-help">{{ $help }}</small>
        @endif
        @if ($fieldErrors !== [])
            <div id="{{ $fieldErrorId }}" class="crm-field-error" role="alert" tabindex="-1">
                @foreach ($fieldErrors as $fieldError)
                    <div>{{ $fieldError['message'] ?? 'Review this field.' }}</div>
                @endforeach
            </div>
        @endif
    </div>
@endforeach

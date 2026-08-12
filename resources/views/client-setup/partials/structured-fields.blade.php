@php
    $depth = $depth ?? 0;
    $prefix = $prefix ?? '';
    $formData = $data ?? [];
    $errorDetails = $errorDetails ?? [];
@endphp

@foreach ($fields as $fieldIndex => $field)
    @php
        $fieldPath = $prefix === '' ? $field['key'] : $prefix . '.' . $field['key'];
        $inputName = 'data[' . str_replace('.', '][', $fieldPath) . ']';
        $value = data_get($formData, $fieldPath, $field['default'] ?? null);
        $fieldId = 'client_setup_' . str_replace(['.', '[', ']', '__'], '_', $fieldPath);
        $isRequired = ($field['requirement'] ?? 'O') === 'R';
        $isOptional = ($field['requirement'] ?? 'O') === 'O';
        $requirementLabel = $isOptional ? '(Optional)' : null;
        $help = $field['help'] ?? null;
        $info = $field['info'] ?? null;
        $infoId = $info ? $fieldId . '_info' : null;
        $groupKey = $field['collapsible_group'] ?? null;
        $previousGroupKey = $fieldIndex > 0 ? ($fields[$fieldIndex - 1]['collapsible_group'] ?? null) : null;
        $nextGroupKey = $fields[$fieldIndex + 1]['collapsible_group'] ?? null;
        $opensCollapsibleGroup = $groupKey !== null && $groupKey !== $previousGroupKey;
        $closesCollapsibleGroup = $groupKey !== null && $groupKey !== $nextGroupKey;
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
        $placeholderExamples = [
            'institution_legal_name' => 'e.g. Gaborone College',
            'institution_common_name' => 'e.g. Gaborone College',
            'prepared_by_name' => 'e.g. Tsaone Obuseng',
            'prepared_by_position' => 'e.g. Registrar',
            'registration_number' => 'e.g. REG-2026-001',
            'accreditation_body' => 'e.g. BQA',
            'provider_number' => 'e.g. P12345',
            'academic_structure' => 'e.g. Faculty → department → programme → level → semester → module',
            'academic_year_naming' => 'e.g. 2026 or 2026/2027',
            'primary_intakes' => 'e.g. January and August',
            'active_intakes' => 'e.g. January and August',
            'entry_requirements' => 'e.g. minimum of 30 points',
            'completion_requirements' => 'e.g. 120 credits required for graduation',
            'markbook_lock_rule' => 'Explain when marks are locked and who can unlock them',
        ];
        $placeholder = $field['placeholder'] ?? ($placeholderExamples[$field['key']] ?? match ($field['type']) {
            'textarea' => 'Describe ' . strtolower($field['label']) . ' in plain language',
            'select' => 'Choose ' . strtolower($field['label']),
            'multiselect' => 'Choose one or more ' . strtolower($field['label']),
            'email' => 'e.g. administrator@school.org',
            'date' => 'e.g. 12/08/2026',
            'month' => 'e.g. August 2026',
            'number' => 'e.g. 2',
            'text' => 'Enter ' . strtolower($field['label']),
            default => null,
        });
    @endphp

    @if ($opensCollapsibleGroup)
        <details class="crm-wizard-collapsible-field" data-collapsible-field>
            <summary class="crm-wizard-collapsible-summary">
                <span>{{ $field['collapsible_group_label'] ?? $field['label'] }}</span>
                <span class="crm-wizard-collapsible-badge">(Optional)</span>
            </summary>
            <div class="crm-wizard-collapsible-body">
    @endif

    @if ($field['type'] === 'repeatable')
        @php
            $rows = is_array($value) && $value !== [] ? $value : ($isRequired ? [[]] : []);
            $placeholder = '__INDEX_' . $depth . '__';
        @endphp
        @if ($field['collapsible'] ?? false)
            <details class="crm-wizard-collapsible-field" data-collapsible-field @if (is_array($value) && $value !== [] || $fieldErrors !== []) open @endif>
                <summary class="crm-wizard-collapsible-summary">
                    <span>{{ $field['label'] }}</span>
                    @if ($requirementLabel)
                        <span class="crm-wizard-collapsible-badge">{{ $requirementLabel }}</span>
                    @endif
                </summary>
                <div class="crm-wizard-collapsible-body">
        @endif
        <fieldset class="crm-wizard-repeatable" data-repeatable-collection data-repeatable-label="{{ $field['label'] }}" data-repeatable-required="{{ $isRequired ? 'true' : 'false' }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
            <legend>{{ $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@elseif ($requirementLabel) <span class="crm-wizard-requirement-label">{{ $requirementLabel }}</span>@endif</legend>
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
        @if ($field['collapsible'] ?? false)
                </div>
            </details>
        @endif
        @if ($closesCollapsibleGroup)
                </div>
            </details>
        @endif
        @continue
    @endif

    @if ($field['collapsible'] ?? false)
        <details class="crm-wizard-collapsible-field" data-collapsible-field @if ($value || $fieldErrors !== []) open @endif>
            <summary class="crm-wizard-collapsible-summary">
                <span>{{ $field['label'] }}</span>
                @if ($requirementLabel)
                    <span class="crm-wizard-collapsible-badge">{{ $requirementLabel }}</span>
                @endif
            </summary>
            <div class="crm-wizard-collapsible-body">
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
                <label for="{{ $fieldId }}">{{ $field['control_label'] ?? $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@elseif ($requirementLabel) <span class="crm-wizard-requirement-label">{{ $requirementLabel }}</span>@endif</label>
            </div>
        @else
            <label for="{{ $fieldId }}">
                {{ $field['label'] }}@if ($isRequired) <span aria-hidden="true">*</span>@elseif ($requirementLabel) <span class="crm-wizard-requirement-label">{{ $requirementLabel }}</span>@endif
                @if ($info)
                    <span class="crm-wizard-info-tip" tabindex="0" role="img" aria-label="More information about {{ $field['label'] }}" aria-describedby="{{ $infoId }}">
                        <i class="bx bx-info-circle" aria-hidden="true"></i>
                        <span id="{{ $infoId }}" class="crm-wizard-info-popover" role="tooltip">
                            <strong>{{ $info['title'] }}</strong>
                            @foreach ($info['items'] as $infoItem)
                                <span>{{ $infoItem }}</span>
                            @endforeach
                        </span>
                    </span>
                @endif
            </label>

            @if ($field['type'] === 'textarea')
                <textarea id="{{ $fieldId }}" name="{{ $inputName }}" rows="4" class="form-control" placeholder="{{ $placeholder }}" @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>{{ is_scalar($value) ? $value : '' }}</textarea>
            @elseif ($field['type'] === 'select')
                <select id="{{ $fieldId }}" name="{{ $inputName }}" class="form-select" @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>
                    <option value="">{{ $placeholder }}</option>
                    @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @elseif ($field['type'] === 'multiselect')
                @php $selectedValues = is_array($value) ? $value : []; @endphp
                <select id="{{ $fieldId }}" name="{{ $inputName }}[]" class="form-select" multiple aria-label="{{ $placeholder }}" @if ($isRequired) required aria-required="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($fieldErrors !== []) aria-invalid="true" @endif @if ($condition) data-conditional-required="true" @endif>
                    <option value="" disabled hidden>{{ $placeholder }}</option>
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
                    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
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

    @if ($field['collapsible'] ?? false)
            </div>
        </details>
    @endif

    @if ($closesCollapsibleGroup)
            </div>
        </details>
    @endif
@endforeach

@php
    $sectionConfig = \App\Services\Pdp\PdpTemplateBlueprints::sharedRowSectionConfig($section->key) ?? [];
    $sectionTitles = [
        'performance_objectives' => 'Shared Performance Objectives',
        'coaching' => 'Shared Coaching / Development Objectives',
        'behavioural_attributes' => 'Shared Behavioural Attributes',
        'personal_development_goals' => 'Shared Personal Development Goals',
        'development_objectives' => 'Shared Development Objectives',
        'personal_attributes' => 'Shared Personal Attributes',
    ];
    $rowLabels = [
        'performance_objectives' => 'Objective',
        'coaching' => 'Coaching Item',
        'behavioural_attributes' => 'Attribute',
        'personal_development_goals' => 'Development Goal',
        'development_objectives' => 'Development Objective',
        'personal_attributes' => 'Attribute',
    ];
    $rowLabel = $rowLabels[$section->key] ?? 'Row';
    $rowHeadingKey = $sectionConfig['row_heading_key'] ?? $rowFields->first()?->key;
    $managedFieldKeys = $section->templateManagedFieldKeys();
    $planOwnedFields = $section->fields
        ->whereNull('parent_field_id')
        ->reject(fn ($field) => in_array($field->key, $managedFieldKeys, true))
        ->sortBy('sort_order')
        ->values();
    $managedFieldLabels = $rowFields->pluck('label')->all();
    $planOwnedLabels = $planOwnedFields->pluck('label')->all();
    $normalizeValue = function ($value): string {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    };
    $placeholders = [
        'performance_objectives' => [
            'objective_category' => 'Select the Part B category',
            'objective' => 'Enter the performance objective',
            'output' => 'Describe the expected output',
            'measure' => 'Describe how this objective will be measured',
            'target' => 'Enter the target for this objective',
        ],
    ];
    $placeholderForField = function ($field) use ($placeholders, $section): string {
        $configured = data_get($placeholders, $section->key . '.' . $field->key);
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $label = \Illuminate\Support\Str::of($field->label)->lower();

        return match ($field->field_type) {
            'select' => 'Select ' . $label,
            default => 'Enter ' . $label,
        };
    };
    $categoryField = $rowFields->firstWhere('key', 'objective_category');
    $categoryOptions = collect(is_array($categoryField?->options_json) ? $categoryField->options_json : []);
    $categoryLines = $categoryOptions
        ->map(fn ($option) => trim((string) data_get($option, 'label', data_get($option, 'value'))))
        ->filter()
        ->implode("\n");
@endphp

<div id="section-{{ $section->key }}" class="section-panel">
    <div class="section-panel-header">
        <div>
            <div class="section-panel-title">{{ $sectionTitles[$section->key] ?? ($section->label . ' Shared Rows') }}</div>
            <p class="section-panel-subtitle mb-0">
                The template defines {{ implode(', ', $managedFieldLabels) ?: 'the shared row definition fields' }}.
                @if ($planOwnedLabels !== [])
                    Plans only capture {{ implode(', ', $planOwnedLabels) }} during review and scoring.
                @else
                    Plans use read-only snapshots of these shared rows.
                @endif
            </p>
        </div>
    </div>
    <div class="section-panel-body">
        @if ($section->key === 'performance_objectives')
            <div class="help-text">
                <div class="help-title">Part B Categories</div>
                <div class="help-content">
                    Define the categories that group Part B objectives, such as Attendance, Academic Performance, or Stakeholder Involvement.
                    Each shared objective row must be assigned to one of these categories.
                </div>
            </div>

            @if ($isDraft)
                <form method="POST" action="{{ route('staff.pdp.templates.sections.builder.update', [$template, $section]) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="builder-{{ $section->key }}-categories">Part B Categories</label>
                            <textarea
                                id="builder-{{ $section->key }}-categories"
                                name="category_options"
                                rows="4"
                                class="form-control"
                                placeholder="Enter one category per line">{{ old('category_options', $categoryLines) }}</textarea>
                        </div>
                    </div>
                    <div class="form-actions mb-4">
                        @include('pdp.partials.submit-button', [
                            'label' => 'Save Categories',
                            'loadingText' => 'Saving categories...',
                            'icon' => 'fas fa-save',
                        ])
                    </div>
                </form>
            @elseif ($categoryOptions->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach ($categoryOptions as $option)
                        <span class="badge-soft badge-soft-primary">
                            {{ data_get($option, 'label', data_get($option, 'value')) }}
                        </span>
                    @endforeach
                </div>
            @endif
        @endif

        @if ($section->rows->isEmpty())
            <div class="empty-state">No shared {{ \Illuminate\Support\Str::of($rowLabel)->lower() }}s have been defined for this template section yet.</div>
        @else
            <div class="d-grid gap-3">
                @foreach ($section->rows as $row)
                    @php
                        $rowHeading = data_get($row->values_json, $rowHeadingKey, $rowLabel . ' Row');
                    @endphp
                    <div class="entry-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold">{{ $rowHeading }}</div>
                            <span class="badge-soft badge-soft-dark">Sort {{ $row->sort_order }}</span>
                        </div>

                        <form method="POST" action="{{ route('staff.pdp.templates.sections.rows.update', [$template, $section, $row]) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-grid">
                                @foreach ($rowFields as $field)
                                    @php
                                        $isWideField = in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true)
                                            || ($section->key === 'performance_objectives' && $field->key === 'objective');
                                        $inputId = 'row-' . $row->id . '-' . $field->key;
                                        $currentValue = old('values.' . $field->key, data_get($row->values_json, $field->key));
                                        $options = collect(is_array($field->options_json) ? $field->options_json : []);
                                        if (
                                            $field->field_type === 'select'
                                            && $currentValue !== null
                                            && $currentValue !== ''
                                            && !$options->contains(fn ($option) => $normalizeValue(data_get($option, 'value')) === $normalizeValue($currentValue))
                                        ) {
                                            $options->prepend(['value' => $currentValue, 'label' => $currentValue]);
                                        }
                                    @endphp
                                    <div class="form-group" style="{{ $isWideField ? 'grid-column: 1 / -1;' : '' }}">
                                        <label class="form-label" for="{{ $inputId }}">{{ $field->label }}</label>
                                        @switch($field->field_type)
                                            @case('textarea')
                                            @case('comment')
                                            @case('rich_text')
                                                <textarea id="{{ $inputId }}" name="values[{{ $field->key }}]" rows="3" class="form-control" placeholder="{{ $placeholderForField($field) }}" @disabled(!$isDraft)>{{ $currentValue }}</textarea>
                                                @break

                                            @case('number')
                                                <input id="{{ $inputId }}" type="number" step="any" name="values[{{ $field->key }}]" class="form-control"
                                                    value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}" @disabled(!$isDraft)>
                                                @break

                                            @case('date')
                                                <input id="{{ $inputId }}" type="date" name="values[{{ $field->key }}]" class="form-control"
                                                    value="{{ $currentValue instanceof \DateTimeInterface ? $currentValue->format('Y-m-d') : $currentValue }}" placeholder="{{ $placeholderForField($field) }}" @disabled(!$isDraft)>
                                                @break

                                            @case('select')
                                                <select id="{{ $inputId }}" name="values[{{ $field->key }}]" class="form-select" @disabled(!$isDraft)>
                                                    <option value="">{{ $placeholderForField($field) }}</option>
                                                    @foreach ($options as $option)
                                                        @php
                                                            $optionValue = $normalizeValue(data_get($option, 'value'));
                                                            $selectedValue = $normalizeValue($currentValue);
                                                        @endphp
                                                        <option value="{{ $optionValue }}" @selected($selectedValue === $optionValue)>
                                                            {{ data_get($option, 'label', $optionValue) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @default
                                                <input id="{{ $inputId }}" type="text" name="values[{{ $field->key }}]" class="form-control"
                                                    value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}" @disabled(!$isDraft)>
                                        @endswitch
                                    </div>
                                @endforeach

                                <div class="form-group">
                                    <label class="form-label" for="row-{{ $row->id }}-sort-order">Sort Order</label>
                                    <input id="row-{{ $row->id }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                        value="{{ old('sort_order', $row->sort_order) }}" @disabled(!$isDraft)>
                                </div>
                            </div>

                            @if ($isDraft)
                                <div class="form-actions d-flex flex-wrap gap-2">
                                    @include('pdp.partials.submit-button', [
                                        'label' => 'Save ' . $rowLabel,
                                        'loadingText' => 'Saving ' . \Illuminate\Support\Str::of($rowLabel)->lower() . '...',
                                        'icon' => 'fas fa-save',
                                    ])
                                </div>
                            @endif
                        </form>

                        @if ($isDraft)
                            <form method="POST" action="{{ route('staff.pdp.templates.sections.rows.destroy', [$template, $section, $row]) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bx bx-trash"></i> Remove {{ $rowLabel }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($isDraft)
            <div class="border-top pt-4 mt-4">
                <h6 class="section-title mt-0">Add Shared {{ $rowLabel }}</h6>
                <form method="POST" action="{{ route('staff.pdp.templates.sections.rows.store', [$template, $section]) }}">
                    @csrf
                    <div class="form-grid">
                        @foreach ($rowFields as $field)
                            @php
                                $isWideField = in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true)
                                    || ($section->key === 'performance_objectives' && $field->key === 'objective');
                                $inputId = 'new-row-' . $section->key . '-' . $field->key;
                                $options = collect(is_array($field->options_json) ? $field->options_json : []);
                                $currentValue = old('values.' . $field->key);
                            @endphp
                            <div class="form-group" style="{{ $isWideField ? 'grid-column: 1 / -1;' : '' }}">
                                <label class="form-label" for="{{ $inputId }}">{{ $field->label }}</label>
                                @switch($field->field_type)
                                    @case('textarea')
                                    @case('comment')
                                    @case('rich_text')
                                        <textarea id="{{ $inputId }}" name="values[{{ $field->key }}]" rows="3" class="form-control" placeholder="{{ $placeholderForField($field) }}">{{ $currentValue }}</textarea>
                                        @break

                                    @case('number')
                                        <input id="{{ $inputId }}" type="number" step="any" name="values[{{ $field->key }}]" class="form-control"
                                            value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}">
                                        @break

                                    @case('date')
                                        <input id="{{ $inputId }}" type="date" name="values[{{ $field->key }}]" class="form-control"
                                            value="{{ $currentValue instanceof \DateTimeInterface ? $currentValue->format('Y-m-d') : $currentValue }}" placeholder="{{ $placeholderForField($field) }}">
                                        @break

                                    @case('select')
                                        <select id="{{ $inputId }}" name="values[{{ $field->key }}]" class="form-select">
                                            <option value="">{{ $placeholderForField($field) }}</option>
                                            @foreach ($options as $option)
                                                @php
                                                    $optionValue = $normalizeValue(data_get($option, 'value'));
                                                    $selectedValue = $normalizeValue($currentValue);
                                                @endphp
                                                <option value="{{ $optionValue }}" @selected($selectedValue === $optionValue)>
                                                    {{ data_get($option, 'label', $optionValue) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break

                                    @default
                                        <input id="{{ $inputId }}" type="text" name="values[{{ $field->key }}]" class="form-control"
                                            value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}">
                                @endswitch
                            </div>
                        @endforeach

                        <div class="form-group">
                            <label class="form-label" for="new-row-{{ $section->key }}-sort-order">Sort Order</label>
                            <input id="new-row-{{ $section->key }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $section->rows->max('sort_order') ? $section->rows->max('sort_order') + 1 : 1) }}">
                        </div>
                    </div>
                    <div class="form-actions">
                        @include('pdp.partials.submit-button', [
                            'label' => 'Add Shared ' . $rowLabel,
                            'loadingText' => 'Adding ' . \Illuminate\Support\Str::of($rowLabel)->lower() . '...',
                            'icon' => 'bx bx-plus',
                            'variant' => 'btn-primary',
                        ])
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

@php
    $normalizeValue = function ($value): string {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    };
    $placeholderMap = [
        'objective_category' => 'Select the Part B category',
        'objective' => 'Enter the performance objective',
        'output' => 'Describe the output for this objective line',
        'measure' => 'Describe how this line will be measured',
        'target' => 'Enter the target for this measure',
    ];
    $placeholderForField = function ($field) use ($placeholderMap): string {
        $configured = $placeholderMap[$field->key] ?? null;
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
    $objectiveField = $rowFields->firstWhere('key', 'objective');
    $categoryOptions = collect(is_array($categoryField?->options_json) ? $categoryField->options_json : []);
    $categoryLines = $categoryOptions
        ->map(fn ($option) => trim((string) data_get($option, 'label', data_get($option, 'value'))))
        ->filter()
        ->implode("\n");
    $currentScope = (string) old('_template_builder_scope', '');
    $scopeMatches = fn (string $scope): bool => $currentScope === $scope;
    $scopedOldValue = function (string $scope, string $key, mixed $fallback = null) use ($currentScope): mixed {
        if ($currentScope !== $scope) {
            return $fallback;
        }

        return old($key, $fallback);
    };
    $scopedOldFieldValue = function (string $scope, string $fieldKey, mixed $fallback = null) use ($currentScope): mixed {
        if ($currentScope !== $scope) {
            return $fallback;
        }

        return old('values.' . $fieldKey, $fallback);
    };
    $categoryLabels = $categoryOptions->mapWithKeys(function ($option) use ($normalizeValue): array {
        $value = $normalizeValue(data_get($option, 'value'));

        return [$value => data_get($option, 'label', $value)];
    });
    $rowsByCategory = collect();

    foreach ($section->rows->sortBy('sort_order')->values() as $row) {
        $categoryValue = trim((string) data_get($row->values_json, 'objective_category'));
        $groupKey = $categoryValue !== '' ? $categoryValue : '__uncategorized';

        if (!$rowsByCategory->has($groupKey)) {
            $rowsByCategory->put($groupKey, collect());
        }

        $rowsByCategory->get($groupKey)->push($row);
    }

    $orderedGroupKeys = $categoryOptions
        ->map(fn ($option) => $normalizeValue(data_get($option, 'value')))
        ->filter()
        ->unique()
        ->values()
        ->all();

    $remainingGroupKeys = $rowsByCategory->keys()
        ->reject(fn ($key) => in_array($key, $orderedGroupKeys, true))
        ->values()
        ->all();

    $objectiveGroups = collect(array_merge($orderedGroupKeys, $remainingGroupKeys))
        ->map(function (string $groupKey) use ($categoryLabels, $rowsByCategory, $section): array {
            $entries = $rowsByCategory->get($groupKey, collect())
                ->sortBy(fn ($row) => sprintf('%05d-%010d', (int) $row->sort_order, (int) $row->id))
                ->values();

            return [
                'key' => $groupKey,
                'label' => $groupKey === '__uncategorized' ? 'Uncategorised' : ($categoryLabels[$groupKey] ?? $groupKey),
                'entries' => $entries,
                'can_add' => $groupKey !== '__uncategorized',
                'new_scope' => 'new-objective-' . $section->id . '-' . ($groupKey === '__uncategorized' ? 'uncategorized' : \Illuminate\Support\Str::slug($groupKey, '-')),
                'default_sort_order' => (($entries->max('sort_order') ?: 0) + 1),
            ];
        })
        ->values();
@endphp

<div id="section-{{ $section->key }}" class="section-panel">
    <div class="section-panel-header">
        <div>
            <div class="section-panel-title">Shared Performance Objectives</div>
            <p class="section-panel-subtitle mb-0">
                Define Part B once at the template level. Objectives are grouped by category so the template stays readable as it grows,
                and each objective can carry multiple output, measure, and target lines while plans only capture objective-level results,
                scoring, and comments.
            </p>
        </div>
    </div>
    <div class="section-panel-body">
        <div class="help-text">
            <div class="help-title">Part B Categories</div>
            <div class="help-content">
                Define the categories that group Part B objectives, such as Attendance, Academic Performance, or Stakeholder Involvement.
            </div>
        </div>

        @if ($isDraft)
            <form method="POST" action="{{ route('staff.pdp.templates.sections.builder.update', [$template, $section]) }}"
                data-template-builder-scope="builder-categories-{{ $section->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_template_builder_scope" value="builder-categories-{{ $section->id }}">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" for="builder-{{ $section->key }}-categories">Part B Categories</label>
                        <textarea id="builder-{{ $section->key }}-categories" name="category_options" rows="4"
                            class="form-control" placeholder="Enter one category per line">{{ $scopedOldValue('builder-categories-' . $section->id, 'category_options', $categoryLines) }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
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

        @if ($objectiveGroups->isEmpty())
            <div class="empty-state">No Part B categories are configured yet.</div>
        @else
            <div class="objective-category-grid">
                @foreach ($objectiveGroups as $group)
                    <div class="objective-category-panel" data-category-key="{{ $group['key'] }}">
                        <div class="objective-category-header">
                            <div>
                                <div class="objective-category-title">{{ $group['label'] }}</div>
                                <p class="objective-category-copy mb-0">
                                    Shared objectives added here will stay under this category when the template is applied to staff plans.
                                </p>
                            </div>
                            <span class="badge-soft badge-soft-dark">{{ $group['entries']->count() }} objectives</span>
                        </div>

                        <div class="objective-category-body">
                            @if ($group['entries']->isEmpty())
                                <div class="empty-state mb-0">No objectives have been defined in this category yet.</div>
                            @else
                                <div class="objective-category-stack">
                                    @foreach ($group['entries'] as $row)
                                        @php
                                            $objectiveValue = data_get($row->values_json, 'objective', 'Objective');
                                            $categoryValue = data_get($row->values_json, 'objective_category');
                                            $rowScope = 'objective-row-' . $row->id;
                                        @endphp
                                        <div class="entry-card" data-objective-row-id="{{ $row->id }}">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $objectiveValue }}</div>
                                                    @if ($categoryValue)
                                                        <span class="badge-soft badge-soft-primary">{{ $categoryValue }}</span>
                                                    @endif
                                                </div>
                                                <span class="badge-soft badge-soft-dark">Sort {{ $row->sort_order }}</span>
                                            </div>

                                            <form method="POST"
                                                action="{{ route('staff.pdp.templates.sections.rows.update', [$template, $section, $row]) }}"
                                                data-template-builder-scope="{{ $rowScope }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="_template_builder_scope" value="{{ $rowScope }}">

                                                <div class="form-grid">
                                                    @foreach ($rowFields as $field)
                                                        @php
                                                            $currentValue = $scopedOldFieldValue($rowScope, $field->key, data_get($row->values_json, $field->key));
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
                                                        <div class="form-group" style="{{ $field->key === 'objective' ? 'grid-column: 1 / -1;' : '' }}">
                                                            <label class="form-label" for="row-{{ $row->id }}-{{ $field->key }}">{{ $field->label }}</label>
                                                            @if ($field->field_type === 'select')
                                                                <select id="row-{{ $row->id }}-{{ $field->key }}" name="values[{{ $field->key }}]"
                                                                    class="form-select" @disabled(!$isDraft)>
                                                                    <option value="">{{ $placeholderForField($field) }}</option>
                                                                    @foreach ($options as $option)
                                                                        @php
                                                                            $optionValue = $normalizeValue(data_get($option, 'value'));
                                                                        @endphp
                                                                        <option value="{{ $optionValue }}" @selected($normalizeValue($currentValue) === $optionValue)>
                                                                            {{ data_get($option, 'label', $optionValue) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input id="row-{{ $row->id }}-{{ $field->key }}" type="text" name="values[{{ $field->key }}]"
                                                                    class="form-control" value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}"
                                                                    @disabled(!$isDraft)>
                                                            @endif
                                                        </div>
                                                    @endforeach

                                                    <div class="form-group">
                                                        <label class="form-label" for="row-{{ $row->id }}-sort-order">Sort Order</label>
                                                        <input id="row-{{ $row->id }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                                            value="{{ $scopedOldValue($rowScope, 'sort_order', $row->sort_order) }}" @disabled(!$isDraft)>
                                                    </div>
                                                </div>

                                                @if ($isDraft)
                                                    <div class="form-actions">
                                                        @include('pdp.partials.submit-button', [
                                                            'label' => 'Save Objective',
                                                            'loadingText' => 'Saving objective...',
                                                            'icon' => 'fas fa-save',
                                                        ])
                                                    </div>
                                                @endif
                                            </form>

                                            <div class="border-top pt-4 mt-4">
                                                <h6 class="section-title mt-0">Output, Measure, and Target Lines</h6>

                                                @if ($row->childRows->isEmpty())
                                                    <div class="empty-state">No output, measure, and target lines have been defined for this objective yet.</div>
                                                @else
                                                    <div class="d-grid gap-3 mb-4">
                                                        @foreach ($row->childRows as $childRow)
                                                            @php
                                                                $childScope = 'detail-row-' . $childRow->id;
                                                            @endphp
                                                            <div class="border rounded p-3">
                                                                <form method="POST"
                                                                    action="{{ route('staff.pdp.templates.sections.rows.update', [$template, $section, $childRow]) }}"
                                                                    data-template-builder-scope="{{ $childScope }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="_template_builder_scope" value="{{ $childScope }}">

                                                                    <div class="form-grid">
                                                                        @foreach ($detailFields as $field)
                                                                            @php
                                                                                $currentValue = $scopedOldFieldValue($childScope, $field->key, data_get($childRow->values_json, $field->key));
                                                                                $isWideField = in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true);
                                                                            @endphp
                                                                            <div class="form-group" style="{{ $isWideField ? 'grid-column: 1 / -1;' : '' }}">
                                                                                <label class="form-label" for="row-{{ $childRow->id }}-{{ $field->key }}">{{ $field->label }}</label>
                                                                                @if (in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true))
                                                                                    <textarea id="row-{{ $childRow->id }}-{{ $field->key }}" name="values[{{ $field->key }}]" rows="3"
                                                                                        class="form-control" placeholder="{{ $placeholderForField($field) }}" @disabled(!$isDraft)>{{ $currentValue }}</textarea>
                                                                                @else
                                                                                    <input id="row-{{ $childRow->id }}-{{ $field->key }}" type="text" name="values[{{ $field->key }}]"
                                                                                        class="form-control" value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}"
                                                                                        @disabled(!$isDraft)>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label class="form-label" for="row-{{ $childRow->id }}-sort-order">Sort Order</label>
                                                                            <input id="row-{{ $childRow->id }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                                                                value="{{ $scopedOldValue($childScope, 'sort_order', $childRow->sort_order) }}" @disabled(!$isDraft)>
                                                                        </div>
                                                                    </div>

                                                                    @if ($isDraft)
                                                                        <div class="form-actions">
                                                                            @include('pdp.partials.submit-button', [
                                                                                'label' => 'Save Detail Line',
                                                                                'loadingText' => 'Saving detail line...',
                                                                                'icon' => 'fas fa-save',
                                                                            ])
                                                                        </div>
                                                                    @endif
                                                                </form>

                                                                @if ($isDraft)
                                                                    <form method="POST"
                                                                        action="{{ route('staff.pdp.templates.sections.rows.destroy', [$template, $section, $childRow]) }}"
                                                                        class="mt-2">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger">
                                                                            <i class="bx bx-trash"></i> Remove Detail Line
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($isDraft)
                                                    @php
                                                        $newDetailScope = 'new-detail-row-' . $row->id;
                                                    @endphp
                                                    <form method="POST"
                                                        action="{{ route('staff.pdp.templates.sections.rows.store', [$template, $section]) }}"
                                                        data-template-builder-scope="{{ $newDetailScope }}">
                                                        @csrf
                                                        <input type="hidden" name="_template_builder_scope" value="{{ $newDetailScope }}">
                                                        <input type="hidden" name="parent_row_id" value="{{ $row->id }}">
                                                        <div class="form-grid">
                                                            @foreach ($detailFields as $field)
                                                                @php
                                                                    $isWideField = in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true);
                                                                    $currentValue = $scopedOldFieldValue($newDetailScope, $field->key);
                                                                @endphp
                                                                <div class="form-group" style="{{ $isWideField ? 'grid-column: 1 / -1;' : '' }}">
                                                                    <label class="form-label" for="new-detail-{{ $row->id }}-{{ $field->key }}">{{ $field->label }}</label>
                                                                    @if (in_array($field->field_type, ['textarea', 'comment', 'rich_text'], true))
                                                                        <textarea id="new-detail-{{ $row->id }}-{{ $field->key }}" name="values[{{ $field->key }}]" rows="3"
                                                                            class="form-control" placeholder="{{ $placeholderForField($field) }}">{{ $currentValue }}</textarea>
                                                                    @else
                                                                        <input id="new-detail-{{ $row->id }}-{{ $field->key }}" type="text" name="values[{{ $field->key }}]"
                                                                            class="form-control" value="{{ $currentValue }}" placeholder="{{ $placeholderForField($field) }}">
                                                                    @endif
                                                                </div>
                                                            @endforeach

                                                            <div class="form-group">
                                                                <label class="form-label" for="new-detail-{{ $row->id }}-sort-order">Sort Order</label>
                                                                <input id="new-detail-{{ $row->id }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                                                    value="{{ $scopedOldValue($newDetailScope, 'sort_order', $row->childRows->max('sort_order') ? $row->childRows->max('sort_order') + 1 : 1) }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-actions">
                                                            @include('pdp.partials.submit-button', [
                                                                'label' => 'Add Detail Line',
                                                                'loadingText' => 'Adding detail line...',
                                                                'icon' => 'bx bx-plus',
                                                                'variant' => 'btn-primary',
                                                            ])
                                                        </div>
                                                    </form>
                                                @endif
                                            </div>

                                            @if ($isDraft)
                                                <form method="POST" action="{{ route('staff.pdp.templates.sections.rows.destroy', [$template, $section, $row]) }}" class="mt-4">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="bx bx-trash"></i> Remove Objective
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($isDraft)
                                <div class="objective-category-footer">
                                    @if ($group['can_add'])
                                        <div class="objective-add-helper">
                                            Add a new objective directly under {{ $group['label'] }}. You can add the output, measure, and target lines immediately after saving it.
                                        </div>
                                        <form method="POST" action="{{ route('staff.pdp.templates.sections.rows.store', [$template, $section]) }}"
                                            data-template-builder-scope="{{ $group['new_scope'] }}">
                                            @csrf
                                            <input type="hidden" name="_template_builder_scope" value="{{ $group['new_scope'] }}">
                                            <input type="hidden" name="values[objective_category]" value="{{ $group['key'] }}">
                                            <div class="form-grid">
                                                @if ($objectiveField)
                                                    <div class="form-group" style="grid-column: 1 / -1;">
                                                        <label class="form-label" for="new-row-{{ $section->key }}-{{ \Illuminate\Support\Str::slug($group['key'], '-') }}-objective">{{ $objectiveField->label }}</label>
                                                        <input id="new-row-{{ $section->key }}-{{ \Illuminate\Support\Str::slug($group['key'], '-') }}-objective"
                                                            type="text" name="values[objective]" class="form-control"
                                                            value="{{ $scopedOldFieldValue($group['new_scope'], 'objective') }}"
                                                            placeholder="{{ $placeholderForField($objectiveField) }}">
                                                    </div>
                                                @endif

                                                <div class="form-group">
                                                    <label class="form-label" for="new-row-{{ $section->key }}-{{ \Illuminate\Support\Str::slug($group['key'], '-') }}-sort-order">Sort Order</label>
                                                    <input id="new-row-{{ $section->key }}-{{ \Illuminate\Support\Str::slug($group['key'], '-') }}-sort-order" type="number" min="1" name="sort_order" class="form-control"
                                                        value="{{ $scopedOldValue($group['new_scope'], 'sort_order', $group['default_sort_order']) }}">
                                                </div>
                                            </div>
                                            <div class="form-actions">
                                                @include('pdp.partials.submit-button', [
                                                    'label' => 'Add Objective',
                                                    'loadingText' => 'Adding objective...',
                                                    'icon' => 'bx bx-plus',
                                                    'variant' => 'btn-primary',
                                                ])
                                            </div>
                                        </form>
                                    @else
                                        <div class="objective-add-helper mb-0">
                                            This block only shows legacy uncategorised objectives. Assign a configured category when editing them so they move into the right Part B group.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

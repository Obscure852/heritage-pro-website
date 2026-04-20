<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    @php
        $periodScopedFields = $sectionData['manual_fields']
            ->filter(fn ($field) => $field->period_scope !== null)
            ->values();
        $editableKeys = collect($sectionData['entry_editable_field_keys'])
            ->flatten()
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->unique()
            ->values();
        $hasUnlockedPeriodScopedFields = $periodScopedFields
            ->pluck('key')
            ->intersect($editableKeys)
            ->isNotEmpty();
        $showPeriodScopedGuidance = $periodScopedFields->isNotEmpty() && !$hasUnlockedPeriodScopedFields;
    @endphp
    <div class="section-panel-header d-flex flex-wrap justify-content-between gap-3">
        <div>
            <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
            <p class="section-panel-subtitle mb-0">
                Generic section entry management driven by the template definition.
            </p>
        </div>
        @if ($sectionData['section']->is_repeatable)
            <span class="badge-soft badge-soft-dark align-self-start">{{ $sectionData['entries']->count() }} entries</span>
        @endif
    </div>

    <div class="section-panel-body">
        @unless ($sectionData['can_manage_entries'])
            <div class="help-text">
                <div class="help-title">Read Only</div>
                <div class="help-content">This section is currently read-only for your role or the relevant review period is not open.</div>
            </div>
        @endunless

        @if ($showPeriodScopedGuidance)
            <div class="help-text">
                <div class="help-title">How Ratings Unlock</div>
                <div class="help-content">
                    Ratings in this section only become selectable when the relevant review is opened from the Review Timeline.
                    Mid-Year and Year-End ratings can only be entered by the supervisor or an authorized PDP official.
                </div>
            </div>
        @endif

        @if ($sectionData['section']->key === 'performance_objectives' && $sectionData['grouped_entries']->isNotEmpty())
            @forelse ($sectionData['grouped_entries'] as $group)
                <div class="mb-4">
                    <h6 class="section-title mt-0">{{ $group['label'] }}</h6>
                    <div class="d-grid gap-3">
                        @foreach ($group['entries'] as $entry)
                            @include('pdp.sections.partials.repeatable-entry-card', [
                                'plan' => $plan,
                                'sectionData' => $sectionData,
                                'entry' => $entry,
                                'viewService' => $viewService,
                                'entryNumber' => $sectionData['entries']->search(fn ($candidate) => $candidate->id === $entry->id) + 1,
                            ])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state">No entries have been added to this section yet.</div>
            @endforelse
        @else
            @forelse ($sectionData['entries'] as $entry)
                @include('pdp.sections.partials.repeatable-entry-card', [
                    'plan' => $plan,
                    'sectionData' => $sectionData,
                    'entry' => $entry,
                    'viewService' => $viewService,
                    'entryNumber' => $loop->iteration,
                ])
            @empty
                <div class="empty-state">No entries have been added to this section yet.</div>
            @endforelse
        @endif

        @if ($sectionData['supports_entry_crud'] && $sectionData['can_create_entries'])
            <div class="border-top pt-4 mt-4">
                <h6 class="section-title mt-0">Add Custom Entry</h6>
                <form method="POST"
                    action="{{ route('staff.pdp.plans.sections.entries.store', [$plan, $sectionData['section']->key]) }}">
                    @csrf
                    <div class="form-grid">
                        @foreach ($sectionData['manual_fields'] as $field)
                            <div class="form-group" style="{{ in_array($field->field_type, ['textarea', 'rich_text', 'comment', 'structured_table'], true) ? 'grid-column: 1 / -1;' : '' }}">
                                @include('pdp.partials.field-input', [
                                    'field' => $field,
                                    'value' => $viewService->inputValue($field),
                                    'options' => $sectionData['field_options'][$field->key] ?? [],
                                    'viewService' => $viewService,
                                    'namePrefix' => 'values',
                                    'formKey' => 'new-' . $sectionData['section']->key,
                                    'disabled' => !in_array($field->key, $sectionData['editable_field_keys'], true),
                                ])
                            </div>
                        @endforeach
                    </div>

                    <div class="form-actions">
                        @include('pdp.partials.submit-button', [
                            'label' => 'Add Entry',
                            'loadingText' => 'Adding entry...',
                            'icon' => 'bx bx-plus',
                            'variant' => 'btn-primary',
                        ])
                    </div>
                </form>
            </div>
        @endif
    </div>
</section>

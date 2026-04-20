<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    <div class="section-panel-header d-flex flex-wrap justify-content-between gap-3">
        <div>
            <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
            <p class="section-panel-subtitle mb-0">
                Objective structure comes from the bound template. Supervisors score at the objective level after reviewing the output, measure, and target lines.
            </p>
        </div>
        <span class="badge-soft badge-soft-dark align-self-start">{{ $sectionData['entries']->count() }} objectives</span>
    </div>

    <div class="section-panel-body">
        @unless ($sectionData['can_manage_entries'])
            <div class="help-text">
                <div class="help-title">Read Only</div>
                <div class="help-content">This section is currently read-only for your role or the relevant review period is not open.</div>
            </div>
        @endunless

        @forelse ($sectionData['grouped_entries'] as $group)
            <div class="mb-4">
                <h6 class="section-title mt-0">{{ $group['label'] }}</h6>
                <div class="d-grid gap-3">
                    @foreach ($group['entries'] as $entry)
                        @php
                            $entryEditableKeys = $sectionData['entry_editable_field_keys'][$entry->id] ?? [];
                            $objectiveValue = data_get($entry->values_json, 'objective', 'Objective');
                            $isTemplateRow = $entry->origin_type === \App\Models\Pdp\PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT;
                        @endphp
                        <div class="entry-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">{{ $objectiveValue }}</div>
                                @if ($isTemplateRow)
                                    <span class="badge-soft badge-soft-primary">Template Objective</span>
                                @endif
                            </div>

                            @if ($isTemplateRow)
                                <div class="help-text mb-3">
                                    <div class="help-title">Template Snapshot</div>
                                    <div class="help-content">
                                        The objective definition and its output, measure, and target lines were copied from the active template when this plan was created.
                                    </div>
                                </div>
                            @endif

                            @if ($entry->childEntries->isNotEmpty())
                                <div class="table-responsive mb-4">
                                    <table class="table align-middle builder-table">
                                        <thead>
                                            <tr>
                                                @foreach ($sectionData['detail_fields'] as $field)
                                                    <th>{{ $field->label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($entry->childEntries as $childEntry)
                                                <tr>
                                                    @foreach ($sectionData['detail_fields'] as $field)
                                                        <td>{{ $viewService->displayValue($field, data_get($childEntry->values_json, $field->key)) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state mb-4">No output, measure, and target lines were snapped into this objective.</div>
                            @endif

                            <form method="POST"
                                action="{{ route('staff.pdp.plans.sections.entries.update', [$plan, $sectionData['section']->key, $entry]) }}">
                                @csrf
                                @method('PUT')

                                <div class="form-grid">
                                    @foreach ($sectionData['evaluation_fields'] as $field)
                                        @php
                                            $isWideField = in_array($field->field_type, ['textarea', 'rich_text', 'comment', 'structured_table'], true);
                                        @endphp
                                        <div class="form-group" style="{{ $isWideField ? 'grid-column: 1 / -1;' : '' }}">
                                            @include('pdp.partials.field-input', [
                                                'field' => $field,
                                                'value' => $viewService->inputValue($field, $entry->values_json),
                                                'options' => $sectionData['field_options'][$field->key] ?? [],
                                                'viewService' => $viewService,
                                                'namePrefix' => 'values',
                                                'formKey' => 'entry-' . $entry->id,
                                                'disabled' => !in_array($field->key, $entryEditableKeys, true),
                                            ])
                                        </div>
                                    @endforeach
                                </div>

                                @if ($entryEditableKeys !== [])
                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Save Objective Review',
                                            'loadingText' => 'Saving review...',
                                            'icon' => 'fas fa-save',
                                        ])
                                    </div>
                                @endif
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty-state">No performance objectives have been added to this section yet.</div>
        @endforelse
    </div>
</section>

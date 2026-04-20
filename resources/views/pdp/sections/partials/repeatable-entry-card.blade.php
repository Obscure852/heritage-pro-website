@php
    $entryEditableKeys = $sectionData['entry_editable_field_keys'][$entry->id] ?? [];
    $canDeleteEntry = $sectionData['entry_can_delete'][$entry->id] ?? false;
    $originLabel = $sectionData['entry_origin_labels'][$entry->id] ?? null;
    $isTemplateRow = $entry->origin_type === \App\Models\Pdp\PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT;
@endphp

<div class="entry-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="fw-semibold">Entry {{ $entryNumber }}</div>
            @if ($originLabel)
                <span class="badge-soft {{ $isTemplateRow ? 'badge-soft-primary' : 'badge-soft-dark' }}">{{ $originLabel }}</span>
            @endif
        </div>
        @if ($canDeleteEntry)
            <form method="POST"
                action="{{ route('staff.pdp.plans.sections.entries.destroy', [$plan, $sectionData['section']->key, $entry]) }}">
                @csrf
                @method('DELETE')
                <div class="action-buttons">
                    <button type="submit" class="btn btn-outline-danger" title="Delete Entry">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </form>
        @endif
    </div>

    @if ($isTemplateRow)
        <div class="help-text mb-3">
            <div class="help-title">Template Snapshot</div>
            <div class="help-content">
                The shared definition fields for this row were copied from the active template when this plan was created and cannot be changed here.
            </div>
        </div>
    @endif

    <form method="POST"
        action="{{ route('staff.pdp.plans.sections.entries.update', [$plan, $sectionData['section']->key, $entry]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            @foreach ($sectionData['manual_fields'] as $field)
                @php
                    $isWideField = in_array($field->field_type, ['textarea', 'rich_text', 'comment', 'structured_table'], true)
                        || ($sectionData['section']->key === 'performance_objectives' && $field->key === 'objective');
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
                    'label' => 'Save Entry',
                    'loadingText' => 'Saving entry...',
                    'icon' => 'fas fa-save',
                ])
            </div>
        @endif
    </form>
</div>

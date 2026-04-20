<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    <div class="section-panel-header">
        <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
        <p class="section-panel-subtitle mb-0">Single-record section driven by the template's manual entry fields.</p>
    </div>
    <div class="section-panel-body">
        @unless ($sectionData['can_manage_entries'])
            <div class="help-text">
                <div class="help-title">Read Only</div>
                <div class="help-content">This section is currently read-only for your role or workflow stage.</div>
            </div>
        @endunless

        <form method="POST"
            action="{{ $sectionData['single_entry']
                ? route('staff.pdp.plans.sections.entries.update', [$plan, $sectionData['section']->key, $sectionData['single_entry']])
                : route('staff.pdp.plans.sections.entries.store', [$plan, $sectionData['section']->key]) }}">
            @csrf
            @if ($sectionData['single_entry'])
                @method('PUT')
            @endif

            <div class="form-grid">
                @foreach ($sectionData['manual_fields'] as $field)
                    <div class="form-group" style="grid-column: 1 / -1;">
                        @include('pdp.partials.field-input', [
                            'field' => $field,
                            'value' => $viewService->inputValue($field, $sectionData['single_entry']?->values_json),
                            'options' => $sectionData['field_options'][$field->key] ?? [],
                            'viewService' => $viewService,
                            'namePrefix' => 'values',
                            'formKey' => 'single-' . $sectionData['section']->key,
                            'disabled' => !in_array($field->key, $sectionData['editable_field_keys'], true),
                        ])
                    </div>
                @endforeach
            </div>

            @if ($sectionData['can_manage_entries'])
                <div class="form-actions">
                    @include('pdp.partials.submit-button', [
                        'label' => $sectionData['single_entry'] ? 'Save Section' : 'Create Section Entry',
                        'loadingText' => $sectionData['single_entry'] ? 'Saving section...' : 'Creating section entry...',
                        'icon' => 'fas fa-save',
                    ])
                </div>
            @endif
        </form>
    </div>
</section>

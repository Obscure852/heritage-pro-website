<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    <div class="section-panel-header">
        <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
        <p class="section-panel-subtitle mb-0">Template-calculated summary values are displayed here.</p>
    </div>
    <div class="section-panel-body">
        <div class="row g-3">
            @foreach ($sectionData['fields'] as $field)
                <div class="col-md-4">
                    @include('pdp.partials.field-display', [
                        'field' => $field,
                        'value' => data_get($sectionData['mapped_values'], $field->key),
                        'viewService' => $viewService,
                    ])
                </div>
            @endforeach
        </div>
    </div>
</section>

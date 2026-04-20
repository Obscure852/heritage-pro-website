@inject('pdpSettings', 'App\Services\Pdp\PdpSettingsService')
@php
    $generalSettings = $pdpSettings->generalSettings();
    $guidanceLines = collect(preg_split('/\r\n|\r|\n/', (string) ($generalSettings['general_guidance'] ?? '')))
        ->map(fn ($line) => trim($line))
        ->filter()
        ->values();
@endphp
<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    <div class="section-panel-header">
        <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
        <p class="section-panel-subtitle mb-0">Mapped and computed staff information from the active template.</p>
    </div>
    <div class="section-panel-body">
        @if ($sectionData['section']->key === 'employee_information' && $guidanceLines->isNotEmpty())
            <div class="help-text mb-3">
                <div class="help-title">General Guidance</div>
                <div class="help-content">
                    <ol class="mb-0 ps-3">
                        @foreach ($guidanceLines as $guidanceLine)
                            <li>{{ $guidanceLine }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif
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

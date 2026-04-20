@extends('layouts.master')

@section('title', 'PDP Template')
@section('page_title', 'PDP Template')
@section('css')
    @include('pdp.partials.theme-css')
    <style>
        .pdp-theme .template-view-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .pdp-theme .template-view-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        .pdp-theme .template-view-stats {
            width: 100%;
            max-width: 360px;
        }

        .pdp-theme .template-view-stats .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .pdp-theme .template-view-stats .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #fff;
        }

        .pdp-theme .template-view-stats .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .pdp-theme .template-view-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .pdp-theme .builder-table th,
        .pdp-theme .builder-table td {
            vertical-align: top;
        }

        .pdp-theme .builder-table .form-control,
        .pdp-theme .builder-table .form-select {
            min-width: 160px;
        }

        .pdp-theme .template-grid {
            display: grid;
            gap: 24px;
        }

        .pdp-theme .objective-category-grid {
            display: grid;
            gap: 20px;
        }

        .pdp-theme .objective-category-panel {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            background: #fff;
            overflow: hidden;
        }

        .pdp-theme .objective-category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .pdp-theme .objective-category-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .pdp-theme .objective-category-copy {
            max-width: 780px;
            color: #64748b;
            font-size: 0.92rem;
        }

        .pdp-theme .objective-category-body {
            padding: 20px;
        }

        .pdp-theme .objective-category-stack {
            display: grid;
            gap: 16px;
        }

        .pdp-theme .objective-category-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .pdp-theme .objective-add-helper {
            margin-bottom: 12px;
            color: #64748b;
            font-size: 0.92rem;
        }

        .pdp-theme .danger-card {
            border: 1px solid rgba(220, 38, 38, 0.25);
            background: #fff7f7;
        }

        .pdp-theme .impact-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .pdp-theme .impact-box {
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 12px;
            background: #fff;
        }

        .pdp-theme .impact-box-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #991b1b;
            line-height: 1;
        }

        .pdp-theme .impact-box-label {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #7f1d1d;
        }

        .pdp-theme .json-textarea {
            min-height: 110px;
            font-family: Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
        }

        @media (max-width: 992px) {
            .pdp-theme .template-view-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .pdp-theme .template-view-stats {
                max-width: none;
            }

            .pdp-theme .impact-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .pdp-theme .template-view-toolbar {
                justify-content: flex-start;
            }

            .pdp-theme .impact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @inject('pdpTemplateService', 'App\Services\Pdp\PdpTemplateService')
    @component('components.breadcrumb')
        @slot('li_1')
            PDP Templates
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.settings.index', ['tab' => 'templates']) }}
        @endslot
        @slot('title')
            PDP Template
        @endslot
    @endcomponent

    @php
        $templateRowSections = $template->sections
            ->sortBy('sequence')
            ->filter(fn ($section) => $section->usesTemplateRows())
            ->values();
        $performanceObjectiveSection = $templateRowSections->firstWhere('key', 'performance_objectives');
        $genericTemplateRowSections = $templateRowSections->reject(fn ($section) => $section->key === 'performance_objectives')->values();
        $employeeSection = $template->sections->firstWhere('key', 'employee_information');
        $templateRowFieldsBySection = $templateRowSections->mapWithKeys(fn ($section) => [
            $section->id => $pdpTemplateService->templateRowFields($section),
        ]);
        $templateChildRowFieldsBySection = $templateRowSections->mapWithKeys(fn ($section) => [
            $section->id => $pdpTemplateService->templateChildRowFields($section),
        ]);
        $employeeFields = $employeeSection ? $employeeSection->fields->whereNull('parent_field_id')->sortBy('sort_order')->values() : collect();
        $isDraft = $template->status === \App\Models\Pdp\PdpTemplate::STATUS_DRAFT;
        $isPublished = $template->status === \App\Models\Pdp\PdpTemplate::STATUS_PUBLISHED;
        $hasDeletionUsage = collect($deletionImpact)->sum() > 0;
    @endphp

    <div class="pdp-theme">
        <div class="page-shell mb-4">
            <div class="page-shell-header">
                <div class="template-view-header">
                    <div class="template-view-copy">
                        <div class="page-shell-title">{{ $template->name }}</div>
                        <div class="page-subtitle">
                            {{ $template->code }} | {{ $template->template_family_key }} | version {{ $template->version }} | {{ ucfirst($template->status) }}
                        </div>
                    </div>
                    <div class="template-view-stats">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $template->sections->count() }}</h4>
                                    <small>Sections</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $template->rollouts_count }}</h4>
                                    <small>Rollouts</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $template->plans_count }}</h4>
                                    <small>Plans Bound</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-shell-body">
                <div class="help-text mb-0">
                    <div class="help-title">Bounded Template Builder</div>
                    <div class="help-content">
                        {{ $template->description ?: 'This template controls the section order, shared objectives, review periods, scoring model, and sign-off flow for bound PDP plans.' }}
                        Source: {{ $template->source_reference ?: 'N/A' }}.
                        Created by: {{ $template->createdBy?->full_name ?? 'System' }}.
                        @if ($isDraft)
                            This draft can still be edited before publication.
                        @else
                            Published and archived templates stay immutable so historical plans remain auditable.
                        @endif
                    </div>
                </div>

                <div class="template-view-toolbar">
                    <form method="POST" action="{{ route('staff.pdp.templates.clone', $template) }}">
                        @csrf
                        @include('pdp.partials.submit-button', [
                            'label' => 'Clone Draft',
                            'loadingText' => 'Cloning draft...',
                            'icon' => 'bx bx-copy',
                            'variant' => 'btn-light',
                        ])
                    </form>

                    @if ($isDraft)
                        <form method="POST" action="{{ route('staff.pdp.templates.publish', $template) }}">
                            @csrf
                            @include('pdp.partials.submit-button', [
                                'label' => 'Publish',
                                'loadingText' => 'Publishing...',
                                'icon' => 'bx bx-upload',
                                'variant' => 'btn-primary',
                            ])
                        </form>
                    @endif

                    @if (!$template->is_default && $template->status !== \App\Models\Pdp\PdpTemplate::STATUS_ARCHIVED)
                        <form method="POST" action="{{ route('staff.pdp.templates.archive', $template) }}">
                            @csrf
                            @include('pdp.partials.submit-button', [
                                'label' => 'Archive',
                                'loadingText' => 'Archiving...',
                                'icon' => 'bx bx-archive',
                                'variant' => 'btn-outline-danger',
                            ])
                        </form>
                    @endif

                    @if ($hasDeletionUsage)
                        <a href="{{ route('staff.pdp.templates.show', ['template' => $template, 'confirm_delete' => 1]) }}" class="btn btn-outline-danger">
                            <i class="bx bx-trash"></i> Delete
                        </a>
                    @else
                        <form method="POST" action="{{ route('staff.pdp.templates.destroy', $template) }}">
                            @csrf
                            @method('DELETE')
                            @include('pdp.partials.submit-button', [
                                'label' => 'Delete',
                                'loadingText' => 'Deleting...',
                                'icon' => 'bx bx-trash',
                                'variant' => 'btn-outline-danger',
                            ])
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if ($isPublished && !$template->is_default)
            <div class="section-panel mb-4">
                <div class="section-panel-header">
                    <div>
                        <div class="section-panel-title">Activate and Apply Template</div>
                        <p class="section-panel-subtitle mb-0">
                            Activating this template makes it current and immediately provisions a new staff cycle from this version.
                        </p>
                    </div>
                </div>
                <div class="section-panel-body">
                    <form method="POST" action="{{ route('staff.pdp.templates.activate', $template) }}">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Cycle Label</label>
                                <input type="text" name="label" class="form-control"
                                    value="{{ old('label', $template->name . ' Cycle ' . now()->year) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cycle Year</label>
                                <input type="number" name="cycle_year" min="2000" max="2100" class="form-control"
                                    value="{{ old('cycle_year', now()->year) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Fallback Supervisor</label>
                                <select name="fallback_supervisor_user_id" class="form-select">
                                    <option value="">Use current administrator</option>
                                    <option value="{{ auth()->id() }}" @selected((string) old('fallback_supervisor_user_id', auth()->id()) === (string) auth()->id())>
                                        {{ auth()->user()?->full_name }}{{ auth()->user()?->position ? ' | ' . auth()->user()->position : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-grid mt-3">
                            <div class="form-group">
                                <label class="form-label">Plan Period Start</label>
                                <input type="date" name="plan_period_start" class="form-control"
                                    value="{{ old('plan_period_start', $suggestedDates['start']->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Plan Period End</label>
                                <input type="date" name="plan_period_end" class="form-control"
                                    value="{{ old('plan_period_end', $suggestedDates['end']->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="auto_provision_new_staff" value="0">
                                    <input type="checkbox" class="form-check-input" id="auto_provision_new_staff"
                                        name="auto_provision_new_staff" value="1"
                                        @checked((bool) old('auto_provision_new_staff', true))>
                                    <label class="form-check-label" for="auto_provision_new_staff">
                                        Auto-provision future eligible staff
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            @include('pdp.partials.submit-button', [
                                'label' => 'Activate & Apply to Staff',
                                'loadingText' => 'Activating template...',
                                'icon' => 'bx bx-check-circle',
                                'variant' => 'btn-primary',
                            ])
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($showDeleteWarning)
            <div class="section-panel danger-card mb-4">
                <div class="section-panel-header">
                    <div>
                        <div class="section-panel-title text-danger">Delete Template and All Bound Records</div>
                        <p class="section-panel-subtitle mb-0">
                            This action permanently removes the template and every rollout, plan, review, entry, and signature tied to it.
                        </p>
                    </div>
                </div>
                <div class="section-panel-body">
                    <div class="help-text mb-0">
                        <div class="help-title">Destructive Action</div>
                        <div class="help-content">
                            If you continue, this template version will be hard deleted. Historical PDP plans from this version will also be permanently removed.
                        </div>
                    </div>

                    <div class="impact-grid">
                        <div class="impact-box">
                            <div class="impact-box-value">{{ $deletionImpact['rollouts'] }}</div>
                            <span class="impact-box-label">Rollouts</span>
                        </div>
                        <div class="impact-box">
                            <div class="impact-box-value">{{ $deletionImpact['plans'] }}</div>
                            <span class="impact-box-label">Plans</span>
                        </div>
                        <div class="impact-box">
                            <div class="impact-box-value">{{ $deletionImpact['reviews'] }}</div>
                            <span class="impact-box-label">Reviews</span>
                        </div>
                        <div class="impact-box">
                            <div class="impact-box-value">{{ $deletionImpact['section_entries'] }}</div>
                            <span class="impact-box-label">Entries</span>
                        </div>
                        <div class="impact-box">
                            <div class="impact-box-value">{{ $deletionImpact['signatures'] }}</div>
                            <span class="impact-box-label">Signatures</span>
                        </div>
                    </div>

                    <div class="form-actions mt-4">
                        <a href="{{ route('staff.pdp.templates.show', $template) }}" class="btn btn-light">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        <form method="POST" action="{{ route('staff.pdp.templates.destroy', $template) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirm_delete" value="1">
                            @include('pdp.partials.submit-button', [
                                'label' => 'Delete Template Permanently',
                                'loadingText' => 'Deleting template...',
                                'icon' => 'bx bx-trash',
                                'variant' => 'btn-outline-danger',
                            ])
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="template-grid">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="section-panel h-100">
                        <div class="section-panel-header">
                            <div>
                                <div class="section-panel-title">Template Metadata</div>
                                <p class="section-panel-subtitle mb-0">Core identity for this template family and version.</p>
                            </div>
                        </div>
                        <div class="section-panel-body">
                            <form method="POST" action="{{ route('staff.pdp.templates.update', $template) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Template Family Key</label>
                                        <input type="text" name="template_family_key" class="form-control"
                                            value="{{ old('template_family_key', $template->template_family_key) }}" @disabled(!$isDraft)>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Template Code</label>
                                        <input type="text" name="code" class="form-control"
                                            value="{{ old('code', $template->code) }}" @disabled(!$isDraft)>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Template Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $template->name) }}" @disabled(!$isDraft)>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Source Reference</label>
                                        <input type="text" name="source_reference" class="form-control"
                                            value="{{ old('source_reference', $template->source_reference) }}" @disabled(!$isDraft)>
                                    </div>
                                    <div class="form-group" style="grid-column: 1 / -1;">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="4" class="form-control" @disabled(!$isDraft)>{{ old('description', $template->description) }}</textarea>
                                    </div>
                                </div>
                                @if ($isDraft)
                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Save Metadata',
                                            'loadingText' => 'Saving metadata...',
                                            'icon' => 'fas fa-save',
                                        ])
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="section-panel h-100">
                        <div class="section-panel-header">
                            <div>
                                <div class="section-panel-title">Version History</div>
                                <p class="section-panel-subtitle mb-0">Other versions in this template family.</p>
                            </div>
                        </div>
                        <div class="section-panel-body">
                            <div class="list-group list-group-flush">
                                @foreach ($relatedTemplates as $relatedTemplate)
                                    <a href="{{ route('staff.pdp.templates.show', $relatedTemplate) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $relatedTemplate->code }}</div>
                                            <div class="text-muted small">{{ ucfirst($relatedTemplate->status) }}</div>
                                        </div>
                                        <span class="badge-soft badge-soft-dark">v{{ $relatedTemplate->version }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-panel">
                <div class="section-panel-header">
                    <div>
                        <div class="section-panel-title">Section Order and Labels</div>
                        <p class="section-panel-subtitle mb-0">Bounded sections stay fixed, but draft labels and sequence can be adjusted here.</p>
                    </div>
                </div>
                <div class="section-panel-body">
                    <form method="POST" action="{{ route('staff.pdp.templates.sections.update', $template) }}">
                        @csrf
                        @method('PUT')
                        <div class="table-responsive">
                            <table class="table align-middle builder-table">
                                <thead>
                                    <tr>
                                        <th>Section Key</th>
                                        <th>Label</th>
                                        <th>Sequence</th>
                                        <th>Repeatable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($template->sections as $section)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $section->key }}</div>
                                                <div class="text-muted small">{{ $section->section_type }}</div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control"
                                                    name="sections[{{ $section->id }}][label]"
                                                    value="{{ old("sections.{$section->id}.label", $section->label) }}"
                                                    @disabled(!$isDraft)>
                                            </td>
                                            <td>
                                                <input type="number" min="1" class="form-control"
                                                    name="sections[{{ $section->id }}][sequence]"
                                                    value="{{ old("sections.{$section->id}.sequence", $section->sequence) }}"
                                                    @disabled(!$isDraft)>
                                            </td>
                                            <td>{{ $section->is_repeatable ? 'Yes' : 'No' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($isDraft)
                            <div class="form-actions">
                                @include('pdp.partials.submit-button', [
                                    'label' => 'Save Section Layout',
                                    'loadingText' => 'Saving sections...',
                                    'icon' => 'fas fa-save',
                                ])
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            @if ($employeeSection)
                <div class="section-panel">
                    <div class="section-panel-header">
                        <div>
                            <div class="section-panel-title">Employee Information Mapping</div>
                            <p class="section-panel-subtitle mb-0">Control how staff profile fields resolve inside this template.</p>
                        </div>
                    </div>
                    <div class="section-panel-body">
                        <form method="POST" action="{{ route('staff.pdp.templates.employee-information.update', $template) }}">
                            @csrf
                            @method('PUT')
                            <div class="table-responsive">
                                <table class="table align-middle builder-table">
                                    <thead>
                                        <tr>
                                            <th>Field Key</th>
                                            <th>Label</th>
                                            <th>Source</th>
                                            <th>Mapping Key</th>
                                            <th>Required</th>
                                            <th>Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employeeFields as $field)
                                            <tr>
                                                <td>{{ $field->key }}</td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        name="fields[{{ $field->id }}][label]"
                                                        value="{{ old("fields.{$field->id}.label", $field->label) }}"
                                                        @disabled(!$isDraft)>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="fields[{{ $field->id }}][mapping_source]" @disabled(!$isDraft)>
                                                        @foreach (['user' => 'User', 'settings' => 'Settings', 'profile_metadata' => 'Profile Metadata', 'plan' => 'Plan', 'computed' => 'Computed'] as $mappingSource => $mappingLabel)
                                                            <option value="{{ $mappingSource }}"
                                                                @selected(old("fields.{$field->id}.mapping_source", $field->mapping_source) === $mappingSource)>
                                                                {{ $mappingLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        name="fields[{{ $field->id }}][mapping_key]"
                                                        value="{{ old("fields.{$field->id}.mapping_key", $field->mapping_key) }}"
                                                        @disabled(!$isDraft)>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="fields[{{ $field->id }}][required]" @disabled(!$isDraft)>
                                                        <option value="0" @selected(!(bool) old("fields.{$field->id}.required", $field->required))>No</option>
                                                        <option value="1" @selected((bool) old("fields.{$field->id}.required", $field->required))>Yes</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" min="1" class="form-control"
                                                        name="fields[{{ $field->id }}][sort_order]"
                                                        value="{{ old("fields.{$field->id}.sort_order", $field->sort_order) }}"
                                                        @disabled(!$isDraft)>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($isDraft)
                                <div class="form-actions">
                                    @include('pdp.partials.submit-button', [
                                        'label' => 'Save Employee Mapping',
                                        'loadingText' => 'Saving employee mapping...',
                                        'icon' => 'fas fa-save',
                                    ])
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif

            @if ($performanceObjectiveSection)
                @include('pdp.templates.partials.performance-objectives-panel', [
                    'template' => $template,
                    'section' => $performanceObjectiveSection,
                    'rowFields' => $templateRowFieldsBySection->get($performanceObjectiveSection->id, collect()),
                    'detailFields' => $templateChildRowFieldsBySection->get($performanceObjectiveSection->id, collect()),
                    'isDraft' => $isDraft,
                ])
            @endif

            @foreach ($genericTemplateRowSections as $templateRowSection)
                @include('pdp.templates.partials.section-rows-panel', [
                    'template' => $template,
                    'section' => $templateRowSection,
                    'rowFields' => $templateRowFieldsBySection->get($templateRowSection->id, collect()),
                    'isDraft' => $isDraft,
                ])
            @endforeach

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="section-panel h-100">
                        <div class="section-panel-header">
                            <div>
                                <div class="section-panel-title">Review Periods</div>
                                <p class="section-panel-subtitle mb-0">Draft-only period cadence and window configuration.</p>
                            </div>
                        </div>
                        <div class="section-panel-body">
                            <form method="POST" action="{{ route('staff.pdp.templates.periods.update', $template) }}">
                                @csrf
                                @method('PUT')
                                <div class="table-responsive">
                                    <table class="table align-middle builder-table">
                                        <thead>
                                            <tr>
                                                <th>Key</th>
                                                <th>Label</th>
                                                <th>Sequence</th>
                                                <th>Summary Label</th>
                                                <th>Include Final</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($template->periods as $period)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="periods[{{ $period->id }}][key]"
                                                            value="{{ old("periods.{$period->id}.key", $period->key) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="periods[{{ $period->id }}][label]"
                                                            value="{{ old("periods.{$period->id}.label", $period->label) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="number" min="1" class="form-control"
                                                            name="periods[{{ $period->id }}][sequence]"
                                                            value="{{ old("periods.{$period->id}.sequence", $period->sequence) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="periods[{{ $period->id }}][summary_label]"
                                                            value="{{ old("periods.{$period->id}.summary_label", $period->summary_label) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <select class="form-select" name="periods[{{ $period->id }}][include_in_final_score]" @disabled(!$isDraft)>
                                                            <option value="0" @selected(!(bool) old("periods.{$period->id}.include_in_final_score", $period->include_in_final_score))>No</option>
                                                            <option value="1" @selected((bool) old("periods.{$period->id}.include_in_final_score", $period->include_in_final_score))>Yes</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                @if ($isDraft)
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="form-grid">
                                                                <div class="form-group">
                                                                    <label class="form-label">Window Type</label>
                                                                    <input type="text" class="form-control"
                                                                        name="periods[{{ $period->id }}][window_type]"
                                                                        value="{{ old("periods.{$period->id}.window_type", $period->window_type) }}">
                                                                </div>
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Open Rule JSON</label>
                                                                    <textarea class="form-control json-textarea" name="periods[{{ $period->id }}][open_rule_json]">{{ old("periods.{$period->id}.open_rule_json", $period->open_rule_json ? json_encode($period->open_rule_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Close Rule JSON</label>
                                                                    <textarea class="form-control json-textarea" name="periods[{{ $period->id }}][close_rule_json]">{{ old("periods.{$period->id}.close_rule_json", $period->close_rule_json ? json_encode($period->close_rule_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($isDraft)
                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Save Review Periods',
                                            'loadingText' => 'Saving periods...',
                                            'icon' => 'fas fa-save',
                                        ])
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="section-panel mb-4">
                        <div class="section-panel-header">
                            <div>
                                <div class="section-panel-title">Scoring Configuration</div>
                                <p class="section-panel-subtitle mb-0">Bounded rating schemes used by the objective and summary calculations.</p>
                            </div>
                        </div>
                        <div class="section-panel-body">
                            <form method="POST" action="{{ route('staff.pdp.templates.ratings.update', $template) }}">
                                @csrf
                                @method('PUT')
                                <div class="table-responsive">
                                    <table class="table align-middle builder-table">
                                        <thead>
                                            <tr>
                                                <th>Scheme</th>
                                                <th>Input Type</th>
                                                <th>Weight</th>
                                                <th>Rounding</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($template->ratingSchemes as $scheme)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $scheme->key }}</div>
                                                        <input type="text" class="form-control mt-2"
                                                            name="schemes[{{ $scheme->id }}][label]"
                                                            value="{{ old("schemes.{$scheme->id}.label", $scheme->label) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="schemes[{{ $scheme->id }}][input_type]"
                                                            value="{{ old("schemes.{$scheme->id}.input_type", $scheme->input_type) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="1" class="form-control"
                                                            name="schemes[{{ $scheme->id }}][weight]"
                                                            value="{{ old("schemes.{$scheme->id}.weight", $scheme->weight) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="schemes[{{ $scheme->id }}][rounding_rule]"
                                                            value="{{ old("schemes.{$scheme->id}.rounding_rule", $scheme->rounding_rule) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                </tr>
                                                @if ($isDraft)
                                                    <tr>
                                                        <td colspan="4">
                                                            <div class="form-grid">
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Scale Config JSON</label>
                                                                    <textarea class="form-control json-textarea" name="schemes[{{ $scheme->id }}][scale_config_json]">{{ old("schemes.{$scheme->id}.scale_config_json", $scheme->scale_config_json ? json_encode($scheme->scale_config_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Conversion Config JSON</label>
                                                                    <textarea class="form-control json-textarea" name="schemes[{{ $scheme->id }}][conversion_config_json]">{{ old("schemes.{$scheme->id}.conversion_config_json", $scheme->conversion_config_json ? json_encode($scheme->conversion_config_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Formula Config JSON</label>
                                                                    <textarea class="form-control json-textarea" name="schemes[{{ $scheme->id }}][formula_config_json]">{{ old("schemes.{$scheme->id}.formula_config_json", $scheme->formula_config_json ? json_encode($scheme->formula_config_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                                    <label class="form-label">Band Config JSON</label>
                                                                    <textarea class="form-control json-textarea" name="schemes[{{ $scheme->id }}][band_config_json]">{{ old("schemes.{$scheme->id}.band_config_json", $scheme->band_config_json ? json_encode($scheme->band_config_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($isDraft)
                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Save Scoring',
                                            'loadingText' => 'Saving scoring...',
                                            'icon' => 'fas fa-save',
                                        ])
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <div class="section-panel">
                        <div class="section-panel-header">
                            <div>
                                <div class="section-panel-title">Approval Steps</div>
                                <p class="section-panel-subtitle mb-0">Sequence and role ownership for signatures on plans from this template.</p>
                            </div>
                        </div>
                        <div class="section-panel-body">
                            <form method="POST" action="{{ route('staff.pdp.templates.approvals.update', $template) }}">
                                @csrf
                                @method('PUT')
                                <div class="table-responsive">
                                    <table class="table align-middle builder-table">
                                        <thead>
                                            <tr>
                                                <th>Key</th>
                                                <th>Label</th>
                                                <th>Role</th>
                                                <th>Sequence</th>
                                                <th>Required</th>
                                                <th>Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($template->approvalSteps as $step)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="steps[{{ $step->id }}][key]"
                                                            value="{{ old("steps.{$step->id}.key", $step->key) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="steps[{{ $step->id }}][label]"
                                                            value="{{ old("steps.{$step->id}.label", $step->label) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="steps[{{ $step->id }}][role_type]"
                                                            value="{{ old("steps.{$step->id}.role_type", $step->role_type) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <input type="number" min="1" class="form-control"
                                                            name="steps[{{ $step->id }}][sequence]"
                                                            value="{{ old("steps.{$step->id}.sequence", $step->sequence) }}"
                                                            @disabled(!$isDraft)>
                                                    </td>
                                                    <td>
                                                        <select class="form-select" name="steps[{{ $step->id }}][required]" @disabled(!$isDraft)>
                                                            <option value="0" @selected(!(bool) old("steps.{$step->id}.required", $step->required))>No</option>
                                                            <option value="1" @selected((bool) old("steps.{$step->id}.required", $step->required))>Yes</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="form-select" name="steps[{{ $step->id }}][comment_required]" @disabled(!$isDraft)>
                                                            <option value="0" @selected(!(bool) old("steps.{$step->id}.comment_required", $step->comment_required))>No</option>
                                                            <option value="1" @selected((bool) old("steps.{$step->id}.comment_required", $step->comment_required))>Yes</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                @if ($isDraft)
                                                    <tr>
                                                        <td colspan="6">
                                                            <div class="form-group">
                                                                <label class="form-label">Period Scope</label>
                                                                <input type="text" class="form-control"
                                                                    name="steps[{{ $step->id }}][period_scope]"
                                                                    value="{{ old("steps.{$step->id}.period_scope", $step->period_scope) }}">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($isDraft)
                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Save Approval Steps',
                                            'loadingText' => 'Saving approval steps...',
                                            'icon' => 'fas fa-save',
                                        ])
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('pdp.partials.theme-script')
@endsection

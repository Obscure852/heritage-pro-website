@extends('layouts.crm-wizard')

@section('title', 'Client Setup — ' . $stageDefinition['label'])

@section('wizard_header')
    <div class="crm-wizard-header-row">
        <div class="crm-wizard-header">
            <p class="crm-wizard-eyebrow">Stage {{ $stageDefinition['number'] + 1 }} of {{ count($navigation) }}</p>
            <h1>{{ $stageDefinition['label'] }}</h1>
            <p>{{ $stageDefinition['description'] }}</p>
        </div>
        <div class="crm-wizard-progress" aria-label="Academic setup progress">
            <div class="crm-wizard-progress-meta">
                <span>Academic readiness</span>
                <strong>{{ $wizardProgress['percentage'] }}%</strong>
            </div>
            <div class="crm-wizard-progress-track" role="progressbar" aria-label="Academic setup progress" aria-valuenow="{{ $wizardProgress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                <span style="width: {{ $wizardProgress['percentage'] }}%"></span>
            </div>
            <small>{{ $wizardProgress['completed'] }} of {{ $wizardProgress['total'] }} required stages complete</small>
        </div>
    </div>
@endsection

@section('content')
    @php
        $currentIndex = array_search($stage, array_column($navigation, 'key'), true);
        $validationErrors = $stageProgress?->validation_errors ?? [];
        $validationErrorDetails = $stageProgress?->validation_error_details ?? [];
    @endphp

    <div class="crm-wizard-mobile-stage crm-card">
        <label for="wizard_stage_selector" class="crm-kicker">Jump to stage</label>
        <select id="wizard_stage_selector" class="form-select" data-wizard-stage-selector>
            @foreach ($navigation as $item)
                <option
                    value="{{ route('client-setup.stage', ['token' => request()->route('token'), 'stage' => $item['key']]) }}"
                    @selected($item['key'] === $stage)
                    @disabled($item['locked'])
                >
                    {{ $item['number'] + 1 }}. {{ $item['label'] }}{{ $item['optional'] ? ' · Optional' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="crm-wizard-layout">
        <aside class="crm-wizard-rail" aria-label="Client setup stages">
            <div class="crm-wizard-rail-heading">
                <div>
                    <p class="crm-kicker">Setup journey</p>
                    <h2>Configuration stages</h2>
                </div>
                <span class="crm-wizard-rail-count">{{ $wizardProgress['completed'] }}/{{ $wizardProgress['total'] }}</span>
            </div>

            <nav class="crm-wizard-stage-list">
                @foreach ($navigation as $item)
                    @php
                        $itemClasses = 'crm-wizard-stage-item state-' . $item['state'];
                    @endphp
                    @if ($item['locked'])
                        <span class="{{ $itemClasses }}" aria-disabled="true">
                    @else
                        <a class="{{ $itemClasses }}" href="{{ route('client-setup.stage', ['token' => request()->route('token'), 'stage' => $item['key']]) }}" @if ($item['current']) aria-current="step" @endif>
                    @endif
                        <span class="crm-wizard-stage-marker" aria-hidden="true">
                            @if ($item['state'] === 'complete')
                                <i class="bx bx-check"></i>
                            @elseif ($item['state'] === 'locked')
                                <i class="bx bx-lock-alt"></i>
                            @else
                                {{ $item['number'] + 1 }}
                            @endif
                        </span>
                        <span class="crm-wizard-stage-copy">
                            <strong>{{ $item['short_label'] }}</strong>
                            <small>{{ $item['state'] === 'current' ? 'Current stage' : ucfirst(str_replace('_', ' ', $item['state'])) }}{{ $item['optional'] ? ' · Optional' : '' }}</small>
                        </span>
                        @if ($item['current'])
                            <span class="crm-wizard-stage-current" aria-hidden="true"><i class="bx bx-right-arrow-alt"></i></span>
                        @endif
                    @if ($item['locked'])
                        </span>
                    @else
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="crm-wizard-rail-note">
                <i class="bx bx-shield-quarter" aria-hidden="true"></i>
                <span>Your draft is saved privately and can be continued later.</span>
            </div>
        </aside>

        <main class="crm-wizard-main" tabindex="-1" data-wizard-main>
            <div class="crm-wizard-stage-meta">
                <span><strong>Institution:</strong> {{ $invitation->contact_name ?: $invitation->email }}</span>
                <span><strong>Last saved:</strong> {{ $stageProgress?->last_saved_at?->format('d M Y, H:i') ?: 'Not saved yet' }}</span>
                <span><strong>Draft activity:</strong> {{ $submission->last_activity_at?->diffForHumans() ?: 'Not started' }}</span>
            </div>

            @if ($validationErrors !== [])
                <div class="crm-wizard-validation-summary" role="alert" tabindex="-1" data-wizard-validation-summary>
                    <strong>This stage needs attention.</strong>
                    <ul>
                        @foreach ($validationErrors as $validationError)
                            @php
                                $validationMessage = is_array($validationError) ? json_encode($validationError) : (string) $validationError;
                                $validationDetail = collect($validationErrorDetails)->first(static fn (array $detail): bool => ($detail['message'] ?? null) === $validationMessage);
                                $validationPath = is_array($validationDetail) ? ($validationDetail['path'] ?? null) : null;
                                $validationTargetId = $validationPath
                                    ? 'client_setup_' . str_replace(['.', '[', ']', '__'], '_', $validationPath) . '_error'
                                    : null;
                            @endphp
                            <li>
                                @if ($validationTargetId)
                                    <a href="#{{ $validationTargetId }}">{{ $validationMessage }}</a>
                                @else
                                    {{ $validationMessage }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($changeRequests->isNotEmpty())
                <section class="crm-wizard-validation-summary" aria-labelledby="change-request-heading">
                    <strong id="change-request-heading">Implementation team requests</strong>
                    <p>Please answer each request below, then save the relevant stage again.</p>
                    @foreach ($changeRequests as $changeRequest)
                        <div class="crm-wizard-change-request">
                            <p><strong>{{ $changeRequest->field_key ?: ($changeRequest->stage_key ?: 'General review') }}</strong> — {{ $changeRequest->body }}</p>
                            <form method="POST" action="{{ route('client-setup.change-request.respond', ['token' => request()->route('token'), 'changeRequest' => $changeRequest]) }}" class="crm-form">
                                @csrf
                                <div class="crm-field">
                                    <label for="client_response_{{ $changeRequest->id }}">Your response</label>
                                    <textarea id="client_response_{{ $changeRequest->id }}" name="client_response" rows="3" required placeholder="Explain what you changed or provide the requested clarification."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bx bx-send"></i> Send response</button>
                            </form>
                        </div>
                    @endforeach
                </section>
            @endif

            <section class="crm-wizard-stage-panel" aria-labelledby="wizard-stage-heading">
                <div class="crm-wizard-stage-panel-header">
                    <div>
                        <p class="crm-kicker">Draft foundation</p>
                        <h2 id="wizard-stage-heading" tabindex="-1" data-wizard-main-heading>{{ $stageDefinition['label'] }}</h2>
                        <p>{{ $stageDefinition['description'] }}</p>
                    </div>
                    @if ($stageProgress)
                        <span class="crm-pill {{ $stageProgress->status === 'complete' ? 'success' : 'primary' }}">{{ ucfirst(str_replace('_', ' ', $stageProgress->status)) }}</span>
                    @else
                        <span class="crm-pill muted">Not started</span>
                    @endif
                </div>

                <div class="crm-wizard-brief">
                    <div class="crm-wizard-brief-icon" aria-hidden="true"><i class="bx bx-list-check"></i></div>
                    <div>
                        <strong>What this stage covers</strong>
                        <ul>
                            @foreach ($stageDefinition['help'] as $helpText)
                                <li>{{ $helpText }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @if ($academicLocked)
                    <div class="crm-wizard-lock-notice" role="status">
                        <i class="bx bx-lock-alt" aria-hidden="true"></i>
                        <div>
                            <strong>Academic configuration submitted</strong>
                            <p>This academic stage is frozen while the implementation team reviews the submitted revision. Supplemental stages remain available.</p>
                        </div>
                    </div>
                @else
                <div class="crm-wizard-stage-content @if (in_array($stage, ['results_lifecycle', 'migration', 'evidence_signoff'], true)) has-stage-attachments @endif">
                <form method="POST" action="{{ route('client-setup.stage.save', ['token' => request()->route('token'), 'stage' => $stage]) }}" class="crm-form" data-wizard-form>
                    @csrf
                    @method('PATCH')

                    @if ($structuredFields !== [])
                        <div class="crm-wizard-structured-fields">
                            @include('client-setup.partials.structured-fields', [
                                'fields' => $structuredFields,
                                'data' => old('data', $stagePayload),
                                'errorDetails' => $validationErrorDetails,
                            ])
                        </div>
                    @else
                        <div class="crm-field">
                            <label for="payload_json">Stage information</label>
                            <textarea id="payload_json" name="payload_json" rows="14" class="form-control" placeholder="Enter the stage information as a JSON object" aria-describedby="payload_json_help" required>{{ old('payload_json', json_encode($stagePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                            <small id="payload_json_help" class="crm-muted">This temporary foundation editor preserves the stage payload while the structured stage schema is completed.</small>
                        </div>
                    @endif

                    <input type="hidden" name="action" value="save" data-wizard-action-input>

                    <div class="crm-field-grid">
                        <div class="crm-field">
                            <label for="status">Save status</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach (config('client_setup.stage_statuses', []) as $status)
                                    <option value="{{ $status }}" @selected(old('status', $stageProgress?->status ?: 'in_progress') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <small class="crm-muted">Use Complete only when the current information is ready for review.</small>
                        </div>
                    </div>

                    <div class="crm-wizard-form-actions">
                        @if ($previousNavigation)
                            <a href="{{ route('client-setup.stage', ['token' => request()->route('token'), 'stage' => $previousNavigation['key']]) }}" class="btn btn-light crm-btn-light">
                                <i class="bx bx-left-arrow-alt"></i> Back
                            </a>
                        @else
                            <span></span>
                        @endif

                        <div class="crm-wizard-form-actions-right">
                            <button type="submit" data-wizard-submit-action="exit" class="btn btn-light crm-btn-light btn-loading">
                                <span class="btn-text"><i class="fas fa-save" aria-hidden="true"></i> Save and exit</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                            <button type="submit" data-wizard-submit-action="save" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save" aria-hidden="true"></i> Save progress</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                            <button type="submit" data-wizard-submit-action="continue" class="btn btn-primary btn-loading">
                                <span class="btn-text">Save and continue <i class="bx bx-right-arrow-alt"></i></span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
                @endif

                @if ($stage === 'results_lifecycle' && ! $academicLocked)
                    <section class="crm-wizard-attachment-panel" aria-labelledby="results-documents-heading">
                        <div class="crm-wizard-stage-panel-header">
                            <div>
                                <p class="crm-kicker">Required supporting documents</p>
                                <h2 id="results-documents-heading">Attach result slip and transcript</h2>
                                <p>Choose a current example or approved template, then click its Upload button. Both attachments must be uploaded before this stage can be completed.</p>
                            </div>
                        </div>

                        <div class="crm-field-grid">
                            @foreach ([
                                ['category' => 'Result slip', 'id' => 'result_slip_attachment', 'label' => 'Result slip attachment', 'help' => 'Upload the current approved result slip template or an example PDF.'],
                                ['category' => 'Transcript', 'id' => 'transcript_attachment', 'label' => 'Transcript attachment', 'help' => 'Upload the current approved transcript template or an example PDF.'],
                            ] as $document)
                                @php($attachment = $submission->attachments->firstWhere('category', $document['category']))
                                <div class="crm-card" style="margin:0">
                                    <div class="crm-card-title">
                                        <div>
                                            <p class="crm-kicker">Required attachment</p>
                                            <h3>{{ $document['category'] }}</h3>
                                        </div>
                                    </div>
                                    @if ($attachment)
                                        <div class="crm-meta-list" style="margin-bottom:1rem">
                                            <div class="crm-meta-row"><span>Uploaded</span><strong>{{ $attachment->original_name }}</strong></div>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('client-setup.attachment-upload', ['token' => request()->route('token')]) }}" enctype="multipart/form-data" class="crm-form" data-attachment-upload-form>
                                        @csrf
                                        <input type="hidden" name="category" value="{{ $document['category'] }}">
                                        <input type="hidden" name="requirement" value="required">
                                        <input type="hidden" name="return_stage" value="results_lifecycle">
                                        @include('client-setup.partials.file-upload', [
                                            'id' => $document['id'],
                                            'name' => 'attachment',
                                            'label' => $document['label'],
                                            'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg',
                                            'help' => $document['help'],
                                        ])
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="bx bx-upload"></i> Upload {{ strtolower($document['category']) }}</span>
                                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading...</span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($stage === 'migration')
                    <section class="crm-wizard-attachment-panel" aria-labelledby="migration-heading">
                        <div class="crm-wizard-stage-panel-header">
                            <div>
                                <p class="crm-kicker">Version {{ config('client_setup.template_version') }} templates</p>
                                <h2 id="migration-heading">Prepare staff and student data</h2>
                                <p>Download the clean workbook, keep its headings unchanged, and upload it here for a row-level validation report. Uploading a file never imports records automatically.</p>
                            </div>
                        </div>
                        <div class="crm-field-grid">
                            @foreach (['staff' => 'Staff workbook', 'students' => 'Student workbook'] as $kind => $label)
                                @php($latestUpload = $submission->migrationUploads->firstWhere('kind', $kind))
                                <div class="crm-card" style="margin:0">
                                    <div class="crm-card-title"><div><p class="crm-kicker">Migration template</p><h3>{{ $label }}</h3></div></div>
                                    <p class="crm-muted">Includes a clean data sheet and an Instructions/Data Dictionary sheet.</p>
                                    <a href="{{ route('client-setup.migration-template.download', ['token' => request()->route('token'), 'kind' => $kind]) }}" class="crm-migration-template-download">
                                        <span class="crm-migration-template-download-icon" aria-hidden="true"><i class="bx bx-download"></i></span>
                                        <span class="crm-migration-template-download-copy">
                                            <strong>Download template</strong>
                                            <small>Get the clean {{ strtolower($label) }} workbook</small>
                                        </span>
                                        <i class="bx bx-right-arrow-alt crm-migration-template-download-arrow" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('client-setup.migration-upload', ['token' => request()->route('token')]) }}" enctype="multipart/form-data" class="crm-form">
                                        @csrf
                                        <input type="hidden" name="kind" value="{{ $kind }}">
                                        @include('client-setup.partials.file-upload', [
                                            'id' => 'migration_file_' . $kind,
                                            'name' => 'file',
                                            'label' => 'Upload completed workbook',
                                            'accept' => '.xlsx,.xls,.csv',
                                            'required' => true,
                                            'help' => 'XLSX, XLS or CSV up to ' . number_format(config('client_setup.migration_upload_max_kb') / 1024, 0) . ' MB.',
                                        ])
                                        <button type="submit" class="btn btn-primary btn-loading"><span class="btn-text"><i class="bx bx-upload"></i> Validate upload</span><span class="btn-spinner d-none">Validating...</span></button>
                                    </form>
                                    @if ($latestUpload)
                                        <div class="crm-meta-list" style="margin-top:1rem">
                                            <div class="crm-meta-row"><span>Latest file</span><strong>{{ $latestUpload->original_name }}</strong></div>
                                            <div class="crm-meta-row"><span>Validation</span><strong>{{ ucfirst(str_replace('_', ' ', $latestUpload->validation_status)) }} · {{ $latestUpload->valid_row_count }}/{{ $latestUpload->row_count }} valid rows</strong></div>
                                        </div>
                                        @if ($latestUpload->validation_errors)
                                            <div class="crm-wizard-validation-summary" style="margin-top:1rem">
                                                <strong>Validation issues</strong>
                                                <ul>
                                                    @foreach (array_slice($latestUpload->validation_errors, 0, 8) as $error)
                                                        <li>Row {{ $error['row'] ?? '?' }}: {{ implode(' ', $error['messages'] ?? []) }}</li>
                                                    @endforeach
                                                </ul>
                                                @if (count($latestUpload->validation_errors) > 8)<small>Showing the first 8 issues. The full report is available to the implementation team.</small>@endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($stage === 'evidence_signoff' && ! $academicLocked)
                    <section class="crm-wizard-attachment-panel" aria-labelledby="attachment-heading">
                        <div class="crm-wizard-stage-panel-header">
                            <div>
                                <p class="crm-kicker">Supporting evidence</p>
                                <h2 id="attachment-heading">Upload policy and sample documents</h2>
                                <p>Files are stored privately and queued for security scanning before CRM review.</p>
                            </div>
                        </div>

                        @if ($submission->attachments->isNotEmpty())
                            <ul class="crm-wizard-attachment-list">
                                @foreach ($submission->attachments as $attachment)
                                    <li>
                                        <i class="bx bx-file" aria-hidden="true"></i>
                                        <span><strong>{{ $attachment->original_name }}</strong><small>{{ $attachment->category }} · {{ ucfirst($attachment->scan_status) }}</small></span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST" action="{{ route('client-setup.attachment-upload', ['token' => request()->route('token')]) }}" enctype="multipart/form-data" class="crm-form">
                            @csrf
                            <div class="crm-field-grid">
                                <div class="crm-field">
                                    <label for="attachment_category">Document category</label>
                                    <input id="attachment_category" name="category" type="text" class="form-control" placeholder="e.g. assessment policy" required>
                                </div>
                                <div class="crm-field">
                                    <label for="attachment_requirement">Requirement level</label>
                                    <select id="attachment_requirement" name="requirement" class="form-select">
                                        <option value="required">Required</option>
                                        <option value="optional" selected>Optional</option>
                                        <option value="if_migrating">If migrating</option>
                                        <option value="if_applicable">If applicable</option>
                                    </select>
                                </div>
                            </div>
                            @include('client-setup.partials.file-upload', [
                                'id' => 'attachment_file',
                                'name' => 'attachment',
                                'label' => 'Select a policy or sample document',
                                'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg',
                                'help' => 'Accepted: PDF, Word, Excel, CSV and image files up to 20 MB.',
                            ])
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="bx bx-upload"></i> Upload document</span>
                                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading...</span>
                                </button>
                            </div>
                        </form>
                    </section>
                @endif
                </div>
            </section>

            <section class="crm-wizard-saved-summary" aria-labelledby="saved-summary-heading">
                <div>
                    <p class="crm-kicker">Resume-safe draft</p>
                    <h2 id="saved-summary-heading">Your progress stays with this setup link</h2>
                </div>
                <p>Optional finance, migration and integration details can be completed later. They do not prevent the required academic stages from moving forward.</p>
            </section>

            @include('client-setup.partials.review-summary', [
                'payload' => $stagePayload,
                'headingId' => 'wizard-review-heading',
            ])

            @if (! $academicReadiness['ready'])
                <section class="crm-wizard-readiness-panel" aria-labelledby="academic-readiness-heading">
                    <div class="crm-wizard-readiness-heading">
                        <div>
                            <p class="crm-kicker">Academic readiness</p>
                            <h2 id="academic-readiness-heading">Not ready for academic submission</h2>
                        </div>
                        <span class="crm-pill warning">{{ count($academicReadiness['missing_stages']) }} stage(s) outstanding</span>
                    </div>
                    <ul>
                        @foreach (array_slice($academicReadiness['missing_fields'], 0, 5) as $missingField)
                            <li><strong>{{ $missingField['label'] }}:</strong> {{ $missingField['message'] }}</li>
                        @endforeach
                    </ul>
                    @if (count($academicReadiness['missing_fields']) > 5)
                        <small class="crm-muted">{{ count($academicReadiness['missing_fields']) - 5 }} more requirement(s) will appear on their relevant stage.</small>
                    @endif
                </section>
            @endif

            @if ($academicReadiness['ready'] && ! in_array($submission->academic_status, ['submitted', 'approved'], true))
                <section class="crm-wizard-submit-panel" aria-labelledby="academic-submit-heading">
                    <div>
                        <p class="crm-kicker">Academic readiness complete</p>
                        <h2 id="academic-submit-heading">Submit the academic configuration</h2>
                        <p>Submitting freezes the academic revision for CRM review. Finance, migration and integrations can continue separately.</p>
                    </div>
                    <form method="POST" action="{{ route('client-setup.academic-submit', ['token' => request()->route('token')]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="bx bx-send"></i> Submit academic configuration</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...</span>
                        </button>
                    </form>
                </section>
            @endif
        </main>
    </div>
@endsection

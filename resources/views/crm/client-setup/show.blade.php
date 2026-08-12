@extends('layouts.crm')

@php
    $submissionPayload = $submission->payloadArray();
    $institution = data_get($submissionPayload, 'scope.institution_legal_name') ?: $submission->customer?->company_name ?: $submission->lead?->company_name ?: 'Unnamed institution';
    $openChanges = $submission->changeRequests->where('status', 'open');
    $stageProgressByKey = $submission->stageProgress->keyBy('stage_key');
@endphp

@push('head')
    <style>
        .crm-submission-tabs-card {
            padding: 0;
            overflow: hidden;
        }

        .crm-submission-tabs-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 20px;
        }

        .crm-submission-tabs-header h2 {
            margin: 0;
            color: var(--fg-1, #172033);
        }

        .crm-submission-tabs-header p:last-child {
            max-width: 700px;
            margin: 7px 0 0;
            color: var(--fg-3, #64748b);
            line-height: 1.55;
        }

        .crm-client-setup-header-actions {
            display: flex;
            justify-content: flex-end;
            width: 100%;
            margin-bottom: 0;
        }

        .crm-client-setup-header-actions .crm-action-row {
            flex-wrap: wrap;
        }

        .crm-submission-tablist {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 0 24px;
            border-top: 1px solid var(--border-1, #e7ebf3);
            border-bottom: 1px solid var(--border-1, #e7ebf3);
            scrollbar-width: thin;
        }

        .crm-submission-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 54px;
            flex: 0 0 auto;
            padding: 0 5px;
            border: 0;
            border-bottom: 3px solid transparent;
            background: transparent;
            color: var(--fg-3, #64748b);
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
        }

        .crm-submission-tab:hover,
        .crm-submission-tab:focus-visible {
            color: var(--brand-indigo-500, #4f46b5);
        }

        .crm-submission-tab:focus-visible {
            outline: 3px solid rgba(79, 70, 181, .2);
            outline-offset: -3px;
        }

        .crm-submission-tab[aria-selected="true"] {
            border-bottom-color: var(--brand-indigo-500, #4f46b5);
            color: var(--brand-indigo-500, #4f46b5);
        }

        .crm-submission-tab-index {
            display: inline-flex;
            width: 23px;
            height: 23px;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 50%;
            color: #64748b;
            font-size: 10px;
        }

        .crm-submission-tab[aria-selected="true"] .crm-submission-tab-index {
            border-color: var(--brand-indigo-500, #4f46b5);
            background: var(--brand-indigo-500, #4f46b5);
            color: #fff;
        }

        .crm-submission-tab-panel {
            padding: 24px;
        }

        .crm-submission-tab-panel[hidden] {
            display: none;
        }

        .crm-submission-panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .crm-submission-panel-heading h3 {
            margin: 0;
            color: var(--fg-1, #172033);
            font-size: 20px;
        }

        .crm-submission-panel-heading p {
            max-width: 760px;
            margin: 6px 0 0;
            color: var(--fg-3, #64748b);
            font-size: 13px;
            line-height: 1.55;
        }

        .crm-submission-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 16px;
        }

        .crm-submission-action-button {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .crm-modal-trigger {
            flex: 0 0 auto;
            min-width: 150px;
            justify-content: center;
            text-align: center;
        }

        .crm-review-action-title {
            align-items: flex-start;
        }

        .crm-review-action-title > div:first-child {
            min-width: 0;
        }

        .crm-client-setup-modal .modal-dialog {
            width: min(calc(100% - 32px), 620px);
            max-width: none;
        }

        .crm-client-setup-modal--wide .modal-dialog {
            width: min(calc(100% - 32px), 760px);
        }

        .crm-client-setup-modal .modal-content {
            overflow: hidden;
            border: 1px solid var(--border-1, #e7ebf3);
            border-radius: 14px;
        }

        .crm-client-setup-modal .crm-modal-form {
            display: block;
        }

        .crm-client-setup-modal .modal-header,
        .crm-client-setup-modal .modal-footer {
            padding: 20px 24px;
            border-color: var(--border-1, #e7ebf3);
        }

        .crm-client-setup-modal .modal-header {
            align-items: flex-start;
            gap: 16px;
        }

        .crm-client-setup-modal .modal-title {
            margin: 0;
            color: var(--fg-1, #172033);
            font-size: 20px;
            line-height: 1.3;
        }

        .crm-client-setup-modal .modal-header .crm-kicker {
            margin-bottom: 6px;
        }

        .crm-client-setup-modal .modal-body {
            padding: 22px 24px 4px;
        }

        .crm-client-setup-modal .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .crm-client-setup-modal .crm-modal-submit {
            width: auto;
            min-width: 150px;
        }

        .crm-review-control-form {
            min-width: 0;
        }

        .crm-review-control-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 158px;
            align-items: end;
            gap: 12px;
        }

        .crm-review-control-row .crm-field {
            min-width: 0;
        }

        .crm-review-control-button {
            width: 158px;
            min-height: 42px;
            justify-content: center;
            text-align: center;
        }

        .crm-submission-field {
            min-width: 0;
            padding: 13px 14px;
            border: 1px solid var(--border-1, #e7ebf3);
            border-radius: 10px;
            background: var(--bg-2, #fbfcff);
        }

        .crm-submission-field.is-empty {
            background: transparent;
        }

        .crm-submission-field dt {
            color: var(--fg-3, #64748b);
            font-size: 11px;
            font-weight: 700;
        }

        .crm-submission-field dd {
            margin: 5px 0 0;
            overflow-wrap: anywhere;
            color: var(--fg-1, #172033);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            white-space: pre-wrap;
        }

        .crm-submission-field.is-empty dd {
            color: var(--fg-3, #64748b);
            font-weight: 500;
        }

        .crm-submission-repeatable {
            grid-column: 1 / -1;
            padding: 16px;
            border: 1px solid var(--border-1, #e7ebf3);
            border-radius: 12px;
            background: var(--bg-2, #fbfcff);
        }

        .crm-submission-repeatable-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .crm-submission-repeatable-heading h4,
        .crm-submission-repeatable-row h5 {
            margin: 0;
            color: var(--fg-1, #172033);
            font-size: 14px;
        }

        .crm-submission-repeatable-heading p {
            margin: 5px 0 0;
            color: var(--fg-3, #64748b);
            font-size: 12px;
        }

        .crm-submission-repeatable-list {
            display: grid;
            gap: 10px;
        }

        .crm-submission-repeatable-row {
            padding: 14px;
            border: 1px solid var(--border-1, #e7ebf3);
            border-radius: 10px;
            background: #fff;
        }

        .crm-submission-repeatable-row .crm-submission-field-grid {
            margin-top: 12px;
        }

        @media (max-width: 767.98px) {
            .crm-submission-tabs-header,
            .crm-submission-tab-panel {
                padding: 18px;
            }

            .crm-submission-tablist {
                padding: 0 18px;
            }

            .crm-submission-panel-heading {
                display: block;
            }

            .crm-submission-panel-heading .crm-pill {
                display: inline-flex;
                margin-top: 12px;
            }

            .crm-submission-field-grid {
                grid-template-columns: 1fr;
            }

            .crm-review-control-row {
                grid-template-columns: 1fr;
            }

            .crm-review-control-button {
                width: 100%;
            }

            .crm-modal-trigger {
                width: 100%;
                margin-top: 14px;
            }

            .crm-review-action-title {
                display: block;
            }

            .crm-client-setup-modal .modal-dialog,
            .crm-client-setup-modal--wide .modal-dialog {
                width: calc(100% - 20px);
                margin: 10px auto;
            }

            .crm-client-setup-modal .modal-header,
            .crm-client-setup-modal .modal-body,
            .crm-client-setup-modal .modal-footer {
                padding-right: 18px;
                padding-left: 18px;
            }

            .crm-client-setup-modal .modal-footer .btn {
                flex: 1 1 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var modalKey = @json(old('_client_setup_modal'));
            var modalId = modalKey === 'note'
                ? 'crm-client-setup-note-modal'
                : (modalKey === 'change_request' ? 'crm-client-setup-change-modal' : null);

            if (!modalId || typeof bootstrap === 'undefined') {
                return;
            }

            var modalElement = document.getElementById(modalId);

            if (modalElement) {
                var modal = typeof bootstrap.Modal.getOrCreateInstance === 'function'
                    ? bootstrap.Modal.getOrCreateInstance(modalElement)
                    : new bootstrap.Modal(modalElement);

                modal.show();
            }
        })();
    </script>
@endpush

@section('title', $institution . ' - Client Setup')
@section('crm_heading', $institution)
@section('crm_subheading', 'Client setup review record with academic readiness, supplemental progress, evidence, revision history, and reviewer actions.')

@section('content')
    <div class="crm-stack">
        <div class="crm-client-setup-header-actions" aria-label="Client setup actions">
            <div class="crm-action-row">
                <a href="{{ route('crm.client-setup.print', $submission) }}" target="_blank" class="btn btn-light crm-btn-light"><i class="bx bx-printer"></i> Printable summary</a>
                <form method="POST" action="{{ route('crm.client-setup.resend', $submission) }}">@csrf<button type="submit" class="btn btn-primary btn-loading"><span class="btn-text"><i class="bx bx-refresh"></i> Resend link</span><span class="btn-spinner d-none">Sending...</span></button></form>
                @if ($canDeleteClientSetup)
                    <form method="POST" action="{{ route('crm.client-setup.destroy', $submission) }}" class="crm-inline-form" onsubmit="return confirm('Permanently delete this client setup record and all related onboarding data? This cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger" aria-label="Delete"><i class="bx bx-trash"></i> Delete</button></form>
                @endif
            </div>
        </div>

        @if ($setupInvitationUrl)
            <section class="crm-card" style="border-color: var(--bs-primary);"><div class="crm-card-title"><div><p class="crm-kicker">One-time link preview</p><h2>Share this link securely</h2><p>This URL is shown after creating or resending an invitation. The emailed link remains the normal client handoff.</p></div></div><div class="crm-inline"><input class="form-control" value="{{ $setupInvitationUrl }}" readonly><button type="button" class="btn btn-light crm-btn-light" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"><i class="bx bx-copy"></i> Copy</button></div></section>
        @endif

        <section class="crm-card crm-submission-tabs-card" aria-labelledby="institution-data-heading">
            <div class="crm-submission-tabs-header">
                <div>
                    <p class="crm-kicker">Institution data</p>
                    <h2 id="institution-data-heading">Wizard categories</h2>
                    <p>Review the client’s saved information one setup category at a time. These tabs follow the same order and labels used in the public client wizard.</p>
                </div>
                <span class="crm-pill primary">{{ count($stages) }} categories</span>
            </div>

            <div class="crm-submission-tablist" role="tablist" aria-label="Institution data categories">
                @foreach ($stages as $stage)
                    @php
                        $stageProgress = $stageProgressByKey->get($stage['key']);
                        $stageStatus = $stageProgress?->status ?: 'not_started';
                    @endphp
                    <button
                        type="button"
                        class="crm-submission-tab"
                        id="submission-tab-{{ $stage['key'] }}"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="submission-panel-{{ $stage['key'] }}"
                        tabindex="{{ $loop->first ? '0' : '-1' }}"
                        data-submission-tab
                        data-tab-target="submission-panel-{{ $stage['key'] }}"
                    >
                        <span class="crm-submission-tab-index">{{ $loop->iteration }}</span>
                        <span>{{ $stage['short_label'] }}</span>
                    </button>
                @endforeach
            </div>

            @foreach ($stages as $stage)
                @php
                    $stageProgress = $stageProgressByKey->get($stage['key']);
                    $stageStatus = $stageProgress?->status ?: 'not_started';
                    $stageFields = config('client_setup_academic.stages.' . $stage['key'], []);
                    $stagePayload = is_array($submissionPayload[$stage['key']] ?? null) ? $submissionPayload[$stage['key']] : [];
                @endphp
                <div
                    id="submission-panel-{{ $stage['key'] }}"
                    class="crm-submission-tab-panel"
                    role="tabpanel"
                    aria-labelledby="submission-tab-{{ $stage['key'] }}"
                    tabindex="0"
                    @if (! $loop->first) hidden @endif
                    data-submission-panel
                >
                    <div class="crm-submission-panel-heading">
                        <div>
                            <p class="crm-kicker">Category {{ $loop->iteration }}{{ $stage['optional'] ? ' · Optional' : ' · Required' }}</p>
                            <h3>{{ $stage['label'] }}</h3>
                            <p>{{ $stage['description'] }}</p>
                        </div>
                        <span class="crm-pill {{ $stageStatus === 'complete' ? 'success' : ($stageStatus === 'in_progress' ? 'primary' : 'muted') }}">{{ ucfirst(str_replace('_', ' ', $stageStatus)) }}</span>
                    </div>

                    @include('crm.client-setup.partials.stage-data', [
                        'fields' => $stageFields,
                        'data' => $stagePayload,
                        'headingPrefix' => 'submission-' . $stage['key'],
                    ])
                </div>
            @endforeach
        </section>

        <section class="crm-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Review control</p><h2>Submission state</h2></div><span class="crm-pill {{ $submission->status === 'changes_requested' ? 'danger' : ($submission->status === 'approved' ? 'success' : 'primary') }}">{{ $statuses[$submission->status] ?? ucfirst($submission->status) }}</span></div>
            <div class="crm-grid cols-2">
                <div class="crm-meta-list"><div class="crm-meta-row"><span>Academic readiness</span><strong>{{ $progress['completed'] }} / {{ $progress['total'] }} required stages · {{ $progress['percentage'] }}%</strong></div><div class="crm-meta-row"><span>Academic status</span><strong>{{ ucfirst(str_replace('_', ' ', $submission->academic_status)) }}</strong></div><div class="crm-meta-row"><span>Academic submitted</span><strong>{{ $submission->academic_submitted_at?->format('d M Y H:i') ?: 'Not submitted' }}</strong></div><div class="crm-meta-row"><span>Last activity</span><strong>{{ $submission->last_activity_at?->format('d M Y H:i') ?: '—' }}</strong></div></div>
                <div class="crm-stack">
                    <form method="POST" action="{{ route('crm.client-setup.assignment', $submission) }}" class="crm-form crm-review-control-form"><div class="crm-review-control-row"><div class="crm-field"><label for="assigned_to_id">Implementation owner</label><select id="assigned_to_id" name="assigned_to_id"><option value="">Unassigned</option>@foreach ($owners as $owner)<option value="{{ $owner->id }}" @selected($submission->assigned_to_id === $owner->id)>{{ $owner->name }}</option>@endforeach</select></div>@csrf @method('PATCH')<button class="btn btn-light crm-btn-light crm-review-control-button" type="submit">Save owner</button></div></form>
                    <form method="POST" action="{{ route('crm.client-setup.status', $submission) }}" class="crm-form crm-review-control-form"><div class="crm-review-control-row"><div class="crm-field"><label for="review_status">Move review status</label><select id="review_status" name="status">@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($submission->status === $value)>{{ $label }}</option>@endforeach</select></div>@csrf @method('PATCH')<button class="btn btn-primary crm-review-control-button" type="submit">Update status</button></div></form>
                </div>
            </div>
        </section>

        <section class="crm-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Migration validation</p><h2>Staff and student upload history</h2><p>Validation results are recorded separately from the eventual import. No workbook is imported automatically from this onboarding flow.</p></div></div>
            @if ($submission->migrationUploads->isEmpty())
                <div class="crm-empty">No staff or student migration workbook has been uploaded.</div>
            @else
                    <div class="crm-table-wrap" role="region" aria-label="Staff and student upload history" tabindex="0"><table class="crm-table"><thead><tr><th>Type</th><th>File</th><th>Template</th><th>Compatibility</th><th>Rows</th><th>Validation</th><th>CRM approval</th><th>Import</th><th>Report</th><th>Uploaded</th></tr></thead><tbody>
                    @foreach ($submission->migrationUploads->sortByDesc('uploaded_at') as $upload)
                        <tr><td>{{ ucfirst($upload->kind) }}</td><td><strong>{{ $upload->original_name }}</strong></td><td>v{{ $upload->template_version }}</td><td><span class="crm-pill {{ $upload->template_compatibility_status === 'compatible' ? 'success' : 'danger' }}">{{ ucfirst(str_replace('_', ' ', $upload->template_compatibility_status ?: 'unknown')) }}</span></td><td>{{ $upload->valid_row_count }}/{{ $upload->row_count }} valid</td><td><span class="crm-pill {{ $upload->validation_status === 'validated' ? 'success' : 'danger' }}">{{ ucfirst(str_replace('_', ' ', $upload->validation_status)) }}</span>@if ($upload->error_count) <span class="crm-muted">· {{ $upload->error_count }} issue group(s)</span>@endif</td><td><span class="crm-pill {{ $upload->crm_approval_status === 'approved' ? 'success' : 'muted' }}">{{ ucfirst($upload->crm_approval_status) }}</span>@if ($upload->validation_status === 'validated' && $upload->template_compatibility_status === 'compatible' && $upload->attachment?->scan_status === 'approved' && $upload->crm_approval_status === 'pending')<form method="POST" action="{{ route('crm.client-setup.migration-uploads.approve', [$submission, $upload]) }}" style="margin-top:.5rem">@csrf @method('PATCH')<button class="btn btn-light crm-btn-light" type="submit">Approve for import</button></form>@elseif ($upload->validation_status === 'validated')<small class="crm-muted">Awaiting scan or compatibility review</small>@endif</td><td><span class="crm-pill {{ $upload->import_status === 'completed' ? 'success' : ($upload->import_status === 'failed' ? 'danger' : 'muted') }}">{{ ucfirst(str_replace('_', ' ', $upload->import_status ?: 'not started')) }}</span>@if ($upload->crm_approval_status === 'approved' && in_array($upload->import_status, ['not_started', 'failed'], true))<form method="POST" action="{{ route('crm.client-setup.migration-uploads.import', [$submission, $upload]) }}" style="margin-top:.5rem">@csrf<button class="btn btn-light crm-btn-light" type="submit">Run controlled import</button></form>@elseif ($upload->import_error)<small class="crm-muted">{{ $upload->import_error }}</small>@endif</td><td><a href="{{ route('crm.client-setup.migration-uploads.validation-report', [$submission, $upload]) }}" class="btn crm-icon-action" title="Download full validation report"><i class="bx bx-download"></i></a></td><td>{{ $upload->uploaded_at?->format('d M Y H:i') }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </section>

        <section class="crm-card"><div class="crm-card-title"><div><p class="crm-kicker">Linked CRM context</p><h2>Institution and contact</h2></div></div><div class="crm-meta-list"><div class="crm-meta-row"><span>Institution</span><strong>{{ $institution }}</strong></div><div class="crm-meta-row"><span>Lead</span><strong>{{ $submission->lead?->company_name ?: 'Not linked' }}</strong></div><div class="crm-meta-row"><span>Customer</span><strong>{{ $submission->customer?->company_name ?: 'Not linked' }}</strong></div><div class="crm-meta-row"><span>Primary contact</span><strong>{{ $submission->primaryContact?->name ?: 'Not linked' }}</strong></div><div class="crm-meta-row"><span>Invitation email</span><strong>{{ $submission->invitations->sortByDesc('created_at')->first()?->email ?: '—' }}</strong></div></div></section>

        <section class="crm-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Optional implementation scope</p><h2>Missing versus deliberately deferred</h2><p>Optional information remains independent from the submitted academic revision. A deferred decision is shown separately from a missing or partially completed section.</p></div></div>
            <div class="crm-table-wrap" role="region" aria-label="Optional implementation scope" tabindex="0"><table class="crm-table"><thead><tr><th>Section</th><th>State</th><th>Validation</th></tr></thead><tbody>
                @foreach ($supplementalSummary['rows'] as $row)
                    <tr><td><strong>{{ $row['label'] }}</strong></td><td><span class="crm-pill {{ $row['state'] === 'complete' ? 'success' : ($row['state'] === 'deferred' ? 'muted' : ($row['state'] === 'in_progress' ? 'primary' : 'danger')) }}">{{ ucfirst(str_replace('_', ' ', $row['state'])) }}</span></td><td>{{ $row['errors'] === [] ? 'No validation errors' : implode(' ', $row['errors']) }}</td></tr>
                @endforeach
            </tbody></table></div>
        </section>

        <div class="crm-grid cols-2">
            <section class="crm-card"><div class="crm-card-title"><div><p class="crm-kicker">Evidence register</p><h2>Uploaded attachments</h2><p>Downloads remain gated until the attachment scanner marks a file approved.</p></div></div>@if ($submission->attachments->isEmpty())<div class="crm-empty">No evidence has been uploaded.</div>@else<div class="crm-table-wrap" role="region" aria-label="Uploaded attachments" tabindex="0"><table class="crm-table"><thead><tr><th>File</th><th>Requirement</th><th>Scan</th><th></th></tr></thead><tbody>@foreach ($submission->attachments as $attachment)<tr><td><strong>{{ $attachment->original_name }}</strong><span class="crm-muted">{{ number_format($attachment->size_bytes / 1024, 1) }} KB · {{ $attachment->uploaded_at?->format('d M Y H:i') }}</span></td><td>{{ $attachment->requirement ?: ucfirst($attachment->category) }}</td><td><span class="crm-pill {{ $attachment->scan_status === 'approved' ? 'success' : 'muted' }}">{{ ucfirst($attachment->scan_status) }}</span></td><td>@if ($attachment->scan_status === 'approved')<a href="{{ route('crm.client-setup.attachment.download', [$submission, $attachment]) }}" class="btn crm-icon-action" title="Download attachment"><i class="bx bx-download"></i></a>@else<span class="crm-muted">Pending</span>@endif</td></tr>@endforeach</tbody></table></div>@endif</section>
            <section class="crm-card">
                <div class="crm-card-title"><div><p class="crm-kicker">Revision history</p><h2>What changed over time</h2><p>Compare any two saved snapshots to see changed and removed values.</p></div></div>
                @if ($submission->revisions->count() < 2)
                    <div class="crm-empty">At least two revisions are required before a comparison can be made.</div>
                @else
                    <form method="GET" action="{{ route('crm.client-setup.revisions.compare', $submission) }}" class="crm-form" style="margin-bottom:1rem">
                        <div class="crm-field-grid cols-2">
                            <div class="crm-field"><label for="revision_from">Earlier revision</label><select id="revision_from" name="from" required>@foreach ($submission->revisions->sortBy('revision_number') as $revision)<option value="{{ $revision->revision_number }}">#{{ $revision->revision_number }} · {{ $revision->created_at?->format('d M Y H:i') }}</option>@endforeach</select></div>
                            <div class="crm-field"><label for="revision_to">Later revision</label><select id="revision_to" name="to" required>@foreach ($submission->revisions->sortBy('revision_number') as $revision)<option value="{{ $revision->revision_number }}" @selected($loop->last)>#{{ $revision->revision_number }} · {{ $revision->created_at?->format('d M Y H:i') }}</option>@endforeach</select></div>
                        </div>
                        <button type="submit" class="btn btn-light crm-btn-light"><i class="bx bx-git-compare"></i> Compare revisions</button>
                    </form>
                    <div class="crm-table-wrap" role="region" aria-label="Revision history" tabindex="0"><table class="crm-table"><thead><tr><th>Revision</th><th>Source</th><th>Stage</th><th>Changed keys</th><th></th></tr></thead><tbody>@foreach ($submission->revisions->sortByDesc('revision_number') as $revision)<tr><td><strong>#{{ $revision->revision_number }}</strong><span class="crm-muted">{{ $revision->created_at?->format('d M Y H:i') }}</span></td><td>{{ ucfirst(str_replace('_', ' ', $revision->source)) }}</td><td>{{ $revision->stage_key ?: 'Full submission' }}</td><td>{{ count($revision->changed_keys ?: []) ?: '—' }}</td><td>@php($previous = $submission->revisions->where('revision_number', '<', $revision->revision_number)->sortByDesc('revision_number')->first()) @if ($previous)<a href="{{ route('crm.client-setup.revisions.compare', ['submission' => $submission, 'from' => $previous->revision_number, 'to' => $revision->revision_number]) }}" class="btn crm-icon-action" title="Compare with previous revision"><i class="bx bx-git-compare"></i></a>@endif</td></tr>@endforeach</tbody></table></div>
                @endif
            </section>
        </div>

        <div class="crm-grid cols-2">
            <section class="crm-card"><div class="crm-card-title crm-review-action-title"><div><p class="crm-kicker">Internal collaboration</p><h2>Reviewer notes</h2></div><button type="button" class="btn btn-primary crm-modal-trigger" data-bs-toggle="modal" data-bs-target="#crm-client-setup-note-modal" aria-controls="crm-client-setup-note-modal"><i class="bx bx-plus"></i> Add note</button></div><div class="crm-list" style="margin-top:1rem">@forelse ($submission->notes->sortByDesc('created_at') as $note)<div class="crm-list-item"><strong>{{ $note->user?->name ?: 'CRM user' }}</strong><span class="crm-muted">{{ $note->created_at?->format('d M Y H:i') }}</span><p>{{ $note->body }}</p></div>@empty<div class="crm-empty">No internal notes yet.</div>@endforelse</div></section>
            <section class="crm-card"><div class="crm-card-title crm-review-action-title"><div><p class="crm-kicker">Client corrections</p><h2>Change requests</h2><p>{{ $openChanges->count() }} open request(s).</p></div><button type="button" class="btn btn-primary crm-modal-trigger" data-bs-toggle="modal" data-bs-target="#crm-client-setup-change-modal" aria-controls="crm-client-setup-change-modal"><i class="bx bx-message-square-add"></i> Request changes</button></div><div class="crm-list" style="margin-top:1rem">@forelse ($submission->changeRequests->sortByDesc('created_at') as $change)<div class="crm-list-item"><div class="crm-inline" style="justify-content:space-between"><strong>{{ $change->stage_key ?: 'General review' }}{{ $change->field_key ? ' · '.$change->field_key : '' }}</strong><span class="crm-pill {{ $change->status === 'open' ? 'danger' : 'success' }}">{{ ucfirst($change->status) }}</span></div><p>{{ $change->body }}</p>@if ($change->client_response)<div class="crm-meta-row"><span>Client response</span><strong>{{ $change->client_response }}</strong></div>@endif<span class="crm-muted">Raised by {{ $change->user?->name ?: 'CRM user' }} · {{ $change->created_at?->format('d M Y H:i') }}</span>@if ($change->status === 'open')<form method="POST" action="{{ route('crm.client-setup.change-requests.resolve', [$submission, $change]) }}" style="margin-top:.5rem">@csrf @method('PATCH')<button class="btn btn-light crm-btn-light" type="submit">Mark resolved</button></form>@endif</div>@empty<div class="crm-empty">No change requests yet.</div>@endforelse</div></section>
        </div>

        <div class="modal fade crm-client-setup-modal" id="crm-client-setup-note-modal" tabindex="-1" aria-labelledby="crm-client-setup-note-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('crm.client-setup.notes.store', $submission) }}" class="crm-modal-form">
                        @csrf
                        <input type="hidden" name="_client_setup_modal" value="note">
                        <div class="modal-header">
                            <div>
                                <p class="crm-kicker">Internal collaboration</p>
                                <h2 class="modal-title" id="crm-client-setup-note-modal-title">Add a private note</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="crm-field">
                                <label for="note_body">Note for the internal team</label>
                                <textarea id="note_body" name="body" rows="5" placeholder="Capture implementation context for the internal team">{{ old('_client_setup_modal') === 'note' ? old('body') : '' }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading crm-modal-submit"><span class="btn-text"><i class="bx bx-plus"></i> Add note</span><span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adding...</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade crm-client-setup-modal crm-client-setup-modal--wide" id="crm-client-setup-change-modal" tabindex="-1" aria-labelledby="crm-client-setup-change-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('crm.client-setup.change-requests.store', $submission) }}" class="crm-modal-form">
                        @csrf
                        <input type="hidden" name="_client_setup_modal" value="change_request">
                        <div class="modal-header">
                            <div>
                                <p class="crm-kicker">Client corrections</p>
                                <h2 class="modal-title" id="crm-client-setup-change-modal-title">Request changes</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="crm-field-grid cols-2">
                                <div class="crm-field"><label for="stage_key">Stage</label><select id="stage_key" name="stage_key"><option value="">General review</option>@foreach ($stages as $stage)<option value="{{ $stage['key'] }}" @selected(old('_client_setup_modal') === 'change_request' && old('stage_key') === $stage['key'])>{{ $stage['label'] }}</option>@endforeach</select></div>
                                <div class="crm-field"><label for="field_key">Field key (optional)</label><input id="field_key" name="field_key" value="{{ old('_client_setup_modal') === 'change_request' ? old('field_key') : '' }}" placeholder="e.g. scope.institution_legal_name"></div>
                            </div>
                            <div class="crm-field"><label for="change_body">What must change?</label><textarea id="change_body" name="body" rows="5" required placeholder="Describe the correction the client needs to make">{{ old('_client_setup_modal') === 'change_request' ? old('body') : '' }}</textarea></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading crm-modal-submit"><span class="btn-text"><i class="bx bx-message-square-add"></i> Request changes</span><span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var tabs = Array.from(document.querySelectorAll('[data-submission-tab]'));
            var panels = Array.from(document.querySelectorAll('[data-submission-panel]'));

            if (tabs.length === 0 || panels.length === 0) {
                return;
            }

            var activateTab = function (tab, updateHash) {
                var targetId = tab.dataset.tabTarget;

                tabs.forEach(function (item) {
                    var active = item === tab;
                    item.setAttribute('aria-selected', active ? 'true' : 'false');
                    item.setAttribute('tabindex', active ? '0' : '-1');
                });

                panels.forEach(function (panel) {
                    panel.hidden = panel.id !== targetId;
                });

                if (updateHash && window.history.replaceState) {
                    window.history.replaceState(null, '', '#' + targetId);
                }
            };

            tabs.forEach(function (tab, index) {
                tab.addEventListener('click', function () {
                    activateTab(tab, true);
                });

                tab.addEventListener('keydown', function (event) {
                    var nextIndex = null;

                    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                        nextIndex = (index + 1) % tabs.length;
                    } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                        nextIndex = (index - 1 + tabs.length) % tabs.length;
                    } else if (event.key === 'Home') {
                        nextIndex = 0;
                    } else if (event.key === 'End') {
                        nextIndex = tabs.length - 1;
                    }

                    if (nextIndex === null) {
                        return;
                    }

                    event.preventDefault();
                    tabs[nextIndex].focus();
                    activateTab(tabs[nextIndex], true);
                });
            });

            var hashTarget = window.location.hash.replace('#', '');
            var initialTab = tabs.find(function (tab) {
                return tab.dataset.tabTarget === hashTarget;
            });

            if (initialTab) {
                activateTab(initialTab, false);
            }
        })();
    </script>
@endpush

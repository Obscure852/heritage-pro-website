@extends('layouts.crm')

@section('title', 'CRM Settings - Imports')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Import CRM users, leads, and contacts from fixed Excel templates with preview, validation, and queued processing.')

@section('crm_actions')
    <a href="{{ route('crm.settings.imports.templates.download', $activeImportEntity) }}" class="btn btn-primary">
        <i class="bx bx-download"></i> Download {{ $importDefinition['label'] }} template
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => 'imports'])

        @include('crm.partials.helper-text', [
            'title' => 'Import Workspace',
            'content' => 'Choose an import area from the tabs, confirm the required template columns, then preview the spreadsheet before queueing it.',
        ])

        @include('crm.settings._import_tabs', ['entityTabs' => $entityTabs, 'activeImportEntity' => $activeImportEntity])

        <div class="crm-grid cols-2">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Template guide</p>
                        <h2>{{ $importDefinition['label'] }} import columns</h2>
                        <p>{{ $importDefinition['description'] }}</p>
                    </div>
                </div>

                <div class="crm-help">
                    Required columns: {{ implode(', ', $importDefinition['required']) }}.
                    Matching key: <strong>{{ $importDefinition['match_key'] }}</strong>.
                </div>

                <div class="crm-import-guide">
                    @foreach ($importDefinition['headings'] as $heading)
                        <div class="crm-import-guide-item">
                            <code>{{ $heading }}</code>
                            <span>{{ in_array($heading, $importDefinition['required'], true) ? 'Required' : 'Optional' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Preview upload</p>
                        <h2>Upload {{ \Illuminate\Support\Str::lower($importDefinition['label']) }} spreadsheet</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.settings.imports.preview') }}" enctype="multipart/form-data" class="crm-form">
                    @csrf
                    <input type="hidden" name="entity" value="{{ $activeImportEntity }}">

                    <div class="crm-field-grid">
                        <div class="crm-field full">
                            <label for="import_file">Excel file</label>
                            <div class="crm-dropzone crm-import-dropzone" data-dropzone>
                                <input id="import_file" name="file" type="file" class="crm-dropzone-input"
                                    accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                    required data-dropzone-input>
                                <div class="crm-dropzone-copy">
                                    <span class="crm-dropzone-icon"><i class="fas fa-file-excel"></i></span>
                                    <strong>Drop the completed template here</strong>
                                    <p>Upload one `.xlsx` or `.xls` file, or click anywhere in this panel to browse.</p>
                                </div>
                                <div class="crm-dropzone-list" data-dropzone-list>
                                    <div class="crm-dropzone-empty">No spreadsheet selected yet.</div>
                                </div>
                            </div>
                            <div class="crm-import-dropzone-meta">
                                <span><i class="bx bx-table"></i> Fixed template only</span>
                                <span><i class="bx bx-check-shield"></i> Preview runs before queueing</span>
                                <span><i class="bx bx-file"></i> Excel only: `.xlsx`, `.xls`</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Preview import</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

        @if ($previewRun)
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Preview</p>
                        <h2>Validated import preview</h2>
                        <p>{{ $previewRun->original_filename }} · {{ $importStatuses[$previewRun->status] ?? ucfirst($previewRun->status) }}</p>
                    </div>
                    <div class="crm-page-tools">
                        @if (($previewRun->created_count + $previewRun->updated_count) > 0)
                            <form method="POST" action="{{ route('crm.settings.imports.confirm', $previewRun) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Confirm and queue</span>
                                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Queueing...</span>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('crm.settings.imports.runs.show', $previewRun) }}" class="btn btn-light crm-btn-light">
                            <i class="bx bx-right-arrow-alt"></i> Open run
                        </a>
                    </div>
                </div>

                <div class="crm-grid cols-4">
                    <div class="crm-metric"><span>Create</span><strong>{{ $previewRun->created_count }}</strong></div>
                    <div class="crm-metric"><span>Update</span><strong>{{ $previewRun->updated_count }}</strong></div>
                    <div class="crm-metric"><span>Skip</span><strong>{{ $previewRun->skipped_count }}</strong></div>
                    <div class="crm-metric"><span>Errors</span><strong>{{ $previewRun->failed_count }}</strong></div>
                </div>

                @if ($previewRows->isEmpty())
                    <div class="crm-empty">No preview rows are available for this import yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Row</th>
                                    <th>Key</th>
                                    <th>Action</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($previewRows as $row)
                                    <tr>
                                        <td>{{ $row->row_number }}</td>
                                        <td>{{ $row->normalized_key ?: 'n/a' }}</td>
                                        <td><span class="crm-pill {{ $row->action === 'create' ? 'success' : ($row->action === 'update' ? 'primary' : ($row->action === 'skip' ? 'muted' : 'danger')) }}">{{ ucfirst($row->action ?? 'pending') }}</span></td>
                                        <td>{{ $row->validation_errors ? implode(' | ', $row->validation_errors) : 'Ready to import' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Recent runs</p>
                    <h2>{{ $importDefinition['label'] }} import history</h2>
                </div>
            </div>

            @if ($recentRuns->isEmpty())
                <div class="crm-empty">No {{ \Illuminate\Support\Str::lower($importDefinition['label']) }} import records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Run</th>
                                <th>Status</th>
                                <th>Counts</th>
                                <th>Initiated by</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRuns as $run)
                                <tr>
                                    <td>
                                        <strong>{{ $run->original_filename }}</strong>
                                        <span class="crm-muted">{{ $run->created_at->format('d M Y H:i') }}</span>
                                    </td>
                                    <td><span class="crm-pill {{ in_array($run->status, ['completed', 'validated'], true) ? 'success' : (in_array($run->status, ['queued', 'processing'], true) ? 'primary' : ($run->status === 'completed_with_errors' ? 'danger' : 'muted')) }}">{{ $importStatuses[$run->status] ?? ucfirst($run->status) }}</span></td>
                                    <td>{{ $run->created_count }} create · {{ $run->updated_count }} update · {{ $run->failed_count }} errors</td>
                                    <td>{{ $run->initiatedBy?->name ?: 'System' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.settings.imports.runs.show', $run) }}" class="btn crm-icon-action" title="Open import run" aria-label="Open import run">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection

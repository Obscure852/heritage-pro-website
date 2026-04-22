@extends('layouts.crm')

@section('title', 'Import Run')
@section('crm_heading', 'Import Run')
@section('crm_subheading', 'Review the processed rows, outcomes, and downloadable results for a queued or completed import.')

@section('crm_actions')
    <a href="{{ route('crm.settings.imports.' . $run->entity) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to {{ \Illuminate\Support\Str::lower($importDefinition['label']) }} imports
    </a>
    @if ($run->hasFailures())
        <a href="{{ route('crm.settings.imports.runs.failures.download', $run) }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-download"></i> Download failures
        </a>
    @endif
    @if ($run->hasPasswordResults())
        <a href="{{ route('crm.settings.imports.runs.passwords.download', $run) }}" class="btn btn-primary">
            <i class="bx bx-download"></i> Download passwords
        </a>
    @endif
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => 'imports'])

        @include('crm.partials.helper-text', [
            'title' => 'Import Run Review',
            'content' => 'Use the tabs to return to the related import area, then review row results here before downloading failures or generated outputs.',
        ])

        @include('crm.settings._import_tabs', ['entityTabs' => $entityTabs, 'activeImportEntity' => $run->entity])

        <div class="crm-grid cols-4">
            <div class="crm-metric"><span>Status</span><strong>{{ $importStatuses[$run->status] ?? ucfirst($run->status) }}</strong></div>
            <div class="crm-metric"><span>Created</span><strong>{{ $run->created_count }}</strong></div>
            <div class="crm-metric"><span>Updated</span><strong>{{ $run->updated_count }}</strong></div>
            <div class="crm-metric"><span>Failed</span><strong>{{ $run->failed_count }}</strong></div>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Run summary</p>
                    <h2>{{ $run->original_filename }}</h2>
                </div>
            </div>

            <div class="crm-meta-list">
                <div class="crm-meta-row"><span>Entity</span><strong>{{ $importDefinition['label'] }}</strong></div>
                <div class="crm-meta-row"><span>Checksum</span><strong>{{ $run->file_checksum }}</strong></div>
                <div class="crm-meta-row"><span>Initiated by</span><strong>{{ $run->initiatedBy?->name ?: 'System' }}</strong></div>
                <div class="crm-meta-row"><span>Started</span><strong>{{ $run->started_at?->format('d M Y H:i') ?: 'Not started' }}</strong></div>
                <div class="crm-meta-row"><span>Completed</span><strong>{{ $run->completed_at?->format('d M Y H:i') ?: 'Not completed' }}</strong></div>
                <div class="crm-meta-row"><span>Last error</span><strong>{{ $run->last_error ?: 'None' }}</strong></div>
            </div>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Rows</p>
                    <h2>Processed row results</h2>
                </div>
            </div>

            @if ($rows->isEmpty())
                <div class="crm-empty">No import rows match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Key</th>
                                <th>Action</th>
                                <th>Record ID</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row->row_number }}</td>
                                    <td>{{ $row->normalized_key ?: 'n/a' }}</td>
                                    <td><span class="crm-pill {{ $row->action === 'create' ? 'success' : ($row->action === 'update' ? 'primary' : ($row->action === 'skip' ? 'muted' : 'danger')) }}">{{ ucfirst($row->action ?? 'pending') }}</span></td>
                                    <td>{{ $row->record_id ?: 'n/a' }}</td>
                                    <td>{{ $row->validation_errors ? implode(' | ', $row->validation_errors) : 'None' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $rows])
            @endif
        </section>
    </div>
@endsection

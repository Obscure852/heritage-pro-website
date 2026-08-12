@extends('layouts.crm')

@section('title', 'Client Setup Reviews')
@section('crm_heading', 'Client Setup Reviews')
@section('crm_subheading', 'Find public onboarding submissions, track academic readiness, and move each institution through review with a clear audit trail.')

@section('crm_header_stats')
    @foreach ($stats as $stat)
        @include('crm.partials.header-stat', ['value' => number_format($stat['value']), 'label' => $stat['label']])
    @endforeach
@endsection

@section('crm_actions')
    <a href="{{ route('crm.client-setup.create') }}" class="btn btn-primary"><i class="bx bx-link-external"></i> New invitation</a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Review inbox',
            'content' => 'Academic submission is the first operational milestone. Optional finance, migration, and integration information can continue after that milestone without hiding the record from the CRM.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Filters</p><h2>Find a submission</h2></div></div>
            <form method="GET" action="{{ route('crm.client-setup.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field"><label for="q">Institution or email</label><input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Institution, linked record, client email"></div>
                    <div class="crm-field"><label for="status">Review status</label><select id="status" name="status"><option value="">All statuses</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="academic_status">Academic status</label><select id="academic_status" name="academic_status"><option value="">All academic states</option>@foreach ($academicStatuses as $value => $label)<option value="{{ $value }}" @selected($filters['academic_status'] === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="assigned_to_id">Implementation owner</label><select id="assigned_to_id" name="assigned_to_id"><option value="">All owners</option>@foreach ($owners as $owner)<option value="{{ $owner->id }}" @selected($filters['assigned_to_id'] === (string) $owner->id)>{{ $owner->name }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="completeness">Completeness</label><select id="completeness" name="completeness"><option value="">All records</option><option value="complete" @selected($filters['completeness'] === 'complete')>Academic ready/submitted</option><option value="incomplete" @selected($filters['completeness'] === 'incomplete')>Academic incomplete</option></select></div>
                    <div class="crm-field"><label for="activity_from">Activity from</label><input id="activity_from" name="activity_from" type="date" value="{{ $filters['activity_from'] }}"></div>
                    <div class="crm-field"><label for="activity_to">Activity to</label><input id="activity_to" name="activity_to" type="date" value="{{ $filters['activity_to'] }}"></div>
                </div>
                <div class="form-actions"><a href="{{ route('crm.client-setup.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a><button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button></div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Submission inbox</p><h2>Client setup records</h2><p>Open a record to review stages, revisions, evidence, notes, and requested changes.</p></div></div>
            @if ($submissions->isEmpty())
                <div class="crm-empty">No client setup submissions match the current filters.</div>
            @else
                <div class="crm-table-wrap" role="region" aria-label="Client setup submission inbox" tabindex="0"><table class="crm-table"><thead><tr><th>Institution</th><th>Academic</th><th>Supplemental</th><th>Review</th><th>Owner</th><th>Last activity</th><th aria-label="Actions"></th></tr></thead><tbody>
                    @foreach ($submissions as $submission)
                        @php($institution = data_get($submission->payloadArray(), 'scope.institution_legal_name') ?: $submission->customer?->company_name ?: $submission->lead?->company_name ?: 'Unnamed institution')
                        <tr>
                            <td><strong><a href="{{ route('crm.client-setup.show', $submission) }}">{{ $institution }}</a></strong><span class="crm-muted">{{ $submission->primaryContact?->name ?: $submission->invitations->first()?->email ?: 'No primary contact' }}</span></td>
                            <td><span class="crm-pill {{ in_array($submission->academic_status, ['submitted', 'approved'], true) ? 'success' : 'primary' }}">{{ $academicStatuses[$submission->academic_status] ?? ucfirst($submission->academic_status) }}</span></td>
                            <td><span class="crm-pill {{ ($submission->supplemental_summary['deferred'] ?? 0) === ($submission->supplemental_summary['total'] ?? 0) ? 'muted' : (($submission->supplemental_summary['in_progress'] ?? 0) > 0 ? 'primary' : 'success') }}">{{ ($submission->supplemental_summary['deferred'] ?? 0) }} deferred · {{ ($submission->supplemental_summary['complete'] ?? 0) }} complete</span></td>
                            <td><span class="crm-pill {{ $submission->status === 'changes_requested' ? 'danger' : ($submission->status === 'approved' ? 'success' : 'muted') }}">{{ $statuses[$submission->status] ?? ucfirst($submission->status) }}</span></td>
                            <td>{{ $submission->assignedTo?->name ?: 'Unassigned' }}</td>
                            <td>{{ $submission->last_activity_at?->format('d M Y H:i') ?: '—' }}</td>
                            <td class="crm-table-actions"><div class="crm-action-row"><a href="{{ route('crm.client-setup.show', $submission) }}" class="btn crm-icon-action" title="Review submission" aria-label="Review submission"><i class="bx bx-right-arrow-alt"></i></a>@if ($canDeleteClientSetup)<form method="POST" action="{{ route('crm.client-setup.destroy', $submission) }}" class="crm-inline-form" onsubmit="return confirm('Permanently delete this client setup record and all related onboarding data? This cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete client setup" aria-label="Delete client setup"><i class="bx bx-trash"></i></button></form>@endif</div></td>
                        </tr>
                    @endforeach
                </tbody></table></div>
                @include('crm.partials.pager', ['paginator' => $submissions])
            @endif
        </section>
    </div>
@endsection

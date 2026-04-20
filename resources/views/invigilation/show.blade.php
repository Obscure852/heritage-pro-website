@extends('layouts.master')

@section('title')
    Invigilation Roster Series
@endsection

@section('css')
    @include('invigilation.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('invigilation.index') }}">Invigilation Roster</a>
        @endslot
        @slot('title')
            {{ $series->name }}
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $flattenedIssues = collect($issues)->flatMap(
            fn ($entries, $category) => collect($entries)->map(fn ($entry) => ['category' => $category] + $entry)
        );
        $totalIssueCount = $flattenedIssues->count();
        $seriesEditable = $series->isEditable();
        $seriesPublished = $series->isPublished();
        $seriesArchived = $series->isArchived();
        $publishBlocked = $series->sessions->isEmpty() || $issueSummary['blocking_conflicts'] > 0;
        $publishTitle = $series->sessions->isEmpty()
            ? 'Add at least one session before publishing.'
            : ($issueSummary['blocking_conflicts'] > 0
                ? 'Resolve all shortages and conflicts before publishing.'
                : 'Publish this invigilation series.');
    @endphp

    <div class="invigilation-container">
        <div class="invigilation-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="mb-1 text-white">{{ $series->name }}</h3>
                    <p class="mb-0 opacity-75">
                        Manage sessions, room coverage, automatic assignment generation, and publishing for this invigilation series.
                    </p>
                    <div class="invigilation-meta-pills mt-3">
                        <span class="summary-chip status-{{ $series->status }}">{{ $statuses[$series->status] ?? ucfirst($series->status) }}</span>
                        <span class="summary-chip pill-muted">{{ $seriesTypes[$series->type] ?? ucfirst($series->type) }}</span>
                        <span class="summary-chip pill-muted">Term {{ $series->term?->term }}, {{ $series->term?->year }}</span>
                        <span class="summary-chip pill-muted">{{ $eligibilityPolicies[$series->eligibility_policy] ?? $series->eligibility_policy }}</span>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $metrics['sessions'] }}</h4>
                                <small class="opacity-75">Sessions</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $metrics['rooms'] }}</h4>
                                <small class="opacity-75">Rooms</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $metrics['coverage'] }}%</h4>
                                <small class="opacity-75">Coverage</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="invigilation-body invigilation-body-workspace">
            <div class="invigilation-workspace-top">
            <div class="help-text">
                <div class="help-title">Series Guidance</div>
                <div class="help-content">
                    @if ($seriesEditable)
                        Keep the page focused on the working roster table. Use the action buttons for settings, issue review, new sessions,
                        generation, and publishing.
                    @elseif ($seriesPublished)
                        This series is published and read-only. Review the roster here, use print views for output, and unpublish it before making changes.
                    @else
                        This series is archived and read-only. Review the roster and reports here, but editing actions are no longer available.
                    @endif
                </div>
            </div>

            @include('invigilation.partials.module-nav', ['current' => 'manager', 'series' => $series])

            <div class="section-toolbar workspace-toolbar">
                <div>
                    <h5 class="invigilation-section-title">Session Workspace</h5>
                    <p class="invigilation-section-subtitle">Expand a session row to manage its details, rooms, and assignments.</p>
                </div>
                <div class="module-header-actions workspace-actions">
                    <a href="{{ route('invigilation.reports.daily.index', ['series_id' => $series->id]) }}" class="btn btn-light">
                        <i class="fas fa-print me-1"></i> Print Views
                    </a>
                    <button type="button" class="btn btn-light invigilation-issue-trigger" data-bs-toggle="modal" data-bs-target="#issuesModal">
                        <i class="fas fa-exclamation-triangle me-1"></i> Issues
                        @if ($totalIssueCount > 0)
                            <span class="invigilation-action-badge">{{ $totalIssueCount }}</span>
                        @endif
                    </button>
                    @if ($seriesEditable)
                        <form action="{{ route('invigilation.generate', $series) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light btn-loading">
                                <span class="btn-text"><i class="fas fa-magic me-1"></i> Generate Roster</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Generating...
                                </span>
                            </button>
                        </form>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                            <i class="fas fa-plus me-1"></i> Add Session
                        </button>
                        <button
                            type="button"
                            class="btn btn-light invigilation-icon-button"
                            data-bs-toggle="modal"
                            data-bs-target="#seriesSettingsModal"
                            aria-label="Series settings"
                            title="Series settings"
                        >
                            <i class="fas fa-cog"></i>
                        </button>
                        <form action="{{ route('invigilation.publish', $series) }}" method="POST" onsubmit="return confirm('Publish this invigilation series?');">
                            @csrf
                            <button
                                type="submit"
                                class="btn btn-primary btn-loading"
                                {{ $publishBlocked ? 'disabled' : '' }}
                                title="{{ $publishTitle }}"
                            >
                                <span class="btn-text"><i class="fas fa-upload me-1"></i> Publish Series</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Publishing...
                                </span>
                            </button>
                        </form>
                    @elseif ($seriesPublished)
                        <form action="{{ route('invigilation.unpublish', $series) }}" method="POST" onsubmit="return confirm('Unpublish this invigilation series and return it to draft mode?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-undo me-1"></i> Unpublish Series</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Unpublishing...
                                </span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            </div>

            <div class="invigilation-workspace-table">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Schedule</th>
                                <th>Rooms</th>
                                <th>Assignments</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($series->sessions as $session)
                                @php
                                    $roomCount = $session->rooms->count();
                                    $candidateCount = $session->rooms->sum('candidate_count');
                                    $requiredSlots = $session->rooms->sum('required_invigilators');
                                    $assignedSlots = $session->rooms->sum(fn ($room) => $room->assignments->count());
                                    $lockedAssignmentCount = $session->rooms->sum(fn ($room) => $room->assignments->where('locked', true)->count());
                                    $coverage = $requiredSlots > 0 ? round(($assignedSlots / $requiredSlots) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="series-table-name">
                                            <strong class="session-row-title">
                                                <span>{{ $session->display_name }}</span>
                                                @if ($lockedAssignmentCount > 0)
                                                    <span class="session-row-lock-indicator" title="{{ $lockedAssignmentCount }} locked assignment(s)">
                                                        <i class="fas fa-lock" aria-hidden="true"></i>
                                                    </span>
                                                @endif
                                            </strong>
                                            <div class="series-table-meta">
                                                {{ $session->gradeSubject?->grade?->name ?? 'No grade' }}
                                                @if ($session->notes)
                                                    | {{ \Illuminate\Support\Str::limit($session->notes, 80) }}
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $session->exam_date?->format('d M Y') }}</strong>
                                        <div class="series-table-meta">
                                            {{ substr((string) $session->start_time, 0, 5) }} - {{ substr((string) $session->end_time, 0, 5) }}
                                            @if ($session->day_of_cycle)
                                                | Day {{ $session->day_of_cycle }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $roomCount }} room(s)</strong>
                                        <div class="series-table-meta">{{ $candidateCount }} candidate(s)</div>
                                    </td>
                                    <td>
                                        <strong>{{ $assignedSlots }}/{{ $requiredSlots }}</strong>
                                        <div class="series-table-meta">
                                            {{ $coverage }}% coverage
                                            @if ($lockedAssignmentCount > 0)
                                                | {{ $lockedAssignmentCount }} locked
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="room-actions justify-content-end">
                                            <button
                                                class="btn btn-light btn-sm"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#session-details-{{ $session->id }}"
                                                aria-expanded="false"
                                            >
                                                <i class="fas {{ $seriesEditable ? 'fa-sliders-h' : 'fa-eye' }} me-1"></i>
                                                {{ $seriesEditable ? 'Manage' : 'View' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse session-detail-row" id="session-details-{{ $session->id }}">
                                    <td colspan="5">
                                        <div class="session-detail-panel">
                                                <div class="section-toolbar session-detail-toolbar">
                                                    <div>
                                                        <h6 class="invigilation-section-title">{{ $session->display_name }}</h6>
                                                        <p class="invigilation-section-subtitle">
                                                            @if ($seriesEditable)
                                                                Update session details, add room allocations, and manage invigilator assignments.
                                                            @else
                                                                Review the published session schedule, room coverage, and assigned invigilators.
                                                            @endif
                                                        </p>
                                                    </div>
                                                    @if ($seriesEditable)
                                                        <div class="room-actions">
                                                            <button
                                                                class="btn btn-light btn-sm"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#room-create-{{ $session->id }}"
                                                                aria-expanded="false"
                                                            >
                                                                <i class="fas fa-door-open me-1"></i> Add Room
                                                            </button>
                                                            <button type="submit" form="session-update-{{ $session->id }}" class="btn btn-light btn-sm btn-loading">
                                                                <span class="btn-text"><i class="fas fa-save me-1"></i> Update Session</span>
                                                                <span class="btn-spinner d-none">
                                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                    Saving...
                                                                </span>
                                                            </button>
                                                            <form action="{{ route('invigilation.sessions.destroy', $session) }}" method="POST" onsubmit="return confirm('Delete this session and all related rooms and assignments?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash me-1"></i> Delete Session
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <div class="session-lock-copy">
                                                            <i class="fas fa-lock me-1"></i> Read-only roster view
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($seriesEditable)
                                                    <form action="{{ route('invigilation.sessions.update', $session) }}" method="POST" id="session-update-{{ $session->id }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="form-grid">
                                                            <div class="form-group grid-span-full">
                                                                <label class="form-label required-label">Subject</label>
                                                                <select class="form-select" name="grade_subject_id">
                                                                    @foreach ($gradeSubjects as $gradeSubject)
                                                                        <option value="{{ $gradeSubject->id }}" {{ (int) $session->grade_subject_id === (int) $gradeSubject->id ? 'selected' : '' }}>
                                                                            {{ $gradeSubject->grade?->name }} | {{ $gradeSubject->subject?->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label">Paper Label</label>
                                                                <input class="form-control" name="paper_label" value="{{ $session->paper_label }}" placeholder="Paper 1">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label required-label">Exam Date</label>
                                                                <input class="form-control" name="exam_date" type="date" value="{{ $session->exam_date?->format('Y-m-d') }}" placeholder="Select exam date">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label">Day of Cycle</label>
                                                                <input class="form-control" name="day_of_cycle" type="number" min="1" max="6" value="{{ $session->day_of_cycle }}" placeholder="Required when timetable checks are enabled">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label required-label">Start Time</label>
                                                                <input class="form-control" name="start_time" type="time" value="{{ substr((string) $session->start_time, 0, 5) }}" placeholder="08:00">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label required-label">End Time</label>
                                                                <input class="form-control" name="end_time" type="time" value="{{ substr((string) $session->end_time, 0, 5) }}" placeholder="09:00">
                                                            </div>
                                                            <div class="form-group grid-span-full">
                                                                <label class="form-label">Notes</label>
                                                                <textarea class="form-control" name="notes" rows="2" placeholder="Special setup notes for this sitting">{{ $session->notes }}</textarea>
                                                            </div>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="session-readonly-grid">
                                                        <div class="module-summary-card">
                                                            <div class="module-summary-label">Subject</div>
                                                            <div class="module-summary-value session-readonly-value">{{ $session->display_name }}</div>
                                                            <div class="module-summary-meta">{{ $session->gradeSubject?->grade?->name ?? 'No grade selected' }}</div>
                                                        </div>
                                                        <div class="module-summary-card">
                                                            <div class="module-summary-label">Schedule</div>
                                                            <div class="module-summary-value session-readonly-value">{{ $session->exam_date?->format('d M Y') }}</div>
                                                            <div class="module-summary-meta">{{ substr((string) $session->start_time, 0, 5) }} - {{ substr((string) $session->end_time, 0, 5) }}</div>
                                                        </div>
                                                        <div class="module-summary-card">
                                                            <div class="module-summary-label">Day of Cycle</div>
                                                            <div class="module-summary-value session-readonly-value">{{ $session->day_of_cycle ?: 'n/a' }}</div>
                                                            <div class="module-summary-meta">{{ $session->notes ?: 'No session notes recorded.' }}</div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="session-detail-summary">
                                                    {{ $requiredSlots }} invigilator slot(s) required across this session.
                                                </div>

                                                @if ($seriesEditable)
                                                    <div class="collapse mt-4" id="room-create-{{ $session->id }}">
                                                        <div class="card-shell session-inline-card">
                                                            <div class="card-body p-3">
                                                                <div class="invigilation-section-header">
                                                                    <div>
                                                                        <h6 class="mb-1">Add Room</h6>
                                                                        <p class="invigilation-section-subtitle">Attach a venue and candidate group to this sitting.</p>
                                                                    </div>
                                                                </div>

                                                                <form action="{{ route('invigilation.rooms.store', $session) }}" method="POST">
                                                                    @csrf
                                                                    <div class="form-grid">
                                                                    <div class="form-group">
                                                                        <label class="form-label required-label">Venue</label>
                                                                        <select class="form-select" name="venue_id">
                                                                            <option value="">Select venue</option>
                                                                            @foreach ($venues as $venue)
                                                                                <option value="{{ $venue->id }}">{{ $venue->name }} ({{ $venue->capacity }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label required-label">Source Type</label>
                                                                        <select class="form-select" name="source_type">
                                                                            @foreach (\App\Models\Invigilation\InvigilationSessionRoom::sourceTypes() as $key => $label)
                                                                                <option value="{{ $key }}">{{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label required-label">Required Invigilators</label>
                                                                        <input class="form-control" type="number" name="required_invigilators" min="1" max="10" value="{{ $series->default_required_invigilators }}">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label">Class Subject</label>
                                                                        <select class="form-select" name="klass_subject_id">
                                                                            <option value="">Not applicable</option>
                                                                            @foreach ($klassSubjectOptions[$session->id] ?? collect() as $klassSubject)
                                                                                <option value="{{ $klassSubject->id }}">
                                                                                    {{ $klassSubject->klass?->name }} | {{ $klassSubject->teacher?->full_name ?? 'No Teacher' }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label">Optional Group</label>
                                                                        <select class="form-select" name="optional_subject_id">
                                                                            <option value="">Not applicable</option>
                                                                            @foreach ($optionalSubjectOptions[$session->id] ?? collect() as $optionalSubject)
                                                                                <option value="{{ $optionalSubject->id }}">
                                                                                    {{ $optionalSubject->name }} | {{ $optionalSubject->teacher?->full_name ?? 'No Teacher' }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="form-label">Candidate Count</label>
                                                                        <input class="form-control" type="number" min="0" name="candidate_count" placeholder="Auto-filled if left blank">
                                                                    </div>
                                                                    <div class="form-group grid-span-full">
                                                                        <label class="form-label">Group Label</label>
                                                                        <input class="form-control" name="group_label" placeholder="Manual group label if needed">
                                                                    </div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end mt-3">
                                                                        <button type="submit" class="btn btn-primary btn-loading">
                                                                            <span class="btn-text"><i class="fas fa-plus me-1"></i> Add Room</span>
                                                                            <span class="btn-spinner d-none">
                                                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                                Creating...
                                                                            </span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="session-room-table mt-4">
                                                    @if ($session->rooms->isEmpty())
                                                        @include('invigilation.partials.empty-state', [
                                                            'icon' => 'fas fa-door-open',
                                                            'title' => 'No room allocations yet for this session.',
                                                            'copy' => 'Attach at least one venue and candidate group before assigning invigilators.',
                                                            'compact' => true,
                                                        ])
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table table-striped align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Venue</th>
                                                                        <th>Source</th>
                                                                        <th>Candidates</th>
                                                                        <th>Coverage</th>
                                                                        <th class="text-end">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($session->rooms as $room)
                                                                        <tr>
                                                                            <td>
                                                                                <strong>{{ $room->venue?->name ?? 'Unknown Venue' }}</strong>
                                                                                <div class="series-table-meta">{{ $room->resolved_group_label }}</div>
                                                                            </td>
                                                                            <td>
                                                                                {{ \App\Models\Invigilation\InvigilationSessionRoom::sourceTypes()[$room->source_type] ?? ucfirst($room->source_type) }}
                                                                                <div class="series-table-meta">
                                                                                    @if ($room->klassSubject)
                                                                                        {{ $room->klassSubject->klass?->name }}
                                                                                    @elseif ($room->optionalSubject)
                                                                                        {{ $room->optionalSubject->name }}
                                                                                    @else
                                                                                        Manual room
                                                                                    @endif
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <strong>{{ $room->candidate_count }}</strong>
                                                                                <div class="series-table-meta">
                                                                                    Capacity {{ $room->venue?->capacity ?? 'n/a' }}
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <strong>{{ $room->assignments->count() }}/{{ $room->required_invigilators }}</strong>
                                                                                <div class="series-table-meta">Invigilator slots filled</div>
                                                                            </td>
                                                                            <td class="text-end">
                                                                                <div class="room-actions justify-content-end">
                                                                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#room-details-{{ $room->id }}">
                                                                                        <i class="fas {{ $seriesEditable ? 'fa-edit' : 'fa-eye' }} me-1"></i>
                                                                                        {{ $seriesEditable ? 'Edit' : 'View' }}
                                                                                    </button>
                                                                                    @if ($seriesEditable)
                                                                                        <form action="{{ route('invigilation.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this room and its assignments?');">
                                                                                            @csrf
                                                                                            @method('DELETE')
                                                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                                                <i class="fas fa-trash me-1"></i> Delete
                                                                                            </button>
                                                                                        </form>
                                                                                    @endif
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="collapse" id="room-details-{{ $room->id }}">
                                                                            <td colspan="5">
                                                                                @if ($seriesEditable)
                                                                                    <form action="{{ route('invigilation.rooms.update', $room) }}" method="POST" class="mb-3">
                                                                                        @csrf
                                                                                        @method('PUT')
                                                                                        <div class="form-grid">
                                                                                            <div class="form-group">
                                                                                                <label class="form-label required-label">Venue</label>
                                                                                                <select class="form-select" name="venue_id">
                                                                                                    @foreach ($venues as $venue)
                                                                                                        <option value="{{ $venue->id }}" {{ (int) $room->venue_id === (int) $venue->id ? 'selected' : '' }}>
                                                                                                            {{ $venue->name }} ({{ $venue->capacity }})
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label required-label">Source Type</label>
                                                                                                <select class="form-select" name="source_type">
                                                                                                    @foreach (\App\Models\Invigilation\InvigilationSessionRoom::sourceTypes() as $key => $label)
                                                                                                        <option value="{{ $key }}" {{ $room->source_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label required-label">Required Invigilators</label>
                                                                                                <input class="form-control" type="number" min="1" max="10" name="required_invigilators" value="{{ $room->required_invigilators }}">
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">Class Subject</label>
                                                                                                <select class="form-select" name="klass_subject_id">
                                                                                                    <option value="">Not applicable</option>
                                                                                                    @foreach ($klassSubjectOptions[$session->id] ?? collect() as $klassSubject)
                                                                                                        <option value="{{ $klassSubject->id }}" {{ (int) $room->klass_subject_id === (int) $klassSubject->id ? 'selected' : '' }}>
                                                                                                            {{ $klassSubject->klass?->name }} | {{ $klassSubject->teacher?->full_name ?? 'No Teacher' }}
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">Optional Group</label>
                                                                                                <select class="form-select" name="optional_subject_id">
                                                                                                    <option value="">Not applicable</option>
                                                                                                    @foreach ($optionalSubjectOptions[$session->id] ?? collect() as $optionalSubject)
                                                                                                        <option value="{{ $optionalSubject->id }}" {{ (int) $room->optional_subject_id === (int) $optionalSubject->id ? 'selected' : '' }}>
                                                                                                            {{ $optionalSubject->name }} | {{ $optionalSubject->teacher?->full_name ?? 'No Teacher' }}
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                <label class="form-label">Candidate Count</label>
                                                                                                <input class="form-control" type="number" min="0" name="candidate_count" value="{{ $room->candidate_count }}" placeholder="Auto-filled if left blank">
                                                                                            </div>
                                                                                            <div class="form-group grid-span-full">
                                                                                                <label class="form-label">Group Label</label>
                                                                                                <input class="form-control" name="group_label" value="{{ $room->group_label }}" placeholder="Manual group label if needed">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="d-flex justify-content-end mt-3">
                                                                                            <button type="submit" class="btn btn-light btn-loading">
                                                                                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save Room</span>
                                                                                                <span class="btn-spinner d-none">
                                                                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                                                    Saving...
                                                                                                </span>
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                @else
                                                                                    <div class="session-readonly-grid room-readonly-grid">
                                                                                        <div class="module-summary-card">
                                                                                            <div class="module-summary-label">Venue</div>
                                                                                            <div class="module-summary-value session-readonly-value">{{ $room->venue?->name ?? 'Unknown Venue' }}</div>
                                                                                            <div class="module-summary-meta">Capacity {{ $room->venue?->capacity ?? 'n/a' }}</div>
                                                                                        </div>
                                                                                        <div class="module-summary-card">
                                                                                            <div class="module-summary-label">Room Group</div>
                                                                                            <div class="module-summary-value session-readonly-value">{{ $room->resolved_group_label }}</div>
                                                                                            <div class="module-summary-meta">{{ \App\Models\Invigilation\InvigilationSessionRoom::sourceTypes()[$room->source_type] ?? ucfirst($room->source_type) }}</div>
                                                                                        </div>
                                                                                        <div class="module-summary-card">
                                                                                            <div class="module-summary-label">Coverage</div>
                                                                                            <div class="module-summary-value session-readonly-value">{{ $room->assignments->count() }}/{{ $room->required_invigilators }}</div>
                                                                                            <div class="module-summary-meta">{{ $room->candidate_count }} candidate(s)</div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif

                                                                                <div class="assignment-stack">
                                                                                    <div class="assignment-stack-header">
                                                                                        <div>
                                                                                            <h6 class="assignment-stack-title">Invigilator Assignments</h6>
                                                                                            <p class="assignment-stack-copy">
                                                                                                @if ($seriesEditable)
                                                                                                    Assign teachers to this room, add notes where needed, and lock assignments that should stay fixed during regeneration.
                                                                                                @else
                                                                                                    Review the published assignment list for this room.
                                                                                                @endif
                                                                                            </p>
                                                                                        </div>
                                                                                        <span class="summary-chip pill-muted">{{ $room->assignments->count() }}/{{ $room->required_invigilators }} filled</span>
                                                                                    </div>
                                                                                    @if ($seriesEditable)
                                                                                        @foreach ($room->assignments as $assignment)
                                                                                            <div class="assignment-row assignment-row-editable {{ $assignment->assignment_source === 'auto' ? 'assignment-auto' : '' }} {{ $assignment->locked ? 'assignment-locked' : '' }}">
                                                                                                <form action="{{ route('invigilation.assignments.update', $assignment) }}" method="POST" id="assignment-update-{{ $assignment->id }}" class="assignment-inline-form">
                                                                                                    @csrf
                                                                                                    @method('PUT')
                                                                                                </form>
                                                                                                <form action="{{ route('invigilation.assignments.destroy', $assignment) }}" method="POST" id="assignment-delete-{{ $assignment->id }}" class="assignment-inline-form" onsubmit="return confirm('Remove this assignment?');">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                </form>
                                                                                                <div class="assignment-edit-grid">
                                                                                                    <div class="assignment-field">
                                                                                                        <label class="form-label mb-1 required-label">Assignment #{{ $assignment->assignment_order }}</label>
                                                                                                        <select class="form-select" name="user_id" form="assignment-update-{{ $assignment->id }}">
                                                                                                            @foreach ($teachers as $teacher)
                                                                                                                <option value="{{ $teacher->id }}" {{ (int) $assignment->user_id === (int) $teacher->id ? 'selected' : '' }}>
                                                                                                                    {{ $teacher->full_name }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                    <div class="assignment-field">
                                                                                                        <label class="form-label mb-1">Notes</label>
                                                                                                        <input class="form-control" name="notes" value="{{ $assignment->notes }}" placeholder="Optional assignment notes" form="assignment-update-{{ $assignment->id }}">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="assignment-row-footer">
                                                                                                    <div class="assignment-meta">
                                                                                                        {{ ucfirst($assignment->assignment_source) }} assignment
                                                                                                    </div>
                                                                                                    <div class="assignment-row-controls">
                                                                                                        <div class="assignment-lock-toggle">
                                                                                                            <div class="form-check">
                                                                                                                <input class="form-check-input" type="checkbox" name="locked" id="locked-{{ $assignment->id }}" value="1" {{ $assignment->locked ? 'checked' : '' }} form="assignment-update-{{ $assignment->id }}">
                                                                                                                <label class="form-check-label" for="locked-{{ $assignment->id }}">Lock</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="assignment-actions">
                                                                                                            <button type="submit" form="assignment-update-{{ $assignment->id }}" class="btn btn-light btn-sm btn-loading">
                                                                                                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save</span>
                                                                                                                <span class="btn-spinner d-none">
                                                                                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                                                                    Saving...
                                                                                                                </span>
                                                                                                            </button>
                                                                                                            <button type="submit" form="assignment-delete-{{ $assignment->id }}" class="btn btn-danger btn-sm">
                                                                                                                <i class="fas fa-trash me-1"></i> Delete
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach

                                                                                        @if ($room->assignments->count() < $room->required_invigilators)
                                                                                            <form action="{{ route('invigilation.assignments.store', $room) }}" method="POST" class="assignment-row assignment-row-create">
                                                                                                @csrf
                                                                                                <div class="assignment-edit-grid">
                                                                                                    <div class="assignment-field">
                                                                                                        <label class="form-label mb-1 required-label">Add Invigilator</label>
                                                                                                        <select class="form-select" name="user_id">
                                                                                                            <option value="">Select teacher</option>
                                                                                                            @foreach ($teachers as $teacher)
                                                                                                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </div>
                                                                                                    <div class="assignment-field">
                                                                                                        <label class="form-label mb-1">Notes</label>
                                                                                                        <input class="form-control" name="notes" placeholder="Optional assignment note">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="assignment-row-footer">
                                                                                                    <div class="assignment-meta">
                                                                                                        Manual assignment
                                                                                                    </div>
                                                                                                    <div class="assignment-row-controls">
                                                                                                        <div class="assignment-lock-toggle">
                                                                                                            <div class="form-check">
                                                                                                                <input class="form-check-input" type="checkbox" name="locked" id="new-lock-{{ $room->id }}" value="1">
                                                                                                                <label class="form-check-label" for="new-lock-{{ $room->id }}">Lock</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="assignment-actions">
                                                                                                            <button type="submit" class="btn btn-primary btn-sm btn-loading">
                                                                                                                <span class="btn-text"><i class="fas fa-plus me-1"></i> Add</span>
                                                                                                                <span class="btn-spinner d-none">
                                                                                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                                                                    Creating...
                                                                                                                </span>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </form>
                                                                                        @endif
                                                                                    @else
                                                                                        @forelse ($room->assignments as $assignment)
                                                                                            <div class="assignment-row assignment-readonly {{ $assignment->assignment_source === 'auto' ? 'assignment-auto' : '' }} {{ $assignment->locked ? 'assignment-locked' : '' }}">
                                                                                                <div class="assignment-field">
                                                                                                    <label class="form-label mb-1">Assignment #{{ $assignment->assignment_order }}</label>
                                                                                                    <div class="session-readonly-value">{{ $assignment->user?->full_name ?? 'Unassigned Teacher' }}</div>
                                                                                                    <div class="assignment-meta mt-1">{{ ucfirst($assignment->assignment_source) }} assignment</div>
                                                                                                </div>
                                                                                                <div class="assignment-field">
                                                                                                    <label class="form-label mb-1">Notes</label>
                                                                                                    <div class="assignment-meta assignment-note-copy">{{ $assignment->notes ?: 'No notes recorded.' }}</div>
                                                                                                </div>
                                                                                                <div class="assignment-lock-state">
                                                                                                    <span class="summary-chip pill-muted">{{ $assignment->locked ? 'Locked' : 'Unlocked' }}</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        @empty
                                                                                            @include('invigilation.partials.empty-state', [
                                                                                                'icon' => 'fas fa-user-check',
                                                                                                'title' => 'No invigilators assigned to this room yet.',
                                                                                                'copy' => 'This room has not been staffed in the published roster snapshot.',
                                                                                                'compact' => true,
                                                                                            ])
                                                                                        @endforelse
                                                                                    @endif
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="invigilation-empty-cell">
                                        @include('invigilation.partials.empty-state', [
                                            'icon' => 'fas fa-calendar-plus',
                                            'title' => 'No sessions have been added to this series yet.',
                                            'copy' => 'Use the Add Session button to create the first exam sitting, then expand the row to manage rooms and assignments.',
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($seriesEditable)
        <div class="modal fade invigilation-modal" id="seriesSettingsModal" tabindex="-1" aria-labelledby="seriesSettingsModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1" id="seriesSettingsModalLabel">Series Settings</h5>
                            <p class="mb-0 opacity-75">Adjust rules, staffing defaults, and schedule handling for this series.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('invigilation.update', $series) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="balancing_policy" value="balanced">
                        <input type="hidden" name="modal_context" value="series-settings">
                        <div class="modal-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required-label" for="series_name">Series Name</label>
                                    <input class="form-control" id="series_name" name="name" value="{{ old('name', $series->name) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="series_type">Type</label>
                                    <select class="form-select" id="series_type" name="type">
                                        @foreach ($seriesTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('type', $series->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="series_eligibility_policy">Eligibility Policy</label>
                                    <select class="form-select" id="series_eligibility_policy" name="eligibility_policy">
                                        @foreach ($eligibilityPolicies as $key => $label)
                                            <option value="{{ $key }}" {{ old('eligibility_policy', $series->eligibility_policy) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="series_timetable_conflict_policy">Timetable Conflict Policy</label>
                                    <select class="form-select" id="series_timetable_conflict_policy" name="timetable_conflict_policy">
                                        @foreach ($timetablePolicies as $key => $label)
                                            <option value="{{ $key }}" {{ old('timetable_conflict_policy', $series->timetable_conflict_policy) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="series_default_required_invigilators">Default Invigilators / Room</label>
                                    <input class="form-control" id="series_default_required_invigilators" name="default_required_invigilators" type="number" min="1" max="10" value="{{ old('default_required_invigilators', $series->default_required_invigilators) }}">
                                </div>
                                <div class="form-group grid-span-full">
                                    <label class="form-label" for="series_notes">Notes</label>
                                    <textarea class="form-control" id="series_notes" name="notes" rows="4">{{ old('modal_context') === 'series-settings' ? old('notes', $series->notes) : $series->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save Series</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade invigilation-modal" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1" id="addSessionModalLabel">Add Exam Session</h5>
                            <p class="mb-0 opacity-75">Create a subject sitting before assigning rooms and invigilators.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('invigilation.sessions.store', $series) }}" method="POST">
                        @csrf
                        <input type="hidden" name="modal_context" value="add-session">
                        <div class="modal-body">
                            <div class="form-grid">
                                <div class="form-group grid-span-full">
                                    <label class="form-label required-label" for="grade_subject_id">Subject</label>
                                    <select class="form-select invigilation-subject-select" id="grade_subject_id" name="grade_subject_id" data-placeholder="Search subject">
                                        <option value="">Select subject</option>
                                        @foreach ($gradeSubjects as $gradeSubject)
                                            <option
                                                value="{{ $gradeSubject->id }}"
                                                data-subject="{{ $gradeSubject->subject?->name }}"
                                                data-grade="{{ $gradeSubject->grade?->name }}"
                                                {{ (int) old('grade_subject_id', 0) === (int) $gradeSubject->id ? 'selected' : '' }}
                                            >
                                                {{ $gradeSubject->subject?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="paper_label">Paper Label</label>
                                    <input class="form-control" id="paper_label" name="paper_label" value="{{ old('paper_label') }}" placeholder="Paper 1">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="exam_date">Exam Date</label>
                                    <input class="form-control" id="exam_date" name="exam_date" type="date" value="{{ old('exam_date') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="day_of_cycle">Day of Cycle</label>
                                    <input class="form-control" id="day_of_cycle" name="day_of_cycle" type="number" min="1" max="6" value="{{ old('day_of_cycle') }}" placeholder="Optional unless timetable checks are enabled">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="start_time">Start Time</label>
                                    <input class="form-control" id="start_time" name="start_time" type="time" value="{{ old('start_time') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label" for="end_time">End Time</label>
                                    <input class="form-control" id="end_time" name="end_time" type="time" value="{{ old('end_time') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="initial_room_required_invigilators">Required Invigilators</label>
                                    <input class="form-control" id="initial_room_required_invigilators" name="initial_room_required_invigilators" type="number" min="1" max="10" value="{{ old('initial_room_required_invigilators', $series->default_required_invigilators) }}" placeholder="Default room staffing">
                                </div>
                                <div class="form-group grid-span-full">
                                    <label class="form-label" for="session_notes">Notes</label>
                                    <textarea class="form-control" id="session_notes" name="notes" rows="3" placeholder="Special setup notes for this sitting">{{ old('modal_context') === 'add-session' ? old('notes') : '' }}</textarea>
                                </div>
                                <div class="form-group grid-span-full">
                                    <div class="session-modal-subsection">
                                        <div class="session-modal-subsection-title">Create First Room (Optional)</div>
                                        <div class="session-modal-subsection-copy">
                                            Add the first venue now so the session starts with a room allocation. You can still use Add Room later for more venues.
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="initial_room_venue_id">Venue</label>
                                    <select class="form-select" id="initial_room_venue_id" name="initial_room_venue_id">
                                        <option value="">Create session only</option>
                                        @foreach ($venues as $venue)
                                            <option value="{{ $venue->id }}" {{ (int) old('initial_room_venue_id', 0) === (int) $venue->id ? 'selected' : '' }}>
                                                {{ $venue->name }} ({{ $venue->capacity }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="initial_room_source_type">Source Type</label>
                                    <select class="form-select" id="initial_room_source_type" name="initial_room_source_type">
                                        @foreach (\App\Models\Invigilation\InvigilationSessionRoom::sourceTypes() as $key => $label)
                                            <option value="{{ $key }}" {{ old('initial_room_source_type', \App\Models\Invigilation\InvigilationSessionRoom::SOURCE_MANUAL) === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted mt-2" id="initial_room_source_help">
                                        Choose how this first room maps to the selected subject.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="initial_room_candidate_count">Candidate Count</label>
                                    <input class="form-control" id="initial_room_candidate_count" name="initial_room_candidate_count" type="number" min="0" value="{{ old('initial_room_candidate_count') }}" placeholder="Auto-filled if left blank">
                                </div>
                                <div class="form-group initial-room-source-field initial-room-klass-field">
                                    <label class="form-label" for="initial_room_klass_subject_id">Class Allocation</label>
                                    <select class="form-select" id="initial_room_klass_subject_id" name="initial_room_klass_subject_id">
                                        <option value="">Select class allocation</option>
                                        @foreach ($addSessionKlassSubjectOptions as $gradeSubjectId => $klassSubjects)
                                            @foreach ($klassSubjects as $klassSubject)
                                                <option
                                                    value="{{ $klassSubject->id }}"
                                                    data-grade-subject-id="{{ $gradeSubjectId }}"
                                                    {{ (int) old('initial_room_klass_subject_id', 0) === (int) $klassSubject->id ? 'selected' : '' }}
                                                >
                                                    {{ $klassSubject->klass?->name }} | {{ $klassSubject->teacher?->full_name ?? 'No Teacher' }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group initial-room-source-field initial-room-optional-field">
                                    <label class="form-label" for="initial_room_optional_subject_id">Optional Group</label>
                                    <select class="form-select" id="initial_room_optional_subject_id" name="initial_room_optional_subject_id">
                                        <option value="">Not applicable</option>
                                        @foreach ($addSessionOptionalSubjectOptions as $gradeSubjectId => $optionalSubjects)
                                            @foreach ($optionalSubjects as $optionalSubject)
                                                <option
                                                    value="{{ $optionalSubject->id }}"
                                                    data-grade-subject-id="{{ $gradeSubjectId }}"
                                                    {{ (int) old('initial_room_optional_subject_id', 0) === (int) $optionalSubject->id ? 'selected' : '' }}
                                                >
                                                    {{ $optionalSubject->name }} | {{ $optionalSubject->teacher?->full_name ?? 'No Teacher' }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group grid-span-full">
                                    <label class="form-label" for="initial_room_group_label">Group Label</label>
                                    <input class="form-control" id="initial_room_group_label" name="initial_room_group_label" value="{{ old('initial_room_group_label') }}" placeholder="Manual group label if needed">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-plus me-1"></i> Add Session</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade invigilation-modal" id="issuesModal" tabindex="-1" aria-labelledby="issuesModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="issuesModalLabel">Issue Summary</h5>
                        <p class="mb-0 opacity-75">Review shortages and conflicts before publishing this series.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="module-summary-grid">
                        <div class="module-summary-card">
                            <div class="module-summary-label">Shortages</div>
                            <div class="module-summary-value">{{ $issueSummary['shortages'] }}</div>
                            <div class="module-summary-meta">Rooms missing required invigilators</div>
                        </div>
                        <div class="module-summary-card">
                            <div class="module-summary-label">Teacher Conflicts</div>
                            <div class="module-summary-value">{{ $issueSummary['teacher_conflicts'] + $issueSummary['timetable_conflicts'] }}</div>
                            <div class="module-summary-meta">Overlap and timetable clashes</div>
                        </div>
                        <div class="module-summary-card">
                            <div class="module-summary-label">Room / Policy Issues</div>
                            <div class="module-summary-value">{{ $issueSummary['room_conflicts'] + $issueSummary['eligibility_conflicts'] }}</div>
                            <div class="module-summary-meta">Room overlap and eligibility blockers</div>
                        </div>
                    </div>

                    @if ($flattenedIssues->isEmpty())
                        @include('invigilation.partials.empty-state', [
                            'icon' => 'fas fa-check-circle',
                            'title' => 'No shortages or conflicts detected right now.',
                            'copy' => 'This series currently has no visible staffing, venue, timetable, or policy blockers.',
                            'compact' => true,
                        ])
                    @else
                        <div class="issue-list issue-list-modal">
                            @foreach ($flattenedIssues as $issue)
                                <div class="issue-card {{ str_contains($issue['category'], 'conflict') ? 'issue-danger' : 'issue-warning' }}">
                                    <div class="issue-card-title">{{ $issue['title'] }}</div>
                                    <div class="issue-card-copy">{{ $issue['detail'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('invigilation.partials.form-loading-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.bootstrap) {
                return;
            }

            if (window.jQuery && $.fn.select2) {
                const renderSubjectOption = function(option) {
                    if (!option.id) {
                        return option.text;
                    }

                    const subject = option.element?.dataset?.subject || option.text;
                    const grade = option.element?.dataset?.grade || '';

                    return $(
                        '<span class="invigilation-select2-option">' +
                            '<span class="invigilation-select2-subject"></span>' +
                            '<span class="invigilation-select2-grade"></span>' +
                        '</span>'
                    )
                        .find('.invigilation-select2-subject')
                        .text(subject)
                        .end()
                        .find('.invigilation-select2-grade')
                        .text(grade)
                        .end();
                };

                const renderSubjectSelection = function(option) {
                    if (!option.id) {
                        return option.text;
                    }

                    const subject = option.element?.dataset?.subject || option.text;
                    const grade = option.element?.dataset?.grade || '';

                    return grade ? `${subject} | ${grade}` : subject;
                };

                $('.invigilation-subject-select').select2({
                    width: '100%',
                    dropdownParent: $('#addSessionModal'),
                    placeholder: $('#grade_subject_id').data('placeholder') || 'Search subject',
                    allowClear: true,
                    templateResult: renderSubjectOption,
                    templateSelection: renderSubjectSelection,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            }

            const subjectSelect = document.getElementById('grade_subject_id');
            const sourceTypeSelect = document.getElementById('initial_room_source_type');
            const klassSubjectSelect = document.getElementById('initial_room_klass_subject_id');
            const optionalSubjectSelect = document.getElementById('initial_room_optional_subject_id');
            const klassField = document.querySelector('.initial-room-klass-field');
            const optionalField = document.querySelector('.initial-room-optional-field');
            const sourceHelp = document.getElementById('initial_room_source_help');

            const filterLinkedRoomOptions = function(selectElement, gradeSubjectId) {
                if (!selectElement) {
                    return;
                }

                Array.from(selectElement.options).forEach(function(option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    const matches = gradeSubjectId !== '' && option.dataset.gradeSubjectId === gradeSubjectId;
                    option.hidden = !matches;
                    option.disabled = !matches;

                    if (!matches && option.selected) {
                        selectElement.value = '';
                    }
                });
            };

            const visibleLinkedOptionCount = function(selectElement) {
                if (!selectElement) {
                    return 0;
                }

                return Array.from(selectElement.options).filter(function(option, index) {
                    return index > 0 && !option.hidden && !option.disabled;
                }).length;
            };

            const firstVisibleLinkedOption = function(selectElement) {
                if (!selectElement) {
                    return null;
                }

                return Array.from(selectElement.options).find(function(option, index) {
                    return index > 0 && !option.hidden && !option.disabled;
                }) || null;
            };

            const updateInitialRoomSourceAvailability = function() {
                if (!sourceTypeSelect) {
                    return;
                }

                const klassSourceOption = sourceTypeSelect.querySelector('option[value="klass_subject"]');
                const optionalSourceOption = sourceTypeSelect.querySelector('option[value="optional_subject"]');
                const klassOptionCount = visibleLinkedOptionCount(klassSubjectSelect);
                const optionalOptionCount = visibleLinkedOptionCount(optionalSubjectSelect);

                if (klassSourceOption) {
                    klassSourceOption.disabled = klassOptionCount === 0;
                }

                if (optionalSourceOption) {
                    optionalSourceOption.disabled = optionalOptionCount === 0;
                }

                if (sourceTypeSelect.value === 'klass_subject' && klassOptionCount === 0) {
                    sourceTypeSelect.value = 'manual';
                }

                if (sourceTypeSelect.value === 'optional_subject' && optionalOptionCount === 0) {
                    sourceTypeSelect.value = 'manual';
                }
            };

            const toggleInitialRoomSourceFields = function() {
                if (!sourceTypeSelect) {
                    return;
                }

                const sourceType = sourceTypeSelect.value;
                const klassOptionCount = visibleLinkedOptionCount(klassSubjectSelect);
                const optionalOptionCount = visibleLinkedOptionCount(optionalSubjectSelect);

                if (sourceHelp) {
                    if (sourceType === 'klass_subject') {
                        sourceHelp.textContent = klassOptionCount > 0
                            ? 'Choose the specific class allocation for this room, such as F1A Science.'
                            : 'No class allocations are available for this subject yet. Use Manual / Mixed Room instead.';
                    } else if (sourceType === 'optional_subject') {
                        sourceHelp.textContent = optionalOptionCount > 0
                            ? 'Choose the specific optional subject group for this room.'
                            : 'No optional subject groups are available for this subject yet. Use Manual / Mixed Room instead.';
                    } else {
                        sourceHelp.textContent = 'Use Manual / Mixed Room when this venue is not tied to one specific class allocation.';
                    }
                }

                if (klassField) {
                    klassField.style.display = sourceType === 'klass_subject' ? 'block' : 'none';
                }

                if (optionalField) {
                    optionalField.style.display = sourceType === 'optional_subject' ? 'block' : 'none';
                }

                if (sourceType === 'klass_subject' && klassSubjectSelect && !klassSubjectSelect.value && klassOptionCount === 1) {
                    const firstOption = firstVisibleLinkedOption(klassSubjectSelect);
                    if (firstOption) {
                        klassSubjectSelect.value = firstOption.value;
                    }
                }

                if (sourceType === 'optional_subject' && optionalSubjectSelect && !optionalSubjectSelect.value && optionalOptionCount === 1) {
                    const firstOption = firstVisibleLinkedOption(optionalSubjectSelect);
                    if (firstOption) {
                        optionalSubjectSelect.value = firstOption.value;
                    }
                }

                if (sourceType !== 'klass_subject' && klassSubjectSelect) {
                    klassSubjectSelect.value = '';
                }

                if (sourceType !== 'optional_subject' && optionalSubjectSelect) {
                    optionalSubjectSelect.value = '';
                }
            };

            const syncInitialRoomOptions = function() {
                const gradeSubjectId = subjectSelect ? subjectSelect.value : '';

                filterLinkedRoomOptions(klassSubjectSelect, gradeSubjectId);
                filterLinkedRoomOptions(optionalSubjectSelect, gradeSubjectId);
                updateInitialRoomSourceAvailability();
                toggleInitialRoomSourceFields();
            };

            if (subjectSelect) {
                subjectSelect.addEventListener('change', syncInitialRoomOptions);
            }

            if (window.jQuery && subjectSelect) {
                $(subjectSelect).on('change.select2', syncInitialRoomOptions);
            }

            if (sourceTypeSelect) {
                sourceTypeSelect.addEventListener('change', toggleInitialRoomSourceFields);
            }

            syncInitialRoomOptions();

            const modalContext = @json(old('modal_context'));
            const modalMap = {
                'series-settings': 'seriesSettingsModal',
                'add-session': 'addSessionModal',
            };
            const modalId = modalMap[modalContext];

            if (!modalId) {
                return;
            }

            const modalElement = document.getElementById(modalId);

            if (modalElement) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        });
    </script>
@endsection

@extends('layouts.master')

@section('title')
    Session Attendance
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }} Attendance
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Session Attendance</h1>
                <p class="page-subtitle">Capture attendance against a concrete session and finalize it only when every eligible student has been marked.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'schedules'])

        <div class="info-note">
            <div class="help-title">Session Context</div>
            <div class="help-content">
                {{ optional($session->session_date)->format('d M Y') }} |
                {{ $session->start_datetime?->format('H:i') ?: 'No start time' }}
                @if ($session->end_datetime)
                    - {{ $session->end_datetime->format('H:i') }}
                @endif
                | {{ $session->location ?: ($activity->default_location ?: 'No location set') }}
            </div>
        </div>

        @if ($session->attendance_locked)
            <div class="help-text">
                <div class="help-title">Attendance Locked</div>
                <div class="help-content">
                    This session has been finalized. Only privileged operators can reopen it for corrections.
                </div>
            </div>
        @endif

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Eligible Students</div>
                    <div class="roster-summary-value">{{ $eligibleEnrollments->count() }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Marked Rows</div>
                    <div class="roster-summary-value">{{ $attendanceSummary['marked_count'] }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Session Status</div>
                    <div class="detail-value">{{ \App\Models\Activities\ActivitySession::statuses()[$session->status] ?? ucfirst($session->status) }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Attendance State</div>
                    <div class="detail-value">{{ $session->attendance_locked ? 'Locked' : 'Open' }}</div>
                </div>
            </div>
        </div>

        <div class="card-shell">
            <div class="card-body p-4">
                <div class="management-header">
                    <div>
                        <h5 class="summary-card-title mb-0">Attendance Register</h5>
                        <p class="management-subtitle">Only students enrolled on the session date appear here. Historical students remain visible if they left after this session occurred.</p>
                    </div>
                </div>

                @if ($eligibleEnrollments->isNotEmpty())
                    <form action="{{ route('activities.attendance.update', [$activity, $session]) }}"
                        method="POST"
                        id="activity-attendance-form"
                        class="needs-validation"
                        novalidate
                        data-activity-form>
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Grade & Class</th>
                                        <th>House</th>
                                        <th>Attendance</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($eligibleEnrollments as $enrollment)
                                        @php
                                            $attendanceRecord = $attendanceMap->get($enrollment->id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="management-item-title">{{ $enrollment->student?->full_name ?: 'Unknown student' }}</div>
                                                <div class="management-item-notes mt-1">
                                                    Joined {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="summary-chip-group">
                                                    @if ($enrollment->gradeSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                                    @endif
                                                    @if ($enrollment->klassSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if ($enrollment->houseSnapshot?->name)
                                                    <span class="summary-chip pill-muted">{{ $enrollment->houseSnapshot->name }}</span>
                                                @else
                                                    <span class="summary-empty">No house snapshot</span>
                                                @endif
                                            </td>
                                            <td style="min-width: 200px;">
                                                <select class="form-select"
                                                    name="attendance[{{ $enrollment->id }}][status]"
                                                    {{ $session->attendance_locked ? 'disabled' : '' }}
                                                    required>
                                                    <option value="">Select status</option>
                                                    @foreach ($attendanceStates as $key => $label)
                                                        <option value="{{ $key }}"
                                                            {{ old('attendance.' . $enrollment->id . '.status', $attendanceRecord?->status) === $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control"
                                                    name="attendance[{{ $enrollment->id }}][remarks]"
                                                    value="{{ old('attendance.' . $enrollment->id . '.remarks', $attendanceRecord?->remarks) }}"
                                                    placeholder="Optional attendance remark"
                                                    {{ $session->attendance_locked ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="form-actions">
                        <a href="{{ route('activities.schedules.index', $activity) }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Back
                        </a>
                        @if (!$session->attendance_locked)
                            <button type="submit" form="activity-attendance-form" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Attendance</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>

                            <form action="{{ route('activities.attendance.finalize', [$activity, $session]) }}" method="POST" data-activity-form class="needs-validation" novalidate>
                                @csrf
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-lock"></i> Finalize Attendance</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </form>
                        @elseif ($canReopenAttendance)
                            <form action="{{ route('activities.attendance.reopen', [$activity, $session]) }}" method="POST" data-activity-form class="needs-validation" novalidate>
                                @csrf
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-unlock"></i> Reopen Attendance</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="summary-empty mb-0">No enrolled students were active for this session date.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection

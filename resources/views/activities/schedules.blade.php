@extends('layouts.master')

@section('title')
    Activity Schedules
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
            {{ $activity->name }} Schedules
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Schedules, Sessions, and Attendance</h1>
                <p class="page-subtitle">Build recurring patterns, generate dated sessions without duplicate collisions, and keep attendance work visible.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'schedules'])

        <div class="help-text">
            <div class="help-title">Scheduling Guidance</div>
            <div class="help-content">
                Use recurring schedules to generate session dates safely, manage one-off sessions, and open attendance from the session register. Attendance is always recorded against a dated session, and finalized sessions stay read-only until reopened by an authorized user.
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Active Schedules</div>
                    <div class="roster-summary-value">{{ $activity->active_schedules_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Upcoming Sessions</div>
                    <div class="roster-summary-value">{{ $activity->upcoming_sessions_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Pending Attendance</div>
                    <div class="roster-summary-value">{{ $activity->pending_attendance_sessions_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Next Session</div>
                    <div class="detail-value">
                        @if ($nextSession)
                            {{ optional($nextSession->session_date)->format('d M Y') }}
                        @else
                            No future session
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                @can('manageSchedules', $activity)
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Create Recurring Schedule</h5>
                            <p class="management-subtitle">Use weekly or biweekly patterns to prepare term-aware session generation.</p>

                            <form action="{{ route('activities.schedules.store', $activity) }}"
                                method="POST"
                                id="activity-schedule-form"
                                class="needs-validation"
                                novalidate
                                data-activity-form>
                                @csrf

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-frequency">Frequency <span class="text-danger">*</span></label>
                                        <select class="form-select @error('frequency') is-invalid @enderror" id="schedule-frequency" name="frequency" required>
                                            <option value="">Select frequency</option>
                                            @foreach ($scheduleFrequencies as $key => $label)
                                                <option value="{{ $key }}" {{ old('frequency') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('frequency')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-day-of-week">Meeting Day <span class="text-danger">*</span></label>
                                        <select class="form-select @error('day_of_week') is-invalid @enderror" id="schedule-day-of-week" name="day_of_week" required>
                                            <option value="">Select day</option>
                                            @foreach ($scheduleDays as $key => $label)
                                                <option value="{{ $key }}" {{ (string) old('day_of_week') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('day_of_week')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-location">Location</label>
                                        <input type="text"
                                            class="form-control @error('location') is-invalid @enderror"
                                            id="schedule-location"
                                            name="location"
                                            value="{{ old('location', $activity->default_location) }}"
                                            placeholder="Use the normal activity venue">
                                        @error('location')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-start-time">Start Time <span class="text-danger">*</span></label>
                                        <input type="time"
                                            class="form-control @error('start_time') is-invalid @enderror"
                                            id="schedule-start-time"
                                            name="start_time"
                                            value="{{ old('start_time') }}"
                                            placeholder="15:00"
                                            required>
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-end-time">End Time <span class="text-danger">*</span></label>
                                        <input type="time"
                                            class="form-control @error('end_time') is-invalid @enderror"
                                            id="schedule-end-time"
                                            name="end_time"
                                            value="{{ old('end_time') }}"
                                            placeholder="16:00"
                                            required>
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-active">Schedule State</label>
                                        <select class="form-select" id="schedule-active" name="active">
                                            <option value="1" {{ old('active', '1') === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('active') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-start-date">Start Date <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            id="schedule-start-date"
                                            name="start_date"
                                            value="{{ old('start_date', optional($activity->term?->start_date)->format('Y-m-d')) }}"
                                            placeholder="YYYY-MM-DD"
                                            required>
                                        @error('start_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="schedule-end-date">End Date</label>
                                        <input type="date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            id="schedule-end-date"
                                            name="end_date"
                                            value="{{ old('end_date', optional($activity->term?->end_date)->format('Y-m-d')) }}"
                                            placeholder="YYYY-MM-DD">
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group grid-span-full mt-3">
                                    <label class="form-label" for="schedule-notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                        id="schedule-notes"
                                        name="notes"
                                        rows="3"
                                        placeholder="Add schedule notes, exceptions, or coordination instructions.">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Schedule</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endcan

                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Recurring Schedules</h5>
                                <p class="management-subtitle">Review existing schedule patterns and generate dated sessions inside a bounded range.</p>
                            </div>
                        </div>

                        @if ($schedules->isNotEmpty())
                            <div class="management-list">
                                @foreach ($schedules as $schedule)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">
                                                    {{ $scheduleDays[$schedule->day_of_week] ?? 'Unknown day' }}
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                                    @if ($schedule->end_time)
                                                        - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                    @endif
                                                </div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip pill-muted">{{ $scheduleFrequencies[$schedule->frequency] ?? ucfirst($schedule->frequency) }}</span>
                                                    <span class="summary-chip {{ $schedule->active ? 'enrollment-status-active' : 'status-archived' }}">
                                                        {{ $schedule->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    <span class="summary-chip pill-muted">{{ $schedule->sessions_count }} generated session(s)</span>
                                                    <span class="summary-chip pill-muted">
                                                        {{ optional($schedule->start_date)->format('d M Y') }} to {{ optional($schedule->end_date)->format('d M Y') ?: 'Open ended' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($schedule->notes)
                                            <div class="management-item-notes">{{ $schedule->notes }}</div>
                                        @endif

                                        @can('manageSchedules', $activity)
                                            <form action="{{ route('activities.schedules.update', [$activity, $schedule]) }}"
                                                method="POST"
                                                class="needs-validation mt-3"
                                                novalidate
                                                data-activity-form>
                                                @csrf
                                                @method('PATCH')

                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label class="form-label" for="frequency-{{ $schedule->id }}">Frequency</label>
                                                        <select class="form-select" id="frequency-{{ $schedule->id }}" name="frequency" required>
                                                            @foreach ($scheduleFrequencies as $key => $label)
                                                                <option value="{{ $key }}" {{ $schedule->frequency === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="day-{{ $schedule->id }}">Meeting Day</label>
                                                        <select class="form-select" id="day-{{ $schedule->id }}" name="day_of_week" required>
                                                            @foreach ($scheduleDays as $key => $label)
                                                                <option value="{{ $key }}" {{ (int) $schedule->day_of_week === (int) $key ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="location-{{ $schedule->id }}">Location</label>
                                                        <input type="text" class="form-control" id="location-{{ $schedule->id }}" name="location" value="{{ $schedule->location }}" placeholder="Use the normal activity venue">
                                                    </div>
                                                </div>

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="start-time-{{ $schedule->id }}">Start Time</label>
                                                        <input type="time" class="form-control" id="start-time-{{ $schedule->id }}" name="start_time" value="{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}" placeholder="15:00" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="end-time-{{ $schedule->id }}">End Time</label>
                                                        <input type="time" class="form-control" id="end-time-{{ $schedule->id }}" name="end_time" value="{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '' }}" placeholder="16:00" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="active-{{ $schedule->id }}">Schedule State</label>
                                                        <select class="form-select" id="active-{{ $schedule->id }}" name="active">
                                                            <option value="1" {{ $schedule->active ? 'selected' : '' }}>Active</option>
                                                            <option value="0" {{ !$schedule->active ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="start-date-{{ $schedule->id }}">Start Date</label>
                                                        <input type="date" class="form-control" id="start-date-{{ $schedule->id }}" name="start_date" value="{{ optional($schedule->start_date)->format('Y-m-d') }}" placeholder="YYYY-MM-DD" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="end-date-{{ $schedule->id }}">End Date</label>
                                                        <input type="date" class="form-control" id="end-date-{{ $schedule->id }}" name="end_date" value="{{ optional($schedule->end_date)->format('Y-m-d') }}" placeholder="YYYY-MM-DD">
                                                    </div>
                                                </div>

                                                <div class="form-group grid-span-full mt-3">
                                                    <label class="form-label" for="notes-{{ $schedule->id }}">Notes</label>
                                                    <textarea class="form-control" id="notes-{{ $schedule->id }}" name="notes" rows="2" placeholder="Add schedule notes, exceptions, or coordination instructions.">{{ $schedule->notes }}</textarea>
                                                </div>

                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary btn-loading">
                                                        <span class="btn-text"><i class="fas fa-save"></i> Save Schedule Changes</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Saving...
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>

                                            <form action="{{ route('activities.schedules.generate', [$activity, $schedule]) }}"
                                                method="POST"
                                                class="needs-validation mt-3"
                                                novalidate
                                                data-activity-form>
                                                @csrf

                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label class="form-label" for="generate-from-{{ $schedule->id }}">Generate From</label>
                                                        <input type="date"
                                                            class="form-control"
                                                            id="generate-from-{{ $schedule->id }}"
                                                            name="generate_from"
                                                            value="{{ optional($schedule->start_date)->format('Y-m-d') }}"
                                                            placeholder="YYYY-MM-DD"
                                                            required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="generate-to-{{ $schedule->id }}">Generate To</label>
                                                        <input type="date"
                                                            class="form-control"
                                                            id="generate-to-{{ $schedule->id }}"
                                                            name="generate_to"
                                                            value="{{ optional($schedule->end_date)->format('Y-m-d') ?: $today->copy()->addWeeks(6)->format('Y-m-d') }}"
                                                            placeholder="YYYY-MM-DD"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary btn-loading">
                                                        <span class="btn-text"><i class="fas fa-calendar-plus"></i> Generate Sessions</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Generating...
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No recurring schedules have been configured yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-stack">
                @can('manageSessions', $activity)
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Add Manual Session</h5>
                            <p class="management-subtitle">Create a one-off or correction session that does not rely on recurring generation.</p>

                            <form action="{{ route('activities.sessions.store', $activity) }}"
                                method="POST"
                                id="activity-session-form"
                                class="needs-validation"
                                novalidate
                                data-activity-form>
                                @csrf
                                <input type="hidden" name="session_type" value="{{ \App\Models\Activities\ActivitySession::TYPE_MANUAL }}">

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-schedule">Linked Schedule</label>
                                        <select class="form-select @error('activity_schedule_id') is-invalid @enderror" id="manual-session-schedule" name="activity_schedule_id">
                                            <option value="">No linked recurring schedule</option>
                                            @foreach ($schedules as $schedule)
                                                <option value="{{ $schedule->id }}" {{ (string) old('activity_schedule_id') === (string) $schedule->id ? 'selected' : '' }}>
                                                    {{ $scheduleDays[$schedule->day_of_week] ?? 'Unknown day' }} | {{ $scheduleFrequencies[$schedule->frequency] ?? ucfirst($schedule->frequency) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('activity_schedule_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-date">Session Date <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('session_date') is-invalid @enderror"
                                            id="manual-session-date"
                                            name="session_date"
                                            value="{{ old('session_date') }}"
                                            placeholder="YYYY-MM-DD"
                                            required>
                                        @error('session_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-status">Session Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="manual-session-status" name="status" required>
                                            @foreach ($sessionStatuses as $key => $label)
                                                <option value="{{ $key }}" {{ old('status', \App\Models\Activities\ActivitySession::STATUS_PLANNED) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-start-time">Start Time <span class="text-danger">*</span></label>
                                        <input type="time"
                                            class="form-control @error('start_time') is-invalid @enderror"
                                            id="manual-session-start-time"
                                            name="start_time"
                                            value="{{ old('start_time') }}"
                                            placeholder="15:00"
                                            required>
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-end-time">End Time</label>
                                        <input type="time"
                                            class="form-control @error('end_time') is-invalid @enderror"
                                            id="manual-session-end-time"
                                            name="end_time"
                                            value="{{ old('end_time') }}"
                                            placeholder="16:00">
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="manual-session-location">Location</label>
                                        <input type="text"
                                            class="form-control @error('location') is-invalid @enderror"
                                            id="manual-session-location"
                                            name="location"
                                            value="{{ old('location', $activity->default_location) }}"
                                            placeholder="Use the normal activity venue">
                                        @error('location')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group grid-span-full mt-3">
                                    <label class="form-label" for="manual-session-notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                        id="manual-session-notes"
                                        name="notes"
                                        rows="3"
                                        placeholder="Record postponement reasons, fixture notes, or supervision instructions.">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Manual Session</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endcan

                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Concrete Sessions</h5>
                                <p class="management-subtitle">Generated and manual sessions appear here for correction, attendance, cancellation, and completion work.</p>
                            </div>
                        </div>

                        @if ($sessions->isNotEmpty())
                            <div class="management-list">
                                @foreach ($sessions as $session)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">
                                                    {{ optional($session->session_date)->format('D, d M Y') }}
                                                    @if ($session->start_datetime)
                                                        | {{ $session->start_datetime->format('H:i') }}
                                                        @if ($session->end_datetime)
                                                            - {{ $session->end_datetime->format('H:i') }}
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip pill-muted">{{ $sessionStatuses[$session->status] ?? ucfirst($session->status) }}</span>
                                                    <span class="summary-chip pill-muted">{{ $sessionTypes[$session->session_type] ?? ucfirst($session->session_type) }}</span>
                                                    <span class="summary-chip {{ $session->attendance_locked ? 'status-archived' : 'enrollment-status-active' }}">
                                                        {{ $session->attendance_locked ? 'Attendance Locked' : 'Attendance Open' }}
                                                    </span>
                                                    @if ($session->schedule)
                                                        <span class="summary-chip pill-muted">
                                                            {{ $scheduleDays[$session->schedule->day_of_week] ?? 'Schedule' }}
                                                        </span>
                                                    @endif
                                                    <span class="summary-chip pill-muted">{{ $session->location ?: ($activity->default_location ?: 'No location set') }}</span>
                                                </div>
                                            </div>
                                            @can('manageAttendance', $activity)
                                                <a href="{{ route('activities.attendance.edit', [$activity, $session]) }}" class="btn btn-light border">
                                                    <i class="fas fa-clipboard-check"></i> Attendance
                                                </a>
                                            @endcan
                                        </div>

                                        @if ($session->notes)
                                            <div class="management-item-notes">{{ $session->notes }}</div>
                                        @endif

                                        @can('manageSessions', $activity)
                                            <form action="{{ route('activities.sessions.update', [$activity, $session]) }}"
                                                method="POST"
                                                class="needs-validation mt-3"
                                                novalidate
                                                data-activity-form>
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="session_type" value="{{ $session->session_type }}">

                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-schedule-{{ $session->id }}">Linked Schedule</label>
                                                        <select class="form-select" id="session-schedule-{{ $session->id }}" name="activity_schedule_id">
                                                            <option value="">No linked recurring schedule</option>
                                                            @foreach ($schedules as $schedule)
                                                                <option value="{{ $schedule->id }}" {{ (int) $session->activity_schedule_id === (int) $schedule->id ? 'selected' : '' }}>
                                                                    {{ $scheduleDays[$schedule->day_of_week] ?? 'Unknown day' }} | {{ $scheduleFrequencies[$schedule->frequency] ?? ucfirst($schedule->frequency) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-date-{{ $session->id }}">Session Date</label>
                                                        <input type="date" class="form-control" id="session-date-{{ $session->id }}" name="session_date" value="{{ optional($session->session_date)->format('Y-m-d') }}" placeholder="YYYY-MM-DD" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-status-{{ $session->id }}">Status</label>
                                                        <select class="form-select" id="session-status-{{ $session->id }}" name="status" required>
                                                            @foreach ($sessionStatuses as $key => $label)
                                                                <option value="{{ $key }}" {{ $session->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-start-{{ $session->id }}">Start Time</label>
                                                        <input type="time" class="form-control" id="session-start-{{ $session->id }}" name="start_time" value="{{ $session->start_datetime?->format('H:i') }}" placeholder="15:00" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-end-{{ $session->id }}">End Time</label>
                                                        <input type="time" class="form-control" id="session-end-{{ $session->id }}" name="end_time" value="{{ $session->end_datetime?->format('H:i') }}" placeholder="16:00">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="session-location-{{ $session->id }}">Location</label>
                                                        <input type="text" class="form-control" id="session-location-{{ $session->id }}" name="location" value="{{ $session->location }}" placeholder="Use the normal activity venue">
                                                    </div>
                                                </div>

                                                <div class="form-group grid-span-full mt-3">
                                                    <label class="form-label" for="session-notes-{{ $session->id }}">Notes</label>
                                                    <textarea class="form-control" id="session-notes-{{ $session->id }}" name="notes" rows="2" placeholder="Record postponement reasons, cancellation context, or supervision instructions.">{{ $session->notes }}</textarea>
                                                </div>

                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary btn-loading">
                                                        <span class="btn-text"><i class="fas fa-save"></i> Save Session Changes</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Saving...
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No sessions have been created yet. Save a recurring schedule or add a manual session to begin attendance operations.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection

@extends('layouts.master')

@section('title')
    Activity Events
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
            {{ $activity->name }} Events
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Events and Results</h1>
                <p class="page-subtitle">Create fixtures, showcases, and competitions, then record student or house outcomes once the event is completed.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'events'])

        <div class="help-text">
            <div class="help-title">Event Guidance</div>
            <div class="help-content">
                Use this page to create event records, update their status, and open result entry once an event is complete. House-linked scoring can be recorded here, but house membership remains owned by the Houses module and is never changed from this workflow.
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Events</div>
                    <div class="roster-summary-value">{{ $activity->events_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Completed</div>
                    <div class="roster-summary-value">{{ $activity->completed_events_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Results Recorded</div>
                    <div class="roster-summary-value">{{ $eventOutputsSummary['total_results'] }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Award and Points Output</div>
                    <div class="detail-value">{{ $eventOutputsSummary['award_count'] }} awards | {{ $eventOutputsSummary['points_total'] }} pts</div>
                </div>
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                @can('manageEvents', $activity)
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Create Event</h5>
                            <p class="management-subtitle">Add the event record here first. Results entry opens from the event card once the event is complete.</p>

                            <form action="{{ route('activities.events.store', $activity) }}"
                                method="POST"
                                class="needs-validation"
                                novalidate
                                data-activity-form>
                                @csrf

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label" for="event-title">Event Title <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('title') is-invalid @enderror"
                                            id="event-title"
                                            name="title"
                                            value="{{ old('title') }}"
                                            placeholder="Enter the fixture, showcase, or event title"
                                            required>
                                        @error('title')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-type">Event Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('event_type') is-invalid @enderror" id="event-type" name="event_type" required>
                                            <option value="">Select event type</option>
                                            @foreach ($createEventTypes as $key => $label)
                                                <option value="{{ $key }}" {{ old('event_type', $eventDefaults['event_type']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('event_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-status">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="event-status" name="status" required>
                                            <option value="">Select status</option>
                                            @foreach ($eventStatuses as $key => $label)
                                                <option value="{{ $key }}" {{ old('status', \App\Models\Activities\ActivityEvent::STATUS_SCHEDULED) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="event-start-date">Start Date <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            id="event-start-date"
                                            name="start_date"
                                            value="{{ old('start_date') }}"
                                            placeholder="YYYY-MM-DD"
                                            required>
                                        @error('start_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-start-time">Start Time <span class="text-danger">*</span></label>
                                        <input type="time"
                                            class="form-control @error('start_time') is-invalid @enderror"
                                            id="event-start-time"
                                            name="start_time"
                                            value="{{ old('start_time') }}"
                                            placeholder="15:00"
                                            required>
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-location">Location</label>
                                        <input type="text"
                                            class="form-control @error('location') is-invalid @enderror"
                                            id="event-location"
                                            name="location"
                                            value="{{ old('location', $activity->default_location) }}"
                                            placeholder="Use the event venue or competition location">
                                        @error('location')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="event-end-date">End Date</label>
                                        <input type="date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            id="event-end-date"
                                            name="end_date"
                                            value="{{ old('end_date') }}"
                                            placeholder="YYYY-MM-DD">
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-end-time">End Time</label>
                                        <input type="time"
                                            class="form-control @error('end_time') is-invalid @enderror"
                                            id="event-end-time"
                                            name="end_time"
                                            value="{{ old('end_time') }}"
                                            placeholder="16:00">
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="event-opponent">Opponent or Partner</label>
                                        <input type="text"
                                            class="form-control @error('opponent_or_partner_name') is-invalid @enderror"
                                            id="event-opponent"
                                            name="opponent_or_partner_name"
                                            value="{{ old('opponent_or_partner_name') }}"
                                            placeholder="Record the opponent school, partner, or host">
                                        @error('opponent_or_partner_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="option-grid mt-3">
                                    <div class="option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="event-house-linked" name="house_linked" value="1" {{ old('house_linked', $eventDefaults['house_linked']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="event-house-linked">House-linked scoring</label>
                                            <span class="option-help">Enable this only when the activity and event need house result rows.</span>
                                        </div>
                                    </div>
                                    <div class="option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="event-publish-calendar" name="publish_to_calendar" value="1" {{ old('publish_to_calendar', $eventDefaults['publish_to_calendar']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="event-publish-calendar">Hold for calendar output</label>
                                            <span class="option-help">This stores publication intent only. LMS calendar sync is still outside v1 scope.</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group grid-span-full mt-3">
                                    <label class="form-label" for="event-description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="event-description"
                                        name="description"
                                        rows="3"
                                        placeholder="Add event notes, hosting details, or outcome context.">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Event</span>
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
                                <h5 class="summary-card-title mb-0">Event Register</h5>
                                <p class="management-subtitle">Each event keeps its own operational state and result record. Complete the event before opening results entry.</p>
                            </div>
                        </div>

                        @if ($events->isNotEmpty())
                            <div class="management-list">
                                @foreach ($events as $event)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">{{ $event->title }}</div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip pill-muted">{{ $eventTypes[$event->event_type] ?? ucfirst($event->event_type) }}</span>
                                                    <span class="summary-chip event-status-{{ $event->status }}">
                                                        {{ $eventStatuses[$event->status] ?? ucfirst($event->status) }}
                                                    </span>
                                                    @if ($event->house_linked)
                                                        <span class="summary-chip pill-primary">House linked</span>
                                                    @endif
                                                    <span class="summary-chip pill-muted">{{ $event->result_summary['total_results'] }} result row(s)</span>
                                                    <span class="summary-chip pill-muted">{{ $event->result_summary['award_count'] }} award(s)</span>
                                                    <span class="summary-chip pill-muted">{{ $event->result_summary['points_total'] }} point(s)</span>
                                                </div>
                                            </div>
                                            <div class="activities-actions mt-0">
                                                <a href="{{ route('activities.results.edit', [$activity, $event]) }}" class="btn btn-light border">
                                                    <i class="fas fa-list-check"></i> Open Results
                                                </a>
                                            </div>
                                        </div>

                                        <div class="management-item-notes">
                                            {{ optional($event->start_datetime)->format('d M Y, H:i') }}
                                            @if ($event->end_datetime)
                                                to {{ $event->end_datetime->format('d M Y, H:i') }}
                                            @endif
                                            | {{ $event->location ?: 'No location set' }}
                                            @if ($event->opponent_or_partner_name)
                                                | {{ $event->opponent_or_partner_name }}
                                            @endif
                                        </div>

                                        @if ($event->description)
                                            <div class="management-item-notes">{{ $event->description }}</div>
                                        @endif

                                        @can('manageEvents', $activity)
                                            <form action="{{ route('activities.events.update', [$activity, $event]) }}"
                                                method="POST"
                                                class="needs-validation event-edit-form"
                                                novalidate
                                                data-activity-form>
                                                @csrf
                                                @method('PATCH')

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-title-{{ $event->id }}">Event Title</label>
                                                        <input type="text" class="form-control" id="event-title-{{ $event->id }}" name="title" value="{{ $event->title }}" placeholder="Enter the event title" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-type-{{ $event->id }}">Event Type</label>
                                                        <select class="form-select" id="event-type-{{ $event->id }}" name="event_type" required>
                                                            @foreach ($eventTypes as $key => $label)
                                                                <option value="{{ $key }}" {{ $event->event_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-status-{{ $event->id }}">Status</label>
                                                        <select class="form-select" id="event-status-{{ $event->id }}" name="status" required>
                                                            @foreach ($eventStatuses as $key => $label)
                                                                <option value="{{ $key }}" {{ $event->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-start-date-{{ $event->id }}">Start Date</label>
                                                        <input type="date" class="form-control" id="event-start-date-{{ $event->id }}" name="start_date" value="{{ optional($event->start_datetime)->format('Y-m-d') }}" placeholder="YYYY-MM-DD" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-start-time-{{ $event->id }}">Start Time</label>
                                                        <input type="time" class="form-control" id="event-start-time-{{ $event->id }}" name="start_time" value="{{ optional($event->start_datetime)->format('H:i') }}" placeholder="15:00" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-location-{{ $event->id }}">Location</label>
                                                        <input type="text" class="form-control" id="event-location-{{ $event->id }}" name="location" value="{{ $event->location }}" placeholder="Use the event venue">
                                                    </div>
                                                </div>

                                                <div class="form-grid mt-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-end-date-{{ $event->id }}">End Date</label>
                                                        <input type="date" class="form-control" id="event-end-date-{{ $event->id }}" name="end_date" value="{{ optional($event->end_datetime)->format('Y-m-d') }}" placeholder="YYYY-MM-DD">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-end-time-{{ $event->id }}">End Time</label>
                                                        <input type="time" class="form-control" id="event-end-time-{{ $event->id }}" name="end_time" value="{{ optional($event->end_datetime)->format('H:i') }}" placeholder="16:00">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="event-opponent-{{ $event->id }}">Opponent or Partner</label>
                                                        <input type="text" class="form-control" id="event-opponent-{{ $event->id }}" name="opponent_or_partner_name" value="{{ $event->opponent_or_partner_name }}" placeholder="Record the opponent or partner">
                                                    </div>
                                                </div>

                                                <div class="option-grid mt-3">
                                                    <div class="option-card">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="house-linked-{{ $event->id }}" name="house_linked" value="1" {{ $event->house_linked ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="house-linked-{{ $event->id }}">House-linked scoring</label>
                                                            <span class="option-help">Keep this on when the event should continue to expose house result rows.</span>
                                                        </div>
                                                    </div>
                                                    <div class="option-card">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="publish-calendar-{{ $event->id }}" name="publish_to_calendar" value="1" {{ $event->publish_to_calendar ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="publish-calendar-{{ $event->id }}">Hold for calendar output</label>
                                                            <span class="option-help">This keeps the publication intent on the event record without syncing to LMS in v1.</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group grid-span-full mt-3">
                                                    <label class="form-label" for="event-description-{{ $event->id }}">Description</label>
                                                    <textarea class="form-control" id="event-description-{{ $event->id }}" name="description" rows="3" placeholder="Add event notes, outcome context, or hosting details.">{{ $event->description }}</textarea>
                                                </div>

                                                <div class="form-actions">
                                                    <a href="{{ route('activities.results.edit', [$activity, $event]) }}" class="btn btn-secondary">
                                                        <i class="fas fa-list-check"></i> Manage Results
                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-loading">
                                                        <span class="btn-text"><i class="fas fa-save"></i> Save Event</span>
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
                            <p class="summary-empty mb-0">No events have been created for this activity yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <h5 class="summary-card-title">Output Snapshot</h5>
                        <p class="management-subtitle">This rolls up the results already captured against all events in this activity.</p>

                        <div class="section-stack compact-stack mt-3">
                            <div class="detail-card">
                                <div class="detail-label">Awards Recorded</div>
                                <div class="detail-value">{{ $eventOutputsSummary['award_count'] }}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">Points Total</div>
                                <div class="detail-value">{{ $eventOutputsSummary['points_total'] }}</div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-label">House Result Rows</div>
                                <div class="detail-value">{{ $eventOutputsSummary['house_results'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-body p-4">
                        <h5 class="summary-card-title">House Linkage Boundary</h5>
                        <p class="management-subtitle">House rows recorded here reference houses for results only.</p>
                        <div class="help-text mb-0">
                            <div class="help-content">
                                Results may reference a house when the event is marked as house linked, but this page never edits student-house allocations. House membership continues to come from the Houses module.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection

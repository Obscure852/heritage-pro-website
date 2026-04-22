@extends('layouts.crm')

@section('title', 'CRM Calendar')
@section('crm_heading', 'CRM Calendar')
@section('crm_subheading', 'Coordinate demos, client check-ins, internal handoffs, and owner follow-ups from one shared scheduling workspace inside the Heritage CRM.')

@section('crm_header_stats')
    @foreach ($metrics as $metric)
        @include('crm.partials.header-stat', [
            'value' => number_format($metric['value']),
            'label' => $metric['label'],
        ])
    @endforeach
@endsection

@section('crm_actions')
    <button type="button" class="btn btn-primary" id="crm-calendar-new-event-trigger">
        <i class="bx bx-plus"></i>
        New Event
    </button>
@endsection

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/libs/@fullcalendar/core/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@fullcalendar/daygrid/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@fullcalendar/timegrid/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@fullcalendar/bootstrap/main.min.css') }}">
    <style>
        .crm-calendar-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 320px);
            gap: 20px;
            align-items: start;
        }

        .crm-calendar-sidebar {
            display: grid;
            gap: 20px;
            position: sticky;
            top: 96px;
            order: 2;
        }

        .crm-calendar-board-card {
            order: 1;
        }

        .crm-calendar-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .crm-calendar-toolbar-group {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .crm-calendar-title {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .crm-calendar-nav-btn,
        .crm-calendar-view-btn {
            border: 1px solid #dbe3ef;
            background: #fff;
            color: #334155;
            border-radius: 3px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            transition: all 0.2s ease;
        }

        .crm-calendar-nav-btn:hover,
        .crm-calendar-view-btn:hover {
            color: #2563eb;
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .crm-calendar-view-btn.is-active {
            border-color: #2563eb;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
        }

        .crm-calendar-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .crm-calendar-summary-card {
            border: 1px solid #eef2f7;
            border-radius: 3px;
            padding: 16px;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }

        .crm-calendar-summary-card span {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .crm-calendar-summary-card strong {
            display: block;
            color: #0f172a;
            font-size: 24px;
            line-height: 1;
        }

        .crm-mini-month {
            display: grid;
            gap: 10px;
        }

        .crm-mini-month-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .crm-mini-month-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .crm-mini-month-grid {
            display: grid;
            gap: 10px;
        }

        .crm-mini-month-weekdays,
        .crm-mini-month-week {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 6px;
        }

        .crm-mini-month-weekdays span {
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .crm-mini-month-day {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 36px;
            border-radius: 3px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            background: #f8fafc;
        }

        .crm-mini-month-day.is-dimmed {
            color: #94a3b8;
            background: #fbfdff;
        }

        .crm-mini-month-day.is-today {
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .crm-mini-month-day.is-selected {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
        }

        .crm-calendar-list {
            display: grid;
            gap: 10px;
        }

        .crm-calendar-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .crm-calendar-list-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .crm-calendar-list-time {
            min-width: 74px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .crm-calendar-list-body {
            min-width: 0;
            flex: 1;
        }

        .crm-calendar-list-body h4 {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .crm-calendar-list-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 12px;
        }

        .crm-calendar-filter-group {
            display: grid;
            gap: 12px;
        }

        .crm-calendar-filter-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .crm-calendar-filter-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            font-weight: 500;
        }

        .crm-calendar-filter-toggle input {
            width: 16px;
            height: 16px;
            margin: 0;
        }

        .crm-calendar-color {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .crm-calendar-filter-caption {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 2px;
        }

        .crm-calendar-board-card {
            padding: 24px;
            overflow: hidden;
        }

        .crm-calendar-stage {
            border: 1px solid #eef2f7;
            border-radius: 3px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
            min-height: 840px;
            overflow: hidden;
        }

        .crm-calendar-stage-inner {
            padding: 18px;
        }

        #crm-calendar-board {
            min-height: 780px;
        }

        .crm-calendar-agenda {
            display: none;
        }

        .crm-calendar-agenda.is-active {
            display: block;
        }

        .crm-calendar-agenda-empty {
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 14px;
        }

        .crm-calendar-agenda-list {
            display: grid;
            gap: 12px;
        }

        .crm-calendar-agenda-item {
            border: 1px solid #eef2f7;
            border-left-width: 4px;
            border-radius: 3px;
            padding: 16px 18px;
            background: #fff;
        }

        .crm-calendar-agenda-item-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .crm-calendar-agenda-item h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .crm-calendar-agenda-item p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .crm-calendar-agenda-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
            color: #64748b;
            font-size: 12px;
        }

        .crm-calendar-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .crm-calendar-form-grid .full-width {
            grid-column: 1 / -1;
        }

        .crm-calendar-inline-fields {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .crm-calendar-inline-fields .form-check {
            margin: 0;
        }

        .crm-calendar-form-help {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 12px;
            color: #64748b;
            font-size: 12px;
        }

        .crm-calendar-modal-view {
            display: grid;
            gap: 16px;
        }

        .crm-calendar-modal-view.hidden {
            display: none;
        }

        .crm-calendar-modal-meta {
            display: grid;
            gap: 12px;
        }

        .crm-calendar-modal-meta-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #475569;
            font-size: 13px;
        }

        .crm-calendar-modal-meta-item i {
            color: #2563eb;
            margin-top: 2px;
        }

        .crm-calendar-modal-attendees {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .crm-calendar-attendee-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 600;
        }

        .crm-calendar-status-scheduled .fc-title,
        .crm-calendar-status-scheduled .fc-list-item-title {
            font-weight: 600;
        }

        .crm-calendar-status-completed {
            opacity: 0.78;
        }

        .crm-calendar-status-cancelled {
            opacity: 0.55;
            text-decoration: line-through;
        }

        .fc-event,
        .fc-event-dot {
            border: 0;
            border-radius: 3px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        }

        .fc-event,
        .fc-event .fc-title {
            color: #fff;
        }

        .fc-unthemed td.fc-today {
            background: rgba(37, 99, 235, 0.06);
        }

        .fc-day-header {
            padding: 10px 0;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .fc-axis,
        .fc-time {
            color: #64748b;
            font-size: 12px;
        }

        .fc-toolbar {
            display: none;
        }

        .crm-calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .crm-calendar-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b;
        }

        .crm-calendar-legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
        }

        .crm-calendar-hidden {
            display: none !important;
        }

        @media (max-width: 1199px) {
            .crm-calendar-shell {
                grid-template-columns: minmax(0, 1fr);
            }

            .crm-calendar-sidebar {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .crm-calendar-form-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .crm-calendar-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .crm-calendar-stage {
                min-height: 640px;
            }

            #crm-calendar-board {
                min-height: 580px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Calendar Workspace',
            'content' => 'Use the sidebar filters and date controls to focus the schedule, then open or create the event you need from the main board.',
        ])

        <div class="crm-calendar-shell">
            <aside class="crm-calendar-sidebar">
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Navigator</p>
                            <h2>{{ $selectedDate->format('F Y') }}</h2>
                            <p>Jump to a different focus date without leaving the CRM shell.</p>
                        </div>
                    </div>

                    <div class="crm-mini-month">
                        <div class="crm-mini-month-header">
                            <h3>{{ $selectedDate->format('F Y') }}</h3>
                            <span class="crm-pill primary">{{ $selectedDate->format('D, j M') }}</span>
                        </div>

                        <div class="crm-mini-month-grid">
                            <div class="crm-mini-month-weekdays">
                                @foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $weekday)
                                    <span>{{ $weekday }}</span>
                                @endforeach
                            </div>

                            @foreach ($miniMonthWeeks as $week)
                                <div class="crm-mini-month-week">
                                    @foreach ($week as $day)
                                        <a
                                            href="{{ route('crm.calendar.index', ['date' => $day['date'], 'view' => $defaultView]) }}"
                                            class="crm-mini-month-day {{ $day['is_current_month'] ? '' : 'is-dimmed' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_selected'] ? 'is-selected' : '' }}"
                                        >
                                            {{ $day['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Calendars</p>
                            <h2>Visible schedules</h2>
                            <p>Toggle personal and shared calendars without losing your current view.</p>
                        </div>
                        @if ($canCreateSharedCalendars)
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#crm-calendar-space-modal">
                                <i class="bx bx-plus"></i>
                                Space
                            </button>
                        @endif
                    </div>

                    <div class="crm-calendar-filter-group">
                        @php($myCalendars = $visibleCalendars->where('group_label', 'my'))
                        @php($otherCalendars = $visibleCalendars->where('group_label', 'other'))

                        @if ($myCalendars->isNotEmpty())
                            <div>
                                <div class="crm-kicker" style="margin-bottom: 10px;">My Calendars</div>
                                @foreach ($myCalendars as $calendar)
                                    <label class="crm-calendar-filter-row">
                                        <span class="crm-calendar-filter-toggle">
                                            <input
                                                type="checkbox"
                                                class="crm-calendar-filter-checkbox"
                                                value="{{ $calendar->id }}"
                                                {{ in_array((int) $calendar->id, $selectedCalendarIds, true) ? 'checked' : '' }}
                                            >
                                            <span class="crm-calendar-color" style="background: {{ $calendar->color }}"></span>
                                            <span>
                                                {{ $calendar->name }}
                                                <span class="crm-calendar-filter-caption">{{ $calendar->type === 'shared' ? 'Shared space' : 'Owned by ' . $calendar->owner_label }}</span>
                                            </span>
                                        </span>
                                        <span class="crm-pill muted">{{ ucfirst($calendar->viewer_permission) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @if ($otherCalendars->isNotEmpty())
                            <div>
                                <div class="crm-kicker" style="margin-bottom: 10px;">Other Calendars</div>
                                @foreach ($otherCalendars as $calendar)
                                    <label class="crm-calendar-filter-row">
                                        <span class="crm-calendar-filter-toggle">
                                            <input
                                                type="checkbox"
                                                class="crm-calendar-filter-checkbox"
                                                value="{{ $calendar->id }}"
                                                {{ in_array((int) $calendar->id, $selectedCalendarIds, true) ? 'checked' : '' }}
                                            >
                                            <span class="crm-calendar-color" style="background: {{ $calendar->color }}"></span>
                                            <span>
                                                {{ $calendar->name }}
                                                <span class="crm-calendar-filter-caption">{{ $calendar->owner_label }}</span>
                                            </span>
                                        </span>
                                        <span class="crm-pill muted">{{ ucfirst($calendar->viewer_permission) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Upcoming</p>
                            <h2>Next commitments</h2>
                            <p>Fast context for what is due next across the selected schedules.</p>
                        </div>
                    </div>

                    @if ($upcomingEvents->isEmpty())
                        <div class="crm-empty">No events are scheduled in the upcoming window yet.</div>
                    @else
                        <div class="crm-calendar-list">
                            @foreach ($upcomingEvents as $event)
                                <div class="crm-calendar-list-item">
                                    <div class="crm-calendar-list-time">
                                        {{ $event['starts_at']->format('d M') }}<br>
                                        {{ $event['all_day'] ? 'All day' : $event['starts_at']->format('H:i') }}
                                    </div>
                                    <div class="crm-calendar-list-body">
                                        <h4>{{ $event['title'] }}</h4>
                                        <div class="crm-calendar-list-meta">
                                            <span>{{ $event['calendar_name'] }}</span>
                                            <span>•</span>
                                            <span>{{ $event['owner_name'] }}</span>
                                            @if ($event['location'])
                                                <span>•</span>
                                                <span>{{ $event['location'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>

            <section class="crm-card crm-calendar-board-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Workspace</p>
                        <h2>Team calendar board</h2>
                        <p>Use month, week, day, or agenda mode. Drag to move or resize events where you have edit access.</p>
                    </div>
                    <div class="crm-calendar-legend">
                        <span class="crm-calendar-legend-item">
                            <span class="crm-calendar-legend-dot" style="background: #2563eb"></span>
                            Scheduled
                        </span>
                        <span class="crm-calendar-legend-item">
                            <span class="crm-calendar-legend-dot" style="background: #0ab39c"></span>
                            Completed
                        </span>
                        <span class="crm-calendar-legend-item">
                            <span class="crm-calendar-legend-dot" style="background: #f06548"></span>
                            Cancelled
                        </span>
                    </div>
                </div>

                <div class="crm-calendar-toolbar">
                    <div class="crm-calendar-toolbar-group">
                        <button type="button" class="crm-calendar-nav-btn" id="crm-calendar-today">Today</button>
                        <button type="button" class="crm-calendar-nav-btn" id="crm-calendar-prev" aria-label="Previous">
                            <i class="bx bx-chevron-left"></i>
                        </button>
                        <button type="button" class="crm-calendar-nav-btn" id="crm-calendar-next" aria-label="Next">
                            <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>

                    <h3 class="crm-calendar-title" id="crm-calendar-title">{{ $selectedDate->format('F Y') }}</h3>

                    <div class="crm-calendar-toolbar-group">
                        <button type="button" class="crm-calendar-view-btn" data-calendar-view="agenda">Agenda</button>
                        <button type="button" class="crm-calendar-view-btn" data-calendar-view="timeGridDay">Day</button>
                        <button type="button" class="crm-calendar-view-btn" data-calendar-view="timeGridWeek">Week</button>
                        <button type="button" class="crm-calendar-view-btn" data-calendar-view="dayGridMonth">Month</button>
                    </div>
                </div>

                <div class="crm-calendar-stage">
                    <div class="crm-calendar-stage-inner">
                        <div id="crm-calendar-board"></div>
                        <div class="crm-calendar-agenda" id="crm-calendar-agenda">
                            <div class="crm-calendar-agenda-list" id="crm-calendar-agenda-list"></div>
                            <div class="crm-calendar-agenda-empty crm-calendar-hidden" id="crm-calendar-agenda-empty">
                                No events found in this agenda window.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="crm-calendar-event-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="crm-calendar-event-modal-title">New calendar event</h5>
                        <div class="text-muted" style="font-size: 13px;">Attach schedule entries to leads, customers, contacts, or requests when relevant.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="crm-calendar-readonly-view" class="crm-calendar-modal-view hidden">
                        <div class="crm-calendar-form-help" id="crm-calendar-readonly-message"></div>
                        <div class="crm-calendar-modal-meta" id="crm-calendar-readonly-meta"></div>
                    </div>

                    <form id="crm-calendar-event-form" class="crm-form">
                        <input type="hidden" name="event_id" id="crm-calendar-event-id">
                        <input type="hidden" name="timezone" value="{{ config('app.timezone') }}" id="crm-calendar-timezone">

                        <div class="crm-calendar-form-grid">
                            <div class="full-width">
                                <div class="crm-calendar-inline-fields">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="crm-calendar-all-day" name="all_day">
                                        <label class="form-check-label" for="crm-calendar-all-day">All day</label>
                                    </div>
                                    <div class="crm-calendar-form-help" style="margin: 0;">
                                        Use busy-only or private visibility for sensitive holds that should not expose details to non-editors.
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-calendar">Calendar</label>
                                <select class="form-select" id="crm-calendar-form-calendar" name="calendar_id" required>
                                    @foreach ($visibleCalendars as $calendar)
                                        @if ($calendar->can_edit)
                                            <option value="{{ $calendar->id }}">{{ $calendar->name }} ({{ ucfirst($calendar->viewer_permission) }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-owner">Owner</label>
                                <select class="form-select" id="crm-calendar-form-owner" name="owner_id">
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ $owner->id === auth()->id() ? 'selected' : '' }}>{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="full-width">
                                <label class="form-label" for="crm-calendar-form-title">Title</label>
                                <input type="text" class="form-control" id="crm-calendar-form-title" name="title" required maxlength="180">
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-start">Start</label>
                                <input type="datetime-local" class="form-control" id="crm-calendar-form-start" name="starts_at" required>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-end">End</label>
                                <input type="datetime-local" class="form-control" id="crm-calendar-form-end" name="ends_at" required>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-status">Status</label>
                                <select class="form-select" id="crm-calendar-form-status" name="status">
                                    @foreach ($calendarStatuses as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-visibility">Visibility</label>
                                <select class="form-select" id="crm-calendar-form-visibility" name="visibility">
                                    @foreach ($calendarVisibility as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="full-width">
                                <label class="form-label" for="crm-calendar-form-location">Location</label>
                                <input type="text" class="form-control" id="crm-calendar-form-location" name="location" maxlength="255">
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-lead">Lead</label>
                                <select class="form-select" id="crm-calendar-form-lead" name="lead_id">
                                    <option value="">No lead</option>
                                    @foreach ($leads as $lead)
                                        <option value="{{ $lead->id }}">{{ $lead->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-customer">Customer</label>
                                <select class="form-select" id="crm-calendar-form-customer" name="customer_id">
                                    <option value="">No customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-contact">Linked contact</label>
                                <select class="form-select" id="crm-calendar-form-contact" name="contact_id">
                                    <option value="">No linked contact</option>
                                    @foreach ($contacts as $contact)
                                        <option
                                            value="{{ $contact->id }}"
                                            data-lead-id="{{ $contact->lead_id }}"
                                            data-customer-id="{{ $contact->customer_id }}"
                                        >
                                            {{ $contact->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-request">Linked request</label>
                                <select class="form-select" id="crm-calendar-form-request" name="request_id">
                                    <option value="">No linked request</option>
                                    @foreach ($crmRequests as $crmRequest)
                                        <option
                                            value="{{ $crmRequest->id }}"
                                            data-lead-id="{{ $crmRequest->lead_id }}"
                                            data-customer-id="{{ $crmRequest->customer_id }}"
                                        >
                                            {{ $crmRequest->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-attendees">Internal attendees</label>
                                <select class="form-select" id="crm-calendar-form-attendees" name="attendee_user_ids[]" multiple size="5">
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="crm-calendar-form-reminders">Reminders</label>
                                <select class="form-select" id="crm-calendar-form-reminders" name="reminder_minutes[]" multiple size="5">
                                    @foreach ($calendarReminderMinutes as $minutes => $label)
                                        <option value="{{ $minutes }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="full-width">
                                <label class="form-label" for="crm-calendar-form-description">Description</label>
                                <textarea class="form-control" id="crm-calendar-form-description" name="description" rows="4" maxlength="5000"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <div class="crm-calendar-inline-fields">
                        <button type="button" class="btn btn-outline-success crm-calendar-hidden" id="crm-calendar-complete-event">
                            <i class="bx bx-check"></i>
                            Complete
                        </button>
                        <button type="button" class="btn btn-outline-warning crm-calendar-hidden" id="crm-calendar-cancel-event">
                            <i class="bx bx-block"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-outline-danger crm-calendar-hidden" id="crm-calendar-delete-event">
                            <i class="bx bx-trash"></i>
                            Delete
                        </button>
                    </div>

                    <div class="crm-calendar-inline-fields">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary btn-loading" id="crm-calendar-save-event">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Event</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canCreateSharedCalendars)
        <div class="modal fade" id="crm-calendar-space-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('crm.calendar.calendars.store') }}" class="crm-form">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">New shared calendar</h5>
                                <div class="text-muted" style="font-size: 13px;">Create a shared planning space for internal projects, finance milestones, or customer programs.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="crm-calendar-form-grid">
                                <div class="full-width">
                                    <label class="form-label" for="crm-calendar-space-name">Name</label>
                                    <input type="text" class="form-control" id="crm-calendar-space-name" name="name" required maxlength="120">
                                </div>
                                <div class="full-width">
                                    <label class="form-label" for="crm-calendar-space-description">Description</label>
                                    <textarea class="form-control" id="crm-calendar-space-description" name="description" rows="3" maxlength="1000"></textarea>
                                </div>
                                <div>
                                    <label class="form-label" for="crm-calendar-space-color">Color</label>
                                    <select class="form-select" id="crm-calendar-space-color" name="color">
                                        @foreach ($calendarColors as $color)
                                            <option value="{{ $color }}">{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="crm-calendar-space-members">Members</label>
                                    <select class="form-select" id="crm-calendar-space-members" name="member_user_ids[]" multiple size="5">
                                        @foreach ($sharedCalendarMembers as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Create Calendar</span>
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
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/core/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/interaction/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/daygrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/timegrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/bootstrap/main.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('crm-calendar-board');
            var agendaEl = document.getElementById('crm-calendar-agenda');
            var agendaListEl = document.getElementById('crm-calendar-agenda-list');
            var agendaEmptyEl = document.getElementById('crm-calendar-agenda-empty');
            var titleEl = document.getElementById('crm-calendar-title');
            var defaultView = @json($defaultView);
            var agendaWindowDays = {{ (int) config('heritage_crm.calendar.agenda_days', 14) }};
            var initialDate = @json($selectedDate->toDateString());
            var lastCalendarView = ['timeGridDay', 'timeGridWeek', 'dayGridMonth'].indexOf(defaultView) !== -1 ? defaultView : 'timeGridWeek';
            var isAgendaMode = defaultView === 'agenda';
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var eventModalEl = document.getElementById('crm-calendar-event-modal');
            var eventModal = new bootstrap.Modal(eventModalEl);
            var eventForm = document.getElementById('crm-calendar-event-form');
            var readonlyView = document.getElementById('crm-calendar-readonly-view');
            var readonlyMessage = document.getElementById('crm-calendar-readonly-message');
            var readonlyMeta = document.getElementById('crm-calendar-readonly-meta');
            var saveButton = document.getElementById('crm-calendar-save-event');
            var deleteButton = document.getElementById('crm-calendar-delete-event');
            var completeButton = document.getElementById('crm-calendar-complete-event');
            var cancelButton = document.getElementById('crm-calendar-cancel-event');
            var titleField = document.getElementById('crm-calendar-form-title');
            var allDayField = document.getElementById('crm-calendar-all-day');
            var startField = document.getElementById('crm-calendar-form-start');
            var endField = document.getElementById('crm-calendar-form-end');
            var leadField = document.getElementById('crm-calendar-form-lead');
            var customerField = document.getElementById('crm-calendar-form-customer');
            var contactField = document.getElementById('crm-calendar-form-contact');
            var requestField = document.getElementById('crm-calendar-form-request');
            var ownerField = document.getElementById('crm-calendar-form-owner');
            var selectedEventId = null;
            var selectedEventData = null;
            var calendarFilterCheckboxes = Array.from(document.querySelectorAll('.crm-calendar-filter-checkbox'));

            function selectedCalendarIds() {
                return calendarFilterCheckboxes
                    .filter(function (checkbox) {
                        return checkbox.checked;
                    })
                    .map(function (checkbox) {
                        return checkbox.value;
                    });
            }

            function setLoadingState(button, loading) {
                if (!button) {
                    return;
                }

                if (loading) {
                    button.classList.add('loading');
                    button.disabled = true;
                } else {
                    button.classList.remove('loading');
                    button.disabled = false;
                }
            }

            function toLocalInputValue(dateLike, allDay) {
                if (!dateLike) {
                    return '';
                }

                var date = new Date(dateLike);

                if (allDay) {
                    date.setHours(9, 0, 0, 0);
                }

                var pad = function (value) {
                    return String(value).padStart(2, '0');
                };

                return [
                    date.getFullYear(),
                    pad(date.getMonth() + 1),
                    pad(date.getDate())
                ].join('-') + 'T' + [
                    pad(date.getHours()),
                    pad(date.getMinutes())
                ].join(':');
            }

            function toDisplayDate(dateLike, allDay) {
                if (!dateLike) {
                    return 'Not set';
                }

                var date = new Date(dateLike);
                var options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };

                if (!allDay) {
                    options.hour = '2-digit';
                    options.minute = '2-digit';
                }

                return date.toLocaleString([], options);
            }

            function notify(message, type) {
                if (window.Swal) {
                    window.Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type || 'success',
                        title: message,
                        showConfirmButton: false,
                        timer: 2600,
                        timerProgressBar: true,
                    });

                    return;
                }

                window.alert(message);
            }

            function updateToolbarButtons(activeView) {
                document.querySelectorAll('.crm-calendar-view-btn').forEach(function (button) {
                    button.classList.toggle('is-active', button.getAttribute('data-calendar-view') === activeView);
                });
            }

            function updateTitle() {
                if (isAgendaMode) {
                    var agendaStart = calendar.getDate();
                    var agendaEnd = new Date(calendar.getDate().valueOf());
                    agendaEnd.setDate(agendaEnd.getDate() + agendaWindowDays);
                    titleEl.textContent = agendaStart.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' }) +
                        ' - ' +
                        agendaEnd.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
                    return;
                }

                titleEl.textContent = calendar.view.title;
            }

            function clearReadonlyView() {
                readonlyView.classList.add('hidden');
                readonlyMessage.textContent = '';
                readonlyMeta.innerHTML = '';
            }

            function setReadonlyView(eventData) {
                readonlyView.classList.remove('hidden');
                readonlyMessage.textContent = eventData.extendedProps && eventData.extendedProps.visibility === 'private'
                    ? 'This event is marked private. You can see that the slot is occupied, but not the underlying details.'
                    : 'This event is shared as busy-only. You can see the time block, but not the underlying content.';

                var fragments = [];
                fragments.push('<div class="crm-calendar-modal-meta-item"><i class="bx bx-calendar"></i><div><strong>' + eventData.title + '</strong><div>' + toDisplayDate(eventData.start, eventData.allDay) + ' to ' + toDisplayDate(eventData.end, eventData.allDay) + '</div></div></div>');

                if (eventData.extendedProps && eventData.extendedProps.calendar_name) {
                    fragments.push('<div class="crm-calendar-modal-meta-item"><i class="bx bx-layer"></i><div>' + eventData.extendedProps.calendar_name + '</div></div>');
                }

                readonlyMeta.innerHTML = fragments.join('');
            }

            function resetFormState() {
                eventForm.reset();
                document.getElementById('crm-calendar-event-id').value = '';
                selectedEventId = null;
                selectedEventData = null;
                clearReadonlyView();
                eventForm.classList.remove('crm-calendar-hidden');
                deleteButton.classList.add('crm-calendar-hidden');
                completeButton.classList.add('crm-calendar-hidden');
                cancelButton.classList.add('crm-calendar-hidden');
                saveButton.classList.remove('crm-calendar-hidden');
                enableForm(true);
                syncRelatedSelectOptions();
            }

            function enableForm(enabled) {
                Array.from(eventForm.elements).forEach(function (element) {
                    element.disabled = !enabled;
                });

                saveButton.disabled = !enabled;
            }

            function syncRelatedSelectOptions() {
                var leadId = leadField.value;
                var customerId = customerField.value;

                Array.from(contactField.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    var matchesLead = !leadId || option.dataset.leadId === leadId;
                    var matchesCustomer = !customerId || option.dataset.customerId === customerId;
                    var visible = matchesLead && matchesCustomer;
                    option.hidden = !visible;
                    option.disabled = !visible;
                });

                if (contactField.selectedOptions[0] && contactField.selectedOptions[0].disabled) {
                    contactField.value = '';
                }

                Array.from(requestField.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    var matchesLead = !leadId || option.dataset.leadId === leadId;
                    var matchesCustomer = !customerId || option.dataset.customerId === customerId;
                    var visible = matchesLead && matchesCustomer;
                    option.hidden = !visible;
                    option.disabled = !visible;
                });

                if (requestField.selectedOptions[0] && requestField.selectedOptions[0].disabled) {
                    requestField.value = '';
                }
            }

            function assignFormValues(eventData) {
                selectedEventId = eventData.id || null;
                selectedEventData = eventData;
                document.getElementById('crm-calendar-event-id').value = selectedEventId || '';
                document.getElementById('crm-calendar-form-calendar').value = eventData.extendedProps.calendar_id || document.getElementById('crm-calendar-form-calendar').value;
                ownerField.value = eventData.extendedProps.owner_id || ownerField.value;
                titleField.value = eventData.title || '';
                allDayField.checked = !!eventData.allDay;
                startField.value = toLocalInputValue(eventData.start, eventData.allDay);
                endField.value = toLocalInputValue(eventData.end || eventData.start, eventData.allDay);
                document.getElementById('crm-calendar-form-status').value = eventData.extendedProps.status || 'scheduled';
                document.getElementById('crm-calendar-form-visibility').value = eventData.extendedProps.visibility || 'standard';
                document.getElementById('crm-calendar-form-location').value = eventData.extendedProps.location || '';
                leadField.value = eventData.extendedProps.lead_id || '';
                customerField.value = eventData.extendedProps.customer_id || '';
                contactField.value = eventData.extendedProps.contact_id || '';
                requestField.value = eventData.extendedProps.request_id || '';
                document.getElementById('crm-calendar-form-description').value = eventData.extendedProps.description || '';

                Array.from(document.getElementById('crm-calendar-form-attendees').options).forEach(function (option) {
                    option.selected = (eventData.extendedProps.attendee_user_ids || []).map(String).indexOf(option.value) !== -1;
                });

                Array.from(document.getElementById('crm-calendar-form-reminders').options).forEach(function (option) {
                    option.selected = (eventData.extendedProps.reminders || []).map(String).indexOf(option.value) !== -1;
                });

                syncRelatedSelectOptions();
            }

            function formPayload() {
                var formData = new FormData(eventForm);
                var payload = {};

                formData.forEach(function (value, key) {
                    if (key.slice(-2) === '[]') {
                        var normalizedKey = key.replace('[]', '');

                        if (!payload[normalizedKey]) {
                            payload[normalizedKey] = [];
                        }

                        payload[normalizedKey].push(value);
                        return;
                    }

                    payload[key] = value;
                });

                payload.all_day = allDayField.checked ? 1 : 0;

                return payload;
            }

            function fetchJson(url, method, payload) {
                return window.fetch(url, {
                    method: method || 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: payload ? JSON.stringify(payload) : undefined,
                }).then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) {
                            var error = new Error(data.message || 'Calendar request failed.');
                            error.payload = data;
                            throw error;
                        }

                        return data;
                    });
                });
            }

            function showValidationErrors(error) {
                if (!error || !error.payload || !error.payload.errors) {
                    notify(error.message || 'Unable to save the calendar event.', 'error');
                    return;
                }

                var messages = [];

                Object.keys(error.payload.errors).forEach(function (field) {
                    messages.push(error.payload.errors[field][0]);
                });

                notify(messages.join(' '), 'error');
            }

            function openCreateModal(startDate, endDate, allDay) {
                resetFormState();
                document.getElementById('crm-calendar-event-modal-title').textContent = 'New calendar event';
                startField.value = toLocalInputValue(startDate, allDay);
                endField.value = toLocalInputValue(endDate, allDay);
                allDayField.checked = !!allDay;
                eventModal.show();
            }

            function openEventModal(eventApi) {
                resetFormState();

                var eventData = {
                    id: eventApi.id,
                    title: eventApi.title,
                    start: eventApi.start,
                    end: eventApi.end,
                    allDay: eventApi.allDay,
                    extendedProps: eventApi.extendedProps || {},
                };

                document.getElementById('crm-calendar-event-modal-title').textContent = 'Edit calendar event';

                if (!eventData.extendedProps.can_view_sensitive && !eventData.extendedProps.can_edit) {
                    setReadonlyView(eventData);
                    eventForm.classList.add('crm-calendar-hidden');
                    saveButton.classList.add('crm-calendar-hidden');
                    deleteButton.classList.add('crm-calendar-hidden');
                    completeButton.classList.add('crm-calendar-hidden');
                    cancelButton.classList.add('crm-calendar-hidden');
                    eventModal.show();
                    return;
                }

                assignFormValues(eventData);
                deleteButton.classList.remove('crm-calendar-hidden');
                completeButton.classList.remove('crm-calendar-hidden');
                cancelButton.classList.remove('crm-calendar-hidden');

                if (!eventData.extendedProps.can_edit) {
                    enableForm(false);
                    saveButton.classList.add('crm-calendar-hidden');
                    deleteButton.classList.add('crm-calendar-hidden');
                    completeButton.classList.add('crm-calendar-hidden');
                    cancelButton.classList.add('crm-calendar-hidden');
                }

                eventModal.show();
            }

            function agendaRange() {
                var start = calendar.getDate();
                var end = new Date(start.valueOf());
                end.setDate(end.getDate() + agendaWindowDays);

                return {
                    start: start,
                    end: end,
                };
            }

            function renderAgendaItems(items) {
                agendaListEl.innerHTML = '';
                agendaEmptyEl.classList.toggle('crm-calendar-hidden', items.length !== 0);

                items.forEach(function (item) {
                    var eventDate = new Date(item.start);
                    var itemEl = document.createElement('button');
                    itemEl.type = 'button';
                    itemEl.className = 'crm-calendar-agenda-item';
                    itemEl.style.borderLeftColor = item.backgroundColor || '#5156be';
                    itemEl.innerHTML = '' +
                        '<div class="crm-calendar-agenda-item-header">' +
                            '<div>' +
                                '<h4>' + item.title + '</h4>' +
                                '<p>' + toDisplayDate(item.start, item.allDay) + (item.end ? ' - ' + toDisplayDate(item.end, item.allDay) : '') + '</p>' +
                            '</div>' +
                            '<span class="crm-pill muted">' + (item.extendedProps.status || 'scheduled').replace('_', ' ') + '</span>' +
                        '</div>' +
                        '<div class="crm-calendar-agenda-meta">' +
                            '<span>' + (item.extendedProps.calendar_name || 'Calendar') + '</span>' +
                            (item.extendedProps.owner_name ? '<span>•</span><span>' + item.extendedProps.owner_name + '</span>' : '') +
                            (item.extendedProps.location ? '<span>•</span><span>' + item.extendedProps.location + '</span>' : '') +
                        '</div>';

                    itemEl.addEventListener('click', function () {
                        openEventModal({
                            id: item.id,
                            title: item.title,
                            start: new Date(item.start),
                            end: item.end ? new Date(item.end) : null,
                            allDay: item.allDay,
                            extendedProps: item.extendedProps || {},
                        });
                    });

                    agendaListEl.appendChild(itemEl);
                });
            }

            function loadAgendaItems() {
                var range = agendaRange();
                var url = new URL(@json(route('crm.calendar.feed')), window.location.origin);
                url.searchParams.set('start', range.start.toISOString());
                url.searchParams.set('end', range.end.toISOString());
                selectedCalendarIds().forEach(function (calendarId) {
                    url.searchParams.append('calendar_ids[]', calendarId);
                });

                fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (items) {
                        renderAgendaItems(items);
                    });
            }

            function switchToAgenda() {
                isAgendaMode = true;
                updateToolbarButtons('agenda');
                calendarEl.classList.add('crm-calendar-hidden');
                agendaEl.classList.add('is-active');
                updateTitle();
                loadAgendaItems();
            }

            function switchToCalendar(viewName) {
                isAgendaMode = false;
                lastCalendarView = viewName || lastCalendarView;
                updateToolbarButtons(lastCalendarView);
                agendaEl.classList.remove('is-active');
                calendarEl.classList.remove('crm-calendar-hidden');
                calendar.changeView(lastCalendarView);
                updateTitle();
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['bootstrap', 'interaction', 'dayGrid', 'timeGrid'],
                themeSystem: 'bootstrap',
                defaultView: lastCalendarView,
                defaultDate: initialDate,
                timeZone: @json(config('app.timezone')),
                header: false,
                editable: true,
                selectable: true,
                eventDurationEditable: true,
                selectMirror: true,
                selectHelper: true,
                allDaySlot: true,
                slotDuration: @json(sprintf('00:%02d:00', (int) config('heritage_crm.calendar.slot_duration_minutes', 30))),
                minTime: @json(config('heritage_crm.calendar.day_start_hour', '07:00:00')),
                maxTime: @json(config('heritage_crm.calendar.day_end_hour', '21:00:00')),
                height: 'auto',
                eventSources: [{
                    url: @json(route('crm.calendar.feed')),
                    method: 'GET',
                    extraParams: function () {
                        return {
                            calendar_ids: selectedCalendarIds()
                        };
                    }
                }],
                datesRender: function () {
                    updateTitle();
                    if (isAgendaMode) {
                        loadAgendaItems();
                    }
                },
                select: function (info) {
                    openCreateModal(info.start, info.end || info.start, info.allDay);
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    openEventModal(info.event);
                },
                eventDrop: function (info) {
                    var payload = {
                        calendar_id: info.event.extendedProps.calendar_id,
                        owner_id: info.event.extendedProps.owner_id,
                        title: info.event.title,
                        starts_at: toLocalInputValue(info.event.start, info.event.allDay),
                        ends_at: toLocalInputValue(info.event.end || info.event.start, info.event.allDay),
                        all_day: info.event.allDay ? 1 : 0,
                        status: info.event.extendedProps.status || 'scheduled',
                        visibility: info.event.extendedProps.visibility || 'standard',
                        location: info.event.extendedProps.location || '',
                        lead_id: info.event.extendedProps.lead_id || '',
                        customer_id: info.event.extendedProps.customer_id || '',
                        contact_id: info.event.extendedProps.contact_id || '',
                        request_id: info.event.extendedProps.request_id || '',
                        attendee_user_ids: info.event.extendedProps.attendee_user_ids || [],
                        reminder_minutes: info.event.extendedProps.reminders || [],
                        description: info.event.extendedProps.description || '',
                        timezone: @json(config('app.timezone')),
                    };

                    fetchJson(@json(url('/crm/calendar/events')) + '/' + info.event.id, 'PATCH', payload)
                        .then(function () {
                            notify('Event updated.');
                            calendar.refetchEvents();
                            if (isAgendaMode) {
                                loadAgendaItems();
                            }
                        })
                        .catch(function (error) {
                            info.revert();
                            showValidationErrors(error);
                        });
                },
                eventResize: function (info) {
                    var payload = {
                        calendar_id: info.event.extendedProps.calendar_id,
                        owner_id: info.event.extendedProps.owner_id,
                        title: info.event.title,
                        starts_at: toLocalInputValue(info.event.start, info.event.allDay),
                        ends_at: toLocalInputValue(info.event.end || info.event.start, info.event.allDay),
                        all_day: info.event.allDay ? 1 : 0,
                        status: info.event.extendedProps.status || 'scheduled',
                        visibility: info.event.extendedProps.visibility || 'standard',
                        location: info.event.extendedProps.location || '',
                        lead_id: info.event.extendedProps.lead_id || '',
                        customer_id: info.event.extendedProps.customer_id || '',
                        contact_id: info.event.extendedProps.contact_id || '',
                        request_id: info.event.extendedProps.request_id || '',
                        attendee_user_ids: info.event.extendedProps.attendee_user_ids || [],
                        reminder_minutes: info.event.extendedProps.reminders || [],
                        description: info.event.extendedProps.description || '',
                        timezone: @json(config('app.timezone')),
                    };

                    fetchJson(@json(url('/crm/calendar/events')) + '/' + info.event.id, 'PATCH', payload)
                        .then(function () {
                            notify('Event duration updated.');
                            calendar.refetchEvents();
                            if (isAgendaMode) {
                                loadAgendaItems();
                            }
                        })
                        .catch(function (error) {
                            info.revert();
                            showValidationErrors(error);
                        });
                }
            });

            calendar.render();

            if (isAgendaMode) {
                switchToAgenda();
            } else {
                switchToCalendar(lastCalendarView);
            }

            document.getElementById('crm-calendar-today').addEventListener('click', function () {
                calendar.today();

                if (isAgendaMode) {
                    loadAgendaItems();
                }
            });

            document.getElementById('crm-calendar-prev').addEventListener('click', function () {
                calendar.prev();

                if (isAgendaMode) {
                    loadAgendaItems();
                }
            });

            document.getElementById('crm-calendar-next').addEventListener('click', function () {
                calendar.next();

                if (isAgendaMode) {
                    loadAgendaItems();
                }
            });

            document.querySelectorAll('.crm-calendar-view-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var viewName = button.getAttribute('data-calendar-view');

                    if (viewName === 'agenda') {
                        switchToAgenda();
                        return;
                    }

                    switchToCalendar(viewName);
                });
            });

            calendarFilterCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    calendar.refetchEvents();

                    if (isAgendaMode) {
                        loadAgendaItems();
                    }
                });
            });

            leadField.addEventListener('change', function () {
                if (leadField.value) {
                    customerField.value = '';
                }

                syncRelatedSelectOptions();
            });

            customerField.addEventListener('change', function () {
                if (customerField.value) {
                    leadField.value = '';
                }

                syncRelatedSelectOptions();
            });

            document.getElementById('crm-calendar-new-event-trigger').addEventListener('click', function () {
                var start = calendar.getDate();
                var end = new Date(start.valueOf());
                end.setHours(end.getHours() + 1);
                openCreateModal(start, end, false);
            });

            saveButton.addEventListener('click', function () {
                setLoadingState(saveButton, true);

                fetchJson(
                    selectedEventId
                        ? @json(url('/crm/calendar/events')) + '/' + selectedEventId
                        : @json(route('crm.calendar.events.store')),
                    selectedEventId ? 'PATCH' : 'POST',
                    formPayload()
                )
                    .then(function (response) {
                        notify(response.message || 'Event saved.');
                        eventModal.hide();
                        calendar.refetchEvents();

                        if (isAgendaMode) {
                            loadAgendaItems();
                        }
                    })
                    .catch(showValidationErrors)
                    .finally(function () {
                        setLoadingState(saveButton, false);
                    });
            });

            deleteButton.addEventListener('click', function () {
                if (!selectedEventId) {
                    return;
                }

                fetchJson(@json(url('/crm/calendar/events')) + '/' + selectedEventId, 'DELETE')
                    .then(function (response) {
                        notify(response.message || 'Event deleted.');
                        eventModal.hide();
                        calendar.refetchEvents();

                        if (isAgendaMode) {
                            loadAgendaItems();
                        }
                    })
                    .catch(showValidationErrors);
            });

            completeButton.addEventListener('click', function () {
                if (!selectedEventId) {
                    return;
                }

                fetchJson(@json(url('/crm/calendar/events')) + '/' + selectedEventId + '/status', 'POST', {
                    status: 'completed'
                })
                    .then(function (response) {
                        notify(response.message || 'Event marked completed.');
                        eventModal.hide();
                        calendar.refetchEvents();

                        if (isAgendaMode) {
                            loadAgendaItems();
                        }
                    })
                    .catch(showValidationErrors);
            });

            cancelButton.addEventListener('click', function () {
                if (!selectedEventId) {
                    return;
                }

                fetchJson(@json(url('/crm/calendar/events')) + '/' + selectedEventId + '/status', 'POST', {
                    status: 'cancelled'
                })
                    .then(function (response) {
                        notify(response.message || 'Event cancelled.');
                        eventModal.hide();
                        calendar.refetchEvents();

                        if (isAgendaMode) {
                            loadAgendaItems();
                        }
                    })
                    .catch(showValidationErrors);
            });
        });
    </script>
@endpush

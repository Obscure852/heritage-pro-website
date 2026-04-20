@extends('layouts.master')

@section('title', 'My Calendar')

@section('css')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <style>
        .calendar-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .calendar-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .calendar-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #1f2937;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Sidebar Cards */
        .sidebar-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .sidebar-card-header {
            padding: 14px 16px;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-card-header.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-bottom: none;
        }

        .sidebar-card-header.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-bottom: none;
        }

        .sidebar-card-body {
            padding: 0;
        }

        .sidebar-card-body.with-padding {
            padding: 16px;
        }

        /* Deadline List */
        .deadline-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .deadline-item:last-child {
            border-bottom: none;
        }

        .deadline-item:hover {
            background: #f9fafb;
        }

        .deadline-title {
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }

        .deadline-course {
            font-size: 12px;
            color: #6b7280;
        }

        .deadline-date {
            font-size: 12px;
            font-weight: 600;
            color: #dc2626;
        }

        /* Legend */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .legend-label {
            font-size: 13px;
            color: #4b5563;
        }

        /* Appointment Link */
        .appointment-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
        }

        .appointment-link:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .appointment-link i {
            color: #3b82f6;
        }

        /* Calendar Customization */
        #calendar {
            min-height: 600px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .fc .fc-button-primary {
            background: #3b82f6;
            border-color: #3b82f6;
        }

        .fc .fc-button-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }

        .fc .fc-daygrid-day-number {
            font-size: 14px;
            padding: 8px;
        }

        .fc-event {
            border-radius: 3px;
            padding: 2px 4px;
            font-size: 12px;
            color: white !important;
            border: none;
        }

        .fc-event .fc-event-title,
        .fc-event .fc-event-time,
        .fc-event-main {
            color: white !important;
        }

        .fc-daygrid-event-dot {
            border-color: white !important;
        }

        .fc-list-event-title a,
        .fc-list-event-time {
            color: inherit;
        }

        /* Modal Styling */
        .modal-header {
            background: #f9fafb;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-header .btn-close {
            filter: none;
        }

        .modal-title {
            font-weight: 600;
            font-size: 18px;
        }

        .modal-title i {
            color: #3b82f6;
        }

        /* Event Detail Modal - Neutral Header */
        .event-detail-header {
            background: #f9fafb;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
        }

        .event-detail-header .btn-close {
            filter: none;
        }

        .event-detail-header .modal-title {
            display: flex;
            align-items: center;
        }

        .event-detail-header .modal-title i {
            color: #3b82f6;
        }

        /* Button styling for modal footer */
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 0;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            margin-left: 6px;
        }

        /* Empty State */
        .empty-state {
            padding: 24px 16px;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 32px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .quick-action-btn {
            flex: 1;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-align: center;
            color: #374151;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
        }

        .quick-action-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #1f2937;
        }

        .quick-action-btn i {
            display: block;
            font-size: 18px;
            margin-bottom: 4px;
            color: #3b82f6;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Calendar
        @endslot
    @endcomponent

    <div class="calendar-container">
        <div class="calendar-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 style="margin:0;"><i class="fas fa-calendar-alt me-2"></i>My Calendar</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track your classes, deadlines, and appointments</p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalEvents ?? 0 }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $eventCounts['class'] ?? 0 }}</h4>
                                <small class="opacity-75">Classes</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $deadlineCount ?? 0 }}</h4>
                                <small class="opacity-75">Deadlines</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $eventCounts['quiz'] ?? 0 }}</h4>
                                <small class="opacity-75">Quizzes</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $appointmentCount ?? 0 }}</h4>
                                <small class="opacity-75">Appointments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="calendar-body">
            <div class="help-text">
                <div class="help-title">Your Learning Calendar</div>
                <div class="help-content">
                    View all your class sessions, assignment deadlines, quizzes, and appointments in one place. Click on any event to see details, or drag events to reschedule them.
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('lms.calendar.preferences') }}" class="btn btn-outline">
                    <i class="fas fa-cog"></i> Preferences
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add Event
                </button>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div id="calendar"></div>
                </div>

                <div class="col-lg-3">
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="{{ route('lms.calendar.my-appointments') }}" class="quick-action-btn">
                            <i class="fas fa-calendar-check"></i>
                            Appointments
                        </a>
                        @can('manage-lms-content')
                        <a href="{{ route('lms.calendar.availability') }}" class="quick-action-btn">
                            <i class="fas fa-clock"></i>
                            Availability
                        </a>
                        @endcan
                    </div>

                    <!-- Upcoming Deadlines -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header danger">
                            <i class="fas fa-exclamation-circle"></i>
                            Upcoming Deadlines
                        </div>
                        <div class="sidebar-card-body" id="deadlinesList">
                            <div class="empty-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p style="margin:0;">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- My Appointments -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header primary">
                            <i class="fas fa-user-clock"></i>
                            My Appointments
                        </div>
                        <div class="sidebar-card-body">
                            <a href="{{ route('lms.calendar.my-appointments') }}" class="appointment-link">
                                <i class="fas fa-calendar-check"></i>
                                View All Appointments
                            </a>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="fas fa-palette"></i>
                            Event Legend
                        </div>
                        <div class="sidebar-card-body with-padding">
                            @foreach(\App\Models\Lms\CalendarEvent::$eventTypes as $type => $label)
                                <div class="legend-item">
                                    <span class="legend-dot" style="background-color: {{ \App\Models\Lms\CalendarEvent::$colors[$type] }};"></span>
                                    <span class="legend-label">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('lms.calendar.store') }}" method="POST" id="addEventForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Add New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Event Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter event title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Event Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" id="eventType" required>
                                        @foreach(\App\Models\Lms\CalendarEvent::$eventTypes as $key => $label)
                                            <option value="{{ $key }}" data-color="{{ \App\Models\Lms\CalendarEvent::$colors[$key] }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date/Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date/Time</label>
                                    <input type="datetime-local" name="end_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="all_day" class="form-check-input" value="1" id="allDayCheck">
                                <label class="form-check-label" for="allDayCheck">All Day Event</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Add event details..."></textarea>
                        </div>

                        <!-- Audience Targeting Section -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <label class="form-label fw-bold"><i class="fas fa-users me-1"></i>Who can see this event?</label>
                            <select class="form-select" id="audienceScope" name="audience_scope">
                                <option value="all">Everyone</option>
                                <option value="course">Specific Courses</option>
                                <option value="grade">Specific Grades</option>
                                <option value="class">Specific Classes</option>
                                <option value="mixed">Mixed (Multiple Types)</option>
                            </select>
                            <small class="text-muted">Select who should be able to see this event</small>
                        </div>

                        <!-- Course Selection (shown when scope includes courses) -->
                        <div class="mb-3 audience-selector" id="courseSelectorGroup" style="display: none;">
                            <label class="form-label">Select Courses</label>
                            <select class="form-select" id="audienceCourses" name="audience_courses[]" multiple>
                                @foreach($courses ?? [] as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Grade Selection -->
                        <div class="mb-3 audience-selector" id="gradeSelectorGroup" style="display: none;">
                            <label class="form-label">Select Grades</label>
                            <select class="form-select" id="audienceGrades" name="audience_grades[]" multiple>
                                @foreach($grades ?? [] as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Class Selection -->
                        <div class="mb-3 audience-selector" id="classSelectorGroup" style="display: none;">
                            <label class="form-label">Select Classes</label>
                            <select class="form-select" id="audienceClasses" name="audience_classes[]" multiple>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}">{{ $class->grade?->name ?? 'Unknown' }} - {{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Location</label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g., Room 101, Library">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-video me-1"></i>Meeting URL</label>
                                    <input type="url" name="meeting_url" class="form-control" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_published" class="form-check-input" value="1" id="isPublishedCheck" checked>
                                        <label class="form-check-label" for="isPublishedCheck">Publish immediately</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 p-3 rounded" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                            <div class="form-check">
                                <input type="checkbox" name="notify_students" class="form-check-input" value="1" id="notifyStudentsCheck">
                                <label class="form-check-label fw-bold" for="notifyStudentsCheck" style="color: #0369a1;">
                                    <i class="fas fa-envelope me-1"></i>
                                    Notify targeted students via email
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1 ms-4">
                                Students in the selected audience will receive an email notification about this event.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header event-detail-header" id="eventDetailHeader">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-day me-2" id="eventDetailIcon"></i>
                        <span id="eventDetailTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventDetailBody">
                </div>
                <div class="modal-footer" id="eventDetailFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: '{{ $preferences->default_view === "day" ? "timeGridDay" : ($preferences->default_view === "week" ? "timeGridWeek" : "dayGridMonth") }}',
                firstDay: {{ $preferences->week_start === 'monday' ? 1 : 0 }},
                weekends: {{ $preferences->show_weekends ? 'true' : 'false' }},
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    list: 'List'
                },
                events: '{{ route("lms.calendar.events") }}',
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    const props = info.event.extendedProps;
                    const eventColor = info.event.backgroundColor || '#3b82f6';

                    document.getElementById('eventDetailTitle').textContent = info.event.title;

                    let body = '';

                    // Event type badge
                    body += `<div class="mb-3"><span class="badge" style="background-color: ${eventColor}; padding: 6px 12px;">${props.type ? props.type.replace('_', ' ').toUpperCase() : 'EVENT'}</span></div>`;

                    // Time
                    const startDate = info.event.start;
                    const endDate = info.event.end;
                    if (info.event.allDay) {
                        body += `<p><i class="fas fa-calendar-day me-2 text-muted"></i><strong>All Day Event</strong> - ${startDate.toLocaleDateString()}</p>`;
                    } else {
                        body += `<p><i class="fas fa-clock me-2 text-muted"></i>${startDate.toLocaleString()}`;
                        if (endDate) {
                            body += ` - ${endDate.toLocaleTimeString()}`;
                        }
                        body += '</p>';
                    }

                    // Description
                    if (props.description) {
                        body += `<p class="mt-3">${props.description}</p>`;
                    }

                    // Location
                    if (props.location) {
                        body += `<p><i class="fas fa-map-marker-alt me-2 text-muted"></i>${props.location}</p>`;
                    }

                    // Meeting URL
                    if (props.meeting_url) {
                        body += `<p><a href="${props.meeting_url}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-video me-2"></i>Join Meeting</a></p>`;
                    }

                    // Instructor (for appointments)
                    if (props.instructor) {
                        body += `<p><i class="fas fa-user me-2 text-muted"></i>With: ${props.instructor}</p>`;
                    }

                    document.getElementById('eventDetailBody').innerHTML = body;

                    // Show edit/delete buttons for user's own events
                    const footer = document.getElementById('eventDetailFooter');
                    if (props.type !== 'appointment' && !info.event.id.toString().startsWith('schedule_') && !info.event.id.toString().startsWith('deadline_')) {
                        // Store event data for editing
                        window.currentEditEvent = {
                            id: info.event.id,
                            title: info.event.title,
                            type: props.type,
                            start: info.event.start,
                            end: info.event.end,
                            allDay: info.event.allDay,
                            description: props.description || '',
                            location: props.location || '',
                            meeting_url: props.meeting_url || '',
                            audience_scope: props.audience_scope || 'all',
                        };

                        footer.innerHTML = `
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="editEvent()">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteEvent(${info.event.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        `;
                    } else {
                        footer.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
                    }

                    new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
                },
                editable: true,
                eventDrop: function(info) {
                    // Only allow dragging for custom events
                    if (info.event.id.toString().startsWith('schedule_') || info.event.id.toString().startsWith('deadline_') || info.event.id.toString().startsWith('appointment_')) {
                        info.revert();
                        return;
                    }

                    fetch(`/lms/calendar/${info.event.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            start_date: info.event.start.toISOString(),
                            end_date: info.event.end ? info.event.end.toISOString() : null
                        })
                    }).then(response => {
                        if (!response.ok) {
                            info.revert();
                        }
                    });
                },
                eventResize: function(info) {
                    fetch(`/lms/calendar/${info.event.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            start_date: info.event.start.toISOString(),
                            end_date: info.event.end ? info.event.end.toISOString() : null
                        })
                    }).then(response => {
                        if (!response.ok) {
                            info.revert();
                        }
                    });
                }
            });
            calendar.render();

            // Load deadlines
            fetch('{{ route("lms.calendar.deadlines") }}')
                .then(r => r.json())
                .then(deadlines => {
                    const list = document.getElementById('deadlinesList');
                    if (deadlines.length === 0) {
                        list.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p style="margin:0;">No upcoming deadlines</p>
                            </div>
                        `;
                        return;
                    }
                    list.innerHTML = deadlines.map(d => `
                        <div class="deadline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="deadline-title">${d.title}</div>
                                    <div class="deadline-course">${d.course?.title || ''}</div>
                                </div>
                                <div class="deadline-date">${new Date(d.due_date).toLocaleDateString()}</div>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    document.getElementById('deadlinesList').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p style="margin:0;">No upcoming deadlines</p>
                        </div>
                    `;
                });

            // All day checkbox behavior
            document.getElementById('allDayCheck').addEventListener('change', function() {
                const startInput = document.querySelector('input[name="start_date"]');
                const endInput = document.querySelector('input[name="end_date"]');

                if (this.checked) {
                    startInput.type = 'date';
                    endInput.type = 'date';
                } else {
                    startInput.type = 'datetime-local';
                    endInput.type = 'datetime-local';
                }
            });

            // Initialize Choices.js for audience selectors
            const choicesConfig = {
                removeItemButton: true,
                searchEnabled: true,
                searchPlaceholderValue: 'Search...',
                placeholderValue: 'Select options',
                noResultsText: 'No results found',
                noChoicesText: 'No options available',
            };

            const coursesChoices = new Choices('#audienceCourses', {
                ...choicesConfig,
                placeholderValue: 'Search and select courses...',
            });

            const gradesChoices = new Choices('#audienceGrades', {
                ...choicesConfig,
                placeholderValue: 'Search and select grades...',
            });

            const classesChoices = new Choices('#audienceClasses', {
                ...choicesConfig,
                placeholderValue: 'Search and select classes...',
            });

            // Audience scope toggle behavior
            document.getElementById('audienceScope').addEventListener('change', function() {
                const scope = this.value;

                // Hide all selectors
                document.querySelectorAll('.audience-selector').forEach(el => el.style.display = 'none');

                // Show relevant selectors based on scope
                if (scope === 'course') {
                    document.getElementById('courseSelectorGroup').style.display = 'block';
                } else if (scope === 'grade') {
                    document.getElementById('gradeSelectorGroup').style.display = 'block';
                } else if (scope === 'class') {
                    document.getElementById('classSelectorGroup').style.display = 'block';
                } else if (scope === 'mixed') {
                    document.getElementById('courseSelectorGroup').style.display = 'block';
                    document.getElementById('gradeSelectorGroup').style.display = 'block';
                    document.getElementById('classSelectorGroup').style.display = 'block';
                }
            });

            // Reset form and Choices when modal is closed
            document.getElementById('addEventModal').addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('addEventForm');
                form.reset();
                form.removeAttribute('data-edit-mode');
                form.removeAttribute('data-event-id');

                // Reset modal title and button
                document.querySelector('#addEventModal .modal-title').innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Add New Event';
                form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save me-1"></i>Save Event';

                // Reset date inputs to datetime-local
                form.querySelector('input[name="start_date"]').type = 'datetime-local';
                form.querySelector('input[name="end_date"]').type = 'datetime-local';

                coursesChoices.removeActiveItems();
                gradesChoices.removeActiveItems();
                classesChoices.removeActiveItems();
                document.querySelectorAll('.audience-selector').forEach(el => el.style.display = 'none');
            });

            // Handle form submission for both create and update
            document.getElementById('addEventForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const isEditMode = form.getAttribute('data-edit-mode') === 'true';
                const eventId = form.getAttribute('data-event-id');

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (isEditMode ? 'Updating...' : 'Saving...');

                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    if (key.endsWith('[]')) {
                        const cleanKey = key.slice(0, -2);
                        if (!data[cleanKey]) data[cleanKey] = [];
                        data[cleanKey].push(value);
                    } else {
                        data[key] = value;
                    }
                });

                // Handle checkboxes
                data.all_day = form.querySelector('input[name="all_day"]').checked ? 1 : 0;
                data.is_published = form.querySelector('input[name="is_published"]').checked ? 1 : 0;
                data.notify_students = form.querySelector('input[name="notify_students"]').checked ? 1 : 0;

                const url = isEditMode ? `/lms/calendar/${eventId}` : '{{ route("lms.calendar.store") }}';
                const method = isEditMode ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                        location.reload();
                    } else {
                        alert(result.message || 'An error occurred. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        });

        function editEvent() {
            const event = window.currentEditEvent;
            if (!event) return;

            // Close the detail modal
            bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();

            // Get the form and modal
            const form = document.getElementById('addEventForm');
            const modal = document.getElementById('addEventModal');

            // Change modal title
            modal.querySelector('.modal-title').innerHTML = '<i class="fas fa-calendar-edit me-2"></i>Edit Event';

            // Set form to edit mode
            form.setAttribute('data-edit-mode', 'true');
            form.setAttribute('data-event-id', event.id);

            // Populate form fields
            form.querySelector('input[name="title"]').value = event.title;
            form.querySelector('select[name="type"]').value = event.type || 'custom';
            form.querySelector('textarea[name="description"]').value = event.description;
            form.querySelector('input[name="location"]').value = event.location;
            form.querySelector('input[name="meeting_url"]').value = event.meeting_url;

            // Handle dates
            const allDayCheck = form.querySelector('input[name="all_day"]');
            const startInput = form.querySelector('input[name="start_date"]');
            const endInput = form.querySelector('input[name="end_date"]');

            if (event.allDay) {
                allDayCheck.checked = true;
                startInput.type = 'date';
                endInput.type = 'date';
                startInput.value = event.start.toISOString().split('T')[0];
                if (event.end) {
                    endInput.value = event.end.toISOString().split('T')[0];
                }
            } else {
                allDayCheck.checked = false;
                startInput.type = 'datetime-local';
                endInput.type = 'datetime-local';

                // Format datetime for input
                const formatDateTime = (date) => {
                    const d = new Date(date);
                    return d.getFullYear() + '-' +
                           String(d.getMonth() + 1).padStart(2, '0') + '-' +
                           String(d.getDate()).padStart(2, '0') + 'T' +
                           String(d.getHours()).padStart(2, '0') + ':' +
                           String(d.getMinutes()).padStart(2, '0');
                };

                startInput.value = formatDateTime(event.start);
                if (event.end) {
                    endInput.value = formatDateTime(event.end);
                }
            }

            // Update submit button text
            form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save me-1"></i>Update Event';

            // Show the modal
            new bootstrap.Modal(modal).show();
        }

        function deleteEvent(eventId) {
            if (!confirm('Are you sure you want to delete this event?')) return;

            // Show loading state in the modal
            document.getElementById('eventDetailBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p class="text-muted mb-0">Deleting event...</p>
                </div>
            `;
            document.getElementById('eventDetailFooter').innerHTML = '';

            fetch(`/lms/calendar/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => {
                if (response.ok) {
                    // Show success message
                    document.getElementById('eventDetailBody').innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success mb-2">Event Deleted</h5>
                            <p class="text-muted mb-0">The event has been successfully removed from your calendar.</p>
                        </div>
                    `;

                    // Close modal and reload after a short delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    document.getElementById('eventDetailBody').innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                            <h5 class="text-danger mb-2">Delete Failed</h5>
                            <p class="text-muted mb-0">Something went wrong. Please try again.</p>
                        </div>
                    `;
                    document.getElementById('eventDetailFooter').innerHTML = `
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    `;
                }
            }).catch(() => {
                // Show error message on network failure
                document.getElementById('eventDetailBody').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger mb-2">Delete Failed</h5>
                        <p class="text-muted mb-0">Network error. Please try again.</p>
                    </div>
                `;
                document.getElementById('eventDetailFooter').innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                `;
            });
        }
    </script>
@endsection

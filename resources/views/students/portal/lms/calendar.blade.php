@extends('layouts.master-student-portal')
@section('title', 'My Calendar')
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #3a5bc7;
            --secondary: #36b9cc;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --purple: #6f42c1;
            --dark: #1a1e2c;
            --gray-100: #f8f9fc;
            --gray-200: #eaecf4;
            --gray-600: #6c757d;
            --gray-800: #2d3748;
        }


        /* Page Container */
        .calendar-page-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Page Header */
        .calendar-page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
        }

        .calendar-page-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .calendar-page-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
        }

        /* Header Stats */
        .header-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .header-stat-item {
            padding: 10px 0;
        }

        .header-stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: white;
        }

        .header-stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        @media (max-width: 768px) {
            .header-stat-item h4 {
                font-size: 1.25rem;
            }

            .header-stat-item small {
                font-size: 0.75rem;
            }

            .calendar-page-header {
                padding: 20px;
            }
        }

        /* Page Body */
        .calendar-page-body {
            padding: 24px;
        }

        /* Content Cards */
        .content-card {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .content-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-card-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-card-header h3 i {
            color: var(--primary);
        }

        .content-card-body {
            padding: 1.5rem;
        }

        /* Calendar Styles */
        #calendar {
            min-height: 600px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .fc .fc-button-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .fc .fc-button-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .fc-event {
            cursor: pointer;
            border-radius: 3px;
            font-size: 0.8rem;
            padding: 2px 4px;
            color: white !important;
        }

        .fc-event .fc-event-title,
        .fc-event .fc-event-time {
            color: white !important;
        }

        /* Legend */
        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-600);
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }

        /* Sidebar */
        .sidebar-widget {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .sidebar-widget-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-widget-header i {
            color: var(--primary);
        }

        .sidebar-widget-body {
            padding: 0;
        }

        .deadline-item, .appointment-item {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            transition: background 0.2s ease;
        }

        .deadline-item:last-child, .appointment-item:last-child {
            border-bottom: none;
        }

        .deadline-item:hover, .appointment-item:hover {
            background: var(--gray-100);
        }

        .deadline-item h5, .appointment-item h5 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .deadline-item .meta, .appointment-item .meta {
            font-size: 0.8rem;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .deadline-badge {
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-weight: 600;
        }

        .deadline-badge.urgent {
            background: rgba(231, 74, 59, 0.1);
            color: var(--danger);
        }

        .deadline-badge.soon {
            background: rgba(246, 194, 62, 0.1);
            color: #b7950b;
        }

        .deadline-badge.normal {
            background: rgba(28, 200, 138, 0.1);
            color: var(--success);
        }

        .empty-widget {
            padding: 2rem;
            text-align: center;
            color: var(--gray-600);
        }

        .empty-widget i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .sidebar-link {
            display: block;
            padding: 0.75rem 1.25rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            border-top: 1px solid var(--gray-200);
            transition: background 0.2s ease;
        }

        .sidebar-link:hover {
            background: var(--gray-100);
        }

        /* Help Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px;
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
            line-height: 1.4;
        }

        /* Event Modal */
        .event-modal-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .event-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .event-details {
            display: grid;
            gap: 0.75rem;
        }

        .event-detail-row {
            display: flex;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .event-detail-row i {
            color: var(--gray-600);
            width: 18px;
        }

        .event-detail-row span {
            color: var(--gray-800);
        }

        @media (max-width: 992px) {
            .calendar-main {
                order: 2;
            }
            .calendar-sidebar {
                order: 1;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('student.lms.my-courses') }}">My Courses</a>
        @endslot
        @slot('title')
            My Calendar
        @endslot
    @endcomponent

    <div class="calendar-page-container">
        <!-- Page Header with Stats -->
        <div class="calendar-page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3>My Calendar</h3>
                    <p>View and manage your academic schedule</p>
                </div>
                <div class="col-md-6">
                    <div class="header-stats">
                        <div class="header-stat-item">
                            <h4>{{ $schedulesCount }}</h4>
                            <small>Schedules</small>
                        </div>
                        <div class="header-stat-item">
                            <h4>{{ $deadlinesCount }}</h4>
                            <small>Deadlines</small>
                        </div>
                        <div class="header-stat-item">
                            <h4>{{ $eventsCount }}</h4>
                            <small>Events</small>
                        </div>
                        <div class="header-stat-item">
                            <h4>{{ $appointmentsCount }}</h4>
                            <small>Appointments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="calendar-page-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Your Academic Calendar</div>
                <div class="help-content">
                    View your class schedules, assignment deadlines, tests, events, and appointments all in one place.
                    Click on any event to view details.
                </div>
            </div>

            <div class="row">
            <!-- Calendar Main -->
            <div class="col-lg-9 calendar-main">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>
                            <i class="mdi mdi-calendar-month"></i>
                            Academic Calendar
                        </h3>
                    </div>
                    <div class="content-card-body">
                        <div id="calendar"></div>

                        <!-- Legend -->
                        <div class="calendar-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background: #36b9cc;"></div>
                                <span>Class Schedule</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #e74a3b;"></div>
                                <span>Deadline</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #4e73df;"></div>
                                <span>Event</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #6f42c1;"></div>
                                <span>Appointment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 calendar-sidebar">
                <!-- Quick Actions -->
                <div class="sidebar-widget">
                    <div class="sidebar-widget-header">
                        <i class="mdi mdi-lightning-bolt"></i>
                        Quick Actions
                    </div>
                    <div class="sidebar-widget-body" style="padding: 1rem;">
                        <a href="{{ route('student.lms.appointments.index') }}" class="btn btn-outline-primary btn-block mb-2 w-100">
                            <i class="mdi mdi-calendar-plus"></i> My Appointments
                        </a>
                        <a href="{{ route('student.lms.my-courses') }}" class="btn btn-outline-secondary btn-block w-100">
                            <i class="mdi mdi-book-open"></i> My Courses
                        </a>
                    </div>
                </div>

                <!-- Upcoming Deadlines Widget -->
                <div class="sidebar-widget">
                    <div class="sidebar-widget-header">
                        <i class="mdi mdi-clipboard-alert"></i>
                        Upcoming Deadlines
                    </div>
                    <div class="sidebar-widget-body" id="deadlinesWidget">
                        <div class="empty-widget">
                            <i class="mdi mdi-loading mdi-spin d-block"></i>
                            <span>Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Content populated by JS -->
                </div>
                <div class="modal-footer" id="eventModalFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const eventModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch('{{ route('student.lms.calendar.events') }}?' + new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr
                    }))
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => {
                        console.error('Error fetching events:', error);
                        failureCallback(error);
                    });
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const event = info.event;
                    const props = event.extendedProps;

                    // Populate modal
                    document.getElementById('eventModalTitle').textContent = event.title;

                    let bodyHtml = '<div class="event-details">';

                    // Event type badge
                    const typeColors = {
                        'schedule': '#36b9cc',
                        'deadline': '#e74a3b',
                        'event': '#4e73df',
                        'appointment': '#6f42c1'
                    };

                    const typeLabels = {
                        'schedule': 'Class Schedule',
                        'deadline': 'Deadline',
                        'event': 'Event',
                        'appointment': 'Appointment'
                    };

                    bodyHtml += `<div class="event-modal-header">
                        <span class="event-type-badge" style="background: ${typeColors[props.type] || '#858796'}20; color: ${typeColors[props.type] || '#858796'};">
                            ${typeLabels[props.type] || props.type}
                        </span>
                    </div>`;

                    // Date/Time
                    const startDate = event.start ? event.start.toLocaleString() : 'N/A';
                    const endDate = event.end ? event.end.toLocaleString() : null;

                    bodyHtml += `<div class="event-detail-row">
                        <i class="mdi mdi-clock-outline"></i>
                        <span>${startDate}${endDate ? ' - ' + endDate : ''}</span>
                    </div>`;

                    // Location
                    if (props.location) {
                        bodyHtml += `<div class="event-detail-row">
                            <i class="mdi mdi-map-marker"></i>
                            <span>${props.location}</span>
                        </div>`;
                    }

                    // Description
                    if (props.description) {
                        bodyHtml += `<div class="event-detail-row">
                            <i class="mdi mdi-text"></i>
                            <span>${props.description}</span>
                        </div>`;
                    }

                    // Check for meeting link (support multiple property names)
                    const meetingLink = props.meeting_url || props.meetingUrl || props.meeting_link || props.meetingLink || props.url || props.link || props.join_url || props.joinUrl;
                    const isOnline = props.is_virtual || props.is_online || props.isVirtual || props.isOnline || props.online || meetingLink;

                    // Show online indicator
                    if (isOnline) {
                        bodyHtml += `<div class="event-detail-row">
                            <i class="mdi mdi-video"></i>
                            <span class="badge bg-info">Online Event</span>
                        </div>`;
                    }

                    // Meeting link in body
                    if (meetingLink) {
                        bodyHtml += `<div class="event-detail-row">
                            <i class="mdi mdi-link-variant"></i>
                            <a href="${meetingLink}" target="_blank" rel="noopener noreferrer">${meetingLink}</a>
                        </div>`;
                    }

                    bodyHtml += '</div>';

                    document.getElementById('eventModalBody').innerHTML = bodyHtml;

                    // Footer with Join Meeting button for online events
                    let footerHtml = '';
                    if (meetingLink) {
                        footerHtml += `<a href="${meetingLink}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                            <i class="mdi mdi-video me-1"></i> Join Meeting
                        </a>`;
                    }
                    footerHtml += '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
                    document.getElementById('eventModalFooter').innerHTML = footerHtml;

                    eventModal.show();
                },
                eventDidMount: function(info) {
                    info.el.setAttribute('title', info.event.title);
                },
                height: 'auto',
                dayMaxEvents: 3,
                navLinks: true,
                nowIndicator: true,
                editable: false,
                selectable: false,
            });

            calendar.render();

            // Load upcoming deadlines
            fetch('{{ route('student.lms.calendar.deadlines') }}')
                .then(response => response.json())
                .then(deadlines => {
                    const widget = document.getElementById('deadlinesWidget');
                    if (deadlines.length === 0) {
                        widget.innerHTML = `<div class="empty-widget">
                            <i class="mdi mdi-check-circle-outline d-block"></i>
                            <span>No upcoming deadlines</span>
                        </div>`;
                        return;
                    }

                    let html = '';
                    deadlines.forEach(deadline => {
                        const dueDate = new Date(deadline.due_datetime);
                        const now = new Date();
                        const daysLeft = Math.ceil((dueDate - now) / (1000 * 60 * 60 * 24));
                        const isUrgent = daysLeft <= 1;
                        const isSoon = daysLeft <= 3 && !isUrgent;
                        const badgeClass = isUrgent ? 'urgent' : (isSoon ? 'soon' : 'normal');

                        html += `<div class="deadline-item">
                            <h5>${deadline.title}</h5>
                            <div class="meta">
                                <span class="deadline-badge ${badgeClass}">${dueDate.toLocaleDateString()}</span>
                                <span>${deadline.course}</span>
                            </div>
                        </div>`;
                    });

                    widget.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading deadlines:', error);
                    document.getElementById('deadlinesWidget').innerHTML = `<div class="empty-widget">
                        <i class="mdi mdi-alert-circle-outline d-block"></i>
                        <span>Failed to load deadlines</span>
                    </div>`;
                });
        });
    </script>
@endsection

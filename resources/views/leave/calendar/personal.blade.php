@extends('layouts.master')
@section('title')
    My Leave Calendar
@endsection
@section('css')
    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        .calendar-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .page-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .calendar-wrapper {
            padding: 24px;
        }

        .legend-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 24px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #374151;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .legend-color.pending {
            background: repeating-linear-gradient(
                45deg,
                #93c5fd,
                #93c5fd 5px,
                #60a5fa 5px,
                #60a5fa 10px
            );
        }

        .legend-color.holiday {
            background: #dc2626;
        }

        .help-text {
            background: #eff6ff;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #1e3a8a;
            font-size: 13px;
            line-height: 1.4;
        }

        #calendar {
            min-height: 600px;
        }

        /* FullCalendar Custom Styles */
        .fc {
            font-family: inherit;
        }

        .fc .fc-toolbar-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
        }

        .fc .fc-button-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            box-shadow: none;
        }

        .fc .fc-button-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #1d4ed8;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background: #eff6ff;
        }

        .fc .fc-daygrid-day-number {
            font-weight: 500;
            color: #374151;
        }

        .fc-event {
            cursor: pointer;
            border: none;
            padding: 2px 6px;
            font-size: 12px;
            border-radius: 4px;
        }

        .fc-event:hover {
            opacity: 0.9;
        }

        /* Pending leave striped pattern */
        .fc-event.pending-leave {
            background: repeating-linear-gradient(
                45deg,
                var(--fc-event-bg-color, #3b82f6),
                var(--fc-event-bg-color, #3b82f6) 5px,
                rgba(255,255,255,0.3) 5px,
                rgba(255,255,255,0.3) 10px
            ) !important;
        }

        .fc-h-event .fc-event-title {
            font-weight: 500;
        }

        /* Event details modal */
        .event-detail-modal .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }

        .event-detail-modal .modal-body {
            padding: 24px;
        }

        .event-info-item {
            display: flex;
            margin-bottom: 12px;
        }

        .event-info-item:last-child {
            margin-bottom: 0;
        }

        .event-info-label {
            width: 100px;
            font-weight: 500;
            color: #6b7280;
            flex-shrink: 0;
        }

        .event-info-value {
            color: #1f2937;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .holiday-badge {
            display: inline-block;
            background: #fef2f2;
            color: #dc2626;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .view-request-btn {
            margin-top: 16px;
        }

        @media (max-width: 768px) {
            .calendar-wrapper {
                padding: 16px;
            }

            .legend-section {
                flex-direction: column;
                gap: 12px;
            }

            .fc .fc-toolbar {
                flex-direction: column;
                gap: 12px;
            }

            .fc .fc-toolbar-title {
                font-size: 1.1rem;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            Human Resources
        @endslot
    @endcomponent

    <div class="calendar-container">
        <div class="page-header">
            <h3><i class="fas fa-calendar-alt me-2"></i>My Leave Calendar</h3>
            <p>View your approved leave, pending requests, and public holidays</p>
        </div>

        <div class="calendar-wrapper">
            <div class="help-text">
                <div class="help-title">Calendar Features</div>
                <div class="help-content">
                    Click on any event to see more details. Use the navigation buttons to move between months.
                    Switch between Month and List views using the buttons in the top right.
                </div>
            </div>

            <div class="legend-section">
                <div class="legend-item">
                    <div class="legend-color" style="background: #3b82f6;"></div>
                    <span>Approved Leave</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color pending"></div>
                    <span>Pending Leave</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color holiday"></div>
                    <span>Public Holiday</span>
                </div>
            </div>

            <div id="calendar"></div>
        </div>
    </div>

    {{-- Event Details Modal --}}
    <div class="modal fade event-detail-modal" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="viewRequestLink" class="btn btn-primary d-none">
                        <i class="fas fa-eye me-1"></i> View Request
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const eventModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            const eventDetailsEl = document.getElementById('eventDetails');
            const viewRequestLink = document.getElementById('viewRequestLink');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                height: 'auto',
                firstDay: 0, // Sunday
                navLinks: true,
                editable: false,
                dayMaxEvents: 3, // Show "+X more" when many events
                events: function(info, successCallback, failureCallback) {
                    fetch('{{ route('leave.calendar.personal-events') }}?' + new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr
                    }))
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                    })
                    .catch(error => {
                        console.error('Error fetching events:', error);
                        failureCallback(error);
                    });
                },
                eventClick: function(info) {
                    const event = info.event;
                    const props = event.extendedProps;

                    let detailsHtml = '';

                    if (props.type === 'leave') {
                        // Leave event
                        const statusClass = props.status === 'approved' ? 'approved' : 'pending';
                        const statusText = props.status.charAt(0).toUpperCase() + props.status.slice(1);

                        detailsHtml = `
                            <div class="event-info-item">
                                <div class="event-info-label">Type</div>
                                <div class="event-info-value">${props.leaveType}</div>
                            </div>
                            <div class="event-info-item">
                                <div class="event-info-label">Start Date</div>
                                <div class="event-info-value">${formatDate(event.start)}</div>
                            </div>
                            <div class="event-info-item">
                                <div class="event-info-label">End Date</div>
                                <div class="event-info-value">${formatDate(event.end ? new Date(event.end.getTime() - 86400000) : event.start)}</div>
                            </div>
                            <div class="event-info-item">
                                <div class="event-info-label">Days</div>
                                <div class="event-info-value">${props.days} day${props.days != 1 ? 's' : ''}</div>
                            </div>
                            <div class="event-info-item">
                                <div class="event-info-label">Status</div>
                                <div class="event-info-value"><span class="status-badge ${statusClass}">${statusText}</span></div>
                            </div>
                        `;

                        // Show view request link
                        viewRequestLink.href = '{{ url('/leave/requests') }}/' + props.requestId;
                        viewRequestLink.classList.remove('d-none');
                    } else if (props.type === 'holiday') {
                        // Holiday event
                        detailsHtml = `
                            <div class="event-info-item">
                                <div class="event-info-label">Holiday</div>
                                <div class="event-info-value">${event.title}</div>
                            </div>
                            <div class="event-info-item">
                                <div class="event-info-label">Date</div>
                                <div class="event-info-value">${formatDate(event.start)}</div>
                            </div>
                            ${props.description ? `
                            <div class="event-info-item">
                                <div class="event-info-label">Description</div>
                                <div class="event-info-value">${props.description}</div>
                            </div>
                            ` : ''}
                            <div class="event-info-item">
                                <div class="event-info-label">Type</div>
                                <div class="event-info-value"><span class="holiday-badge">Public Holiday</span></div>
                            </div>
                        `;

                        // Hide view request link for holidays
                        viewRequestLink.classList.add('d-none');
                    }

                    document.getElementById('eventDetailModalLabel').textContent = event.title;
                    eventDetailsEl.innerHTML = detailsHtml;
                    eventModal.show();
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    const props = info.event.extendedProps;
                    let tooltipText = info.event.title;

                    if (props.type === 'leave') {
                        tooltipText += ` (${props.status})`;
                    }

                    info.el.setAttribute('title', tooltipText);
                }
            });

            calendar.render();

            // Helper function to format dates
            function formatDate(date) {
                if (!date) return '';
                const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
                return date.toLocaleDateString('en-US', options);
            }
        });
    </script>
@endsection

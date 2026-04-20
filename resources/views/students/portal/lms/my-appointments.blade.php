@extends('layouts.master-student-portal')
@section('title', 'My Appointments')
@section('css')
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #3a5bc7;
            --secondary: #36b9cc;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --purple: #6f42c1;
            --gray-100: #f8f9fc;
            --gray-200: #eaecf4;
            --gray-600: #6c757d;
            --gray-800: #2d3748;
        }

        /* Page Container */
        .appointments-page-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Page Header */
        .appointments-page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
        }

        .appointments-page-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .appointments-page-header p {
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

            .appointments-page-header {
                padding: 20px;
            }
        }

        /* Page Body */
        .appointments-page-body {
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
            padding: 0;
        }

        /* Appointment Items */
        .appointment-item {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: background 0.2s ease;
        }

        .appointment-item:last-child {
            border-bottom: none;
        }

        .appointment-item:hover {
            background: var(--gray-100);
        }

        .appointment-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .appointment-icon.scheduled {
            background: rgba(78, 115, 223, 0.1);
            color: var(--primary);
        }

        .appointment-icon.completed {
            background: rgba(54, 185, 204, 0.1);
            color: var(--secondary);
        }

        .appointment-icon.cancelled {
            background: rgba(108, 117, 125, 0.1);
            color: var(--gray-600);
        }

        .appointment-content {
            flex: 1;
            min-width: 0;
        }

        .appointment-content h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .appointment-content .meta {
            font-size: 0.85rem;
            color: var(--gray-600);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .appointment-content .meta span {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .appointment-content .notes {
            font-size: 0.85rem;
            color: var(--gray-600);
            background: var(--gray-100);
            padding: 0.5rem 0.75rem;
            border-radius: 3px;
            margin-top: 0.5rem;
        }

        .appointment-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-weight: 600;
        }

        .status-badge.scheduled {
            background: rgba(78, 115, 223, 0.1);
            color: var(--primary);
        }

        .status-badge.completed {
            background: rgba(54, 185, 204, 0.1);
            color: var(--secondary);
        }

        .status-badge.cancelled {
            background: rgba(108, 117, 125, 0.1);
            color: var(--gray-600);
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

        /* Empty State */
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            border-radius: 3px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--gray-600);
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0;
        }

        /* Available Schedules */
        .schedule-card {
            background: var(--gray-100);
            border-radius: 3px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .schedule-card:hover {
            background: #fff;
            border-color: var(--primary);
        }

        .schedule-card h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .schedule-card .meta {
            font-size: 0.85rem;
            color: var(--gray-600);
            margin-bottom: 0.75rem;
        }

        .schedule-card .meta span {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 0.25rem;
        }

        @media (max-width: 768px) {
            .appointment-item {
                flex-direction: column;
            }

            .appointment-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
@endsection

@section('content')
    <div class="appointments-page-container">
        <!-- Page Header with Stats -->
        <div class="appointments-page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3>My Appointments</h3>
                    <p>Book and manage your appointments with instructors</p>
                </div>
                <div class="col-md-6">
                    <div class="header-stats">
                        <div class="header-stat-item">
                            <h4>{{ $upcomingAppointments->count() }}</h4>
                            <small>Upcoming</small>
                        </div>
                        <div class="header-stat-item">
                            <h4>{{ $pastAppointments->count() }}</h4>
                            <small>Completed</small>
                        </div>
                        <div class="header-stat-item">
                            <h4>{{ $cancelledAppointments->count() }}</h4>
                            <small>Cancelled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="appointments-page-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Manage Your Appointments</div>
                <div class="help-content">
                    Book appointments with instructors and manage your schedule. You can cancel appointments up to 2 hours before the scheduled time.
                </div>
            </div>

            <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Upcoming Appointments -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>
                            <i class="mdi mdi-calendar-clock"></i>
                            Upcoming Appointments
                        </h3>
                        <span class="badge bg-primary">{{ $upcomingAppointments->count() }}</span>
                    </div>
                    <div class="content-card-body">
                        @forelse($upcomingAppointments as $appointment)
                            <div class="appointment-item">
                                <div class="appointment-icon {{ $appointment->status }}">
                                    <i class="mdi mdi-account-clock"></i>
                                </div>
                                <div class="appointment-content">
                                    <h4>{{ $appointment->schedule->user->name ?? 'Instructor' }}</h4>
                                    <div class="meta">
                                        <span>
                                            <i class="mdi mdi-clock-outline"></i>
                                            {{ $appointment->start_time->format('l, M d, Y \a\t H:i') }}
                                        </span>
                                        @if($appointment->schedule->location)
                                            <span>
                                                <i class="mdi mdi-map-marker"></i>
                                                {{ $appointment->schedule->location }}
                                            </span>
                                        @endif
                                        <span class="status-badge {{ $appointment->status }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                    @if($appointment->student_notes)
                                        <div class="notes">
                                            <strong>Notes:</strong> {{ $appointment->student_notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="appointment-actions">
                                    @if($appointment->meeting_url)
                                        <a href="{{ $appointment->meeting_url }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-video"></i> Join
                                        </a>
                                    @endif
                                    @if($appointment->start_time->gt(now()->addHours(2)))
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-cancel-appointment"
                                                data-appointment-id="{{ $appointment->id }}"
                                                data-appointment-date="{{ $appointment->start_time->format('M d, H:i') }}">
                                            <i class="mdi mdi-close"></i> Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="mdi mdi-calendar-blank"></i>
                                </div>
                                <h4>No Upcoming Appointments</h4>
                                <p>Book an appointment with an instructor to get started.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Past Appointments -->
                @if($pastAppointments->count() > 0)
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>
                                <i class="mdi mdi-history"></i>
                                Past Appointments
                            </h3>
                        </div>
                        <div class="content-card-body">
                            @foreach($pastAppointments as $appointment)
                                <div class="appointment-item">
                                    <div class="appointment-icon completed">
                                        <i class="mdi mdi-check-circle"></i>
                                    </div>
                                    <div class="appointment-content">
                                        <h4>{{ $appointment->schedule->user->name ?? 'Instructor' }}</h4>
                                        <div class="meta">
                                            <span>
                                                <i class="mdi mdi-clock-outline"></i>
                                                {{ $appointment->start_time->format('l, M d, Y \a\t H:i') }}
                                            </span>
                                            @if($appointment->schedule->location)
                                                <span>
                                                    <i class="mdi mdi-map-marker"></i>
                                                    {{ $appointment->schedule->location }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Cancelled Appointments -->
                @if($cancelledAppointments->count() > 0)
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>
                                <i class="mdi mdi-calendar-remove"></i>
                                Cancelled Appointments
                            </h3>
                        </div>
                        <div class="content-card-body">
                            @foreach($cancelledAppointments as $appointment)
                                <div class="appointment-item">
                                    <div class="appointment-icon cancelled">
                                        <i class="mdi mdi-cancel"></i>
                                    </div>
                                    <div class="appointment-content">
                                        <h4>{{ $appointment->schedule->user->name ?? 'Instructor' }}</h4>
                                        <div class="meta">
                                            <span>
                                                <i class="mdi mdi-clock-outline"></i>
                                                {{ $appointment->start_time->format('l, M d, Y \a\t H:i') }}
                                            </span>
                                            <span class="status-badge cancelled">Cancelled</span>
                                        </div>
                                        @if($appointment->cancellation_reason)
                                            <div class="notes">
                                                <strong>Reason:</strong> {{ $appointment->cancellation_reason }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar - Book Appointment -->
            <div class="col-lg-4">
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>
                            <i class="mdi mdi-calendar-plus"></i>
                            Book Appointment
                        </h3>
                    </div>
                    <div class="content-card-body" style="padding: 1.25rem;">
                        @forelse($availableSchedules as $schedule)
                            <div class="schedule-card">
                                <h5>{{ $schedule->title ?: 'Office Hours' }}</h5>
                                <div class="meta">
                                    <span>
                                        <i class="mdi mdi-account"></i>
                                        {{ $schedule->user->name ?? 'Instructor' }}
                                    </span>
                                    @foreach($schedule->windows as $window)
                                        <span>
                                            <i class="mdi mdi-calendar"></i>
                                            {{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$window->day_of_week] }}s, {{ \Carbon\Carbon::parse($window->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($window->end_time)->format('H:i') }}
                                        </span>
                                    @endforeach
                                    <span>
                                        <i class="mdi mdi-clock-outline"></i>
                                        {{ $schedule->slot_duration }} minutes per slot
                                    </span>
                                    @if($schedule->meeting_url)
                                        <span>
                                            <i class="mdi mdi-video"></i>
                                            Virtual Meeting
                                        </span>
                                    @elseif($schedule->location)
                                        <span>
                                            <i class="mdi mdi-map-marker"></i>
                                            {{ $schedule->location }}
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('student.lms.appointments.slots', $schedule->id) }}" class="btn btn-sm btn-primary w-100">
                                    View Available Slots
                                </a>
                            </div>
                        @empty
                            <div class="empty-state" style="padding: 2rem 1rem;">
                                <div class="empty-state-icon">
                                    <i class="mdi mdi-calendar-blank"></i>
                                </div>
                                <h4>No Available Schedules</h4>
                                <p>No instructors have published availability schedules for your courses.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h3>
                            <i class="mdi mdi-link-variant"></i>
                            Quick Links
                        </h3>
                    </div>
                    <div class="content-card-body" style="padding: 1.25rem;">
                        <a href="{{ route('student.lms.calendar.index') }}" class="btn btn-outline-primary btn-block w-100 mb-2">
                            <i class="mdi mdi-calendar"></i> View Full Calendar
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-block w-100">
                            <i class="mdi mdi-home"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel your appointment on <strong id="cancelAppointmentDate"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <textarea class="form-control" id="cancelReason" rows="2" placeholder="Enter cancellation reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Cancel Appointment</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
            let currentAppointmentId = null;

            // Handle cancel button clicks
            document.querySelectorAll('.btn-cancel-appointment').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    currentAppointmentId = this.dataset.appointmentId;
                    document.getElementById('cancelAppointmentDate').textContent = this.dataset.appointmentDate;
                    document.getElementById('cancelReason').value = '';
                    cancelModal.show();
                });
            });

            // Handle cancel confirmation
            document.getElementById('confirmCancelBtn').addEventListener('click', function() {
                if (!currentAppointmentId) return;

                const reason = document.getElementById('cancelReason').value;
                const btn = this;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Cancelling...';

                fetch(`{{ url('student/lms/appointments') }}/${currentAppointmentId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cancelModal.hide();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to cancel appointment');
                        btn.disabled = false;
                        btn.innerHTML = 'Cancel Appointment';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = 'Cancel Appointment';
                });
            });
        });
    </script>
@endsection

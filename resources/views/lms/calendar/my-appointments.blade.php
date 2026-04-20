@extends('layouts.master')

@section('title', 'My Appointments')

@section('css')
    <style>
        .appointments-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .appointments-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .appointments-body {
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
            border-left: 4px solid #10b981;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
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

        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .section-card-header {
            padding: 16px 20px;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-card-header.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-bottom: none;
        }

        .section-card-header.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-bottom: none;
        }

        .section-card-header.secondary {
            background: #f9fafb;
            color: #374151;
        }

        .section-card-body {
            padding: 0;
        }

        .section-card-body.with-padding {
            padding: 20px;
        }

        /* Appointment Items */
        .appointment-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .appointment-item:last-child {
            border-bottom: none;
        }

        .appointment-item:hover {
            background: #f9fafb;
        }

        .appointment-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .appointment-meta {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .appointment-meta i {
            width: 16px;
            color: #9ca3af;
        }

        .appointment-note {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
            background: #f9fafb;
            padding: 8px 12px;
            border-radius: 3px;
            margin-top: 8px;
        }

        .appointment-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-outline-danger {
            background: white;
            color: #dc2626;
            border: 1px solid #dc2626;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
            color: white;
        }

        .btn-outline-primary {
            background: white;
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Empty State */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Past Appointment Item */
        .past-appointment-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .past-appointment-item:last-child {
            border-bottom: none;
        }

        .past-appointment-title {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .past-appointment-instructor {
            color: #6b7280;
            font-size: 13px;
        }

        .past-appointment-date {
            font-size: 12px;
            color: #9ca3af;
        }

        .cancellation-reason {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        /* Sidebar Card */
        .sidebar-info-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .sidebar-info-card p {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.calendar.index') }}">Calendar</a>
        @endslot
        @slot('title')
            My Appointments
        @endslot
    @endcomponent

    <div class="appointments-container">
        <div class="appointments-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-calendar-check me-2"></i>My Appointments</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage your scheduled appointments with instructors</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $upcomingAppointments->count() }}</h4>
                                <small class="opacity-75">Upcoming</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $pastAppointments->where('status', 'completed')->count() }}</h4>
                                <small class="opacity-75">Completed</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $pastAppointments->where('status', 'cancelled')->count() }}</h4>
                                <small class="opacity-75">Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="appointments-body">
            <div class="help-text">
                <div class="help-title">Your Appointments</div>
                <div class="help-content">
                    View and manage your scheduled appointments with instructors. You can join virtual meetings directly from here or cancel appointments if needed.
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <!-- Upcoming Appointments -->
                    <div class="section-card">
                        <div class="section-card-header success">
                            <i class="fas fa-clock"></i>
                            Upcoming Appointments
                        </div>
                        <div class="section-card-body">
                            @forelse($upcomingAppointments as $appointment)
                                <div class="appointment-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="appointment-title">{{ $appointment->schedule->title }}</div>
                                            <div class="appointment-meta">
                                                <i class="fas fa-user"></i>
                                                {{ $appointment->schedule->user->name }}
                                                @if($appointment->schedule->course)
                                                    <span class="text-muted">• {{ $appointment->schedule->course->title }}</span>
                                                @endif
                                            </div>
                                            <div class="appointment-meta">
                                                <i class="fas fa-calendar"></i>
                                                {{ $appointment->start_time->format('l, M j, Y') }} at {{ $appointment->start_time->format('g:i A') }}
                                                <span class="text-muted">({{ $appointment->duration_minutes ?? 30 }} min)</span>
                                            </div>
                                            @if($appointment->schedule->location)
                                                <div class="appointment-meta">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    {{ $appointment->schedule->location }}
                                                </div>
                                            @endif
                                            @if($appointment->student_notes)
                                                <div class="appointment-note">
                                                    <i class="fas fa-sticky-note me-1"></i> {{ $appointment->student_notes }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="appointment-actions">
                                            <span class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                                            <div class="d-flex gap-2">
                                                @if($appointment->meeting_url)
                                                    <a href="{{ $appointment->meeting_url }}" target="_blank" class="btn btn-success btn-sm">
                                                        <i class="fas fa-video"></i> Join
                                                    </a>
                                                @endif
                                                <form action="{{ route('lms.calendar.cancel-appointment', $appointment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>No upcoming appointments</p>
                                    <p class="text-muted mt-2">Book an appointment with your instructor to get started</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Past Appointments -->
                    <div class="section-card">
                        <div class="section-card-header secondary">
                            <i class="fas fa-history"></i>
                            Past Appointments
                        </div>
                        <div class="section-card-body">
                            @forelse($pastAppointments as $appointment)
                                <div class="past-appointment-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="past-appointment-title">{{ $appointment->schedule->title }}</span>
                                            <span class="past-appointment-instructor">with {{ $appointment->schedule->user->name }}</span>
                                            <div class="past-appointment-date">
                                                {{ $appointment->start_time->format('M j, Y \a\t g:i A') }}
                                            </div>
                                            @if($appointment->status === 'cancelled' && $appointment->cancellation_reason)
                                                <div class="cancellation-reason">
                                                    <i class="fas fa-info-circle me-1"></i>Reason: {{ $appointment->cancellation_reason }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state" style="padding: 24px;">
                                    <i class="fas fa-history" style="font-size: 32px;"></i>
                                    <p>No past appointments</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Book New Appointment -->
                    <div class="section-card">
                        <div class="section-card-header primary">
                            <i class="fas fa-plus-circle"></i>
                            Book an Appointment
                        </div>
                        <div class="section-card-body with-padding">
                            <div class="sidebar-info-card">
                                <p>Browse available office hours and tutoring sessions to book an appointment with your instructors.</p>
                                <a href="{{ route('lms.courses.index') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Browse Courses
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <i class="fas fa-link"></i>
                            Quick Links
                        </div>
                        <div class="section-card-body">
                            <a href="{{ route('lms.calendar.index') }}" class="appointment-item d-flex align-items-center text-decoration-none" style="padding: 12px 20px;">
                                <i class="fas fa-calendar-alt me-3 text-primary"></i>
                                <span class="text-dark">Back to Calendar</span>
                            </a>
                            <a href="{{ route('lms.courses.index') }}" class="appointment-item d-flex align-items-center text-decoration-none" style="padding: 12px 20px;">
                                <i class="fas fa-book me-3 text-primary"></i>
                                <span class="text-dark">My Courses</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

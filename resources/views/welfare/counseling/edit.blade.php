@extends('layouts.master')

@section('title')
    Counseling Session #{{ $session->id }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Counseling Container */
        .counseling-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .counseling-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .counseling-body {
            padding: 24px;
        }

        /* Info Badge on Colored Background */
        .info-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 3px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .info-badge strong {
            color: white;
            display: block;
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .info-badge span {
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .required-field::after {
            content: " *";
            color: red;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        /* Nav Tabs */
        .nav-tabs .nav-link {
            border-radius: 3px 3px 0 0 !important;
        }

        .nav-tabs .nav-link.active {
            color: #6366f1;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
@endsection

@section('content')
    @php
        $statusColors = [
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'no_show' => 'danger',
        ];
        $statusColor = $statusColors[$session->status] ?? 'secondary';
        $confColors = [2 => 'info', 3 => 'warning', 4 => 'danger'];
        $confLabels = [2 => 'Restricted', 3 => 'Confidential', 4 => 'Highly Confidential'];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.counseling.index') }}">Back</a>
        @endslot
        @slot('title')
            Session #{{ $session->id }}
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i>
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i>
                    <strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i>
                    <strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i>
                <strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <!-- Header -->
    <div class="counseling-container mb-3">
        <div class="counseling-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">{{ $session->student->full_name ?? 'Unknown Student' }}</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        Session #{{ $session->id }} •
                        {{ ucfirst($session->session_type) }} •
                        {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('d M Y \a\t H:i') : 'N/A' }}
                        @if ($session->duration_minutes)
                            ({{ $session->duration_minutes }} mins)
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="info-badge text-center">
                                <strong>Status</strong>
                                <span class="d-block">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-badge text-center">
                                <strong>Level {{ $session->confidentiality_level }}</strong>
                                <span class="d-block" style="font-size:14px;">{{ $confLabels[$session->confidentiality_level] ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                                type="button" role="tab" aria-controls="details" aria-selected="true">
                                Session Details
                            </button>
                        </li>
                        @if ($session->status === 'scheduled')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="complete-tab" data-bs-toggle="tab" data-bs-target="#complete"
                                    type="button" role="tab" aria-controls="complete" aria-selected="false">
                                    Complete Session
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="no-show-tab" data-bs-toggle="tab" data-bs-target="#no-show"
                                    type="button" role="tab" aria-controls="no-show" aria-selected="false">
                                    No Show
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cancel-tab" data-bs-toggle="tab" data-bs-target="#cancel"
                                    type="button" role="tab" aria-controls="cancel" aria-selected="false">
                                    Cancel Session
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="details" role="tabpanel"
                            aria-labelledby="details-tab">
                            <form action="{{ route('welfare.counseling.update', $session) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="student">Student</label>
                                            <input type="text" class="form-control form-control" id="student"
                                                value="{{ $session->student->full_name ?? '-' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="location">Location</label>
                                            <input type="text" name="location" class="form-control form-control"
                                                id="location" value="{{ old('location', $session->location) }}"
                                                placeholder="e.g., Counseling Room 1">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="session_type">Session
                                                Type</label>
                                            <select name="session_type" class="form-select form-select"
                                                id="session_type" required>
                                                <option value="individual"
                                                    {{ $session->session_type === 'individual' ? 'selected' : '' }}>
                                                    Individual
                                                </option>
                                                <option value="group"
                                                    {{ $session->session_type === 'group' ? 'selected' : '' }}>
                                                    Group</option>
                                                <option value="family"
                                                    {{ $session->session_type === 'family' ? 'selected' : '' }}>
                                                    Family</option>
                                                <option value="crisis"
                                                    {{ $session->session_type === 'crisis' ? 'selected' : '' }}>
                                                    Crisis</option>
                                                <option value="follow_up"
                                                    {{ $session->session_type === 'follow_up' ? 'selected' : '' }}>Follow
                                                    Up
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="session_date">Date &
                                                Time</label>
                                            <input type="text" name="session_date"
                                                class="form-control form-control flatpickr-datetime" id="session_date"
                                                value="{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('Y-m-d H:i') : '' }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="duration_minutes">Duration</label>
                                            <select name="duration_minutes" class="form-select form-select" id="duration_minutes">
                                                <option value="30" {{ $session->duration_minutes == 30 ? 'selected' : '' }}>30
                                                    minutes
                                                </option>
                                                <option value="45" {{ $session->duration_minutes == 45 ? 'selected' : '' }}>45
                                                    minutes
                                                </option>
                                                <option value="60" {{ $session->duration_minutes == 60 ? 'selected' : '' }}>60
                                                    minutes
                                                </option>
                                                <option value="90" {{ $session->duration_minutes == 90 ? 'selected' : '' }}>90
                                                    minutes
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="counsellor_id">Counsellor</label>
                                            <select name="counsellor_id" class="form-control form-control"
                                                id="counsellor-select" required>
                                                @foreach ($counsellors as $counsellor)
                                                    <option value="{{ $counsellor->id }}"
                                                        {{ $session->counsellor_id == $counsellor->id ? 'selected' : '' }}>
                                                        {{ $counsellor->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="welfare_case_id">Linked Welfare Case</label>
                                            <select name="welfare_case_id" class="form-control form-control"
                                                id="case-select">
                                                <option value="">Not linked to a case</option>
                                                @foreach ($cases as $case)
                                                    <option value="{{ $case->id }}"
                                                        {{ $session->welfare_case_id == $case->id ? 'selected' : '' }}>
                                                        {{ $case->case_number }} - {{ Str::limit($case->title, 30) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="purpose">Purpose / Agenda</label>
                                            <textarea name="purpose" class="form-control form-control" id="purpose" rows="3"
                                                placeholder="What will be discussed in this session?">{{ old('purpose', $session->purpose) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Confidentiality Level</label>
                                            <div class="d-flex gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="confidentiality_level" value="2" id="level-2"
                                                        {{ $session->confidentiality_level == 2 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="level-2">
                                                        <span class="badge bg-info">Level 2 - Restricted</span>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="confidentiality_level" value="3" id="level-3"
                                                        {{ $session->confidentiality_level == 3 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="level-3">
                                                        <span class="badge bg-warning">Level 3 - Confidential</span>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="confidentiality_level" value="4" id="level-4"
                                                        {{ $session->confidentiality_level == 4 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="level-4">
                                                        <span class="badge bg-danger">Level 4 - Highly Confidential</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($canViewNotes ?? false)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="presenting_issue">Presenting Issue</label>
                                                <textarea name="presenting_issue" class="form-control form-control" id="presenting_issue" rows="3"
                                                    placeholder="Describe the presenting issue...">{{ old('presenting_issue', $session->presenting_issue) }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($session->status !== 'completed')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="session_notes">Session Notes</label>
                                                    <textarea name="session_notes" class="form-control form-control" id="session_notes" rows="6"
                                                        placeholder="Record session notes here. These are confidential and only visible to authorized counselors.">{{ old('session_notes', $session->session_notes) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Session Notes</label>
                                                    <div class="border rounded p-3 bg-light">
                                                        {!! nl2br(e($session->session_notes ?? 'No notes recorded.')) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($session->recommendations)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Recommendations</label>
                                                    <div class="border rounded p-3 bg-light">
                                                        {!! nl2br(e($session->recommendations)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($session->follow_up_required)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Follow-up Required:</strong>
                                                    {{ $session->follow_up_notes ?? 'A follow-up session has been recommended.' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('welfare.counseling.index') }}"
                                                class="btn btn-secondary btn me-2">
                                                <i class="fas fa-arrow-back"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary btn">
                                                <i class="fas fa-save font-size-16 align-middle me-2"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if ($session->status === 'scheduled')
                            <div class="tab-pane fade" id="complete" role="tabpanel" aria-labelledby="complete-tab">
                                <p class="text-muted mb-3">Mark this session as completed after it has taken place.</p>
                                <form action="{{ route('welfare.counseling.complete', $session) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Session Summary</label>
                                        <textarea name="session_notes" class="form-control form-control" rows="3"
                                            placeholder="Brief summary of the session..."></textarea>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="follow_up_required"
                                            id="follow-up" value="1">
                                        <label class="form-check-label" for="follow-up">
                                            Follow-up session required
                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success btn">
                                            <i class="fas fa-check me-1"></i> Mark as Completed
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="no-show" role="tabpanel" aria-labelledby="no-show-tab">
                                <p class="text-muted mb-3">Mark if the student did not attend the scheduled session.</p>
                                <form action="{{ route('welfare.counseling.no-show', $session) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="no_show_notes" class="form-control form-control" rows="3"
                                            placeholder="Any relevant notes about the missed session..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning btn"
                                            onclick="return confirm('Mark this session as no-show?')">
                                            <i class="fas fa-user-times me-1"></i> Mark as No Show
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="cancel" role="tabpanel" aria-labelledby="cancel-tab">
                                <p class="text-muted mb-3">Cancel this session if it will no longer take place.</p>
                                <form action="{{ route('welfare.counseling.cancel', $session) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Cancellation Reason</label>
                                        <textarea name="cancellation_reason" class="form-control form-control" rows="3"
                                            placeholder="Reason for cancellation..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-secondary btn"
                                            onclick="return confirm('Are you sure you want to cancel this session?')">
                                            <i class="fas fa-x me-1"></i> Cancel Session
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($session->status !== 'scheduled')
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-4">
                        @if ($session->status === 'completed')
                            <i class="fas fa-check-circle text-success font-size-48"></i>
                            <h5 class="mt-3">Session Completed</h5>
                            <p class="text-muted mb-0">
                                This session was completed on
                                {{ $session->completed_at ? \Carbon\Carbon::parse($session->completed_at)->format('d M Y \a\t H:i') : $session->updated_at->format('d M Y \a\t H:i') }}
                            </p>
                        @elseif ($session->status === 'cancelled')
                            <i class="fas fa-times-circle text-secondary font-size-48"></i>
                            <h5 class="mt-3">Session Cancelled</h5>
                            <p class="text-muted mb-0">
                                This session was cancelled.
                                @if ($session->cancellation_reason)
                                    <br><strong>Reason:</strong> {{ $session->cancellation_reason }}
                                @endif
                            </p>
                        @elseif ($session->status === 'no_show')
                            <i class="fas fa-user-times text-danger font-size-48"></i>
                            <h5 class="mt-3">Student No Show</h5>
                            <p class="text-muted mb-0">
                                The student did not attend this scheduled session.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#case-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Case',
                shouldSort: true
            });

            new Choices('#counsellor-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Counsellor',
                shouldSort: true
            });

            flatpickr('.flatpickr-datetime', {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                time_24hr: true
            });
        });
    </script>
@endsection

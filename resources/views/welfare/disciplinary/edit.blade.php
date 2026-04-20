@extends('layouts.master')

@section('title')
    Disciplinary Record #{{ $record->id }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Disciplinary Header */
        .disciplinary-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .disciplinary-body {
            padding: 24px 28px;
            background: white;
            border-radius: 0 0 3px 3px;
        }

        .info-badge {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 3px;
            font-size: 13px;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-body {
            padding: 20px;
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

        /* Required Field */
        .required-field::after {
            content: " *";
            color: #dc2626;
        }

        /* Tabs */
        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            border-bottom-color: #93c5fd;
        }

        .nav-tabs-custom .nav-link.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            font-weight: 600;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    @php
        $severityColors = [
            'minor' => 'secondary',
            'moderate' => 'warning',
            'major' => 'danger',
            'severe' => 'dark',
            1 => 'secondary',
            2 => 'warning',
            3 => 'danger',
            4 => 'dark',
        ];
        $severity = $record->incidentType->severity ?? 'minor';
        $severityColor = $severityColors[$severity] ?? 'secondary';

        $statusColors = [
            'reported' => 'info',
            'investigating' => 'warning',
            'pending_action' => 'warning',
            'action_in_progress' => 'danger',
            'resolved' => 'success',
            'appealed' => 'primary',
        ];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.disciplinary.index') }}">Back</a>
        @endslot
        @slot('title')
            Record #{{ $record->id }}
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

    <!-- Header Card -->
    <div class="card mb-3">
        <div class="disciplinary-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1 text-white">Disciplinary Record #{{ $record->id }}</h4>
                    <p class="mb-0 opacity-75">{{ $record->incidentType->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-{{ $severityColor }}">{{ ucfirst($severity) }}</span>
                    <span
                        class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$record->status] ?? 'secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="disciplinary-body">
            <div class="d-flex flex-wrap gap-2">
                <div class="info-badge">
                    <i class="fas fa-user me-1"></i>
                    Student: {{ $record->student->full_name ?? 'Unknown Student' }}
                </div>
                <div class="info-badge">
                    <i class="fas fa-calendar me-1"></i>
                    Date: {{ $record->incident_date ? $record->incident_date->format('d M Y') : 'N/A' }}
                </div>
                @if ($record->location)
                    <div class="info-badge">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        Location: {{ $record->location }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                                type="button" role="tab" aria-controls="details" aria-selected="true">
                                Incident Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="apply-action-tab" data-bs-toggle="tab"
                                data-bs-target="#apply-action" type="button" role="tab" aria-controls="apply-action"
                                aria-selected="false">
                                Apply Disciplinary Action
                                @if ($record->action_id)
                                    <span class="badge bg-success ms-2">Applied</span>
                                @else
                                    <span class="badge bg-secondary ms-2">Pending</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="parent-notification-tab" data-bs-toggle="tab"
                                data-bs-target="#parent-notification" type="button" role="tab"
                                aria-controls="parent-notification" aria-selected="false">
                                Parent Notification
                                @if ($record->parent_notified)
                                    <span class="badge bg-success ms-2">Notified</span>
                                @else
                                    <span class="badge bg-danger ms-2">Not Notified</span>
                                @endif
                            </button>
                        </li>
                        @if ($record->status !== 'resolved')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="resolve-tab" data-bs-toggle="tab" data-bs-target="#resolve"
                                    type="button" role="tab" aria-controls="resolve" aria-selected="false">
                                    Resolve Incident
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="details" role="tabpanel"
                            aria-labelledby="details-tab">
                            <form action="{{ route('welfare.disciplinary.update', $record) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="student">Student</label>
                                            <input type="text" class="form-control form-control" id="student"
                                                value="{{ $record->student->full_name ?? '-' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="incident_date">Incident
                                                Date</label>
                                            <input type="text" name="incident_date"
                                                class="form-control form-control flatpickr-date" id="incident_date"
                                                value="{{ $record->incident_date ? $record->incident_date->format('Y-m-d') : '' }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label" for="incident_time">Time</label>
                                            <input type="text" name="incident_time"
                                                class="form-control form-control flatpickr-time" id="incident_time"
                                                value="{{ $record->incident_time ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="incident_type_id">Incident
                                                Type</label>
                                            <select name="incident_type_id" class="form-select form-select"
                                                id="incident_type_id" required>
                                                @foreach ($incidentTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $record->incident_type_id == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="location">Location</label>
                                            <input type="text" name="location" class="form-control form-control"
                                                id="location" value="{{ old('location', $record->location) }}"
                                                placeholder="e.g., Classroom 4B">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="description">Description</label>
                                            <textarea name="description" class="form-control form-control" id="description" rows="4" required>{{ old('description', $record->description) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="witnesses">Witnesses</label>
                                            <textarea name="witnesses" class="form-control form-control" id="witnesses" rows="2"
                                                placeholder="One per line">{{ old('witnesses', is_array($record->witnesses) ? implode("\n", $record->witnesses) : $record->witnesses) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="evidence">Evidence</label>
                                            <textarea name="evidence" class="form-control form-control" id="evidence" rows="2"
                                                placeholder="One per line">{{ old('evidence', is_array($record->evidence) ? implode("\n", $record->evidence) : $record->evidence) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                @if ($record->welfareCase)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-link me-2"></i>
                                                <strong>Linked Case:</strong>
                                                <a href="{{ route('welfare.cases.edit', $record->welfareCase) }}"
                                                    class="alert-link">
                                                    {{ $record->welfareCase->case_number }} -
                                                    {{ $record->welfareCase->title }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('welfare.disciplinary.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="fas fa-arrow-left me-1"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="apply-action" role="tabpanel" aria-labelledby="apply-action-tab">
                            @if (!$record->action_id)
                                <form action="{{ route('welfare.disciplinary.apply-action', $record) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Disciplinary Action</label>
                                        <select name="action_id" class="form-select form-select" required>
                                            <option value="">Select Action</option>
                                            @foreach ($actions as $action)
                                                <option value="{{ $action->id }}">
                                                    {{ $action->name }} (Level {{ $action->severity_level }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" name="action_start_date"
                                                    class="form-control form-control" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" name="action_end_date"
                                                    class="form-control form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="action_notes" class="form-control form-control" rows="2"
                                            placeholder="Additional notes about the action..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-check me-1"></i> Apply Action
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-2">{{ $record->action->name ?? 'N/A' }}</h6>
                                    <p class="text-muted mb-2">{{ $record->action->description ?? '' }}</p>
                                    @if ($record->action_start_date || $record->action_end_date)
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $record->action_start_date ? $record->action_start_date->format('d M Y') : '' }}
                                            @if ($record->action_end_date)
                                                - {{ $record->action_end_date->format('d M Y') }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="parent-notification" role="tabpanel"
                            aria-labelledby="parent-notification-tab">
                            @if (!$record->parent_notified)
                                <form action="{{ route('welfare.disciplinary.notify-parent', $record) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Notification Method</label>
                                        <select name="notification_method" class="form-select form-select">
                                            <option value="phone">Phone Call</option>
                                            <option value="email">Email</option>
                                            <option value="meeting">In-Person Meeting</option>
                                            <option value="letter">Letter</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="parent_notification_notes" class="form-control form-control" rows="3"
                                            placeholder="Notes about the communication with parent..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-1"></i> Mark Parent as Notified
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle text-success font-size-48"></i>
                                    <h6 class="mt-2">Parent/Guardian Notified</h6>
                                    <p class="text-muted mb-0">
                                        Notified on
                                        {{ $record->parent_notified_at ? $record->parent_notified_at->format('d M Y \a\t H:i') : 'Date not recorded' }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($record->status !== 'resolved')
                            <div class="tab-pane fade" id="resolve" role="tabpanel" aria-labelledby="resolve-tab">
                                <form action="{{ route('welfare.disciplinary.resolve', $record) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label required-field">Resolution Notes</label>
                                        <textarea name="resolution_notes" class="form-control form-control" rows="3"
                                            placeholder="Summary of how the incident was resolved..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Mark this incident as resolved?')">
                                            <i class="fas fa-check-circle me-1"></i> Mark as Resolved
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

    @if ($record->status === 'resolved')
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-check-circle text-success font-size-48"></i>
                        <h6 class="mt-2">Incident Resolved</h6>
                        <p class="text-muted mb-0">
                            Resolved by {{ $record->resolvedBy->full_name ?? 'Unknown' }}<br>
                            {{ $record->resolved_at ? $record->resolved_at->format('d M Y \a\t H:i') : '' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            flatpickr('.flatpickr-time', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true
            });
        });
    </script>
@endsection

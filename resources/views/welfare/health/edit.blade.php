@extends('layouts.master')

@section('title')
    Health Incident #{{ $incident->id }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Health Header */
        .health-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .health-body {
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
        $severity = $incident->incidentType->severity ?? null;
        $severityColors = [
            'minor' => 'success',
            'moderate' => 'warning',
            'serious' => 'danger',
            'emergency' => 'dark',
        ];
        $severityColor = $severityColors[$severity] ?? 'info';

        $statusColors = [
            'reported' => 'info',
            'treated' => 'warning',
            'monitoring' => 'primary',
            'resolved' => 'success',
        ];
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.health.index') }}">Back</a>
        @endslot
        @slot('title')
            Incident #{{ $incident->id }}
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
        <div class="health-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1 text-white">Health Incident #{{ $incident->id }}</h4>
                    <p class="mb-0 opacity-75">{{ $incident->incidentType->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    @if ($severity)
                        <span class="badge bg-{{ $severityColor }}">{{ ucfirst($severity) }}</span>
                    @endif
                    <span
                        class="badge bg-{{ $statusColors[$incident->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$incident->status] ?? 'secondary' }}">
                        {{ ucfirst($incident->status ?? 'Reported') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="health-body">
            <div class="d-flex flex-wrap gap-2">
                <div class="info-badge">
                    <i class="fas fa-user me-1"></i>
                    Student: {{ $incident->student->full_name ?? 'Unknown Student' }}
                </div>
                <div class="info-badge">
                    <i class="fas fa-calendar me-1"></i>
                    Date: {{ $incident->incident_date ? \Carbon\Carbon::parse($incident->incident_date)->format('d M Y') : 'N/A' }}
                    @if ($incident->incident_time)
                        at {{ $incident->incident_time }}
                    @endif
                </div>
                @if ($incident->location)
                    <div class="info-badge">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        Location: {{ $incident->location }}
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
                            <button class="nav-link" id="parent-notification-tab" data-bs-toggle="tab"
                                data-bs-target="#parent-notification" type="button" role="tab"
                                aria-controls="parent-notification" aria-selected="false">
                                Parent Notification
                                @if ($incident->parent_notified)
                                    <span class="badge bg-success ms-2">Done</span>
                                @else
                                    <span class="badge bg-warning ms-2">Pending</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resolution-tab" data-bs-toggle="tab" data-bs-target="#resolution"
                                type="button" role="tab" aria-controls="resolution" aria-selected="false">
                                Resolution
                                @if ($incident->status === 'resolved')
                                    <span class="badge bg-success ms-2">Resolved</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <form action="{{ route('welfare.health.update', $incident) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="student">Student</label>
                                            <input type="text" class="form-control form-control" id="student"
                                                value="{{ $incident->student->full_name ?? '-' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="incident_date">Incident
                                                Date</label>
                                            <input type="text" name="incident_date"
                                                class="form-control form-control flatpickr-date" id="incident_date"
                                                value="{{ $incident->incident_date ? \Carbon\Carbon::parse($incident->incident_date)->format('Y-m-d') : '' }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label" for="incident_time">Time</label>
                                            <input type="text" name="incident_time"
                                                class="form-control form-control flatpickr-time" id="incident_time"
                                                value="{{ $incident->incident_time ?? '' }}">
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
                                                        {{ $incident->incident_type_id == $type->id ? 'selected' : '' }}>
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
                                                id="location" value="{{ old('location', $incident->location) }}"
                                                placeholder="e.g., Playground, Classroom">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="description">Description</label>
                                            <textarea name="description" class="form-control form-control" id="description" rows="4" required>{{ old('description', $incident->description) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="symptoms">Symptoms</label>
                                            <textarea name="symptoms" class="form-control form-control" id="symptoms" rows="2"
                                                placeholder="Observable symptoms...">{{ old('symptoms', $incident->symptoms) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="treatment_given">Treatment Provided</label>
                                            <textarea name="treatment_given" class="form-control form-control" id="treatment_given"
                                                rows="3"
                                                placeholder="Describe the treatment or first aid provided...">{{ old('treatment_given', $incident->treatment_given) }}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="treated_by">Treated By</label>
                                            <select name="treated_by" class="form-control form-control"
                                                id="treated_by">
                                                <option value="">Select Staff Member</option>
                                                @foreach ($staff as $member)
                                                    <option value="{{ $member->id }}"
                                                        {{ $incident->treated_by == $member->id ? 'selected' : '' }}>
                                                        {{ $member->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Actions Taken</label>
                                            <div class="d-flex flex-wrap gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="sent_home"
                                                        value="1" id="sent-home"
                                                        {{ $incident->sent_home ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="sent-home">Student sent
                                                        home</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="called_ambulance" value="1" id="ambulance"
                                                        {{ $incident->called_ambulance ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="ambulance">Ambulance
                                                        called</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="follow_up_required" value="1" id="follow-up"
                                                        {{ $incident->follow_up_required ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="follow-up">Follow-up
                                                        required</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($incident->follow_up_required)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="follow_up_notes">Follow-up Notes</label>
                                                <textarea name="follow_up_notes" class="form-control form-control" id="follow_up_notes" rows="3"
                                                    placeholder="Record follow-up observations and actions...">{{ $incident->follow_up_notes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($incident->welfareCase)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-link me-2"></i>
                                                <strong>Linked Case:</strong>
                                                <a href="{{ route('welfare.cases.edit', $incident->welfareCase) }}"
                                                    class="alert-link">
                                                    {{ $incident->welfareCase->case_number }} -
                                                    {{ $incident->welfareCase->title }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('welfare.health.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="fas fa-arrow-left me-1"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="parent-notification" role="tabpanel"
                            aria-labelledby="parent-notification-tab">
                            @if (!$incident->parent_notified)
                                <form action="{{ route('welfare.health.notify-parent', $incident) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Notification Method</label>
                                        <select name="notification_method" class="form-select form-select">
                                            <option value="phone">Phone Call</option>
                                            <option value="sms">SMS</option>
                                            <option value="email">Email</option>
                                            <option value="in_person">In Person</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notification_notes" class="form-control form-control" rows="2"
                                            placeholder="Notes about the communication..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check"></i> Mark Parent as Notified
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle text-success font-size-48"></i>
                                    <h6 class="mt-2">Parent Notified</h6>
                                    <p class="text-muted mb-0">
                                        {{ $incident->parent_notified_at ? \Carbon\Carbon::parse($incident->parent_notified_at)->format('d M Y \a\t H:i') : '' }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="resolution" role="tabpanel" aria-labelledby="resolution-tab">
                            @if ($incident->status !== 'resolved')
                                <form action="{{ route('welfare.health.resolve', $incident) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Resolution Notes</label>
                                        <textarea name="resolution_notes" class="form-control form-control" rows="3"
                                            placeholder="Summary of the outcome..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check-circle"></i> Mark as Resolved
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle text-success font-size-48"></i>
                                    <h6 class="mt-2">Incident Resolved</h6>
                                    <p class="text-muted mb-0">
                                        {{ $incident->resolved_at ? \Carbon\Carbon::parse($incident->resolved_at)->format('d M Y \a\t H:i') : '' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#treated_by', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Staff Member',
                shouldSort: true
            });

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

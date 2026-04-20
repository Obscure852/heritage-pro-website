@extends('layouts.master')

@section('title')
    Log Health Incident
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Health Create Header */
        .health-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .health-body {
            padding: 24px 28px;
            background: white;
            border-radius: 0 0 3px 3px;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.health.index') }}">Back</a>
        @endslot
        @slot('title')
            Log Incident
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="health-header">
                    <h4 class="mb-1 text-white">Log Health Incident</h4>
                    <p class="mb-0 opacity-75">Record student health incidents, injuries, and illnesses</p>
                </div>
                <div class="health-body">
                    <form method="POST" action="{{ route('welfare.health.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="student_id">Student</label>
                                    <select name="student_id" class="form-control form-control" id="student-select"
                                        required>
                                        <option value="">Select Student</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}"
                                                {{ old('student_id', $selectedStudent?->id) == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->currentGrade->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="welfare_case_id">Link to Welfare Case</label>
                                    <select name="welfare_case_id" class="form-control form-control" id="case-select">
                                        <option value="">Not linked</option>
                                        @foreach ($cases ?? [] as $case)
                                            <option value="{{ $case->id }}"
                                                {{ old('welfare_case_id') == $case->id ? 'selected' : '' }}>
                                                {{ $case->case_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="incident_type_id">Incident Type</label>
                                    <select name="incident_type_id" class="form-select form-select" id="incident_type_id"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach ($incidentTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('incident_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="location">Location</label>
                                    <input type="text" name="location" class="form-control form-control"
                                        id="location" value="{{ old('location') }}"
                                        placeholder="e.g., Playground, Classroom 3A">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="incident_date">Date</label>
                                    <input type="text" name="incident_date"
                                        class="form-control form-control flatpickr-date" id="incident_date"
                                        value="{{ old('incident_date', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="incident_time">Time</label>
                                    <input type="text" name="incident_time"
                                        class="form-control form-control flatpickr-time" id="incident_time"
                                        value="{{ old('incident_time', now()->format('H:i')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="description">Description</label>
                                    <textarea name="description" class="form-control form-control" id="description" rows="4"
                                        placeholder="Describe the health incident, symptoms, and circumstances..." required>{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="treatment_given">Treatment / Action
                                        Taken</label>
                                    <textarea name="treatment_given" class="form-control form-control" id="treatment_given" rows="3"
                                        placeholder="What treatment or first aid was provided?">{{ old('treatment_given') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="treated_by">Treated By</label>
                                    <select name="treated_by" class="form-control form-control" id="treated-by-select">
                                        <option value="">Select Staff Member</option>
                                        @foreach ($staff as $member)
                                            <option value="{{ $member->id }}"
                                                {{ old('treated_by', auth()->id()) == $member->id ? 'selected' : '' }}>
                                                {{ $member->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required-field">Severity Level</label>
                                    <div class="d-flex gap-2 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="severity"
                                                value="minor" id="severity-minor"
                                                {{ old('severity', 'minor') === 'minor' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="severity-minor">
                                                <span class="badge bg-success">Minor</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="severity"
                                                value="moderate" id="severity-moderate"
                                                {{ old('severity') === 'moderate' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="severity-moderate">
                                                <span class="badge bg-warning">Moderate</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="severity"
                                                value="serious" id="severity-serious"
                                                {{ old('severity') === 'serious' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="severity-serious">
                                                <span class="badge bg-danger">Serious</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="severity"
                                                value="emergency" id="severity-emergency"
                                                {{ old('severity') === 'emergency' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="severity-emergency">
                                                <span class="badge bg-dark">Emergency</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Follow-up Actions</label>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="parent_notified"
                                                value="1" id="parent-notified"
                                                {{ old('parent_notified') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="parent-notified">Parent/Guardian
                                                notified</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sent_home"
                                                value="1" id="sent-home" {{ old('sent_home') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sent-home">Student sent home</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="called_ambulance"
                                                value="1" id="called-ambulance"
                                                {{ old('called_ambulance') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="called-ambulance">Ambulance
                                                called</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="follow_up_required"
                                                value="1" id="follow-up"
                                                {{ old('follow_up_required') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="follow-up">Follow-up required</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('welfare.health.index') }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Log Incident
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
            new Choices('#student-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Student',
                shouldSort: true
            });

            new Choices('#case-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Case',
                shouldSort: true
            });

            new Choices('#treated-by-select', {
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

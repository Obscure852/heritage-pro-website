@extends('layouts.master')

@section('title')
    Report Disciplinary Incident
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Disciplinary Create Header */
        .disciplinary-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .disciplinary-body {
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
            <a class="text-muted" href="{{ route('welfare.disciplinary.index') }}">Back</a>
        @endslot
        @slot('title')
            Report Incident
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
                <div class="disciplinary-header">
                    <h4 class="mb-1 text-white">Report Disciplinary Incident</h4>
                    <p class="mb-0 opacity-75">Document student behavior incidents and violations</p>
                </div>
                <div class="disciplinary-body">
                    <form method="POST" action="{{ route('welfare.disciplinary.store') }}">
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
                                                {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->currentGrade->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="welfare_case_id">Link to Case</label>
                                    <select name="welfare_case_id" class="form-control form-control" id="case-select">
                                        <option value="">Not linked to a case</option>
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
                                        <option value="">Select Incident Type</option>
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
                                        placeholder="e.g., Classroom 4B, Playground">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="incident_date">Incident Date</label>
                                    <input type="text" name="incident_date"
                                        class="form-control form-control flatpickr-date" id="incident_date"
                                        value="{{ old('incident_date') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="incident_time">Time</label>
                                    <input type="text" name="incident_time"
                                        class="form-control form-control flatpickr-time" id="incident_time"
                                        value="{{ old('incident_time') }}" placeholder="HH:MM">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="description">Description</label>
                                    <textarea name="description" class="form-control form-control" id="description" rows="4"
                                        placeholder="Describe the incident in detail..." required>{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="witnesses">Witnesses</label>
                                    <textarea name="witnesses" class="form-control form-control" id="witnesses" rows="2"
                                        placeholder="List any witnesses to the incident...">{{ old('witnesses') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="evidence">Evidence</label>
                                    <textarea name="evidence" class="form-control form-control" id="evidence" rows="2"
                                        placeholder="Any evidence collected...">{{ old('evidence') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('welfare.disciplinary.index') }}"
                                        class="btn btn-secondary me-2">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Submit Report
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

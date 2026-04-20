@extends('layouts.master')

@section('title')
    Schedule Counseling Session
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
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .counseling-body {
            padding: 24px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.counseling.index') }}">Back</a>
        @endslot
        @slot('title')
            Schedule Session
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

    <div class="counseling-container">
        <div class="counseling-header">
            <h3 style="margin:0;">Schedule Counseling Session</h3>
            <p style="margin:6px 0 0 0; opacity:.9;">Book a new counseling session with a student</p>
        </div>
        <div class="counseling-body">
            <form method="POST" action="{{ route('welfare.counseling.store') }}">
                @csrf

                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="counsellor_id">Counsellor</label>
                                    <select name="counsellor_id" class="form-control form-control" id="counsellor-select"
                                        required>
                                        <option value="">Select Counsellor</option>
                                        @foreach ($counsellors as $counsellor)
                                            <option value="{{ $counsellor->id }}"
                                                {{ old('counsellor_id', auth()->id()) == $counsellor->id ? 'selected' : '' }}>
                                                {{ $counsellor->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="welfare_case_id">Welfare Case (Optional)</label>
                                    <select name="welfare_case_id" class="form-control form-control" id="case-select">
                                        <option value="">Not linked to a case</option>
                                        @foreach ($cases as $case)
                                            <option value="{{ $case->id }}"
                                                {{ old('welfare_case_id') == $case->id ? 'selected' : '' }}>
                                                {{ $case->case_number }} - {{ Str::limit($case->title, 30) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="session_type">Session Type</label>
                                    <select name="session_type" class="form-select form-select" required>
                                        <option value="individual"
                                            {{ old('session_type') === 'individual' ? 'selected' : '' }}>Individual
                                        </option>
                                        <option value="group" {{ old('session_type') === 'group' ? 'selected' : '' }}>
                                            Group</option>
                                        <option value="family" {{ old('session_type') === 'family' ? 'selected' : '' }}>
                                            Family</option>
                                        <option value="crisis" {{ old('session_type') === 'crisis' ? 'selected' : '' }}>
                                            Crisis</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="session_date">Date & Time</label>
                                    <input type="text" name="session_date"
                                        class="form-control form-control flatpickr-datetime" id="session_date"
                                        value="{{ old('session_date') }}" placeholder="2025-11-28 12:45" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="duration">Duration (minutes)</label>
                                    <select name="duration" class="form-select form-select" id="duration">
                                        <option value="30" {{ old('duration') == 30 ? 'selected' : '' }}>30 minutes
                                        </option>
                                        <option value="45" {{ old('duration', 45) == 45 ? 'selected' : '' }}>45 minutes
                                        </option>
                                        <option value="60" {{ old('duration') == 60 ? 'selected' : '' }}>60 minutes
                                        </option>
                                        <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>90 minutes
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="location">Location</label>
                                    <input type="text" name="location" class="form-control form-control"
                                        id="location" value="{{ old('location') }}"
                                        placeholder="e.g., Counseling Office Room 2">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="purpose">Purpose / Agenda</label>
                                    <textarea name="purpose" class="form-control form-control" id="purpose" rows="3"
                                        placeholder="Describe the purpose of this session...">{{ old('purpose') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field">Confidentiality Level</label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="confidentiality_level"
                                                value="2" id="level-2"
                                                {{ old('confidentiality_level', 2) == 2 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level-2">
                                                <span class="badge bg-info">Level 2 - Restricted</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="confidentiality_level"
                                                value="3" id="level-3"
                                                {{ old('confidentiality_level') == 3 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level-3">
                                                <span class="badge bg-warning">Level 3 - Confidential</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="confidentiality_level"
                                                value="4" id="level-4"
                                                {{ old('confidentiality_level') == 4 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level-4">
                                                <span class="badge bg-danger">Level 4 - Highly Confidential</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('welfare.counseling.index') }}"
                                        class="btn btn-secondary me-2">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                </div>
            </form>
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
                minDate: 'today',
                time_24hr: true
            });
        });
    </script>
@endsection

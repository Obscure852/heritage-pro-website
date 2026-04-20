@extends('layouts.master')

@section('title')
    Report Safeguarding Concern
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Safeguarding Container */
        .safeguarding-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .safeguarding-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .safeguarding-body {
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
            <a href="{{ route('welfare.safeguarding.index') }}">Back</a>
        @endslot
        @slot('title')
            Report Concern
        @endslot
    @endcomponent

    <div class="alert alert-danger mb-3">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Important:</strong> If a child is in immediate danger, contact emergency services immediately. This form is
        for documenting concerns that require follow-up.
    </div>

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

    <div class="safeguarding-container">
        <div class="safeguarding-header">
            <h3 style="margin:0;">Report Safeguarding Concern</h3>
            <p style="margin:6px 0 0 0; opacity:.9;">Document child protection concerns confidentially</p>
        </div>
        <div class="safeguarding-body">
            <form method="POST" action="{{ route('welfare.safeguarding.store') }}">
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="lead_officer_id">Assign Lead Officer</label>
                                    <select name="lead_officer_id" class="form-control form-control" id="officer-select">
                                        <option value="">Select Lead Officer</option>
                                        @foreach ($officers as $officer)
                                            <option value="{{ $officer->id }}"
                                                {{ old('lead_officer_id') == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="category_id">Category</label>
                                    <select name="category_id" class="form-select form-select" id="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="source_of_concern">Source of
                                        Concern</label>
                                    <select name="source_of_concern" class="form-select form-select"
                                        id="source_of_concern" required>
                                        <option value="">Select Source</option>
                                        <option value="student_disclosure"
                                            {{ old('source_of_concern') === 'student_disclosure' ? 'selected' : '' }}>
                                            Student Disclosure</option>
                                        <option value="staff_observation"
                                            {{ old('source_of_concern') === 'staff_observation' ? 'selected' : '' }}>Staff
                                            Observation</option>
                                        <option value="parent_report"
                                            {{ old('source_of_concern') === 'parent_report' ? 'selected' : '' }}>Parent
                                            Report</option>
                                        <option value="peer_report"
                                            {{ old('source_of_concern') === 'peer_report' ? 'selected' : '' }}>Peer Report
                                        </option>
                                        <option value="external_referral"
                                            {{ old('source_of_concern') === 'external_referral' ? 'selected' : '' }}>
                                            External Referral</option>
                                        <option value="anonymous"
                                            {{ old('source_of_concern') === 'anonymous' ? 'selected' : '' }}>Anonymous
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="date_identified">Date of Concern</label>
                                    <input type="text" name="date_identified"
                                        class="form-control form-control flatpickr-date" id="date_identified"
                                        value="{{ old('date_identified', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="concern_details">Description of
                                        Concern</label>
                                    <textarea name="concern_details" class="form-control form-control" id="concern_details" rows="5"
                                        placeholder="Describe the concern in detail. Include what was said/observed, when, where, and who was present..."
                                        required>{{ old('concern_details') }}</textarea>
                                    <small class="text-muted">Use the child's own words where possible. Be factual and
                                        objective.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="immediate_actions">Immediate Actions Taken</label>
                                    <textarea name="immediate_actions" class="form-control form-control" id="immediate_actions" rows="3"
                                        placeholder="What immediate actions have been taken to ensure the child's safety?">{{ old('immediate_actions') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field">Risk Assessment</label>
                                    <div class="d-flex gap-2 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="risk_level"
                                                value="low" id="risk-low"
                                                {{ old('risk_level') === 'low' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="risk-low">
                                                <span class="badge bg-success">Low</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="risk_level"
                                                value="medium" id="risk-medium"
                                                {{ old('risk_level', 'medium') === 'medium' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="risk-medium">
                                                <span class="badge bg-warning">Medium</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="risk_level"
                                                value="high" id="risk-high"
                                                {{ old('risk_level') === 'high' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="risk-high">
                                                <span class="badge bg-danger">High</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="risk_level"
                                                value="critical" id="risk-critical"
                                                {{ old('risk_level') === 'critical' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="risk-critical">
                                                <span class="badge bg-dark">Critical</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" name="confidentiality_level" value="4">
                                    <small class="text-muted"><i class="fas fa-lock me-1"></i>Safeguarding records are
                                        automatically set to <span class="badge bg-danger">Level 4 - Highly
                                            Confidential</span></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('welfare.safeguarding.index') }}"
                                        class="btn btn-secondary btn-sm me-2">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-shield-alt font-size-16 align-middle me-2"></i> Submit Concern
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

            new Choices('#officer-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Lead Officer',
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
        });
    </script>
@endsection

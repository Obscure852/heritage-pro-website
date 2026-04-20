@extends('layouts.master')

@section('title')
    Create Welfare Case
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        /* Page Container */
        .welfare-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .welfare-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .welfare-body {
            padding: 24px;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .required-field::after {
            content: " *";
            color: #dc2626;
            margin-left: 4px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }

        /* Checkboxes */
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.cases.index') }}">Back</a>
        @endslot
        @slot('title')
            New Case
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

    <div class="welfare-container">
        <div class="welfare-header">
            <h4 class="mb-1 text-white"><i class="fas fa-folder-plus me-2"></i>Create Welfare Case</h4>
            <p class="mb-0 opacity-75">Record a new welfare case for student support and monitoring</p>
        </div>
        <div class="welfare-body">
            <form method="POST" action="{{ route('welfare.cases.store') }}">
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
                                                {{ $selectedStudent?->id == $student->id || old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->currentGrade->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="welfare_type_id">Welfare Type</label>
                                    <select name="welfare_type_id" class="form-select form-select"
                                        id="welfare-type-select" required>
                                        <option value="">Select Type</option>
                                        @foreach ($welfareTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('welfare_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field" for="title">Case Title</label>
                                    <input type="text" name="title"
                                        class="form-control form-control @error('title') is-invalid @enderror"
                                        id="title" value="{{ old('title') }}"
                                        placeholder="Brief description of the case" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="summary">Summary / Notes</label>
                                    <textarea name="summary" class="form-control form-control" id="summary" rows="4"
                                        placeholder="Provide detailed information about the case, including background, current situation, and any relevant notes...">{{ old('summary') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label required-field">Priority</label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority" value="low"
                                                id="priority-low" {{ old('priority') === 'low' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-low">
                                                <span class="badge bg-secondary">Low</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority" value="medium"
                                                id="priority-medium"
                                                {{ old('priority', 'medium') === 'medium' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-medium">
                                                <span class="badge bg-primary">Medium</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority"
                                                value="high" id="priority-high"
                                                {{ old('priority') === 'high' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-high">
                                                <span class="badge bg-warning">High</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="priority"
                                                value="critical" id="priority-critical"
                                                {{ old('priority') === 'critical' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority-critical">
                                                <span class="badge bg-danger">Critical</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="incident_date">Incident Date</label>
                                    <input type="text" name="incident_date"
                                        class="form-control form-control flatpickr-date" id="incident_date"
                                        value="{{ old('incident_date') }}" placeholder="Select date">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="assigned_to">Assigned To</label>
                                    <select name="assigned_to" class="form-control form-control" id="staff-select">
                                        <option value="">Leave Unassigned</option>
                                        @foreach ($staff as $member)
                                            <option value="{{ $member->id }}"
                                                {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                                                {{ $member->full_name }} @if ($member->position)
                                                    - {{ $member->position }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('welfare.cases.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>Create Case
                    </button>
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
            // Initialize Choices.js for student select
            new Choices('#student-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Student',
                shouldSort: true
            });

            new Choices('#welfare-type-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Welfare Type',
                shouldSort: true
            });

            new Choices('#staff-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Staff',
                shouldSort: true
            });

            // Initialize Flatpickr
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            // Handle priority checkboxes - ensure only one can be selected at a time
            document.querySelectorAll('input[name="priority"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        // Uncheck all other priority checkboxes
                        document.querySelectorAll('input[name="priority"]').forEach(cb => {
                            if (cb !== this) {
                                cb.checked = false;
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

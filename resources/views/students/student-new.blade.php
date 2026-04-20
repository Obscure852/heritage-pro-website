@extends('layouts.master')
@section('title')
    New Student
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .form-section {
            margin-bottom: 28px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        .form-control,
        .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: #212529;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            height: 28px;
            line-height: 28px;
        }

        .text-danger {
            font-size: 12px;
            margin-top: 4px;
        }

        .input-icon-group {
            position: relative;
        }

        .input-icon-group .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 13px;
            pointer-events: none;
            z-index: 2;
        }

        .input-icon-group .form-control {
            padding-left: 40px;
        }

        .input-icon-group.flatpickr-wrapper {
            display: block !important;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
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
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
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

        /* Loading Button Styles */
        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 16px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            New Student
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    @if (str_contains(session('error'), 'Student(s) with the same name already exists'))
                        <form method="POST" action="{{ route('students.store') }}" class="position-absolute"
                            style="right: 40px; top: 50%; transform: translateY(-50%);">
                            @csrf
                            @foreach (old() as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $arrayKey => $arrayValue)
                                        <input type="hidden" name="{{ $key }}[{{ $arrayKey }}]"
                                            value="{{ $arrayValue }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input type="hidden" name="bypass_duplicate_check" value="1">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-save me-1"></i> Save Anyway
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle label-icon"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <div class="page-header">
                    <h4 class="page-title text-muted">Create Student</h4>
                </div>

                <form class="needs-validation" method="POST" action="{{ route('students.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="last_updated_by" value="{{ auth()->user()->fullName }}">

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Personal Information</div>
                            <div class="help-content">Basic details about the student including names and identification.
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label required">First Name</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="first_name" class="form-control"
                                        value="{{ old('first_name') }}" placeholder="First name">
                                </div>
                                @error('first_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Middle Name</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="middle_name" class="form-control"
                                        value="{{ old('middle_name') }}" placeholder="Middle name (optional)">
                                </div>
                                @error('middle_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Last Name</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}"
                                        placeholder="Last name">
                                </div>
                                @error('last_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Candidate Number</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-id-badge input-icon"></i>
                                    <input type="text" name="exam_number" class="form-control"
                                        value="{{ old('exam_number') }}" placeholder="e.g., H34872557">
                                </div>
                                @error('exam_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">ID/Passport Number</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-id-badge input-icon"></i>
                                    <input type="text" name="id_number" class="form-control" id="id_number"
                                        value="{{ old('id_number') }}" placeholder="e.g., 765 218 812">
                                </div>
                                @error('id_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Date of Birth</label>
                                <div class="input-icon-group flatpickr-wrapper" data-toggle="student-dob-picker">
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="text" name="date_of_birth" class="form-control" id="date_of_birth"
                                        data-input value="{{ old('date_of_birth') }}" placeholder="dd/mm/yyyy" maxlength="10">
                                </div>
                                @error('date_of_birth')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select gender</option>
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Nationality</label>
                                <select name="nationality" id="nationality" class="form-select">
                                    <option value="">Select nationality</option>
                                    @foreach ($nationalities as $nationality)
                                        <option value="{{ $nationality->name }}"
                                            {{ old('nationality') == $nationality->name ? 'selected' : '' }}>
                                            {{ $nationality->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nationality')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Academic Information</div>
                            <div class="help-content">Select the grade, class, and other academic details for the student.
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label required">Grade</label>
                                <select name="grade_id" id="grade" class="form-select">
                                    <option value="">Select grade</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grade_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Class</label>
                                <select name="klass_id" id="klass" class="form-select">
                                    <option value="">Select class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            data-grade="{{ App\Models\Grade::find($class->grade_id)->name }}"
                                            {{ old('klass_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('klass_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Sponsor</label>
                                <select name="sponsor_id" id="sponsor_id" class="form-select">
                                    <option value="">Select sponsor</option>
                                    @foreach ($sponsors as $sponsor)
                                        <option value="{{ $sponsor->id }}"
                                            {{ old('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                                            {{ $sponsor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sponsor_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">Select type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->type }}"
                                            {{ old('type') == $type->type ? 'selected' : '' }}>
                                            {{ $type->type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">House</label>
                                <select name="house" class="form-select">
                                    <option value="">Select house (optional)</option>
                                    @foreach ($houses as $house)
                                        <option value="{{ $house->id }}"
                                            {{ old('house') == $house->id ? 'selected' : '' }}>
                                            {{ $house->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('house')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($school_data->boarding)
                            <div class="col-md-3">
                                <label class="form-label">Boarding Status</label>
                                <select name="is_boarding" class="form-select">
                                    <option value="0" {{ old('is_boarding') == '0' || old('is_boarding') === null ? 'selected' : '' }}>Day</option>
                                    <option value="1" {{ old('is_boarding') == '1' ? 'selected' : '' }}>Boarding</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Students
                        </a>
                        @can('manage-students')
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Create Student</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        Creating...
                                    </span>
                                </button>
                            @endif
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function formatDateInput(input) {
                input.addEventListener('input', function(e) {
                    const digits = e.target.value.replace(/\D/g, '').slice(0, 8);
                    let formatted = digits;

                    if (digits.length > 2) {
                        formatted = digits.slice(0, 2) + '/' + digits.slice(2);
                    }

                    if (digits.length > 4) {
                        formatted = formatted.slice(0, 5) + '/' + digits.slice(4);
                    }

                    e.target.value = formatted;
                });
            }

            const dateOfBirthInput = document.getElementById('date_of_birth');
            if (dateOfBirthInput) {
                formatDateInput(dateOfBirthInput);

                if (typeof flatpickr === 'function') {
                    flatpickr('[data-toggle="student-dob-picker"]', {
                        wrap: true,
                        allowInput: true,
                        dateFormat: 'd/m/Y',
                        maxDate: 'today',
                        disableMobile: true,
                    });
                }
            }

            // Initialize Select2 with search on sponsor dropdown
            $('#sponsor_id').select2({
                placeholder: 'Search sponsor...',
                allowClear: true,
                width: '100%',
            });

            // Initialize Select2 with search on nationality dropdown
            $('#nationality').select2({
                placeholder: 'Search nationality...',
                allowClear: true,
                width: '100%',
            });

            const gradeSelect = document.getElementById('grade');
            const classSelect = document.getElementById('klass');

            if (gradeSelect && classSelect) {
                gradeSelect.addEventListener('change', function() {
                    const selectedGrade = this.options[this.selectedIndex].text;
                    const options = classSelect.querySelectorAll('option');

                    options.forEach(option => {
                        if (option.getAttribute('data-grade') === selectedGrade || !option
                            .getAttribute('data-grade')) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    classSelect.value = '';
                });
            }

            // ID number formatting
            const idNumberInput = document.getElementById('id_number');
            if (idNumberInput) {
                idNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 15) value = value.substring(0, 15);

                    if (value.length >= 3) {
                        value = value.replace(/(\d{3})(\d{3})?(\d{3})?/, (match, p1, p2, p3) => {
                            let formatted = p1;
                            if (p2) formatted += ' ' + p2;
                            if (p3) formatted += ' ' + p3;
                            return formatted;
                        });
                    }

                    e.target.value = value;
                });
            }

            // Loading button handler
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn && form.checkValidity()) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Auto dismiss alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection

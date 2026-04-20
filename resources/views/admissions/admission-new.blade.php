@extends('layouts.master')
@section('title')
    New Admission
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

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
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
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

        .input-icon-group .form-control,
        .input-icon-group .form-select {
            padding-left: 40px;
        }

        .input-icon-group.flatpickr-wrapper {
            display: block !important;
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
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
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

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
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('admissions.index') }}">Admissions</a>
        @endslot
        @slot('title')
            New Admission
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">New Admission</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Create New Admission</div>
            <div class="help-content">
                Fill in the required information below to create a new student admission. Fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('admissions.admissions-create') }}" novalidate>
            @csrf
            <input type="hidden" value="{{ auth()->user()->id ?? 1 }}" name="last_updated_by" required>

            <h3 class="section-title">Personal Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text"
                            class="form-control @error('first_name') is-invalid @enderror"
                            name="first_name" id="first_name" placeholder="Andrew"
                            value="{{ old('first_name') }}" required>
                    </div>
                    @error('first_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="middle_name">Middle Name</label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text"
                            class="form-control @error('middle_name') is-invalid @enderror"
                            name="middle_name" id="middle_name" placeholder="James"
                            value="{{ old('middle_name') }}">
                    </div>
                    @error('middle_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text"
                            class="form-control @error('last_name') is-invalid @enderror"
                            name="last_name" id="last_name" value="{{ old('last_name') }}"
                            placeholder="Breitbart" required>
                    </div>
                    @error('last_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="id_number">ID/Passport Number <span class="text-danger">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-id-badge input-icon"></i>
                        <input type="text"
                            class="form-control @error('id_number') is-invalid @enderror"
                            name="id_number" id="id_number" value="{{ old('id_number') }}"
                            placeholder="765 2188 12" required>
                    </div>
                    @error('id_number')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                    <div class="input-icon-group flatpickr-wrapper" data-toggle="flatpickr-dob">
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="text" data-input
                            class="form-control @error('date_of_birth') is-invalid @enderror"
                            name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                            placeholder="dd/mm/yyyy" required>
                    </div>
                    @error('date_of_birth')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                    <select class="form-select @error('gender') is-invalid @enderror"
                        name="gender" id="gender" data-trigger required>
                        <option value="">Select Gender</option>
                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="nationality">Nationality <span class="text-danger">*</span></label>
                    <select class="form-select @error('nationality') is-invalid @enderror"
                        name="nationality" id="nationality" data-trigger required>
                        <option value="">Select Nationality</option>
                        @foreach ($nationalities as $nationality)
                            <option value="{{ $nationality->name }}"
                                {{ old('nationality') == $nationality->name ? 'selected' : '' }}>
                                {{ $nationality->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="sponsor_id">Parent/Sponsor <span class="text-danger">*</span></label>
                    <select class="form-select @error('sponsor_id') is-invalid @enderror"
                        name="sponsor_id" id="sponsor_id" data-trigger required>
                        <option value="">Select Sponsor</option>
                        @foreach ($sponsors as $sponsor)
                            <option value="{{ $sponsor->id }}"
                                {{ old('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                                {{ $sponsor->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sponsor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text"
                            class="form-control @error('phone') is-invalid @enderror"
                            name="phone" id="phone" value="{{ old('phone') }}"
                            placeholder="76 543 213" required>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Application Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror"
                        name="status" id="status" data-trigger required>
                        <option value="">Select Status</option>
                        <option value="Current" {{ old('status') == 'Current' ? 'selected' : '' }}>Current</option>
                        <option value="Left" {{ old('status') == 'Left' ? 'selected' : '' }}>Left</option>
                        <option value="To Join" {{ old('status') == 'To Join' ? 'selected' : '' }}>To Join</option>
                        <option value="Deleted" {{ old('status') == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="grade_applying_for">Grade Applying For <span class="text-danger">*</span></label>
                    <select class="form-select @error('grade_applying_for') is-invalid @enderror"
                        name="grade_applying_for" id="grade_applying_for" data-trigger required>
                        <option value="">Select Grade</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->name }}"
                                {{ old('grade_applying_for') == $grade->name ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_applying_for')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="year">Year <span class="text-danger">*</span></label>
                    <select class="form-select @error('year') is-invalid @enderror"
                        name="year" id="year" data-trigger required>
                        <option value="">Select Year</option>
                        @php
                            $currentYear = date('Y');
                            $endYear = $currentYear + 3;
                        @endphp
                        @for ($year = $currentYear; $year <= $endYear; $year++)
                            <option value="{{ $year }}"
                                {{ old('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="application_date">Application Date <span class="text-danger">*</span></label>
                    <div class="input-icon-group flatpickr-wrapper" data-toggle="flatpickr-app">
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="text" data-input
                            class="form-control @error('application_date') is-invalid @enderror"
                            name="application_date" id="application_date"
                            value="{{ old('application_date', date('d/m/Y')) }}"
                            placeholder="dd/mm/yyyy" required>
                    </div>
                    @error('application_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="term_id">Term <span class="text-danger">*</span></label>
                    <select class="form-select @error('term_id') is-invalid @enderror"
                        name="term_id" id="term_id" data-trigger required>
                        <option value="">Select Term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}"
                                {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                Term {{ $term->term }}, {{ $term->year }}
                            </option>
                        @endforeach
                    </select>
                    @error('term_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('admissions.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                @if (!session('is_past_term'))
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Admission</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
                        </span>
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeChoices();
            initializeDatepickers();
            initializeFormValidation();
            initializeAlertDismissal();
        });

        function initializeDatepickers() {
            flatpickr('[data-toggle="flatpickr-dob"]', {
                wrap: true,
                dateFormat: 'd/m/Y',
                maxDate: 'today',
                disableMobile: true
            });

            flatpickr('[data-toggle="flatpickr-app"]', {
                wrap: true,
                dateFormat: 'd/m/Y',
                maxDate: 'today',
                disableMobile: true
            });
        }

        function initializeChoices() {
            const selectElements = document.querySelectorAll('select.form-select');
            selectElements.forEach(function(element) {
                new Choices(element, {
                    searchEnabled: false,
                    removeItemButton: false,
                    shouldSort: false,
                    itemSelectText: '',
                    classNames: {
                        containerOuter: 'choices'
                    }
                });
            });

            const searchableSelects = document.querySelectorAll('select[data-trigger]');
            searchableSelects.forEach(function(element) {
                new Choices(element, {
                    searchEnabled: true,
                    removeItemButton: false,
                    shouldSort: false,
                    itemSelectText: '',
                    classNames: {
                        containerOuter: 'choices'
                    },
                    searchFields: ['label', 'value'],
                    searchPlaceholderValue: 'Type to search...',
                    searchResultLimit: 10
                });
            });
        }

        function initializeFormValidation() {
            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();

                        const firstInvalidElement = form.querySelector(':invalid');
                        if (firstInvalidElement) {
                            firstInvalidElement.focus();
                            firstInvalidElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    } else {
                        // Show loading state on submit button
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    }

                    form.classList.add('was-validated');
                }, false);

                setupCustomValidation(form);
            });
        }

        function setupCustomValidation(form) {
            const phoneInput = form.querySelector('#phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 8) value = value.substr(0, 8);
                    if (value.length >= 2) {
                        value = value.replace(/(\d{2})(\d{3})?(\d{3})?/, function(match, p1, p2, p3) {
                            let parts = [p1];
                            if (p2) parts.push(p2);
                            if (p3) parts.push(p3);
                            return parts.join(' ');
                        });
                    }
                    e.target.value = value;

                    const cleanNumber = value.replace(/\s/g, '');
                    if (cleanNumber.length === 8 && /^7[1-9]\d{6}$/.test(cleanNumber)) {
                        phoneInput.setCustomValidity('');
                    } else {
                        phoneInput.setCustomValidity('Please enter a valid 8-digit phone number starting with 7');
                    }
                });
            }

            const yearSelect = form.querySelector('#year');
            if (yearSelect) {
                yearSelect.addEventListener('change', function(e) {
                    const selectedYear = parseInt(e.target.value);
                    const currentYear = new Date().getFullYear();

                    if (selectedYear < currentYear) {
                        yearSelect.setCustomValidity('Year cannot be in the past');
                    } else if (selectedYear > currentYear + 5) {
                        yearSelect.setCustomValidity('Year cannot be more than 5 years in the future');
                    } else {
                        yearSelect.setCustomValidity('');
                    }
                });
            }
        }

        const idInput = document.querySelector('#id_number');
        if (idInput) {
            idInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value.length > 15) value = value.substr(0, 15);
                if (value.length >= 3) {
                    value = value.replace(/(\d{3})(\d{3})?(\d{3})?/, function(match, p1, p2, p3) {
                        let parts = [p1];
                        if (p2) parts.push(p2);
                        if (p3) parts.push(p3);
                        return parts.join(' ');
                    });
                }
                e.target.value = value;
            });
        }

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    } else {
                        alert.classList.remove('show');
                        alert.classList.add('fade');
                    }
                }, 5000);
            });
        }

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                const forms = document.querySelectorAll('.needs-validation');
                forms.forEach(form => form.classList.remove('was-validated'));
            }
        });
    </script>
@endsection

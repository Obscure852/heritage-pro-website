@extends('layouts.master')
@section('title')
    Human Resources
@endsection
<?php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); ?>
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

        .input-icon-group .form-control {
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
            <a class="text-muted font-size-14" href="{{ route('staff.index') }}">Staff</a>
        @endslot
        @slot('title')
            New Staff
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
                    <i class="mdi mdi-block-helper label-icon"></i>
                    <strong>{{ session('error') }}</strong>

                    @if (str_contains(session('error'), 'Staff member(s) with the same name already exists'))
                        <form method="POST" action="{{ route('staff.staff-create') }}" class="position-absolute"
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
                            <button type="submit" class="btn btn-warning btn-sm btn-loading">
                                <span class="btn-text">
                                    <i class="fas fa-save me-1"></i> Save Anyway
                                </span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </form>
                    @endif

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
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
            <h1 class="page-title">New Staff Member</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Create New Staff Member</div>
            <div class="help-content">
                Fill in the required information below to create a new staff member. Fields marked with <span
                    class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('staff.staff-create') }}" novalidate>
            @csrf
            <input type="hidden" value="{{ auth()->user()->id }}" name="last_updated_by" required>

            <div class="section-title">Personal Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="validationCustom01">First Name <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname"
                            id="validationCustom01" placeholder="Ray" value="{{ old('firstname') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="middlename">Middle Name</label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control @error('middlename') is-invalid @enderror" name="middlename"
                            id="middlename" placeholder="Jnr" value="{{ old('middlename') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastname">Last Name <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname"
                            id="lastname" placeholder="Lane" value="{{ old('lastname') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            id="email" placeholder="raylane@heritagepro.co" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Date of Birth <span class="required">*</span></label>
                    <div class="input-icon-group flatpickr-wrapper" data-toggle="staff-dob-picker">
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="text" class="form-control @error('date_of_birth') is-invalid @enderror"
                            id="date_of_birth" name="date_of_birth" data-input value="{{ old('date_of_birth') }}"
                            placeholder="dd/mm/yyyy" maxlength="10">
                    </div>
                </div>
                <div class="form-group">
                    <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                    <select class="form-select @error('gender') is-invalid @enderror" name="gender" id="gender">
                        <option value="">Select Gender ...</option>
                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="id_number">ID/Passport Number <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-id-badge input-icon"></i>
                        <input type="text" name="id_number" class="form-control @error('id_number') is-invalid @enderror"
                            id="id_number" value="{{ old('id_number') }}" placeholder="762 188 124">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            id="phone" value="{{ old('phone') }}" placeholder="78 876 123">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nationality">Nationality <span class="required">*</span></label>
                    <select name="nationality" id="nationality"
                        class="form-select @error('nationality') is-invalid @enderror">
                        <option value="">Select Nationality ...</option>
                        @foreach ($nationalities ?? [] as $nationality)
                            <option value="{{ $nationality->name }}"
                                {{ old('nationality') == $nationality->name ? 'selected' : '' }}>
                                {{ $nationality->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Employment Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="position">Position <span class="required">*</span></label>
                    <select name="position" id="position" class="form-select @error('position') is-invalid @enderror">
                        <option value="">Select position ...</option>
                        @foreach ($positions ?? [] as $position)
                            <option value="{{ $position->name }}"
                                {{ old('position') == $position->name ? 'selected' : '' }}>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Department <span class="required">*</span></label>
                    <select name="department" id="department"
                        class="form-select @error('department') is-invalid @enderror">
                        <option value="">Select department ...</option>
                        @foreach ($departments ?? [] as $department)
                            <option value="{{ $department->name }}"
                                {{ old('department') == $department->name ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="area_of_work">Area of Work <span class="required">*</span></label>
                    <select name="area_of_work" id="area_of_work"
                        class="form-select @error('area_of_work') is-invalid @enderror">
                        <option value="">Select Area of Work ...</option>
                        @foreach ($area_of_work ?? [] as $area)
                            <option value="{{ $area->name }}"
                                {{ old('area_of_work') == $area->name ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid mt-3">
                <div class="form-group">
                    <label class="form-label" for="reporting_to">Reporting To</label>
                    <select name="reporting_to" id="reporting_to"
                        class="form-select @error('reporting_to') is-invalid @enderror">
                        <option value="">Select staff member ...</option>
                        @foreach ($users ?? [] as $user)
                            <option value="{{ $user->id }}"
                                {{ (string) old('reporting_to') === (string) $user->id ? 'selected' : '' }}>
                                {{ trim($user->firstname . ' ' . $user->lastname) }}
                                @if (!empty($user->position))
                                    - {{ $user->position }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="personal_payroll_number">Personal Payroll Number</label>
                    <div class="input-icon-group">
                        <i class="fas fa-id-badge input-icon"></i>
                        <input type="text" name="personal_payroll_number"
                            class="form-control @error('personal_payroll_number') is-invalid @enderror"
                            id="personal_payroll_number" value="{{ old('personal_payroll_number') }}"
                            placeholder="PPN-1001">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_of_appointment">Date of Appointment</label>
                    <div class="input-icon-group flatpickr-wrapper" data-toggle="staff-appointment-picker">
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="text" name="date_of_appointment"
                            class="form-control @error('date_of_appointment') is-invalid @enderror"
                            id="date_of_appointment" data-input value="{{ old('date_of_appointment') }}"
                            placeholder="dd/mm/yyyy" maxlength="10">
                    </div>
                </div>
            </div>

            <div class="form-grid mt-3">
                <div class="form-group">
                    <label class="form-label" for="earning_band">Grade (Earning Band)</label>
                    <select name="earning_band" id="earning_band"
                        class="form-select @error('earning_band') is-invalid @enderror">
                        <option value="">Select earning band ...</option>
                        @foreach ($earningBands ?? [] as $earningBand)
                            <option value="{{ $earningBand->name }}"
                                {{ old('earning_band') === $earningBand->name ? 'selected' : '' }}>
                                {{ $earningBand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="dpsm_personal_file_number">DPSM Personal File No</label>
                    <div class="input-icon-group">
                        <i class="fas fa-id-badge input-icon"></i>
                        <input type="text" name="dpsm_personal_file_number"
                            class="form-control @error('dpsm_personal_file_number') is-invalid @enderror"
                            id="dpsm_personal_file_number" value="{{ old('dpsm_personal_file_number') }}"
                            placeholder="81716">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status <span class="required">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="">Select Status ...</option>
                        <option value="Current" {{ old('status') == 'Current' ? 'selected' : '' }}>Current</option>
                        <option value="Left" {{ old('status') == 'Left' ? 'selected' : '' }}>Left</option>
                        <option value="To Join" {{ old('status') == 'To Join' ? 'selected' : '' }}>To Join</option>
                        <option value="Deleted" {{ old('status') == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
            </div>

            <div class="form-grid mt-3">
                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="required">*</span></label>
                    <div class="input-icon-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                            id="username" placeholder="Lane" value="{{ old('username') }}">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                @if (!session('is_past_term'))
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text">
                            <i class="fas fa-save me-1"></i> Create Staff Member
                        </span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
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

            ['date_of_birth', 'date_of_appointment'].forEach(function(id) {
                const input = document.getElementById(id);
                if (input) {
                    formatDateInput(input);
                }
            });

            if (typeof flatpickr === 'function') {
                flatpickr('[data-toggle="staff-dob-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="staff-appointment-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    disableMobile: true,
                });
            }

            const firstnameInput = document.getElementById('validationCustom01');
            const lastnameInput = document.getElementById('lastname');
            const usernameInput = document.getElementById('username');

            function updateUsername() {
                const firstname = firstnameInput.value.toLowerCase().substring(0, 3);
                const lastname = lastnameInput.value.toLowerCase().replace(/\s+/g, '');
                usernameInput.value = firstname + lastname;
            }

            firstnameInput.addEventListener('input', updateUsername);
            lastnameInput.addEventListener('input', updateUsername);
        });

        // Loading button animation
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
@endsection

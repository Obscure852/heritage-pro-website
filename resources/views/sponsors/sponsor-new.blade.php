@extends('layouts.master')
@section('title')
    New Sponsor
@endsection
@section('css')
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #dc3545;
        }

        .was-validated .form-control:invalid:focus,
        .was-validated .form-select:invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }

        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .was-validated .form-control:valid:focus,
        .was-validated .form-select:valid:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .form-select.is-invalid:not([multiple]):not([size]),
        .form-select.is-invalid:not([multiple])[size="1"] {
            padding-right: 4.125rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-position: right 0.75rem center, center right 2.25rem;
            background-size: 16px 12px, calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label,
        .form-floating>.form-select~label {
            opacity: .65;
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
        }

        .alert {
            position: relative;
            padding: 1rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        .alert-dismissible {
            padding-right: 3rem;
        }

        .alert-dismissible .btn-close {
            position: absolute;
            right: 0;
            top: 0;
            padding: 1.25rem 1rem;
        }

        /* Form Container */
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

        /* Form Sections */
        .form-section {
            margin-bottom: 28px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Help Text */
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
            font-size: 14px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border-radius: 3px;
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

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
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

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        /* Loading Button */
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
            <a href="{{ route('sponsors.index') }}">Back</a>
        @endslot
        @slot('title')
            New Sponsor
        @endslot
    @endcomponent
    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="form-container">
        <div class="page-header">
            <h4 class="page-title">Create New Parent/Sponsor</h4>
        </div>

        <form id="sponsorForm" class="needs-validation" method="POST"
            action="{{ route('sponsors.sponsor-store') }}" novalidate>
            @csrf
            <input type="hidden" name="last_updated_by" value="{{ auth()->user()->full_name ?? null }}">

            <div class="form-section">
                <div class="help-text">
                    <div class="help-title">Basic Information & Contact Details</div>
                    <div class="help-content">Provide the parent/sponsor's basic details including name, title, contact information, and date of birth.</div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required-field" for="title">Title</label>
                            <select class="form-select @error('title') is-invalid @enderror"
                                name="title" id="title" required>
                                <option value="">Title ...</option>
                                <option value="Mr" {{ old('title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Mrs" {{ old('title') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                <option value="Ms" {{ old('title') == 'Ms' ? 'selected' : '' }}>Ms</option>
                                <option value="Dr" {{ old('title') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                <option value="Miss" {{ old('title') == 'Miss' ? 'selected' : '' }}>Miss</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required-field" for="first_name">First Name</label>
                            <div class="input-icon-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    id="first_name" placeholder="Amantle" value="{{ old('first_name') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required-field" for="last_name">Last Name</label>
                            <div class="input-icon-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    id="last_name" placeholder="Miller" value="{{ old('last_name') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <div class="input-icon-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email" placeholder="abaori@heritagepro.co" value="{{ old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone</label>
                            <div class="input-icon-group">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" placeholder="78 654 123" value="{{ old('phone') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="telephone">Telephone</label>
                            <div class="input-icon-group">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" name="telephone"
                                    class="form-control"
                                    id="telephone" placeholder="395 0555" value="{{ old('telephone') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="date_of_birth">Date of Birth</label>
                            <div class="input-icon-group flatpickr-wrapper" data-toggle="sponsor-dob-picker">
                                <i class="fas fa-calendar input-icon"></i>
                                <input type="text" name="date_of_birth"
                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                    id="date_of_birth" data-input value="{{ old('date_of_birth') }}"
                                    placeholder="dd/mm/yyyy" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required-field" for="gender">Gender</label>
                            <select class="form-select @error('gender') is-invalid @enderror"
                                name="gender" id="gender" required>
                                <option value="">Select gender ...</option>
                                <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>M</option>
                                <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>F</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Identification & Status</div>
                            <div class="help-content">ID number, nationality, relation to student, and current status.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="id_number">ID/Passport No:</label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-id-badge input-icon"></i>
                                        <input type="text" name="id_number"
                                            class="form-control form-control @error('id_number') is-invalid @enderror"
                                            id="id_number" placeholder="988 8248 87" value="{{ old('id_number') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="nationality">Nationality</label>
                                    <select name="nationality" data-trigger
                                        class="form-select form-select @error('nationality') is-invalid @enderror"
                                        id="nationality">
                                        <option value="">Select Nationality ...</option>
                                        @foreach ($nationalities as $nationality)
                                            <option value="{{ $nationality->name }}"
                                                {{ old('nationality') == $nationality->name ? 'selected' : '' }}>
                                                {{ $nationality->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="relation">Relation</label>
                                    <select name="relation" data-trigger
                                        class="form-select form-select @error('relation') is-invalid @enderror"
                                        id="relation">
                                        <option value="">Select relation ...</option>
                                        <option value="Mother" {{ old('relation') == 'Mother' ? 'selected' : '' }}>Mother
                                        </option>
                                        <option value="Grandmother"
                                            {{ old('relation') == 'Grandmother' ? 'selected' : '' }}>Grandmother</option>
                                        <option value="Father" {{ old('relation') == 'Father' ? 'selected' : '' }}>Father
                                        </option>
                                        <option value="Grandfather"
                                            {{ old('relation') == 'Grandfather' ? 'selected' : '' }}>Grandfather</option>
                                        <option value="Brother" {{ old('relation') == 'Brother' ? 'selected' : '' }}>
                                            Brother</option>
                                        <option value="Sister" {{ old('relation') == 'Sister' ? 'selected' : '' }}>Sister
                                        </option>
                                        <option value="Uncle" {{ old('relation') == 'Uncle' ? 'selected' : '' }}>Uncle
                                        </option>
                                        <option value="Auntie" {{ old('relation') == 'Auntie' ? 'selected' : '' }}>Auntie
                                        </option>
                                        <option value="Relative" {{ old('relation') == 'Relative' ? 'selected' : '' }}>
                                            Relative
                                        </option>
                                        <option value="Other" {{ old('relation') == 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <select name="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                        id="status" data-trigger>
                                        <option value="">Select status ...</option>
                                        <option value="Current" {{ old('status') == 'Current' ? 'selected' : '' }}>Current
                                        </option>
                                        <option value="Deleted" {{ old('status') == 'Deleted' ? 'selected' : '' }}>Deleted
                                        </option>
                                        <option value="Past" {{ old('status') == 'Past' ? 'selected' : '' }}>Past
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Professional Information</div>
                            <div class="help-content">Employment details and academic year.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="year">Year</label>
                                    <select name="year"
                                        class="form-select form-select @error('year') is-invalid @enderror"
                                        id="year">
                                        <option value="">Select Year ...</option>
                                        @for ($year = date('Y'); $year <= date('Y') + 3; $year++)
                                            <option value="{{ $year }}"
                                                {{ (string) old('year', date('Y')) === (string) $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="profession">Profession</label>
                                    <select name="profession" data-trigger
                                        class="form-select form-select @error('profession') is-invalid @enderror"
                                        id="profession">
                                        <option value="">Select Profession ...</option>
                                        <option value="Accountant"
                                            {{ old('profession') == 'Accountant' ? 'selected' : '' }}>Accountant</option>
                                        <option value="Architect"
                                            {{ old('profession') == 'Architect' ? 'selected' : '' }}>Architect</option>
                                        <option value="Banker" {{ old('profession') == 'Banker' ? 'selected' : '' }}>
                                            Banker</option>
                                        <option value="Business Owner"
                                            {{ old('profession') == 'Business Owner' ? 'selected' : '' }}>Business Owner
                                        </option>
                                        <option value="Chef" {{ old('profession') == 'Chef' ? 'selected' : '' }}>Chef
                                        </option>
                                        <option value="Civil Engineer"
                                            {{ old('profession') == 'Civil Engineer' ? 'selected' : '' }}>Civil Engineer
                                        </option>
                                        <option value="Civil Servant"
                                            {{ old('profession') == 'Civil Servant' ? 'selected' : '' }}>Civil Servant
                                        </option>
                                        <option value="Dentist" {{ old('profession') == 'Dentist' ? 'selected' : '' }}>
                                            Dentist</option>
                                        <option value="Electrician"
                                            {{ old('profession') == 'Electrician' ? 'selected' : '' }}>Electrician</option>
                                        <option value="Farmer" {{ old('profession') == 'Farmer' ? 'selected' : '' }}>
                                            Farmer</option>
                                        <option value="Financial Analyst"
                                            {{ old('profession') == 'Financial Analyst' ? 'selected' : '' }}>Financial
                                            Analyst</option>
                                        <option value="Graphic Designer"
                                            {{ old('profession') == 'Graphic Designer' ? 'selected' : '' }}>Graphic
                                            Designer</option>
                                        <option value="Human Resources Manager"
                                            {{ old('profession') == 'Human Resources Manager' ? 'selected' : '' }}>Human
                                            Resources Manager</option>
                                        <option value="IT Personnel"
                                            {{ old('profession') == 'IT Personnel' ? 'selected' : '' }}>IT Personnel
                                        </option>
                                        <option value="Lawyer" {{ old('profession') == 'Lawyer' ? 'selected' : '' }}>
                                            Lawyer</option>
                                        <option value="Marketing Manager"
                                            {{ old('profession') == 'Marketing Manager' ? 'selected' : '' }}>Marketing
                                            Manager</option>
                                        <option value="Mechanical Engineer"
                                            {{ old('profession') == 'Mechanical Engineer' ? 'selected' : '' }}>Mechanical
                                            Engineer</option>
                                        <option value="Medical Doctor"
                                            {{ old('profession') == 'Medical Doctor' ? 'selected' : '' }}>Medical Doctor
                                        </option>
                                        <option value="Miner" {{ old('profession') == 'Miner' ? 'selected' : '' }}>Miner
                                        </option>
                                        <option value="Nurse" {{ old('profession') == 'Nurse' ? 'selected' : '' }}>Nurse
                                        </option>
                                        <option value="Pharmacist"
                                            {{ old('profession') == 'Pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                                        <option value="Police Officer"
                                            {{ old('profession') == 'Police Officer' ? 'selected' : '' }}>Police Officer
                                        </option>
                                        <option value="Real Estate Agent"
                                            {{ old('profession') == 'Real Estate Agent' ? 'selected' : '' }}>Real Estate
                                            Agent</option>
                                        <option value="Teacher" {{ old('profession') == 'Teacher' ? 'selected' : '' }}>
                                            Teacher</option>
                                        <option value="Other" {{ old('profession') == 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="work_place">Work Place</label>
                                    <input type="text" name="work_place"
                                        class="form-control form-control"
                                        id="work_place" placeholder="Heritage" value="{{ old('work_place') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('sponsors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>Back to Parents
                        </a>
                        @can('manage-sponsors')
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text">
                                        <i class="fas fa-save me-1"></i>Create Parent
                                    </span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2"
                                            role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            @endif
                        @endcan
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

        const dobInput = document.getElementById('date_of_birth');
        if (dobInput) {
            formatDateInput(dobInput);

            if (typeof flatpickr === 'function') {
                flatpickr('[data-toggle="sponsor-dob-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    disableMobile: true,
                });
            }
        }

        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, '').slice(0,8);
                v = v.replace(
                    /(\d{2})(\d{3})?(\d{3})?/,
                    (_, a, b, c) => [a, b, c].filter(Boolean).join(' ')
                );
                e.target.value = v;
            });
        }

        const idInput = document.getElementById('id_number');
        if (idInput) {
            idInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/[^\dA-Za-z]/g, '').slice(0,15);
                v = v.replace(
                    /([A-Za-z0-9]{3})([A-Za-z0-9]{3})?([A-Za-z0-9]{0,9})?/,
                    (_, a, b, c) => [a, b, c].filter(Boolean).join(' ')
                );
                e.target.value = v;
            });
        }

        const telInput = document.getElementById('telephone');
        if (telInput) {
            telInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, '').slice(0,7);
                v = v.replace(
                    /(\d{3})(\d{1,4})?/,
                    (_, a, b) => [a, b].filter(Boolean).join(' ')
                );
                e.target.value = v;
            });
        }

        // Loading button animation
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

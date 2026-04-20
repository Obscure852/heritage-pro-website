@extends('layouts.master')
@section('title')
    Edit {{ $user->full_name ?? '' . ' Information' }}
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

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-current {
            background: #d1fae5;
            color: #065f46;
        }

        .status-left {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-to-join {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .status-deleted {
            background: #f3f4f6;
            color: #4b5563;
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

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 32px;
        }

        .form-tabs-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            padding-bottom: 4px;
        }

        .form-tabs-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .form-tabs-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .form-tabs .nav-tabs {
            border: none;
            flex-wrap: nowrap;
            min-width: max-content;
        }

        .form-tabs .nav-tabs .nav-item {
            flex: 0 0 auto;
        }

        .form-tabs .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            color: #6b7280;
            font-weight: 500;
            padding: 16px 24px;
            border-radius: 0;
            transition: all 0.2s;
        }

        .form-tabs .nav-tabs .nav-link:hover {
            color: #374151;
            background: #f9fafb;
        }

        .form-tabs .nav-tabs .nav-link.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: none;
        }

        .form-tabs .nav-tabs .nav-link i {
            margin-right: 8px;
            font-size: 16px;
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

        .input-group .select2-container {
            flex: 1 1 auto;
            width: auto !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 3px;
            border: 1px solid #d1d5db;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-dropdown {
            border-color: #d1d5db;
            font-size: 14px;
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

        .last-updated {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .table-responsive {
            margin-top: 16px;
        }

        /* Action Buttons (Table) */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 20px;
        }

        /* Filter Controls */
        .controls .input-group-text {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #6b7280;
        }

        .controls .form-control,
        .controls .form-select {
            border-color: #d1d5db;
            font-size: 14px;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .controls .btn-light {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #374151;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .controls .btn-light:hover {
            background: #e5e7eb;
            border-color: #9ca3af;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #3b82f6;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-tabs .nav-tabs .nav-link {
                padding: 12px 16px;
                font-size: 13px;
            }

            .form-tabs .nav-tabs .nav-link i {
                display: none;
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
            Edit Staff
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
            <div>
                <h1 class="page-title">{{ $user->full_name ?? 'Staff Member' }}</h1>
                <small class="text-muted">Edit staff member information</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php $status = data_get($user, 'filter.name', 'Current'); ?>
                <?php $statusLower = strtolower(str_replace(' ', '-', $status)); ?>
                <?php $statusClass = in_array($statusLower, ['current', 'left', 'to-join', 'deleted'], true) ? 'status-' . $statusLower : 'status-current'; ?>
                <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
            </div>
        </div>

        <div class="form-tabs">
            <div class="form-tabs-scroll">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                            <i class="fas fa-id-card"></i>
                            <span class="d-none d-sm-inline">Basic Profile</span>
                            <span class="d-inline d-sm-none">Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                            <i class="fas fa-certificate"></i>
                            <span class="d-none d-sm-inline">Qualifications</span>
                            <span class="d-inline d-sm-none">Quals</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                            <i class="fas fa-building"></i>
                            <span class="d-none d-sm-inline">Work History</span>
                            <span class="d-inline d-sm-none">Work</span>
                        </a>
                    </li>
                    @can('access-setup')
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                                <i class="fas fa-shield-halved"></i>
                                <span class="d-none d-sm-inline">Roles</span>
                                <span class="d-inline d-sm-none">Roles</span>
                            </a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#settings2" role="tab">
                            <i class="fas fa-clock-rotate-left"></i>
                            <span class="d-none d-sm-inline">Login History</span>
                            <span class="d-inline d-sm-none">Logins</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#academic" role="tab">
                            <i class="fas fa-chart-bar"></i>
                            <span class="d-none d-sm-inline">Academic</span>
                            <span class="d-inline d-sm-none">Academic</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#communication" role="tab">
                            <i class="fas fa-envelope"></i>
                            <span class="d-none d-sm-inline">Communication History</span>
                            <span class="d-inline d-sm-none">Comms</span>
                        </a>
                    </li>
                    @can('manage-hr')
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#roles1" role="tab">
                                <i class="fas fa-sliders"></i>
                                <span class="d-none d-sm-inline">Settings</span>
                                <span class="d-inline d-sm-none">Settings</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="home1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Basic Profile Information</div>
                    <div class="help-content">
                        Update the staff member's personal information and employment details. Fields marked with <span
                            class="text-danger">*</span> are required.
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <form class="needs-validation" method="post"
                            action="{{ route('staff.staff-update', $user->id) }}" novalidate
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="last_updated_by" value="{{ auth()->user()->id }}" required>

                            <div class="row">
                                <div class="col-md-10">
                                    <h3 class="section-title">Personal Details</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom01">First Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="firstname" id="validationCustom01"
                                                    class="form-control @error('firstname') is-invalid @enderror"
                                                    placeholder="First name"
                                                    value="{{ old('firstname', $user->firstname ?? '') }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom02">Middle Name</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="middlename" id="validationCustom02"
                                                    class="form-control @error('middlename') is-invalid @enderror"
                                                    placeholder="Middle name"
                                                    value="{{ old('middlename', $user->middlename ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom03">Last Name <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="lastname" id="validationCustom03"
                                                    class="form-control @error('lastname') is-invalid @enderror"
                                                    placeholder="Last name"
                                                    value="{{ old('lastname', $user->lastname ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom07">ID/Passport Number <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-id-badge input-icon"></i>
                                                <input type="text" name="id_number" id="validationCustom07"
                                                    class="form-control @error('id_number') is-invalid @enderror"
                                                    placeholder="ID/Passport Number"
                                                    value="{{ old('id_number', $user->formatted_id_number ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom05">Date of Birth <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group flatpickr-wrapper" data-toggle="staff-edit-dob-picker">
                                                <i class="fas fa-calendar input-icon"></i>
                                                <input type="text" name="date_of_birth" id="validationCustom05"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    data-input placeholder="dd/mm/yyyy" maxlength="10"
                                                    value="{{ old('date_of_birth', $user->formatted_date_of_birth ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="gender">Gender <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select @error('gender') is-invalid @enderror"
                                                name="gender" id="gender">
                                                <option value="">Select Gender</option>
                                                <?php $gender = strtoupper((string) data_get($user, 'gender', '')); ?>
                                                <option value="M" {{ $gender === 'M' ? 'selected' : '' }}>Male</option>
                                                <option value="F" {{ $gender === 'F' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom04">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-envelope input-icon"></i>
                                                <input type="email" name="email" id="validationCustom04"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    placeholder="Email Address"
                                                    value="{{ old('email', $user->email ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom08">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-phone input-icon"></i>
                                                <input type="text" name="phone" id="validationCustom08"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    placeholder="Phone Number"
                                                    value="{{ old('phone', $user->formatted_phone ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Nationality <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" data-trigger name="nationality">
                                                <option value="">Select Nationality</option>
                                                @foreach ($nationalities as $nationality)
                                                    <option value="{{ $nationality->name }}"
                                                        {{ $user->nationality == $nationality->name ? 'selected' : '' }}>
                                                        {{ $nationality->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <h3 class="section-title">Employment Details</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label" for="validationCustom06">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="validationCustom06" data-trigger
                                                class="form-select">
                                                <option value="">Select status</option>
                                                @foreach ($states as $state)
                                                    <option value="{{ $state->name }}"
                                                        {{ $user->status == $state->name ? 'selected' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <?php $isSchoolHeadAvailable = \App\Models\User::isSchoolHeadPositionAvailable(); ?>
                                            <?php $isCurrentUserSchoolHead = $user->position === 'School Head'; ?>
                                            <label for="position" class="form-label">Position <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="position" id="position" data-trigger>
                                                <option value="">Select Position</option>
                                                @foreach ($positions as $position)
                                                    @if ($position->name !== 'School Head' || $isSchoolHeadAvailable || $isCurrentUserSchoolHead)
                                                        <option value="{{ $position->name }}"
                                                            {{ $user->position == $position->name || (is_numeric($user->position) && (int) $user->position === (int) $position->id) ? 'selected' : '' }}>
                                                            {{ $position->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="area_of_work">Area Of Work <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="area_of_work" id="area_of_work"
                                                data-trigger>
                                                <option value="">Select Area of Work</option>
                                                @if (!empty($area_of_work))
                                                    @foreach ($area_of_work as $area)
                                                        <option value="{{ $area->name }}"
                                                            {{ $area->name == $user->area_of_work ? 'selected' : '' }}>
                                                            {{ $area->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label class="form-label">Department <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="department" data-trigger>
                                                <option value="">Select Department</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->name }}"
                                                        {{ $user->department == $department->name || (is_numeric($user->department) && (int) $user->department === (int) $department->id) ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Reporting To</label>
                                            <select class="form-select" name="reporting_to" data-trigger>
                                                <option value="">Select Staff</option>
                                                @foreach ($users as $u)
                                                    <option value="{{ $u->id }}"
                                                        {{ (string) old('reporting_to', $user->reporting_to) === (string) $u->id ? 'selected' : '' }}>
                                                        {{ $u->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Custom Filter
                                                <a href="{{ route('staff.staff-settings') }}">
                                                    <i class="fas fa-link"></i>
                                                </a>
                                            </label>
                                            <select class="form-select" name="user_filter_id" data-trigger>
                                                <option value="">Select a Filter</option>
                                                @foreach ($filters as $filter)
                                                    <option value="{{ $filter->id }}"
                                                        {{ (string) old('user_filter_id', $user->user_filter_id) === (string) $filter->id ? 'selected' : '' }}>
                                                        {{ $filter->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label class="form-label" for="personal_payroll_number">Personal Payroll Number</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-id-badge input-icon"></i>
                                                <input type="text" name="personal_payroll_number" id="personal_payroll_number"
                                                    class="form-control @error('personal_payroll_number') is-invalid @enderror"
                                                    value="{{ old('personal_payroll_number', $user->personal_payroll_number ?? '') }}"
                                                    placeholder="PPN-1001">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="date_of_appointment">Date of Appointment</label>
                                            <div class="input-icon-group flatpickr-wrapper" data-toggle="staff-edit-appointment-picker">
                                                <i class="fas fa-calendar input-icon"></i>
                                                <input type="text" name="date_of_appointment" id="date_of_appointment"
                                                    class="form-control @error('date_of_appointment') is-invalid @enderror"
                                                    data-input value="{{ old('date_of_appointment', optional($user->date_of_appointment)->format('d/m/Y')) }}"
                                                    placeholder="dd/mm/yyyy" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="earning_band">Grade (Earning Band)</label>
                                            <select name="earning_band" id="earning_band"
                                                class="form-select @error('earning_band') is-invalid @enderror"
                                                data-trigger>
                                                <option value="">Select earning band</option>
                                                @foreach ($earningBands ?? [] as $earningBand)
                                                    <option value="{{ $earningBand->name }}"
                                                        {{ old('earning_band', $user->earning_band ?? '') === $earningBand->name ? 'selected' : '' }}>
                                                        {{ $earningBand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="dpsm_personal_file_number">DPSM Personal File No</label>
                                            <input type="text" name="dpsm_personal_file_number" id="dpsm_personal_file_number"
                                                class="form-control @error('dpsm_personal_file_number') is-invalid @enderror"
                                                value="{{ old('dpsm_personal_file_number', $user->dpsm_personal_file_number ?? '') }}"
                                                placeholder="81716">
                                        </div>
                                    </div>

                                    <h3 class="section-title">Settings</h3>
                                    @can('manage-hr')
                                        <div class="form-group mb-3">
                                            <input class="form-check-input" type="checkbox" name="disabled" id="disabled"
                                                {{ !$user->active ? 'checked' : '' }}>
                                            <label style="margin-left: 6px;" for="disabled">
                                                Disable Login
                                            </label>
                                        </div>
                                    @endcan

                                    <div class="mt-3">
                                        <div class="d-flex flex-wrap gap-4" style="font-size: 13px;">
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-calendar-plus me-2" style="color: #10b981;"></i>
                                                <span>
                                                    <strong>Created:</strong>
                                                    {{ $user->created_at ? $user->created_at->format('d M, Y') : 'N/A' }}
                                                    @if($user->created_at)
                                                        <span class="text-secondary ms-1">({{ $user->created_at->diffForHumans() }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-edit me-2" style="color: #3b82f6;"></i>
                                                <span>
                                                    <strong>Last Edited:</strong>
                                                    {{ $user->updated_at ? $user->updated_at->format('d M, Y H:i') : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2" style="margin-top: 56px;">
                                    <div class="card" style="position: sticky; top: 20px;">
                                        <div class="card-body p-2">
                                            <div class="text-center mb-2">
                                                <label for="photoInputStaff" style="cursor: pointer; display: block;">
                                                    <img id="imagePreviewStaff"
                                                        src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/users/default-profile.png') }}"
                                                        class="img-fluid rounded w-100"
                                                        style="max-height: 180px; object-fit: contain;"
                                                        alt="Staff Photo"
                                                        onerror="this.src='{{ asset('assets/images/users/default-profile.png') }}'">
                                                </label>
                                                <input type="file" class="form-control" id="photoInputStaff"
                                                    name="avatar" accept="image/*" style="display: none;">
                                            </div>
                                            <div class="mb-0">
                                                <p class="text-muted small text-center mb-0">
                                                    Photo-size (3.5 x 4.5 cm)
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a class="btn btn-secondary" href="{{ route('staff.index') }}">
                                    <i class="fas fa-arrow-left"></i> Back to Staff List
                                </a>
                                @can('manage-hr')
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text">
                                            <i class="fas fa-save"></i> Update Staff Member
                                        </span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Updating...
                                        </span>
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="profile1" role="tabpanel">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-12">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search qualifications..."
                                            id="qualSearchInput">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-light" id="resetQualFilters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('manage-hr')
                            <a href="{{ route('staff.add-x-qualifications', $user->id) }}" class="btn btn-primary"><i
                                    class="fas fa-plus"></i> New Qualification
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="staff-qualifications" class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Qual. Code</th>
                                        <th>Qualification</th>
                                        <th>University/College</th>
                                        <th>Start</th>
                                        <th>Completion</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($user->qualifications) && $user->qualifications->count() > 0)
                                        @foreach ($user->qualifications as $qualification)
                                            <tr class="qual-row"
                                                data-search="{{ strtolower($qualification->qualification_code . ' ' . $qualification->qualification . ' ' . $qualification->pivot->college) }}">
                                                <td>{{ $qualification->qualification_code }}</td>
                                                <td>{{ $qualification->qualification }}</td>
                                                <td>{{ $qualification->pivot->college }}</td>
                                                <td>{{ $qualification->pivot->start_date }}</td>
                                                <td>{{ $qualification->pivot->completion_date }}</td>
                                                <td class="text-end">
                                                    <div class="action-buttons">
                                                        <a class="btn btn-outline-info"
                                                            href="{{ route('staff.edit-x-qualification', ['id' => $user->id, 'qualificationId' => $qualification->id]) }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a class="btn btn-outline-danger"
                                                            href="{{ route('staff.remove-x-qualification', ['userId' => $user->id, 'id' => $qualification->id]) }}"
                                                            onclick="return confirm('Are you sure you want to remove this qualification?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-graduation-cap"
                                                    style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-2 mb-0">No qualifications recorded for this staff member.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="messages1" role="tabpanel">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-12">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search work history..."
                                            id="workSearchInput">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-light" id="resetWorkFilters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('manage-hr')
                            <a href="{{ route('staff.add-work-history', $user->id) }}" class="btn btn-primary"><i
                                    class="fas fa-plus"></i> New Work History
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="staff-work-history" class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Place of Work</th>
                                        <th>Type of Work</th>
                                        <th>Role</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($work_history) && count($work_history) > 0)
                                        @foreach ($work_history as $work)
                                            <tr class="work-row"
                                                data-search="{{ strtolower($work->workplace . ' ' . $work->type_of_work . ' ' . $work->role) }}">
                                                <td>{{ $work->workplace }}</td>
                                                <td>{{ $work->type_of_work }}</td>
                                                <td>{{ $work->role }}</td>
                                                <td>{{ $work->start }}</td>
                                                <td>{{ $work->end }}</td>
                                                <td class="text-end">
                                                    <div class="action-buttons">
                                                        <a class="btn btn-outline-info"
                                                            href="{{ route('staff.edit-work-history', ['id' => $user->id, 'work' => $work]) }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @can('manage-hr')
                                                            <a class="btn btn-outline-danger"
                                                                href="{{ route('staff.remove-work-history', ['id' => $work->id]) }}">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-briefcase" style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-2 mb-0">No work history recorded for this staff member.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @can('access-setup')
                <div class="tab-pane" id="settings1" role="tabpanel">
                    <!-- Role Management Header -->
                    <div class="help-text">
                        <div class="help-title">System Role Management</div>
                        <div class="help-content">
                            Assign and manage system roles for this staff member. Each role defines specific permissions and
                            access levels within the system.
                        </div>
                    </div>

                    <!-- Role Statistics Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="card border shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-user-check text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h4 class="mb-1 text-dark">{{ $user->roles->count() }}</h4>
                                    <small class="text-muted">Active Roles</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-shield-alt text-info" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h4 class="mb-1 text-dark">{{ $roles->count() }}</h4>
                                    <small class="text-muted">Available Roles</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-check-circle text-success" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h4 class="mb-1 text-dark">{{ $user->roles->where('name', '!=', '')->count() }}</h4>
                                    <small class="text-muted">Configured</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border shadow-sm">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-clock text-warning" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h4 class="mb-1 text-dark" style="font-size: 1rem;">
                                        {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}</h4>
                                    <small class="text-muted">Last Updated</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Assignment Form -->
                    <?php if (auth()->check() && auth()->user()->can('canAllocateRoles')): ?>
                        <?php
                            $moduleVisibility = app(\App\Services\ModuleVisibilityService::class);
                            $visibleRoles = $moduleVisibility->getVisibleRoles($roles);
                        ?>
                        <form method="POST" action="{{ route('staff.staff-role-allocation', $user->id) }}" class="mb-4">
                            @csrf
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <select name="role" class="form-select select2-role-search" id="roleSelect"
                                        aria-label="Select role with search" required>
                                        <option value="" selected>Choose a role to assign...</option>
                                        @foreach ($visibleRoles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ $user->roles->contains($role->id) ? 'disabled' : '' }}>
                                                {{ $role->name }}
                                                @if ($role->description)
                                                    - {{ Str::limit($role->description, 30) }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-plus"></i> Assign Role
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <!-- Assigned Roles Table -->
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($user->roles->count() > 0)
                                    @foreach ($user->roles as $index => $role)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                                    <strong>{{ $role->name }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $role->description ? Str::limit($role->description, 50) : 'No description' }}
                                            </td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    @can('canAllocateRoles')
                                                        <button type="button" class="btn btn-outline-danger"
                                                            onclick="confirmRoleRemoval('{{ $role->name }}', '{{ route('setup.staff-role-deallocation', ['userId' => $user->id, 'roleId' => $role->id]) }}')"
                                                            title="Remove Role">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-user-shield" style="font-size: 32px; opacity: 0.3;"></i>
                                            <p class="mt-2 mb-0">No roles assigned yet</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan
            <div class="tab-pane" id="academic" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0" id="academicTitle" style="font-weight:600;color:#1f2937;font-size:14px;"></h6>
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="academicTermSelect" style="width:200px;">
                        </select>
                    </div>
                </div>
                <div id="academicLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading academic data...</p>
                </div>
                <div id="academicEmpty" class="text-center text-muted py-5 d-none">
                    <i class="fas fa-chalkboard-teacher" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">No teaching assignments found for this staff member.</p>
                </div>
                <div id="academicContent"></div>
            </div>
            <div class="tab-pane" id="communication" role="tabpanel">
                @php
                    $whatsappEnabled = $communicationChannels['whatsapp_enabled'] ?? false;
                    $whatsappConsent = $user->channelConsents->where('channel', 'whatsapp')->first();
                    $consentStatus = $whatsappConsent->status ?? 'opted_out';
                @endphp
                @if ($whatsappEnabled)
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="mb-1">WhatsApp Consent</h6>
                                            <small class="text-muted">Consent must be recorded before direct or broadcast WhatsApp sends.</small>
                                        </div>
                                        <span class="badge {{ $consentStatus === 'opted_in' ? 'bg-success' : ($consentStatus === 'revoked' ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ strtoupper(str_replace('_', ' ', $consentStatus)) }}
                                        </span>
                                    </div>
                                    <form method="POST" action="{{ route('staff.communication-consent', $user->id) }}" class="row g-2 align-items-end">
                                        @csrf
                                        <input type="hidden" name="channel" value="whatsapp">
                                        <div class="col-md-4">
                                            <label class="form-label">Status</label>
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="opted_in" {{ $consentStatus === 'opted_in' ? 'selected' : '' }}>Opted In</option>
                                                <option value="opted_out" {{ $consentStatus === 'opted_out' ? 'selected' : '' }}>Opted Out</option>
                                                <option value="revoked" {{ $consentStatus === 'revoked' ? 'selected' : '' }}>Revoked</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Source</label>
                                            <input type="text" class="form-control form-control-sm" name="source" value="{{ $whatsappConsent->source ?? 'staff_admin' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-save me-1"></i> Save Consent
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <input type="text" class="form-control form-control-sm" name="notes" value="{{ $whatsappConsent->notes ?? '' }}">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row align-items-center mb-3">
                    <div class="col-lg-10 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-11 col-md-11 col-sm-12">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search communications..."
                                            id="commSearchInput">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-light" id="resetCommFilters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="communications" class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Body</th>
                                        <th>Segments</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($user->messages->count() > 0)
                                        @foreach ($user->messages as $message)
                                            <tr class="comm-row" data-search="{{ strtolower($message->body ?? '') }}">
                                                <td>{{ $message->created_at ? $message->created_at->format('Y-m-d H:i') : '' }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ ($message->channel ?? 'sms') === 'whatsapp' ? 'bg-success' : 'bg-info' }}">
                                                        {{ strtoupper($message->channel ?? 'sms') }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($message->author)->full_name ?? ($school_data->school_name ?? '') }}</td>
                                                <td>{{ $message->recipient_address ?? ($user->phone ?? '') }}</td>
                                                <td>{{ Str::limit($message->body ?? '', 50) }}</td>
                                                <td>
                                                    @if (($message->channel ?? 'sms') === 'whatsapp')
                                                        {{ $message->template_name ?? 'Template message' }}
                                                    @else
                                                        {{ $message->sms_count ?? '' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $deliveryStatus = strtolower($message->delivery_status ?? $message->status ?? 'pending');
                                                        $deliveryClass = match ($deliveryStatus) {
                                                            'delivered' => 'bg-success',
                                                            'failed', 'rejected', 'undelivered' => 'bg-danger',
                                                            'queued', 'sent', 'accepted' => 'bg-warning text-dark',
                                                            default => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $deliveryClass }}">{{ strtoupper($deliveryStatus) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-comments" style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-2 mb-0">No communication history for this staff member.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="settings2" role="tabpanel">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-10 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-11 col-md-11 col-sm-12">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control"
                                            placeholder="Search IP address or changes..." id="logSearchInput">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-light" id="resetLogFilters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="logins" class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 150px;">IP Address</th>
                                        <th>Changes</th>
                                        <th style="width: 180px;">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($user->logs) && count($user->logs) > 0)
                                        @php
                                            $stringifyChangeValue = function ($value): string {
                                                if (is_string($value) || is_numeric($value)) {
                                                    return (string) $value;
                                                }

                                                if (is_bool($value)) {
                                                    return $value ? 'true' : 'false';
                                                }

                                                if ($value === null) {
                                                    return 'null';
                                                }

                                                if (is_array($value)) {
                                                    $flattened = \Illuminate\Support\Arr::dot($value);

                                                    if ($flattened === []) {
                                                        return '[]';
                                                    }

                                                    return collect($flattened)->map(function ($item, $key) {
                                                        if (is_bool($item)) {
                                                            $item = $item ? 'true' : 'false';
                                                        } elseif ($item === null) {
                                                            $item = 'null';
                                                        } elseif (is_array($item)) {
                                                            $item = json_encode($item);
                                                        }

                                                        return is_string($key) ? "{$key}: {$item}" : (string) $item;
                                                    })->implode(', ');
                                                }

                                                return json_encode($value) ?: '[complex data]';
                                            };
                                        @endphp
                                        @foreach ($user->logs as $index => $log)
                                            @php
                                                $changesText = '';
                                                if (isset($log->changes['data']) && is_array($log->changes['data'])) {
                                                    $changesText = implode(' ', array_keys($log->changes['data']));
                                                }
                                            @endphp
                                            <tr class="log-row"
                                                data-search="{{ strtolower(($log->ip_address ?? '') . ' ' . $changesText) }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                                                <td>
                                                    @if (isset($log->changes['data']) && is_array($log->changes['data']))
                                                        @foreach ($log->changes['data'] as $field => $value)
                                                            <span class="badge bg-info me-1">{{ ucfirst(str_replace('_', ' ', $field)) }}:
                                                                {{ Str::limit($stringifyChangeValue($value), 15) }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">No changes</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-history" style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-2 mb-0">No login history for this staff member.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @can('manage-hr')
                <div class="tab-pane" id="roles1" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="padding:20px;">
                                <h5 class="mb-3">Upload Staff Signature</h5>
                                <div class="alert alert-danger d-none" id="signatureError" role="alert"></div>

                                <form action="{{ route('staff.signature', $user->id) }}" method="post"
                                    enctype="multipart/form-data" id="signatureForm">
                                    @csrf
                                    <div class="mb-3">
                                        <div class="custom-file-input">
                                            <input type="file" name="signature" id="signature"
                                                accept="image/png,image/jpeg,image/jpg,image/gif">
                                            <label for="signature" class="file-input-label">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-signature"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <span class="file-label">Choose Signature Image</span>
                                                    <span class="file-hint" id="signatureHint">PNG, JPG or GIF. Crop to
                                                        the standard signature size before upload.</span>
                                                    <span class="file-selected d-none" id="signatureFileName"></span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary btn-loading" type="submit">
                                            <span class="btn-text">
                                                <i class="fas fa-upload"></i> Upload Signature
                                            </span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Uploading...
                                            </span>
                                        </button>
                                    </div>
                                </form>

                                <div id="preview-signature" class="mt-3"></div>

                                @if (!empty($user->signature_path))
                                    <div class="mt-3 p-3 border rounded">
                                        <p class="text-muted mb-2"><strong>Current Signature:</strong></p>
                                        <img height="150" src="{{ URL::asset($user->signature_path) }}"
                                            alt="{{ $user->full_name }}'s Signature" class="img-fluid">
                                    </div>
                                @else
                                    <div class="mt-3 p-3 border rounded text-center text-muted">
                                        <i class="fas fa-signature" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No signature uploaded.</p>
                                    </div>
                                @endif

                                <hr class="my-4">

                                <h5 class="mb-3">SMS Message Signature</h5>
                                <form action="{{ route('staff.sms-signature', $user->id) }}" method="post">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Enter your SMS signature text</label>
                                        <textarea class="form-control" name="sms_signature" id="sms_signature" rows="4"
                                            placeholder="e.g., Best regards, [Your Name]">{{ $user->sms_signature ?? '' }}</textarea>
                                        <small class="text-muted">This signature will be appended to SMS messages sent from
                                            your account.</small>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary btn-loading" id="save-signature" type="submit">
                                            <span class="btn-text">
                                                <i class="fas fa-save"></i> Save SMS Signature
                                            </span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
    </div>
    </div>
    </div>

    @include('components.crop-modal')
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

            ['validationCustom05', 'date_of_appointment'].forEach(function(id) {
                const input = document.getElementById(id);
                if (input) {
                    formatDateInput(input);
                }
            });

            if (typeof flatpickr === 'function') {
                flatpickr('[data-toggle="staff-edit-dob-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="staff-edit-appointment-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    disableMobile: true,
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const signatureForm = document.getElementById('signatureForm');
            const signatureInput = document.getElementById('signature');
            const signatureError = document.getElementById('signatureError');
            const signatureHint = document.getElementById('signatureHint');
            const signatureFileName = document.getElementById('signatureFileName');
            const previewSignature = document.getElementById('preview-signature');
            const SIGNATURE_WIDTH = 650;
            const SIGNATURE_HEIGHT = 250;

            if (!signatureInput || !signatureError || !signatureHint || !signatureFileName || !previewSignature) {
                return;
            }

            function clearSignatureError() {
                signatureError.classList.add('d-none');
                signatureError.textContent = '';
            }

            function setSignatureSelection(fileName) {
                signatureFileName.textContent = fileName;
                signatureFileName.classList.remove('d-none');
                signatureHint.classList.add('d-none');
            }

            function clearSignaturePreview() {
                if (previewSignature.dataset.objectUrl) {
                    URL.revokeObjectURL(previewSignature.dataset.objectUrl);
                    delete previewSignature.dataset.objectUrl;
                }
                previewSignature.innerHTML = '';
            }

            function renderSignaturePreview(file) {
                clearSignatureError();
                clearSignaturePreview();

                const previewUrl = URL.createObjectURL(file);
                previewSignature.dataset.objectUrl = previewUrl;
                previewSignature.innerHTML = `
                    <div class="p-3 border rounded text-center">
                        <p class="text-muted mb-2"><strong>Cropped Preview (${SIGNATURE_WIDTH} x ${SIGNATURE_HEIGHT})</strong></p>
                        <img src="${previewUrl}" alt="Signature preview" class="img-fluid" style="max-height: 250px; object-fit: contain;">
                    </div>
                `;
            }

            function buildSignatureFileName(originalName) {
                const fallbackName = 'signature';
                const baseName = (originalName || fallbackName).replace(/\.[^.]+$/, '').trim() || fallbackName;
                return `${baseName}-cropped.png`;
            }

            function createSignatureFile(blob, fileName) {
                return new File([blob], fileName, {
                    type: blob.type || 'image/png',
                    lastModified: Date.now()
                });
            }

            CropHelper.bindAjaxFallback(signatureForm, 'signature', signatureInput, {
                onSuccess: function(payload) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: payload.message || 'Signature uploaded successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    window.setTimeout(function() {
                        window.location.reload();
                    }, 500);
                },
                onError: function(error) {
                    signatureError.classList.remove('d-none');
                    signatureError.textContent = error.message || 'Failed to upload the cropped signature. Please try again.';
                }
            });

            CropHelper.init(signatureInput, function(blob, meta) {
                const croppedFileName = buildSignatureFileName(meta && meta.sourceFile ? meta.sourceFile.name : '');
                const croppedFile = createSignatureFile(blob, croppedFileName);
                CropHelper.attachFileToInput(signatureInput, croppedFile);

                setSignatureSelection(croppedFile.name);
                renderSignaturePreview(croppedFile);
                CropHelper.hideModal();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Signature cropped. Click upload to save it.',
                    showConfirmButton: false,
                    timer: 3000
                });
            }, {
                title: 'Crop Signature',
                aspectRatio: NaN,
                outputWidth: SIGNATURE_WIDTH,
                outputHeight: SIGNATURE_HEIGHT,
                outputMimeType: 'image/png',
                allowedTypes: ['image/jpeg', 'image/png', 'image/gif'],
                fileTypeErrorMessage: 'Please select a PNG, JPG or GIF image.',
                maxFileSize: 8 * 1024 * 1024,
                maxFileSizeErrorMessage: 'Please select an image smaller than 8 MB before cropping.',
                cropperOptions: {
                    viewMode: 0,
                    autoCropArea: 0.75,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    wheelZoomRatio: 0.05,
                    ready: function() {
                        var cropper = this.cropper;

                        if (!cropper) {
                            return;
                        }

                        window.requestAnimationFrame(function() {
                            var containerData = cropper.getContainerData();
                            var imageData = cropper.getImageData();

                            if (!containerData || !imageData || !imageData.naturalWidth || !imageData.naturalHeight) {
                                return;
                            }

                            var cropBoxWidth = containerData.width * 0.8;
                            var cropBoxHeight = containerData.height * 0.38;

                            cropper.setCropBoxData({
                                left: (containerData.width - cropBoxWidth) / 2,
                                top: (containerData.height - cropBoxHeight) / 2,
                                width: cropBoxWidth,
                                height: cropBoxHeight
                            });

                            cropper.zoomTo(Math.min(
                                cropBoxWidth / imageData.naturalWidth,
                                cropBoxHeight / imageData.naturalHeight
                            ) * 0.95);
                            cropper.center();
                        });
                    }
                }
            });
        });


        $('#qualificationModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');

            var modal = $(this);
            var form = modal.find('form');

            if (action === 'edit') {
                var updateRoute = button.data('update-route');
                form.attr('action', updateRoute);
            }
        });


        $('#editWorkHistoryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');

            var modal = $(this);
            var form = modal.find('form');

            if (action === 'edit') {
                var updateRoute = button.data('update-route');
                form.attr('action', updateRoute);
            }
        });

        function confirmRoleRemoval(roleName, removeUrl) {
            if (confirm(
                    `Are you sure you want to remove the "${roleName}" role from this staff member?\n\nThis action cannot be undone.`
                )) {
                // Create and submit form for role removal
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = removeUrl;

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);

                // Add method spoofing for DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }


        $(document).ready(function() {
            // Initialize DataTables with search and length menu disabled
            const dataTableConfig = {
                searching: false,
                lengthChange: false,
                pageLength: 20,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            };

            // Helper function to safely initialize DataTable (prevents reinitialization error)
            function initDataTable(selector) {
                if ($(selector).length && !$.fn.DataTable.isDataTable(selector)) {
                    $(selector).DataTable(dataTableConfig);
                }
            }

            initDataTable('#logins');
            initDataTable('#staff-qualifications');
            initDataTable('#staff-work-history');
            initDataTable('#communications');
        });

        // Custom filtering for Qualifications
        document.getElementById('qualSearchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.qual-row').forEach(row => {
                const searchData = row.dataset.search || '';
                row.style.display = searchData.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('resetQualFilters')?.addEventListener('click', function() {
            document.getElementById('qualSearchInput').value = '';
            document.querySelectorAll('.qual-row').forEach(row => row.style.display = '');
        });

        // Custom filtering for Work History
        document.getElementById('workSearchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.work-row').forEach(row => {
                const searchData = row.dataset.search || '';
                row.style.display = searchData.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('resetWorkFilters')?.addEventListener('click', function() {
            document.getElementById('workSearchInput').value = '';
            document.querySelectorAll('.work-row').forEach(row => row.style.display = '');
        });

        // Custom filtering for Communications
        document.getElementById('commSearchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.comm-row').forEach(row => {
                const searchData = row.dataset.search || '';
                row.style.display = searchData.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('resetCommFilters')?.addEventListener('click', function() {
            document.getElementById('commSearchInput').value = '';
            document.querySelectorAll('.comm-row').forEach(row => row.style.display = '');
        });

        // Custom filtering for Login History
        document.getElementById('logSearchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.log-row').forEach(row => {
                const searchData = row.dataset.search || '';
                row.style.display = searchData.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('resetLogFilters')?.addEventListener('click', function() {
            document.getElementById('logSearchInput').value = '';
            document.querySelectorAll('.log-row').forEach(row => row.style.display = '');
        });

        document.addEventListener('DOMContentLoaded', function() {
            function activateTab(tabHref) {
                var tabToActivate = document.querySelector('a[href="' + tabHref + '"]');
                if (tabToActivate) {
                    var tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                }
            }

            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                activateTab(activeTab);
            }

            var tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    localStorage.setItem('activeTab', event.target.getAttribute('href'));
                });
            });

            var otherInfoForm = document.querySelector('#settings1 form');
            if (otherInfoForm) {
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'active_tab';
                hiddenInput.value = '#settings1';
                otherInfoForm.appendChild(hiddenInput);
            }

            var serverActiveTab = "{{ session('active_tab') }}";
            if (serverActiveTab) {
                activateTab(serverActiveTab);
                {{ session()->forget('active_tab') }};
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const photoInputStaff = document.getElementById('photoInputStaff');
            const imagePreviewStaff = document.getElementById('imagePreviewStaff');

            CropHelper.init(photoInputStaff, function(blob) {
                // Replace the file input contents with the cropped blob
                // Use DataTransfer where supported (Chrome, Firefox), fallback for Safari
                try {
                    var dt = new DataTransfer();
                    dt.items.add(new File([blob], 'avatar.jpg', { type: 'image/jpeg' }));
                    photoInputStaff.files = dt.files;
                } catch (e) {
                    // Safari fallback: store blob on the form element for manual retrieval
                    photoInputStaff._croppedBlob = blob;
                }

                // Update the preview image and revoke old ObjectURL to prevent memory leaks
                var url = URL.createObjectURL(blob);
                var targetImg;
                if (imagePreviewStaff.tagName === 'IMG') {
                    if (imagePreviewStaff._objectUrl) {
                        URL.revokeObjectURL(imagePreviewStaff._objectUrl);
                    }
                    imagePreviewStaff.src = url;
                    imagePreviewStaff._objectUrl = url;
                    targetImg = imagePreviewStaff;
                } else {
                    imagePreviewStaff.innerHTML =
                        '<img src="' + url + '" class="img-fluid rounded w-100" style="max-height: 180px; object-fit: contain;" alt="Cropped Photo">';
                    targetImg = imagePreviewStaff.querySelector('img');
                    targetImg._objectUrl = url;
                }

                CropHelper.hideModal();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Photo cropped. Remember to save your changes.',
                    showConfirmButton: false,
                    timer: 3000
                });
            });

            // Safari fallback: intercept form submit to inject cropped blob via FormData
            var staffForm = photoInputStaff.closest('form');
            if (staffForm) {
                staffForm.addEventListener('submit', function(e) {
                    if (photoInputStaff._croppedBlob) {
                        e.preventDefault();
                        var formData = new FormData(staffForm);
                        formData.set('avatar', photoInputStaff._croppedBlob, 'avatar.jpg');
                        var xhr = new XMLHttpRequest();
                        xhr.open(staffForm.method, staffForm.action, true);
                        xhr.onload = function() {
                            window.location.href = staffForm.action;
                        };
                        xhr.onerror = function() {
                            Swal.fire('Error', 'Failed to save. Please try again.', 'error');
                        };
                        xhr.send(formData);
                    }
                });
            }
        });

        $(document).ready(function() {
            $('#settings1 .select2-role-search').select2({
                placeholder: "Choose a role to assign...",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#settings1')
            });
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

        // Academic Tab
        (function() {
            const userId = {{ $user->id }};
            let academicLoaded = false;
            let termsPopulated = false;
            let echartsLoaded = false;
            let chartInstances = [];

            const echartsColors = {
                male: '#5470c6',
                female: '#ee6666',
                total: '#91cc75',
                'A*': '#4caf50',
                'A': '#91cc75',
                'B': '#5470c6',
                'C': '#fac858',
                'D': '#fc8452',
                'E': '#ee6666',
                'F': '#9a60b4',
                'G': '#73c0de',
                'U': '#9e9e9e',
                'NS': '#d3d3d3',
            };

            function loadEcharts() {
                return new Promise((resolve) => {
                    if (echartsLoaded || typeof echarts !== 'undefined') {
                        echartsLoaded = true;
                        resolve();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js';
                    script.onload = () => { echartsLoaded = true; resolve(); };
                    document.head.appendChild(script);
                });
            }

            function fetchAcademicData(termId) {
                const loading = document.getElementById('academicLoading');
                const empty = document.getElementById('academicEmpty');
                const content = document.getElementById('academicContent');

                loading.classList.remove('d-none');
                empty.classList.add('d-none');
                content.innerHTML = '';

                // Destroy old chart instances
                chartInstances.forEach(c => c.dispose());
                chartInstances = [];

                let url = `/staff/academic-data/${userId}`;
                if (termId) url += `?term_id=${termId}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    loading.classList.add('d-none');

                    // Populate terms dropdown once
                    if (!termsPopulated && data.terms && data.terms.length) {
                        const sel = document.getElementById('academicTermSelect');
                        sel.innerHTML = '';
                        data.terms.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.label;
                            if (t.label === data.termLabel) opt.selected = true;
                            sel.appendChild(opt);
                        });
                        termsPopulated = true;
                    }

                    document.getElementById('academicTitle').textContent =
                        'End Of Term Exam Performance Analysis - ' + data.termLabel;

                    const subjectKeys = Object.keys(data.subjects || {});
                    if (subjectKeys.length === 0) {
                        empty.classList.remove('d-none');
                        return;
                    }

                    loadEcharts().then(() => {
                        subjectKeys.forEach(subjectName => {
                            const rows = data.subjects[subjectName];
                            const totalRow = data.totals[subjectName];
                            const level = (data.levels && data.levels[subjectName]) || 'Junior';
                            const scale = data.gradeScales[level] || data.gradeScales['Junior'];
                            const gradeColumns = scale.grades;
                            const percentageColumns = Object.keys(scale.percentages);

                            // Section wrapper
                            const section = document.createElement('div');
                            section.className = 'mb-4';

                            // Subject title
                            const title = document.createElement('h6');
                            title.style.cssText = 'font-weight:600;color:#1f2937;padding-bottom:8px;border-bottom:1px solid #e5e7eb;margin-bottom:12px;';
                            title.textContent = subjectName + ' Analysis';
                            section.appendChild(title);

                            // Table
                            const tableWrap = document.createElement('div');
                            tableWrap.className = 'table-responsive';
                            tableWrap.style.marginBottom = '16px';

                            const table = document.createElement('table');
                            table.className = 'table table-bordered table-sm';
                            table.style.fontSize = '10px';

                            // Build header
                            const thead = document.createElement('thead');
                            const hr1 = document.createElement('tr');
                            hr1.style.cssText = 'background:#f9fafb;';

                            const thClass = document.createElement('th');
                            thClass.rowSpan = 2;
                            thClass.textContent = 'Class';
                            thClass.style.cssText = 'vertical-align:middle;font-weight:600;color:#374151;';
                            hr1.appendChild(thClass);

                            const thSubject = document.createElement('th');
                            thSubject.rowSpan = 2;
                            thSubject.textContent = 'Subject';
                            thSubject.style.cssText = 'vertical-align:middle;font-weight:600;color:#374151;';
                            hr1.appendChild(thSubject);

                            gradeColumns.forEach(g => {
                                const th = document.createElement('th');
                                th.colSpan = 3;
                                th.textContent = g;
                                th.style.cssText = 'text-align:center;font-weight:600;color:#374151;';
                                hr1.appendChild(th);
                            });

                            ['NS', 'Total w/ Scores', 'Total Enrolled'].forEach(label => {
                                const th = document.createElement('th');
                                th.colSpan = 3;
                                th.textContent = label;
                                th.style.cssText = 'text-align:center;font-weight:600;color:#374151;';
                                hr1.appendChild(th);
                            });

                            percentageColumns.forEach(p => {
                                const th = document.createElement('th');
                                th.colSpan = 3;
                                th.textContent = p;
                                th.style.cssText = 'text-align:center;font-weight:600;color:#374151;';
                                hr1.appendChild(th);
                            });

                            thead.appendChild(hr1);

                            // Sub-header row
                            const hr2 = document.createElement('tr');
                            hr2.style.cssText = 'background:#f9fafb;';
                            const subCount = gradeColumns.length + 3 + percentageColumns.length;
                            for (let i = 0; i < subCount; i++) {
                                ['M','F','T'].forEach(s => {
                                    const th = document.createElement('th');
                                    th.textContent = s;
                                    th.style.cssText = 'text-align:center;font-size:9px;color:#6b7280;';
                                    hr2.appendChild(th);
                                });
                            }
                            thead.appendChild(hr2);
                            table.appendChild(thead);

                            // Build body
                            const tbody = document.createElement('tbody');

                            function buildDataRow(d, isTotals) {
                                const tr = document.createElement('tr');
                                if (isTotals) tr.style.cssText = 'font-weight:600;background:#f3f3f3;';

                                const tdClass = document.createElement('td');
                                tdClass.textContent = isTotals ? 'Totals' : (d.class_name || '');
                                tr.appendChild(tdClass);

                                const tdSubject = document.createElement('td');
                                tdSubject.textContent = isTotals ? '' : (d.subject_name || '');
                                tr.appendChild(tdSubject);

                                // Grade columns
                                gradeColumns.forEach(g => {
                                    const m = d.grades?.[g]?.M ?? 0;
                                    const f = d.grades?.[g]?.F ?? 0;
                                    [m, f, m + f].forEach(v => {
                                        const td = document.createElement('td');
                                        td.textContent = v;
                                        td.style.textAlign = 'center';
                                        tr.appendChild(td);
                                    });
                                });

                                // NS
                                const nsM = d.grades?.NS?.M ?? 0;
                                const nsF = d.grades?.NS?.F ?? 0;
                                [nsM, nsF, nsM + nsF].forEach(v => {
                                    const td = document.createElement('td');
                                    td.textContent = v;
                                    td.style.textAlign = 'center';
                                    tr.appendChild(td);
                                });

                                // Total w/ Scores
                                const twM = d.totalMale ?? 0;
                                const twF = d.totalFemale ?? 0;
                                [twM, twF, twM + twF].forEach(v => {
                                    const td = document.createElement('td');
                                    td.textContent = v;
                                    td.style.textAlign = 'center';
                                    tr.appendChild(td);
                                });

                                // Total Enrolled
                                const teM = d.totalEnrolled?.M ?? 0;
                                const teF = d.totalEnrolled?.F ?? 0;
                                [teM, teF, teM + teF].forEach(v => {
                                    const td = document.createElement('td');
                                    td.textContent = v;
                                    td.style.textAlign = 'center';
                                    tr.appendChild(td);
                                });

                                // Percentages
                                percentageColumns.forEach(p => {
                                    const pM = d[p]?.M ?? 0;
                                    const pF = d[p]?.F ?? 0;
                                    const tTotal = (d.totalMale ?? 0) + (d.totalFemale ?? 0);
                                    // Compute T% from combined grade counts
                                    const pScale = scale.percentages[p] || [];
                                    let tSum = 0;
                                    pScale.forEach(g => {
                                        tSum += (d.grades?.[g]?.M ?? 0) + (d.grades?.[g]?.F ?? 0);
                                    });
                                    const pT = tTotal > 0 ? Math.round(tSum / tTotal * 10000) / 100 : 0;

                                    [pM + '%', pF + '%', pT + '%'].forEach(v => {
                                        const td = document.createElement('td');
                                        td.textContent = v;
                                        td.style.textAlign = 'center';
                                        tr.appendChild(td);
                                    });
                                });

                                return tr;
                            }

                            rows.forEach(d => tbody.appendChild(buildDataRow(d, false)));
                            if (totalRow) tbody.appendChild(buildDataRow(totalRow, true));
                            table.appendChild(tbody);
                            tableWrap.appendChild(table);
                            section.appendChild(tableWrap);

                            // Chart container
                            const chartDiv = document.createElement('div');
                            chartDiv.style.cssText = 'width:100%;height:400px;margin-bottom:24px;';
                            section.appendChild(chartDiv);
                            content.appendChild(section);

                            // Render ECharts
                            const chart = echarts.init(chartDiv);
                            chartInstances.push(chart);

                            const xLabels = rows.map(r => r.class_name || 'N/A');
                            const gradesForChart = [...gradeColumns, 'NS'];

                            const series = gradesForChart.flatMap(grade => {
                                const mData = rows.map(r => r.grades?.[grade]?.M ?? 0);
                                const fData = rows.map(r => r.grades?.[grade]?.F ?? 0);
                                const tData = rows.map((r, i) => mData[i] + fData[i]);
                                return [
                                    {
                                        name: grade + ' (M)',
                                        type: 'bar',
                                        stack: 'Male',
                                        color: echartsColors[grade] || '#ccc',
                                        data: mData
                                    },
                                    {
                                        name: grade + ' (F)',
                                        type: 'bar',
                                        stack: 'Female',
                                        color: echartsColors[grade] || '#ccc',
                                        data: fData,
                                        itemStyle: { borderColor: '#555', borderWidth: 0.5 }
                                    },
                                    {
                                        name: grade + ' (T)',
                                        type: 'line',
                                        lineStyle: { type: 'dashed', width: 2 },
                                        color: echartsColors[grade] || '#ccc',
                                        data: tData
                                    }
                                ];
                            });

                            chart.setOption({
                                title: {
                                    text: subjectName + ' - Grade Distribution',
                                    left: 'center',
                                    textStyle: { fontSize: 14 }
                                },
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: { type: 'shadow' }
                                },
                                legend: {
                                    top: 30,
                                    type: 'scroll',
                                    data: gradesForChart.flatMap(g => [g + ' (M)', g + ' (F)', g + ' (T)'])
                                },
                                grid: {
                                    top: 80,
                                    bottom: 30,
                                    left: '3%',
                                    right: '4%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: 'category',
                                    data: xLabels,
                                    axisLabel: { interval: 0, rotate: 30 }
                                },
                                yAxis: {
                                    type: 'value',
                                    name: 'Number of Students'
                                },
                                toolbox: {
                                    right: 20,
                                    feature: {
                                        saveAsImage: {},
                                        dataView: {},
                                        magicType: { type: ['line', 'bar', 'stack'] },
                                        restore: {}
                                    }
                                },
                                series: series
                            });

                            // Resize observer
                            const ro = new ResizeObserver(() => chart.resize());
                            ro.observe(chartDiv);
                        });
                    });
                })
                .catch(err => {
                    loading.classList.add('d-none');
                    content.innerHTML = '<div class="alert alert-danger">Failed to load academic data. Please try again.</div>';
                    console.error('Academic data error:', err);
                });
            }

            // Listen for tab activation
            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.getAttribute('href') === '#academic') {
                        if (!academicLoaded) {
                            academicLoaded = true;
                            const sel = document.getElementById('academicTermSelect');
                            fetchAcademicData(sel.value || null);
                        }
                        // Resize charts when tab becomes visible
                        chartInstances.forEach(c => c.resize());
                    }
                });
            });

            // Term dropdown change
            document.getElementById('academicTermSelect').addEventListener('change', function() {
                fetchAcademicData(this.value);
            });
        })();
    </script>
@endsection

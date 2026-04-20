@extends('layouts.master')
@section('title')
    Edit {{ $student->full_name ?? '' . ' Information' }}
@endsection
@section('css')
    <style>
        /* Remove card border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        /* Status Badge */
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

        .status-graduated {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-transferred {
            background: #fef3c7;
            color: #92400e;
        }

        .status-suspended {
            background: #fecaca;
            color: #dc2626;
        }

        .status-deceased {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Student Type Badge */
        .student-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            margin-left: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            vertical-align: middle;
        }

        /* Tab Styling */
        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
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

        @media (max-width: 768px) {
            .form-tabs .nav-tabs .nav-link {
                padding: 12px 16px;
                font-size: 13px;
            }

            .form-tabs .nav-tabs .nav-link i {
                margin-right: 4px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        /* Help Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
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
            line-height: 1.5;
            margin: 0;
        }

        .activity-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .activity-summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }

        .activity-summary-card .summary-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
        }

        .activity-summary-card .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-top: 6px;
        }

        .activity-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
        }

        .activity-panel-title {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .activity-panel-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .activity-summary-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .activity-summary-item {
            border: 1px solid #eef2f7;
            border-radius: 10px;
            padding: 14px 16px;
            background: #fbfdff;
        }

        .activity-summary-item-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .activity-summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .activity-summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
        }

        .activity-summary-chip.activity-pill-muted {
            background: #e5eefb;
            color: #31528f;
        }

        .activity-summary-chip.activity-pill-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .activity-summary-chip.activity-pill-success {
            background: #dcfce7;
            color: #166534;
        }

        .activity-summary-chip.activity-pill-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .activity-summary-note {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .activity-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Form Section */
        .form-section {
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        /* Grade Form */
        .jce-grade-form {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 1.5rem;
        }

        .jce-grade-form .form-label {
            font-weight: bold;
            color: #495057;
        }

        .jce-grade-form .form-select {
            border-color: #ced4da;
        }

        /* Hide file input button */
        input[type="file"]::-webkit-file-upload-button {
            display: none;
        }

        input[type="file"]::file-selector-button {
            display: none;
        }

        /* Form Labels */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
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

        /* Select2 - match form-select styling */
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: #374151;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Form Controls - Input Box Styling */
        .form-control,
        .form-select {
            width: 100%;
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

        /* Button Styling (Design Standards) */
        .btn {
            padding: 10px 16px;
            border-radius: 3px;
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

        /* Action Buttons (Tables) */
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
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
        }

        .action-buttons .btn i {
            font-size: 16px;
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

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        /* Photo Card */
        .photo-card {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 3px;
            padding: 16px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .photo-card:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
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
            flex-shrink: 0;
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

        .existing-file {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f0fdf4;
            border-radius: 3px;
            border: 1px solid #bbf7d0;
        }

        .existing-file i {
            color: #16a34a;
        }

        .existing-file a {
            color: #16a34a;
            font-weight: 500;
            text-decoration: none;
        }

        .existing-file a:hover {
            text-decoration: underline;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            Edit Student
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
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">
                                {{ $student->full_name }}
                                @if ($student->type)
                                    <span class="student-type-badge"
                                        style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                        <i class="fas fa-universal-access" style="font-size: 10px;"></i>
                                        {{ $student->type->type }}
                                    </span>
                                @endif
                            </h1>
                            <div class="page-subtitle">Edit student information</div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span
                                class="status-badge status-{{ strtolower($student->status ?? 'current') }}">{{ $student->status ?? 'Current' }}</span>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="form-tabs">
                        <div class="form-tabs-scroll">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-id-card"></i></span>
                                        <span class="d-none d-sm-block"><i class="fas fa-id-card me-2"></i>Basic
                                            Information</span>
                                    </a>
                                </li>
                                @if ($showPsleTab ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#psle" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-award"></i></span>
                                            <span class="d-none d-sm-block"><i class="fas fa-award me-2"></i>PSLE</span>
                                        </a>
                                    </li>
                                @endif
                                @if ($showJceTab ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#jce" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-certificate"></i></span>
                                            <span class="d-none d-sm-block"><i class="fas fa-certificate me-2"></i>JCE</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-graduation-cap"></i></span>
                                        <span class="d-none d-sm-block"><i
                                                class="fas fa-graduation-cap me-2"></i>Academic</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#activities-summary" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-running"></i></span>
                                        <span class="d-none d-sm-block"><i class="fas fa-running me-2"></i>Activities</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#books" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-book-open"></i></span>
                                        <span class="d-none d-sm-block"><i class="fas fa-book-open me-2"></i>Books</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#departures" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-door-open"></i></span>
                                        <span class="d-none d-sm-block"><i class="fas fa-door-open me-2"></i>Leaving</span>
                                    </a>
                                </li>
                                @can('students-health')
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-notes-medical"></i></span>
                                            <span class="d-none d-sm-block"><i class="fas fa-notes-medical me-2"></i>Health</span>
                                        </a>
                                    </li>
                                @endcan
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-exclamation-triangle"></i></span>
                                        <span class="d-none d-sm-block"><i
                                                class="fas fa-exclamation-triangle me-2"></i>Behaviour</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Tab panes -->
                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="home1" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Profile</div>
                                <p class="help-content">View and update the student's personal, academic, and family
                                    information. Fields marked with <span style="color:red;">*</span> are required.</p>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <form class="needs-validation" method="post"
                                        action="{{ route('students.update', $student->id) }}" novalidate
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('POST')
                                        <input type="hidden" name="last_updated_by"
                                            value="{{ auth()->user()->full_name }}" required>

                                        <div class="row">
                                            <!-- Left Column (Form Fields) -->
                                            <div class="col-lg-10 col-md-9">
                                                <!-- Personal Details Section -->
                                                <div class="form-section">
                                                    <div class="section-title">Personal Details</div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="first_name">First Name
                                                                    <span style="color:red;">*</span></label>
                                                                <div class="input-icon-group">
                                                                    <i class="fas fa-user input-icon"></i>
                                                                    <input type="text" class="form-control"
                                                                        name="first_name" id="first_name"
                                                                        placeholder="First name"
                                                                        value="{{ old('first_name', $student->first_name) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="last_name">Last Name <span
                                                                        style="color:red;">*</span></label>
                                                                <div class="input-icon-group">
                                                                    <i class="fas fa-user input-icon"></i>
                                                                    <input type="text" class="form-control"
                                                                        name="last_name" id="last_name"
                                                                        placeholder="Last name"
                                                                        value="{{ old('last_name', $student->last_name) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="date_of_birth">Date of
                                                                    Birth <span style="color:red;">*</span></label>
                                                                <div class="input-icon-group flatpickr-wrapper" data-toggle="student-edit-dob-picker">
                                                                    <i class="fas fa-calendar input-icon"></i>
                                                                    <input type="text"
                                                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                                                        name="date_of_birth" id="date_of_birth" data-input
                                                                        value="{{ old('date_of_birth', $student->formatted_date_of_birth) }}"
                                                                        placeholder="dd/mm/yyyy" maxlength="10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="gender">Gender <span
                                                                        style="color:red;">*</span></label>
                                                                <select
                                                                    class="form-select @error('gender') is-invalid @enderror"
                                                                    name="gender" id="gender">
                                                                    <option value="">Select ...</option>
                                                                    <option value="M"
                                                                        {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>
                                                                        Male</option>
                                                                    <option value="F"
                                                                        {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>
                                                                        Female</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="id_number">ID No. <span
                                                                        style="color:red;">*</span></label>
                                                                <div class="input-icon-group">
                                                                    <i class="fas fa-id-badge input-icon"></i>
                                                                    <input type="text"
                                                                        class="form-control @error('id_number') is-invalid @enderror"
                                                                        name="id_number" id="id_number"
                                                                        placeholder="XXX XXX XXX"
                                                                        value="{{ old('id_number', $student->formatted_id_number) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="nationality">Nationality
                                                                    <span style="color:red;">*</span></label>
                                                                <select
                                                                    class="form-select @error('nationality') is-invalid @enderror"
                                                                    name="nationality" id="nationality" data-trigger>
                                                                    <option value="">Select Nationality ...</option>
                                                                    @foreach ($nationalities as $nationality)
                                                                        <option value="{{ $nationality->name }}"
                                                                            {{ old('nationality', $student->nationality) == $nationality->name ? 'selected' : '' }}>
                                                                            {{ $nationality->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="email">Email</label>
                                                                <div class="input-icon-group">
                                                                    <i class="fas fa-envelope input-icon"></i>
                                                                    <input type="text" class="form-control" name="email"
                                                                        id="email" placeholder="student@gmail.com"
                                                                        value="{{ old('email', $student->email) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="exam_number">Candidate
                                                                    Number</label>
                                                                <div class="input-icon-group">
                                                                    <i class="fas fa-id-badge input-icon"></i>
                                                                    <input type="text" class="form-control"
                                                                        name="exam_number" id="exam_number"
                                                                        placeholder="H34872557"
                                                                        value="{{ old('exam_number', $student->exam_number) }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Academic Details Section -->
                                                <div class="form-section">
                                                    <div class="section-title">Academic Details</div>
                                                    <div class="row">
                                                        @php $studentGrade = $student->studentTerm()->grade->id ?? 0; @endphp
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="grade">Grade <span
                                                                        style="color:red;">*</span></label>
                                                                <select name="grade_id" id="grade"
                                                                    class="form-select">
                                                                    <option value="">Select Grade ...</option>
                                                                    @foreach ($grades as $grade)
                                                                        <option value="{{ $grade->id }}"
                                                                            {{ $studentGrade == $grade->id ? 'selected' : '' }}>
                                                                            {{ $grade->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        @php $studentClass = optional($student->currentClass())->id ?? 0; @endphp
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="klass">Class <a
                                                                        href="{{ route('academic.index') }}"><i
                                                                            class="fas fa-external-link-alt"
                                                                            style="font-size: 11px;"></i></a></label>
                                                                <select name="klass_id" id="klass"
                                                                    class="form-select">
                                                                    <option value="">Select Class ...</option>
                                                                    @foreach ($classes as $class)
                                                                        <option value="{{ $class->id }}"
                                                                            {{ $studentClass == $class->id ? 'selected' : '' }}>
                                                                            {{ $class->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="status">Status <span
                                                                        style="color:red;">*</span></label>
                                                                <select
                                                                    class="form-select @error('status') is-invalid @enderror"
                                                                    name="status" id="status" data-trigger>
                                                                    <option value="">Select Status ...</option>
                                                                    @foreach ($statuses as $status)
                                                                        <option value="{{ $status->name }}"
                                                                            {{ old('status', $student->status) == $status->name ? 'selected' : '' }}>
                                                                            {{ $status->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="type">Type <a
                                                                        href="{{ route('students.students-settings') }}"><i
                                                                            class="fas fa-external-link-alt"
                                                                            style="font-size: 11px;"></i></a></label>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <select
                                                                        class="form-select @error('type') is-invalid @enderror"
                                                                        name="student_type_id" id="type">
                                                                        <option value="" data-color="">Select Type
                                                                            ...</option>
                                                                        @foreach ($types as $type)
                                                                            <option value="{{ $type->id }}"
                                                                                data-color="{{ $type->color ?? '' }}"
                                                                                {{ old('student_type_id', $student->student_type_id) == $type->id ? 'selected' : '' }}>
                                                                                {{ $type->type }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span id="typeColorIndicator"
                                                                        style="display: {{ $student->type && $student->type->color ? 'inline-block' : 'none' }};
                                                                               width: 32px; height: 32px; border-radius: 6px; flex-shrink: 0;
                                                                               background-color: {{ $student->type->color ?? '#e5e7eb' }};
                                                                               border: 2px solid rgba(0,0,0,0.1);"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="house">House <a
                                                                        href="{{ route('house.index') }}"><i
                                                                            class="fas fa-external-link-alt"
                                                                            style="font-size: 11px;"></i></a></label>
                                                                <select name="house" id="house"
                                                                    class="form-select @error('house') is-invalid @enderror">
                                                                    <option value="">Select House ...</option>
                                                                    @foreach ($houses as $house)
                                                                        <option value="{{ $house->id }}"
                                                                            {{ old('house', $student->house ? $student->house->id : null) == $house->id ? 'selected' : '' }}>
                                                                            {{ $house->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="filter">Filter <a
                                                                        href="{{ route('students.students-settings') }}"><i
                                                                            class="fas fa-external-link-alt"
                                                                            style="font-size: 11px;"></i></a></label>
                                                                <select
                                                                    class="form-select @error('filter') is-invalid @enderror"
                                                                    name="student_filter_id" id="filter" data-trigger>
                                                                    <option value="">Select Filter ...</option>
                                                                    @foreach ($filters as $filter)
                                                                        <option value="{{ $filter->id }}"
                                                                            {{ old('student_filter_id', $student->student_filter_id) == $filter->id ? 'selected' : '' }}>
                                                                            {{ $filter->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Family & Sponsor Section -->
                                                <div class="form-section">
                                                    <div class="section-title">Family & Sponsor</div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="sponsor_id">Sponsor <span
                                                                        style="color:red;">*</span>
                                                                    @if ($student->sponsor)
                                                                        <a
                                                                            href="{{ route('sponsors.sponsor-edit', $student->sponsor->id) }}">
                                                                            <i class="fas fa-external-link-alt"
                                                                                style="font-size: 11px;"></i>
                                                                        </a>
                                                                    @else
                                                                        <small style="padding: 2px;"
                                                                            class="bg-warning text-white rounded">No
                                                                            sponsor</small>
                                                                    @endif
                                                                </label>
                                                                <select
                                                                    class="form-select @error('sponsor_id') is-invalid @enderror"
                                                                    name="sponsor_id" id="sponsor_id">
                                                                    <option value="">Select Sponsor ...</option>
                                                                    @foreach ($sponsors as $sponsor)
                                                                        <option value="{{ $sponsor->id }}"
                                                                            {{ old('sponsor_id', $student->sponsor_id) == $sponsor->id ? 'selected' : '' }}>
                                                                            {{ $sponsor->full_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="parent_is_staff">Parent is
                                                                    Staff</label>
                                                                <select class="form-select" name="parent_is_staff"
                                                                    id="parent_is_staff" data-trigger>
                                                                    <option value="">Select ...</option>
                                                                    <option value="1"
                                                                        {{ old('parent_is_staff', $student->parent_is_staff) == true ? 'selected' : '' }}>
                                                                        Yes</option>
                                                                    <option value="0"
                                                                        {{ old('parent_is_staff', $student->parent_is_staff) == false ? 'selected' : '' }}>
                                                                        No</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        @if($school_data->boarding)
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="is_boarding">Boarding Status</label>
                                                                <select class="form-select" name="is_boarding"
                                                                    id="is_boarding" data-trigger>
                                                                    <option value="0"
                                                                        {{ !old('is_boarding', $student->is_boarding) ? 'selected' : '' }}>
                                                                        Day</option>
                                                                    <option value="1"
                                                                        {{ old('is_boarding', $student->is_boarding) ? 'selected' : '' }}>
                                                                        Boarding</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right Column (Photo Section) -->
                                            <div class="col-lg-2 col-md-3">
                                                <div class="photo-card">
                                                    <label for="photoInput" style="cursor: pointer; display: block;">
                                                        <img id="imagePreview"
                                                            src="{{ $student->photo_path ? asset($student->photo_path) : asset('assets/images/users/default-profile.png') }}"
                                                            class="img-fluid rounded w-100"
                                                            style="max-height: 180px; object-fit: contain;"
                                                            alt="Student Photo"
                                                            onerror="this.src='{{ asset('assets/images/users/default-profile.png') }}'">
                                                    </label>
                                                    <input type="file" class="form-control" id="photoInput"
                                                        name="photo_path" accept="image/*" style="display: none;">
                                                    <small class="text-muted d-block mt-2">Click to upload photo<br>(3.5 x
                                                        4.5 cm)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Buttons and Last Edited Section -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-end">
                                                    @can('manage-students')
                                                        @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                                            <a href="{{ route('students.delete-student', $student->id) }}"
                                                                class="btn btn-danger me-2"
                                                                onclick="return confirm('Are you sure you want to delete this student?')">
                                                                <i class="fas fa-trash me-1"></i> Delete
                                                            </a>
                                                        @endif
                                                    @endcan
                                                    <a href="{{ route('students.index') }}"
                                                        class="btn btn-secondary me-2">
                                                        <i class="fas fa-arrow-left me-1"></i> Back
                                                    </a>
                                                    @if (Auth::user()->can('manage-students') || (!session('is_past_term') && !Auth::user()->hasRoles('Teacher')))
                                                        <button type="submit" class="btn btn-primary btn-loading">
                                                            <span class="btn-text"><i class="fas fa-save me-1"></i>
                                                                Update</span>
                                                            <span class="btn-spinner d-none">
                                                                <span class="spinner-border spinner-border-sm me-2"
                                                                    role="status" aria-hidden="true"></span>
                                                                Saving...
                                                            </span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            if (is_numeric($student->last_updated_by)) {
                                                $user = \App\Models\User::find($student->last_updated_by);
                                                $editor = $user?->name ?? 'Unknown User';
                                            } else {
                                                $editor = $student->last_updated_by ?: 'Admin';
                                            }
                                        @endphp
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap gap-4" style="font-size: 13px;">
                                                    <div class="d-flex align-items-center text-muted">
                                                        <i class="fas fa-calendar-plus me-2" style="color: #10b981;"></i>
                                                        <span>
                                                            <strong>Created:</strong>
                                                            {{ $student->created_at->format('d M, Y') }}
                                                            <span
                                                                class="text-secondary ms-1">({{ $student->created_at->diffForHumans() }})</span>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center text-muted">
                                                        <i class="fas fa-edit me-2" style="color: #3b82f6;"></i>
                                                        <span>
                                                            <strong>Last Edited:</strong>
                                                            {{ $student->updated_at->format('d M, Y H:i') }}
                                                            by {{ $editor }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if ($showPsleTab ?? false)
                            <div class="tab-pane" id="psle" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">PSLE Results</div>
                                    <p class="help-content">Record the student's Primary School Leaving Examination grades.
                                        These results are used for academic tracking and reporting.</p>
                                </div>
                                <form action="{{ route('students.create-psle', $student->id) }}" method="POST"
                                    class="jce-grade-form">
                                    @csrf
                                    <div class="row g-3">
                                        @php
                                            $subjects = [
                                                'overall_grade' => 'Overall',
                                                'mathematics_grade' => 'Mathematics',
                                                'english_grade' => 'English',
                                                'science_grade' => 'Science',
                                                'setswana_grade' => 'Setswana',
                                                'agriculture_grade' => 'Agriculture',
                                                'social_studies_grade' => 'Social Studies',
                                                'religious_and_moral_education_grade' => 'Religious & Moral Education',
                                            ];
                                            $grades = ['A', 'B', 'C', 'D', 'E', 'F', 'U'];
                                        @endphp

                                        @foreach ($subjects as $key => $subject)
                                            <div class="col-md-4 mb-3">
                                                <label for="{{ $key }}"
                                                    class="form-label">{{ $subject }}</label>
                                                <select name="{{ $key }}" id="{{ $key }}"
                                                    class="form-select @error($key) is-invalid @enderror">
                                                    <option value="">Select Grade</option>
                                                    @foreach ($grades as $grade)
                                                        <option value="{{ $grade }}"
                                                            {{ old($key, $student->psle->$key ?? '') == $grade ? 'selected' : '' }}>
                                                            {{ $grade }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error($key)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> Save PSLE
                                                Grades</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if ($showJceTab ?? false)
                            <div class="tab-pane" id="jce" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">JCE Results</div>
                                    <p class="help-content">Record the student's Junior Certificate Examination grades.
                                        These results are used for academic tracking and subject selection.</p>
                                </div>
                                <form action="{{ route('students.create-jce', $student->id) }}" method="POST"
                                    class="jce-grade-form">
                                    @csrf
                                    <div class="row g-3">
                                        @php
                                            $subjects = [
                                                'overall' => 'Overall',
                                                'mathematics' => 'Mathematics',
                                                'english' => 'English',
                                                'science' => 'Science',
                                                'setswana' => 'Setswana',
                                                'design_and_technology' => 'Design & Technology',
                                                'home_economics' => 'Home Economics',
                                                'agriculture' => 'Agriculture',
                                                'social_studies' => 'Social Studies',
                                                'moral_education' => 'Moral Education',
                                                'music' => 'Music',
                                                'physical_education' => 'Physical Education',
                                                'religious_education' => 'Religious Education',
                                                'art' => 'Art',
                                                'office_procedures' => 'Office Procedures',
                                                'accounting' => 'Accounting',
                                                'french' => 'French',
                                            ];
                                            $grades = ['A', 'B', 'C', 'D', 'E', 'F', 'U'];
                                        @endphp

                                        @foreach ($subjects as $key => $subject)
                                            <div class="col-md-4 mb-3">
                                                <label for="{{ $key }}"
                                                    class="form-label">{{ $subject }}</label>
                                                <select name="{{ $key }}" id="{{ $key }}"
                                                    class="form-select">
                                                    <option value="">Select Grade</option>
                                                    @foreach ($grades as $grade)
                                                        <option value="{{ $grade }}"
                                                            {{ old($key, $student->jce->$key ?? '') == $grade ? 'selected' : '' }}>
                                                            {{ $grade }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> Save JCE Grades</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="tab-pane" id="profile1" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Academic Performance </div>
                                <p class="help-content">View the student's exam results, grades, and teacher comments
                                    across all terms. This provides a comprehensive academic history.</p>
                            </div>

                            @if (($showPsleTab ?? false) && $student->psle && $student->psle->overall_grade)
                                @php
                                    $psleGradeBgTop = match (strtoupper($student->psle->overall_grade)) {
                                        'MERIT' => 'purple',
                                        'A' => 'success',
                                        'B' => 'primary',
                                        'C' => 'info',
                                        'D' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded border">
                                    <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                    <strong class="me-2">PSLE Grade:</strong>
                                    <span
                                        class="badge bg-{{ $psleGradeBgTop }} fs-6">{{ $student->psle->overall_grade }}</span>
                                </div>
                            @elseif (($showJceTab ?? false) && $student->jce && $student->jce->overall)
                                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded border">
                                    <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                    <strong class="me-2">JCE Grade:</strong>
                                    <span class="badge bg-secondary fs-6">{{ $student->jce->overall }}</span>
                                </div>
                            @endif

                            @php
                                $examGroups = $student->tests
                                    ? $student->tests->where('type', 'Exam')->groupBy('term_id')
                                    : collect([]);
                            @endphp

                            @if ($examGroups->isNotEmpty())
                                <!-- Filters Row -->
                                <div class="row align-items-center mb-3">
                                    <div class="col-lg-8 col-md-8">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control"
                                                        placeholder="Search subject..." id="academicSearch">
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <select class="form-select" id="termFilter">
                                                    <option value="">All Terms</option>
                                                    @foreach ($examGroups as $termId => $termExams)
                                                        <option value="{{ $termId }}">Term
                                                            {{ $termExams->first()->term->term ?? '' }},
                                                            {{ $termExams->first()->year }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-sm-6">
                                                <button type="button" class="btn btn-light"
                                                    id="academicReset">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 text-end">
                                        <a href="{{ route('students.export-progress-report', $student->id) }}"
                                            class="btn btn-primary">
                                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                                        </a>
                                    </div>
                                </div>

                                <!-- Tables grouped by Term -->
                                @foreach ($examGroups as $termId => $termExams)
                                    @php
                                        $termCalc = $termCalculations[$termId] ?? null;
                                        $totalPoints = $termCalc['totalPoints'] ?? 0;
                                        $totalScore = $termCalc['totalScore'] ?? 0;
                                        $totalOutOf = $termCalc['totalOutOf'] ?? 0;
                                        $averagePercentage = $termCalc['averagePercentage'] ?? 0;
                                        $termOverallGrade = $termCalc['overallGrade'] ?? null;
                                        $firstExam = $termExams->first();
                                        $termNumber = $firstExam && $firstExam->term ? $firstExam->term->term : '';
                                        $termYear = $firstExam ? $firstExam->year ?? '' : '';
                                    @endphp
                                    <div class="term-group mb-4" data-term-id="{{ $termId }}">
                                        <div class="section-title">Term {{ $termNumber }}, {{ $termYear }}
                                            Performance</div>
                                        <div class="table-responsive">
                                            @if ($usesJuniorAcademicLayout ?? false)
                                                {{-- Junior: Shows Points column with total points --}}
                                                <table class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px;">#</th>
                                                            <th>Subject</th>
                                                            <th class="text-center" style="width: 100px;">Percentage</th>
                                                            <th class="text-center" style="width: 80px;">Points</th>
                                                            <th class="text-center" style="width: 80px;">Grade</th>
                                                            <th>Comments</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($termExams as $loopIndex => $test)
                                                            @php
                                                                $comment = $student
                                                                    ->getSubjectComment(
                                                                        $termId,
                                                                        $test->grade_subject_id ?? 0,
                                                                    )
                                                                    ->first();
                                                                $grade = $test->pivot->grade ?? null;
                                                                $gradeBg = match ($grade) {
                                                                    'A' => 'success',
                                                                    'B' => 'primary',
                                                                    'C' => 'info',
                                                                    'D' => 'warning',
                                                                    default => 'danger',
                                                                };
                                                                $subjectName =
                                                                    $test->subject && $test->subject->subject
                                                                        ? $test->subject->subject->name
                                                                        : 'Unknown Subject';
                                                            @endphp
                                                            <tr class="academic-row"
                                                                data-search="{{ strtolower($subjectName) }}"
                                                                data-term="{{ $termId }}">
                                                                <td class="fw-medium">{{ $loopIndex + 1 }}</td>
                                                                <td>{{ $subjectName }}</td>
                                                                <td class="text-center">
                                                                    {{ $test->pivot->percentage ?? 0 }}%</td>
                                                                <td class="text-center">{{ $test->pivot->points ?? 0 }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge bg-{{ $gradeBg }}">{{ $grade ?? '-' }}</span>
                                                                </td>
                                                                <td class="text-muted">
                                                                    {{ Str::limit($comment->remarks ?? '—', 50) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-light fw-bold">
                                                            <td colspan="3" class="text-end">Total Points:</td>
                                                            <td class="text-center">{{ $totalPoints }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            @elseif ($usesSeniorAcademicLayout ?? false)
                                                {{-- Senior: Shows Points column with Best 6 Subjects total --}}
                                                @php
                                                    $bestSubjects = $termCalc['bestSubjects'] ?? [];
                                                @endphp
                                                <table class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px;">#</th>
                                                            <th>Subject</th>
                                                            <th class="text-center" style="width: 100px;">Percentage</th>
                                                            <th class="text-center" style="width: 80px;">Points</th>
                                                            <th class="text-center" style="width: 80px;">Grade</th>
                                                            <th>Comments</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($termExams as $loopIndex => $test)
                                                            @php
                                                                $comment = $student
                                                                    ->getSubjectComment(
                                                                        $termId,
                                                                        $test->grade_subject_id ?? 0,
                                                                    )
                                                                    ->first();
                                                                $grade = $test->pivot->grade ?? null;
                                                                $gradeBg = match ($grade) {
                                                                    'A' => 'success',
                                                                    'B' => 'primary',
                                                                    'C' => 'info',
                                                                    'D' => 'warning',
                                                                    default => 'danger',
                                                                };
                                                                $subjectName =
                                                                    $test->subject && $test->subject->subject
                                                                        ? $test->subject->subject->name
                                                                        : 'Unknown Subject';
                                                                // Check if this subject is in the best 6
                                                                $isInBest6 = collect($bestSubjects)->contains(function (
                                                                    $best,
                                                                ) use ($subjectName) {
                                                                    return strtolower($best['subject']) ===
                                                                        strtolower($subjectName);
                                                                });
                                                            @endphp
                                                            <tr class="academic-row {{ $isInBest6 ? 'table-success' : '' }}"
                                                                data-search="{{ strtolower($subjectName) }}"
                                                                data-term="{{ $termId }}">
                                                                <td class="fw-medium">{{ $loopIndex + 1 }}</td>
                                                                <td>
                                                                    {{ $subjectName }}
                                                                    @if ($isInBest6)
                                                                        <i class="fas fa-star text-warning ms-1"
                                                                            title="Best 6"></i>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ $test->pivot->percentage ?? 0 }}%</td>
                                                                <td class="text-center">{{ $test->pivot->points ?? 0 }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge bg-{{ $gradeBg }}">{{ $grade ?? '-' }}</span>
                                                                </td>
                                                                <td class="text-muted">
                                                                    {{ Str::limit($comment->remarks ?? '—', 50) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-light fw-bold">
                                                            <td colspan="3" class="text-end">
                                                                Best 6 Subjects Total Points:
                                                            </td>
                                                            <td class="text-center">{{ $totalPoints }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            @elseif ($usesPrimaryAcademicLayout ?? false)
                                                {{-- Primary: Shows Possible Marks and Actual Marks (no Points) --}}
                                                <table class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px;">#</th>
                                                            <th>Subject</th>
                                                            <th class="text-center" style="width: 110px;">Possible Marks
                                                            </th>
                                                            <th class="text-center" style="width: 100px;">Actual Marks
                                                            </th>
                                                            <th class="text-center" style="width: 80px;">%</th>
                                                            <th class="text-center" style="width: 80px;">Grade</th>
                                                            <th>Comments</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($termExams as $loopIndex => $test)
                                                            @php
                                                                $comment = $student
                                                                    ->getSubjectComment(
                                                                        $termId,
                                                                        $test->grade_subject_id ?? 0,
                                                                    )
                                                                    ->first();
                                                                $grade = $test->pivot->grade ?? null;
                                                                $gradeBg = match ($grade) {
                                                                    'A' => 'success',
                                                                    'B' => 'primary',
                                                                    'C' => 'info',
                                                                    'D' => 'warning',
                                                                    default => 'danger',
                                                                };
                                                                $subjectName =
                                                                    $test->subject && $test->subject->subject
                                                                        ? $test->subject->subject->name
                                                                        : 'Unknown Subject';
                                                            @endphp
                                                            <tr class="academic-row"
                                                                data-search="{{ strtolower($subjectName) }}"
                                                                data-term="{{ $termId }}">
                                                                <td class="fw-medium">{{ $loopIndex + 1 }}</td>
                                                                <td>{{ $subjectName }}</td>
                                                                <td class="text-center">{{ $test->out_of ?? 100 }}</td>
                                                                <td class="text-center">{{ $test->pivot->score ?? 0 }}
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ $test->pivot->percentage ?? 0 }}%</td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge bg-{{ $gradeBg }}">{{ $grade ?? '-' }}</span>
                                                                </td>
                                                                <td class="text-muted">
                                                                    {{ Str::limit($comment->remarks ?? '—', 50) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-light fw-bold">
                                                            <td colspan="2" class="text-end">Total:</td>
                                                            <td class="text-center">{{ $totalOutOf }}</td>
                                                            <td class="text-center">{{ $totalScore }}</td>
                                                            <td class="text-center">{{ $averagePercentage }}%</td>
                                                            <td class="text-center">
                                                                @if ($termOverallGrade)
                                                                    @php
                                                                        $overallGradeBg = match (
                                                                            $termOverallGrade->grade ?? ''
                                                                        ) {
                                                                            'A' => 'success',
                                                                            'B' => 'primary',
                                                                            'C' => 'info',
                                                                            'D' => 'warning',
                                                                            default => 'danger',
                                                                        };
                                                                    @endphp
                                                                    <span
                                                                        class="badge bg-{{ $overallGradeBg }}">{{ $termOverallGrade->grade ?? '-' }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">-</span>
                                                                @endif
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-graduation-cap" style="font-size: 48px; opacity: 0.5;"></i>
                                    <p class="mt-3 mb-0">No exam marks recorded for this student.</p>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane" id="activities-summary" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Activities</div>
                                <p class="help-content">Review the student current activity participation and the billing state of activity charges captured for the selected term.</p>
                            </div>

                            <div class="activity-summary-grid">
                                <div class="activity-summary-card">
                                    <div class="summary-label">Active Activities</div>
                                    <div class="summary-value">{{ $activitySummary['summary']['active_count'] }}</div>
                                </div>
                                <div class="activity-summary-card">
                                    <div class="summary-label">Charges Raised</div>
                                    <div class="summary-value">{{ $activitySummary['summary']['charge_count'] }}</div>
                                </div>
                                <div class="activity-summary-card">
                                    <div class="summary-label">Posted Charges</div>
                                    <div class="summary-value">{{ $activitySummary['summary']['posted_count'] }}</div>
                                </div>
                                <div class="activity-summary-card">
                                    <div class="summary-label">Pending / Blocked</div>
                                    <div class="summary-value">{{ $activitySummary['summary']['pending_count'] }} / {{ $activitySummary['summary']['blocked_count'] }}</div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="activity-panel">
                                        <div class="activity-panel-title">Current Participation</div>
                                        <div class="activity-panel-subtitle">Activities where this student still has an active roster entry in the selected term.</div>

                                        @if ($activitySummary['activeEnrollments']->isNotEmpty())
                                            <div class="activity-summary-list">
                                                @foreach ($activitySummary['activeEnrollments'] as $enrollment)
                                                    <div class="activity-summary-item">
                                                        <div class="activity-summary-item-title">
                                                            {{ $enrollment->activity?->name ?: 'Unknown activity' }}
                                                        </div>
                                                        <div class="activity-summary-meta">
                                                            @if ($enrollment->activity?->code)
                                                                <span class="activity-summary-chip activity-pill-primary">{{ $enrollment->activity->code }}</span>
                                                            @endif
                                                            @if ($enrollment->gradeSnapshot?->name)
                                                                <span class="activity-summary-chip activity-pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                                            @endif
                                                            @if ($enrollment->klassSnapshot?->name)
                                                                <span class="activity-summary-chip activity-pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                                            @endif
                                                            <span class="activity-summary-chip activity-pill-success">Joined {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted">No active activity participation is recorded for this student in the selected term.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="activity-panel">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="activity-panel-title">Activity Charge State</div>
                                                <div class="activity-panel-subtitle">Posted charges link to the annual invoice. Pending or blocked charges need invoice follow-up.</div>
                                            </div>
                                            @can('collect-fees')
                                                <a href="{{ route('fees.collection.students.account', $student) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-wallet me-1"></i> Fee Account
                                                </a>
                                            @endcan
                                        </div>

                                        @if ($activitySummary['charges']->isNotEmpty())
                                            <div class="activity-summary-list">
                                                @foreach ($activitySummary['charges']->take(6) as $charge)
                                                    <div class="activity-summary-item">
                                                        <div class="activity-summary-item-title">
                                                            {{ $charge->activity?->name ?: 'Activity charge' }}
                                                            <span class="ms-2">{{ format_currency($charge->amount) }}</span>
                                                        </div>
                                                        <div class="activity-summary-meta">
                                                            <span class="activity-summary-chip {{ $charge->billing_status === \App\Models\Activities\ActivityFeeCharge::STATUS_POSTED ? 'activity-pill-success' : ($charge->billing_status === \App\Models\Activities\ActivityFeeCharge::STATUS_BLOCKED ? 'activity-pill-warning' : 'activity-pill-primary') }}">
                                                                {{ \App\Models\Activities\ActivityFeeCharge::statuses()[$charge->billing_status] ?? ucfirst($charge->billing_status) }}
                                                            </span>
                                                            <span class="activity-summary-chip activity-pill-muted">
                                                                {{ \App\Models\Activities\ActivityFeeCharge::chargeTypes()[$charge->charge_type] ?? ucfirst(str_replace('_', ' ', $charge->charge_type)) }}
                                                            </span>
                                                            @if ($charge->event?->title)
                                                                <span class="activity-summary-chip activity-pill-muted">{{ $charge->event->title }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="activity-summary-note">
                                                            @if ($charge->invoice)
                                                                Linked to invoice {{ $charge->invoice->invoice_number }} ({{ ucfirst($charge->invoice->status) }}).
                                                            @elseif ($charge->billing_status === \App\Models\Activities\ActivityFeeCharge::STATUS_PENDING)
                                                                Waiting for an active annual invoice before posting.
                                                            @else
                                                                Billing follow-up is required before this charge can post.
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted">No activity charges are recorded for this student in the selected term.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($activitySummary['historicalEnrollments']->isNotEmpty())
                                <div class="activity-panel mt-4">
                                    <div class="activity-panel-title">Recent Activity History</div>
                                    <div class="activity-panel-subtitle">Historical roster changes remain visible for audit and follow-up.</div>

                                    <div class="activity-summary-list">
                                        @foreach ($activitySummary['historicalEnrollments'] as $enrollment)
                                            <div class="activity-summary-item">
                                                <div class="activity-summary-item-title">{{ $enrollment->activity?->name ?: 'Unknown activity' }}</div>
                                                <div class="activity-summary-meta">
                                                    <span class="activity-summary-chip activity-pill-muted">{{ \App\Models\Activities\ActivityEnrollment::statuses()[$enrollment->status] ?? ucfirst($enrollment->status) }}</span>
                                                    @if ($enrollment->gradeSnapshot?->name)
                                                        <span class="activity-summary-chip activity-pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                                    @endif
                                                    @if ($enrollment->klassSnapshot?->name)
                                                        <span class="activity-summary-chip activity-pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                                    @endif
                                                </div>
                                                <div class="activity-summary-note">
                                                    Joined {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }},
                                                    left {{ optional($enrollment->left_at)->format('d M Y') ?: 'n/a' }}.
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane" id="books" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Textbook Management</div>
                                <p class="help-content">Track textbooks allocated to this student. You can allocate new
                                    books, view current allocations, and process returns.</p>
                            </div>

                            <!-- Filters Row -->
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-8 col-md-12">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-lg-4 col-md-4 col-sm-6">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" placeholder="Search book..."
                                                    id="bookSearch">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6">
                                            <select class="form-select" id="bookStatusFilter">
                                                <option value="">All Status</option>
                                                <option value="Active">Active</option>
                                                <option value="Returned">Returned</option>
                                                <option value="Overdue">Overdue</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <button type="button" class="btn btn-light" id="bookReset">Reset</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                                    <a href="{{ route('students.clearance-form', $student->id) }}"
                                        class="btn btn-outline-info me-2">
                                        <i class="fas fa-file-alt me-1"></i> Clearance
                                    </a>
                                    <a href="{{ route('students.get-book-allocation', $student->id) }}"
                                        class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Allocate Book
                                    </a>
                                </div>
                            </div>

                            @php
                                $hasAllocations = false;
                                foreach ($bookAllocations as $allocations) {
                                    if ($allocations->count() > 0) {
                                        $hasAllocations = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if ($hasAllocations)
                                <!-- Table -->
                                <div class="table-responsive">
                                    <table id="books-table" class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Book Title</th>
                                                <th>Accession No.</th>
                                                <th>Allocated</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                @can('students-view')
                                                    @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                                        <th class="text-end">Actions</th>
                                                    @endif
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $bookIndex = 0; @endphp
                                            @foreach ($bookAllocations as $gradeName => $allocations)
                                                @foreach ($allocations as $allocation)
                                                    @php
                                                        $bookIndex++;
                                                        $status = $allocation->return_date
                                                            ? 'Returned'
                                                            : ($allocation->due_date < now()
                                                                ? 'Overdue'
                                                                : 'Active');
                                                    @endphp
                                                    <tr class="book-row"
                                                        data-search="{{ strtolower($allocation->copy->book->title . ' ' . $allocation->accession_number) }}"
                                                        data-status="{{ $status }}">
                                                        <td class="fw-medium">{{ $bookIndex }}</td>
                                                        <td>{{ $allocation->copy->book->title }}</td>
                                                        <td class="text-muted">{{ $allocation->accession_number }}</td>
                                                        <td>{{ $allocation->allocation_date->format('M d, Y') }}</td>
                                                        <td>{{ $allocation->due_date->format('M d, Y') }}</td>
                                                        <td>
                                                            @if ($allocation->return_date)
                                                                <span class="badge bg-success">Returned</span>
                                                            @elseif($allocation->due_date < now())
                                                                <span class="badge bg-danger">Overdue</span>
                                                            @else
                                                                <span class="badge bg-primary">Active</span>
                                                            @endif
                                                        </td>
                                                        @can('students-view')
                                                            @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                                                <td>
                                                                    <div class="action-buttons">
                                                                        <a href="{{ route('students.edit-book-allocation', ['id' => $student->id, 'allocationId' => $allocation->id]) }}"
                                                                            class="btn btn-sm btn-outline-warning"
                                                                            title="Return Book">
                                                                            <i class="fas fa-undo"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        @endcan
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-book-open" style="font-size: 48px; opacity: 0.5;"></i>
                                    <p class="mt-3 mb-0">No book allocations found for this student.</p>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane" id="departures" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Departure</div>
                                <p class="help-content">Record departure information when a student leaves the school.
                                    Include the last attendance date, reason for leaving, and destination school if
                                    applicable.</p>
                            </div>
                            <form action="{{ route('students.create-student-departures') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="text-muted mb-0">Departure Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="last_day_of_attendance" class="form-label">
                                                Last Day of Attendance<span style="color:red;">*</span>
                                            </label>
                                            <div class="input-icon-group flatpickr-wrapper" data-toggle="student-departure-picker">
                                                <i class="fas fa-calendar input-icon"></i>
                                                <input type="text" name="last_day_of_attendance"
                                                    id="last_day_of_attendance"
                                                    class="form-control @error('last_day_of_attendance') is-invalid @enderror"
                                                    data-input
                                                    value="{{ old('last_day_of_attendance', optional(optional($student->departure)->last_day_of_attendance)->format('d/m/Y')) }}"
                                                    placeholder="dd/mm/yyyy" maxlength="10" required>
                                            </div>
                                            @error('last_day_of_attendance')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="reason_for_leaving" class="form-label">
                                                Reason for Leaving<span style="color:red;">*</span>
                                            </label>
                                            @php
                                                $reasons = [
                                                    'Graduation',
                                                    'Transfer to another school',
                                                    'Relocation',
                                                    'Withdrawal',
                                                    'Dropout - Pregnancy',
                                                    'Illness',
                                                    'Expulsion',
                                                    'Other',
                                                ];
                                                $selectedReason = old(
                                                    'reason_for_leaving',
                                                    optional($student->departure)->reason_for_leaving,
                                                );
                                            @endphp
                                            <select name="reason_for_leaving" id="reason_for_leaving" data-trigger
                                                class="form-select @error('reason_for_leaving') is-invalid @enderror"
                                                required>
                                                <option value="" disabled {{ !$selectedReason ? 'selected' : '' }}>
                                                    Select a reason</option>
                                                @foreach ($reasons as $reason)
                                                    <option value="{{ $reason }}"
                                                        {{ $selectedReason == $reason ? 'selected' : '' }}>
                                                        {{ $reason }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('reason_for_leaving')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3" id="other_reason_group" style="display: none;">
                                            <label for="reason_for_leaving_other" class="form-label">
                                                Please specify other reason <span style="color:red;">*</span>
                                            </label>
                                            <input type="text" name="reason_for_leaving_other"
                                                id="reason_for_leaving_other"
                                                class="form-control @error('reason_for_leaving_other') is-invalid @enderror"
                                                placeholder="Moving out of town"
                                                value="{{ old('reason_for_leaving_other', optional($student->departure)->reason_for_leaving_other) }}">
                                            @error('reason_for_leaving_other')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="text-muted mb-0">Next Steps</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="new_school_name" class="form-label">Name of New School (if
                                                applicable)</label>
                                            <input type="text" name="new_school_name" id="new_school_name"
                                                class="form-control @error('new_school_name') is-invalid @enderror"
                                                placeholder="Chris Bail Prep School"
                                                value="{{ old('new_school_name', optional($student->departure)->new_school_name) }}">
                                            @error('new_school_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_school_contact_number" class="form-label">Contact Number of
                                                New
                                                School</label>
                                            <input type="text" name="new_school_contact_number"
                                                id="new_school_contact_number"
                                                class="form-control @error('new_school_contact_number') is-invalid @enderror"
                                                placeholder="391 4321"
                                                value="{{ old('new_school_contact_number', optional($student->departure)->new_school_contact_number) }}">
                                            @error('new_school_contact_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="text-muted mb-0">Outstanding Items</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Textbooks & Other School Property</label>
                                            <div class="form-check">
                                                <input type="radio" name="property_returned" id="property_returned_yes"
                                                    class="form-check-input @error('property_returned') is-invalid @enderror"
                                                    value="1"
                                                    {{ old('property_returned', optional($student->departure)->property_returned) === true ? 'checked' : '' }}
                                                    required>
                                                <label class="form-check-label" for="property_returned_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" name="property_returned" id="property_returned_no"
                                                    class="form-check-input @error('property_returned') is-invalid @enderror"
                                                    value="0"
                                                    {{ old('property_returned', optional($student->departure)->property_returned) === false ? 'checked' : '' }}
                                                    required>
                                                <label class="form-check-label" for="property_returned_no">No</label>
                                            </div>
                                            @error('property_returned')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', optional($student->departure)->notes) }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                    <div class="mt-3 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> Submit Departure
                                                Form</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Submitting...
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>

                        @can('students-health')
                            <div class="tab-pane" id="messages1" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">Health Information</div>
                                    <p class="help-content">Manage the student's medical records including allergies,
                                        conditions, medications, and emergency contacts. This information is confidential.</p>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <form class="needs-validation" method="post"
                                            action="{{ route('students.create-student-medicals') }}"
                                            enctype="multipart/form-data" novalidate>
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <div class="mb-3">
                                                <label for="health_history" class="form-label">Health History</label>
                                                <textarea name="health_history" id="health_history" class="form-control" rows="3">{{ old('health_history', optional($student->studentMedicals)->health_history ?? '') }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Blood Group</label><br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="a_negative"
                                                        value="A-"
                                                        {{ optional($student->studentMedicals)->a_negative != false ? 'checked' : '' }}
                                                        id="a_negative">
                                                    <label class="form-check-label" for="a_negative">A-</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="a_positive"
                                                        value="A+"
                                                        {{ optional($student->studentMedicals)->a_positive != false ? 'checked' : '' }}
                                                        id="a_positive">
                                                    <label class="form-check-label" for="a_positive">A+</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="b_positive"
                                                        value="B+"
                                                        {{ optional($student->studentMedicals)->b_positive != false ? 'checked' : '' }}
                                                        id="b_positive">
                                                    <label class="form-check-label" for="b_positive">B+</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="b_negative"
                                                        value="B-"
                                                        {{ optional($student->studentMedicals)->b_negative != false ? 'checked' : '' }}
                                                        id="b_negative">
                                                    <label class="form-check-label" for="b_negative">B-</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="ab_negative"
                                                        value="AB-"
                                                        {{ optional($student->studentMedicals)->ab_negative != false ? 'checked' : '' }}
                                                        id="ab_negative">
                                                    <label class="form-check-label" for="ab_negative">AB-</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="ab_positive"
                                                        value="AB+"
                                                        {{ optional($student->studentMedicals)->ab_positive != false ? 'checked' : '' }}
                                                        id="ab_positive">
                                                    <label class="form-check-label" for="ab_positive">AB+</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="o_negative"
                                                        value="O-"
                                                        {{ optional($student->studentMedicals)->o_negative != false ? 'checked' : '' }}
                                                        id="o_negative">
                                                    <label class="form-check-label" for="o_negative">O-</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="o_positive"
                                                        value="O+"
                                                        {{ optional($student->studentMedicals)->o_positive != false ? 'checked' : '' }}
                                                        id="o_positive">
                                                    <label class="form-check-label" for="o_positive">O+</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Immunization Records</label>
                                                <div class="custom-file-input">
                                                    <input type="file" name="immunization_records"
                                                        id="immunization_records" accept=".pdf,.jpg,.jpeg,.png">
                                                    <label for="immunization_records" class="file-input-label">
                                                        <div class="file-input-icon">
                                                            <i class="fas fa-file-medical"></i>
                                                        </div>
                                                        <div class="file-input-text">
                                                            <span class="file-label">Choose File</span>
                                                            <span class="file-hint" id="immunizationHint">PDF, JPG, PNG (Max
                                                                5MB)</span>
                                                            <span class="file-selected d-none"
                                                                id="immunizationFileName"></span>
                                                        </div>
                                                    </label>
                                                </div>
                                                @if (optional($student->studentMedicals)->immunization_records != null)
                                                    @php
                                                        $filePath = optional($student->studentMedicals)
                                                            ->immunization_records;
                                                        $fileUrl = asset('storage/' . $filePath);
                                                        $fileName = basename($filePath);
                                                    @endphp
                                                    <div class="existing-file">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Current file:</span>
                                                        <a href="{{ $fileUrl }}" target="_blank"
                                                            download>{{ $fileName }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Allergies & Food Preferences</label><br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="peanuts"
                                                        value="Peanuts"
                                                        {{ optional($student->studentMedicals)->peanuts != false ? 'checked' : '' }}
                                                        id="peanuts">
                                                    <label class="form-check-label" for="peanuts">Peanuts</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="red_meat"
                                                        value="Red meat"
                                                        {{ optional($student->studentMedicals)->red_meat != false ? 'checked' : '' }}
                                                        id="red_meat">
                                                    <label class="form-check-label" for="red_meat">Red Meat</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="vegetarian"
                                                        value="Vegetarian"
                                                        {{ optional($student->studentMedicals)->vegetarian != false ? 'checked' : '' }}
                                                        id="vegetarian">
                                                    <label class="form-check-label" for="vegetarian">Vegetarian</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="other_allergies" class="form-label">Other Allergies</label>
                                                <textarea name="other_allergies" id="other_allergies" class="form-control" rows="3">{{ old('other_allergies', optional($student->studentMedicals)->other_allergies ?? '') }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Limb Disabilities</label><br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="left_leg"
                                                        value="Left Leg"
                                                        {{ optional($student->studentMedicals)->left_leg != false ? 'checked' : '' }}
                                                        id="left_leg">
                                                    <label class="form-check-label" for="left_leg">Left Leg</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="right_leg"
                                                        value="Right Leg"
                                                        {{ optional($student->studentMedicals)->right_leg != false ? 'checked' : '' }}
                                                        id="right_leg">
                                                    <label class="form-check-label" for="right_leg">Right Leg</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="left_hand"
                                                        value="Left Arm"
                                                        {{ optional($student->studentMedicals)->left_hand != false ? 'checked' : '' }}
                                                        id="left_hand">
                                                    <label class="form-check-label" for="left_hand">Left Arm</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="right_hand"
                                                        value="Right Arm"
                                                        {{ optional($student->studentMedicals)->right_hand != false ? 'checked' : '' }}
                                                        id="right_hand">
                                                    <label class="form-check-label" for="right_hand">Right Arm</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="other_disabilities" class="form-label">Other
                                                    Disabilities</label>
                                                <textarea name="other_disabilities" id="other_disabilities" class="form-control" rows="3">{{ old('other_disabilities', optional($student->studentMedicals)->other_disabilities ?? '') }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Eye Sight & Hearing</label><br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="left_eye"
                                                        value="Left Eye"
                                                        {{ optional($student->studentMedicals)->left_eye != false ? 'checked' : '' }}
                                                        id="left_eye">
                                                    <label class="form-check-label" for="left_eye">Left Eye</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="right_eye"
                                                        value="Right Eye"
                                                        {{ optional($student->studentMedicals)->right_eye != false ? 'checked' : '' }}
                                                        id="right_eye">
                                                    <label class="form-check-label" for="right_eye">Right Eye</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="left_ear"
                                                        value="Left Ear"
                                                        {{ optional($student->studentMedicals)->left_ear != false ? 'checked' : '' }}
                                                        id="left_ear">
                                                    <label class="form-check-label" for="left_ear">Left Ear</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="right_ear"
                                                        value="Right Ear"
                                                        {{ optional($student->studentMedicals)->right_ear != false ? 'checked' : '' }}
                                                        id="right_ear">
                                                    <label class="form-check-label" for="right_ear">Right Ear</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="medical_conditions" class="form-label">Medical
                                                    Conditions</label>
                                                <textarea name="medical_conditions" id="medical_conditions" class="form-control" rows="3">{{ old('medical_conditions', optional($student->studentMedicals)->medical_conditions ?? '') }}</textarea>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <a class="btn btn-secondary me-2"
                                                    href="{{ route('students.show', $student->id) }}">
                                                    <i class="fas fa-arrow-left me-1"></i> Back
                                                </a>
                                                @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                                    <button type="submit" class="btn btn-primary btn-loading">
                                                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                                aria-hidden="true"></span>
                                                            Saving...
                                                        </span>
                                                    </button>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endcan

                        <div class="tab-pane" id="settings1" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Behaviour Records</div>
                                <p class="help-content">Track and manage student behaviour incidents. Record new incidents,
                                    view history, and monitor patterns over time.</p>
                            </div>

                            <!-- Filters Row -->
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-8 col-md-12">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-lg-4 col-md-4 col-sm-6">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" placeholder="Search..."
                                                    id="behaviourSearch">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6">
                                            <select class="form-select" id="behaviourTypeFilter">
                                                <option value="">All Types</option>
                                                <option value="Tardiness">Tardiness</option>
                                                <option value="Absence">Absence</option>
                                                <option value="Fighting">Fighting</option>
                                                <option value="Drugs & Narcotics">Drugs & Narcotics</option>
                                                <option value="Bullying">Bullying</option>
                                                <option value="Dressing">Dressing</option>
                                                <option value="Misconduct">Misconduct</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <button type="button" class="btn btn-light"
                                                id="behaviourReset">Reset</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                                    @if (Auth::user()->can('manage-students') || !session('is_past_term'))
                                        <a class="btn btn-primary"
                                            href="{{ route('students.add-student-behaviour', $student->id) }}">
                                            <i class="fas fa-plus me-1"></i> New Incident
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table id="behaviour-table" class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Date</th>
                                            <th>Behaviour Type</th>
                                            <th>Description</th>
                                            <th>Action Taken</th>
                                            @can('manage-students')
                                                <th class="text-end">Actions</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($student->studentBehaviour->isNotEmpty())
                                            @foreach ($student->studentBehaviour as $index => $behaviour)
                                                <tr class="behaviour-row"
                                                    data-search="{{ strtolower($behaviour->behaviour_type . ' ' . $behaviour->action_taken . ' ' . $behaviour->description) }}"
                                                    data-type="{{ $behaviour->behaviour_type }}">
                                                    <td class="fw-medium">{{ $index + 1 }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($behaviour->date)->format('M d, Y') }}
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-warning text-dark">{{ $behaviour->behaviour_type }}</span>
                                                    </td>
                                                    <td class="text-muted">
                                                        {{ Str::limit($behaviour->description, 50) ?? '—' }}</td>
                                                    <td class="text-muted">
                                                        {{ Str::limit($behaviour->action_taken, 50) ?? '—' }}</td>
                                                    @can('manage-students')
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="{{ route('students.edit-student-behaviour', ['studentId' => $student->id, 'id' => $behaviour->id]) }}"
                                                                    class="btn btn-sm btn-outline-info" title="Edit">
                                                                    <i class="bx bx-edit-alt"></i>
                                                                </a>
                                                                <a href="{{ route('students.remove-student-behaviour', $behaviour->id) }}"
                                                                    class="btn btn-sm btn-outline-danger" title="Delete"
                                                                    onclick="return confirm('Are you sure you want to delete this record?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            @if ($student->studentBehaviour->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-clipboard-check" style="font-size: 48px; opacity: 0.5;"></i>
                                    <p class="mt-3 mb-0">No behaviour incidents recorded for this student.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modals -->
                    <div class="modal" id="allocationModal" tabindex="-1" role="dialog"
                        data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title">Student Class Allocation</h6>
                                </div>
                                <div class="modal-body">
                                    <p>Modal body text goes here.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="bookModal" tabindex="-1" aria-labelledby="bookModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bookModalLabel">Allocate/Return Book</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="bookForm" action="{{ route('students.student-book-allocation') }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="operation" id="operation" value="allocate">
                                        <input type="hidden" name="allocation_id" id="allocation_id"
                                            value="">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="book_id" class="form-label">Book</label>
                                                <select name="book_id" id="book_id" class="form-select" required>
                                                    <option value="">Select Book</option>
                                                    @foreach ($books as $book)
                                                        <option value="{{ $book->id }}">{{ $book->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="accession_number" class="form-label">Accession
                                                    Number</label>
                                                <input type="text" name="accession_number" id="accession_number"
                                                    class="form-control" required>
                                            </div>
                                            <div class="col-md-6 allocate-only">
                                                <label for="allocation_date" class="form-label">Allocation Date</label>
                                                <div class="input-icon-group flatpickr-wrapper" data-toggle="student-allocation-picker">
                                                    <i class="fas fa-calendar input-icon"></i>
                                                    <input type="text" name="allocation_date" id="allocation_date"
                                                        class="form-control" data-input placeholder="dd/mm/yyyy" maxlength="10">
                                                </div>
                                            </div>
                                            <div class="col-md-6 allocate-only">
                                                <label for="due_date" class="form-label">Due Date</label>
                                                <div class="input-icon-group flatpickr-wrapper" data-toggle="student-due-date-picker">
                                                    <i class="fas fa-calendar input-icon"></i>
                                                    <input type="text" name="due_date" id="due_date"
                                                        class="form-control" data-input placeholder="dd/mm/yyyy" maxlength="10">
                                                </div>
                                            </div>
                                            <div class="col-md-6 allocate-only">
                                                <label for="condition_on_allocation" class="form-label">Condition on
                                                    Allocation</label>
                                                <select name="condition_on_allocation" id="condition_on_allocation"
                                                    class="form-select">
                                                    <option value="New">New</option>
                                                    <option value="Good">Good</option>
                                                    <option value="Fair">Fair</option>
                                                    <option value="Poor">Poor</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 return-only">
                                                <label for="return_date" class="form-label">Return Date</label>
                                                <div class="input-icon-group flatpickr-wrapper" data-toggle="student-return-date-picker">
                                                    <i class="fas fa-calendar input-icon"></i>
                                                    <input type="text" name="return_date" id="return_date"
                                                        class="form-control" data-input placeholder="dd/mm/yyyy" maxlength="10">
                                                </div>
                                            </div>
                                            <div class="col-md-6 return-only">
                                                <label for="condition_on_return" class="form-label">Condition on
                                                    Return</label>
                                                <select name="condition_on_return" id="condition_on_return"
                                                    class="form-select">
                                                    <option value="Good">Good</option>
                                                    <option value="Fair">Fair</option>
                                                    <option value="Poor">Poor</option>
                                                    <option value="Damaged">Damaged</option>
                                                    <option value="Lost">Lost</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label for="modal_notes" class="form-label">Notes</label>
                                                <textarea name="notes" id="modal_notes" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    @can('manage-students')
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary"
                                            onclick="submitForm()">Submit</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
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

            ['date_of_birth', 'last_day_of_attendance', 'allocation_date', 'due_date', 'return_date'].forEach(function(id) {
                const input = document.getElementById(id);
                if (input) {
                    formatDateInput(input);
                }
            });

            if (typeof flatpickr === 'function') {
                flatpickr('[data-toggle="student-edit-dob-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="student-departure-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    maxDate: 'today',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="student-allocation-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="student-due-date-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    disableMobile: true,
                });

                flatpickr('[data-toggle="student-return-date-picker"]', {
                    wrap: true,
                    allowInput: true,
                    dateFormat: 'd/m/Y',
                    disableMobile: true,
                });
            }

            // Initialize Select2 with search on sponsor dropdown
            $('#sponsor_id').select2({
                placeholder: 'Search sponsor...',
                allowClear: true,
                width: '100%',
            });

            const gradeSelect = document.getElementById('grade');
            const classSelect = document.getElementById('klass');
            let initialGradeIndex = gradeSelect.selectedIndex;

            gradeSelect.addEventListener('change', function() {
                const userConfirmed = window.confirm(
                    'If you are going to change the grade, make sure the class is changed to the right grade as well. Continue?'
                );

                if (userConfirmed) {
                    const gradeId = this.value;
                    classSelect.innerHTML = '<option value="">Select Class ...</option>';
                    classSelect.disabled = true;

                    var baseUrl =
                        "{{ route('students.classes-by-grade', ['gradeId' => 'tempGradeId']) }}";
                    var finalUrl = baseUrl.replace('tempGradeId', gradeId);

                    fetch(finalUrl)
                        .then(response => response.json())
                        .then(response => {
                            if (response.success) {
                                response.data.forEach(klass => {
                                    const option = document.createElement('option');
                                    option.value = klass.id;
                                    option.textContent = klass.name;
                                    classSelect.appendChild(option);
                                });
                            } else {
                                console.error('Error:', response.message);
                                alert('Failed to load classes. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to load classes. Please try again.');
                        })
                        .finally(() => {
                            classSelect.disabled = false;
                        });
                } else {
                    this.selectedIndex = initialGradeIndex;
                }
            });

            function activateTab(tabHref) {
                const tabToActivate = document.querySelector('a[href="' + tabHref + '"]');
                if (tabToActivate) {
                    const tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                }
            }

            const activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                activateTab(activeTab);
            }

            const tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    localStorage.setItem('activeTab', event.target.getAttribute('href'));
                });
            });

            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const currentTab = localStorage.getItem('activeTab');
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'active_tab';
                    hiddenField.value = currentTab;
                    this.appendChild(hiddenField);

                    // Loading button animation
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn && form.checkValidity()) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            const serverActiveTab = "{{ session('active_tab') }}";
            if (serverActiveTab) {
                activateTab(serverActiveTab);
                {{ session()->forget('active_tab') }};
            }

            function toggleOtherReason() {
                const reasonSelect = document.getElementById('reason_for_leaving');
                const otherReasonGroup = document.getElementById('other_reason_group');
                if (reasonSelect.value === 'Other') {
                    otherReasonGroup.style.display = 'block';
                } else {
                    otherReasonGroup.style.display = 'none';
                }
            }

            const reasonSelect = document.getElementById('reason_for_leaving');
            reasonSelect.addEventListener('change', toggleOtherReason);
            toggleOtherReason();

            // Immunization file input handler
            const immunizationInput = document.getElementById('immunization_records');
            if (immunizationInput) {
                const immunizationHint = document.getElementById('immunizationHint');
                const immunizationFileName = document.getElementById('immunizationFileName');

                immunizationInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const fileSize = file.size / 1024 / 1024; // Convert to MB

                        if (fileSize > 5) {
                            alert('File size exceeds 5MB. Please choose a smaller file.');
                            this.value = '';
                            immunizationHint.classList.remove('d-none');
                            immunizationFileName.classList.add('d-none');
                            return;
                        }

                        immunizationHint.classList.add('d-none');
                        immunizationFileName.classList.remove('d-none');
                        immunizationFileName.textContent = `${file.name} (${fileSize.toFixed(2)} MB)`;
                    } else {
                        immunizationHint.classList.remove('d-none');
                        immunizationFileName.classList.add('d-none');
                        immunizationFileName.textContent = '';
                    }
                });
            }

            // Student type color indicator
            const typeSelect = document.getElementById('type');
            const typeColorIndicator = document.getElementById('typeColorIndicator');
            if (typeSelect && typeColorIndicator) {
                typeSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const color = selectedOption.getAttribute('data-color');
                    if (color) {
                        typeColorIndicator.style.backgroundColor = color;
                        typeColorIndicator.style.display = 'inline-block';
                    } else {
                        typeColorIndicator.style.display = 'none';
                    }
                });
            }
        });

        // Table filtering
        $(document).ready(function() {
            // Behaviour table filtering
            const behaviourSearch = document.getElementById('behaviourSearch');
            const behaviourTypeFilter = document.getElementById('behaviourTypeFilter');
            const behaviourReset = document.getElementById('behaviourReset');

            function filterBehaviourTable() {
                const searchTerm = behaviourSearch ? behaviourSearch.value.toLowerCase() : '';
                const typeFilter = behaviourTypeFilter ? behaviourTypeFilter.value : '';
                const rows = document.querySelectorAll('.behaviour-row');

                rows.forEach(function(row) {
                    const searchData = row.getAttribute('data-search') || '';
                    const typeData = row.getAttribute('data-type') || '';

                    const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
                    const matchesType = typeFilter === '' || typeData === typeFilter;

                    row.style.display = (matchesSearch && matchesType) ? '' : 'none';
                });
            }

            if (behaviourSearch) behaviourSearch.addEventListener('keyup', filterBehaviourTable);
            if (behaviourTypeFilter) behaviourTypeFilter.addEventListener('change', filterBehaviourTable);
            if (behaviourReset) {
                behaviourReset.addEventListener('click', function() {
                    if (behaviourSearch) behaviourSearch.value = '';
                    if (behaviourTypeFilter) behaviourTypeFilter.value = '';
                    filterBehaviourTable();
                });
            }

            // Books table filtering
            const bookSearch = document.getElementById('bookSearch');
            const bookStatusFilter = document.getElementById('bookStatusFilter');
            const bookReset = document.getElementById('bookReset');

            function filterBookTable() {
                const searchTerm = bookSearch ? bookSearch.value.toLowerCase() : '';
                const statusFilter = bookStatusFilter ? bookStatusFilter.value : '';
                const rows = document.querySelectorAll('.book-row');

                rows.forEach(function(row) {
                    const searchData = row.getAttribute('data-search') || '';
                    const statusData = row.getAttribute('data-status') || '';

                    const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
                    const matchesStatus = statusFilter === '' || statusData === statusFilter;

                    row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                });
            }

            if (bookSearch) bookSearch.addEventListener('keyup', filterBookTable);
            if (bookStatusFilter) bookStatusFilter.addEventListener('change', filterBookTable);
            if (bookReset) {
                bookReset.addEventListener('click', function() {
                    if (bookSearch) bookSearch.value = '';
                    if (bookStatusFilter) bookStatusFilter.value = '';
                    filterBookTable();
                });
            }

            // Academic table filtering
            const academicSearch = document.getElementById('academicSearch');
            const termFilter = document.getElementById('termFilter');
            const academicReset = document.getElementById('academicReset');

            function filterAcademicTable() {
                const searchTerm = academicSearch ? academicSearch.value.toLowerCase() : '';
                const termValue = termFilter ? termFilter.value : '';
                const termGroups = document.querySelectorAll('.term-group');

                termGroups.forEach(function(group) {
                    const groupTermId = group.getAttribute('data-term-id') || '';
                    const rows = group.querySelectorAll('.academic-row');
                    let visibleCount = 0;

                    // If filtering by term and this group doesn't match, hide entire group
                    if (termValue !== '' && groupTermId !== termValue) {
                        group.style.display = 'none';
                        return;
                    }

                    // Filter individual rows by search term
                    rows.forEach(function(row) {
                        const searchData = row.getAttribute('data-search') || '';
                        const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);

                        if (matchesSearch) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Hide group if no visible rows after search
                    group.style.display = visibleCount > 0 ? '' : 'none';
                });
            }

            if (academicSearch) academicSearch.addEventListener('keyup', filterAcademicTable);
            if (termFilter) termFilter.addEventListener('change', filterAcademicTable);
            if (academicReset) {
                academicReset.addEventListener('click', function() {
                    if (academicSearch) academicSearch.value = '';
                    if (termFilter) termFilter.value = '';
                    filterAcademicTable();
                });
            }
        });

        function submitForm() {
            const form = document.getElementById('bookForm');
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#bookModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
        }

        $('#bookModal').on('show.bs.modal', function(e) {
            const button = e.relatedTarget;
        });

        const photoInput = document.getElementById('photoInput');
        const imagePreview = document.getElementById('imagePreview');

        CropHelper.init(photoInput, function(blob) {
            // Replace the file input contents with the cropped blob
            try {
                var dt = new DataTransfer();
                dt.items.add(new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
                photoInput.files = dt.files;
            } catch (e) {
                photoInput._croppedBlob = blob;
            }

            // Update the preview image
            var url = URL.createObjectURL(blob);
            if (imagePreview._objectUrl) {
                URL.revokeObjectURL(imagePreview._objectUrl);
            }
            imagePreview.src = url;
            imagePreview._objectUrl = url;

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

        // Safari fallback: intercept form submit to inject cropped blob
        var studentForm = photoInput.closest('form');
        if (studentForm) {
            studentForm.addEventListener('submit', function(e) {
                if (photoInput._croppedBlob) {
                    e.preventDefault();
                    var formData = new FormData(studentForm);
                    formData.set('photo_path', photoInput._croppedBlob, 'photo.jpg');
                    var xhr = new XMLHttpRequest();
                    xhr.open(studentForm.method, studentForm.action, true);
                    xhr.onload = function() {
                        window.location.href = studentForm.action;
                    };
                    xhr.onerror = function() {
                        Swal.fire('Error', 'Failed to save. Please try again.', 'error');
                    };
                    xhr.send(formData);
                }
            });
        }
    </script>
@endsection

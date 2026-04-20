@extends('layouts.master')
@section('title')
    Edit {{ $sponsor->full_name ?? '' . ' Information' }}
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

        .status-deleted {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-past {
            background: #f3f4f6;
            color: #4b5563;
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
            font-size: 14px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .tab-content {
            padding-top: 24px;
        }

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
        }

        .form-tabs .nav-tabs {
            border: none;
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

        .form-section {
            margin-bottom: 28px;
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


        /* Hide DataTables default controls */
        .dataTables_length,
        .dataTables_filter {
            display: none !important;
        }

        /* Table Header Styling from Admissions Index */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Filter Controls */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        /* Table Cell Styling from Admissions Index */
        .student-cell,
        .sponsor-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar,
        .sponsor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
        }

        .student-avatar-placeholder,
        .sponsor-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

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
            font-size: 16px;
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

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
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
            Edit Sponsor
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
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $sponsor->full_name }}</h1>
                <div class="page-subtitle">Edit sponsor information</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="status-badge status-{{ strtolower($sponsor->status ?? 'current') }}">{{ $sponsor->status ?? 'Current' }}</span>
            </div>
        </div>

        <div class="form-tabs">
            <ul class="nav nav-tabs d-flex justify-content-start" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-id-card"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-id-card text-muted me-2"></i>Profile
                            Information</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-users"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-users text-muted me-2"></i>Children</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-clock-rotate-left"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-clock-rotate-left text-muted me-2"></i>Portal login
                            history</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-file-lines"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-file-lines text-muted me-2"></i>Other
                            Information</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#communication" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-envelope"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-envelope text-muted me-2"></i>Communication
                            History</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#roles1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-sliders"></i></span>
                        <span class="d-none d-sm-block"><i class="fas fa-sliders text-muted me-2"></i>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane active" id="home1" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <form class="needs-validation" method="POST"
                            action="{{ route('sponsors.sponsor-update', $sponsor->id) }}" novalidate>
                            @csrf
                            <input type="hidden" name="last_updated_by" value="{{ auth()->user()->full_name ?? null }}">

                            <div class="form-section">
                                <div class="help-text">
                                    <div class="help-title">Basic Information & Contact Details</div>
                                    <div class="help-content">Provide the parent/sponsor's basic details including name,
                                        title, contact information, and date of birth.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="title">Title</label>
                                            <select class="form-select @error('title') is-invalid @enderror"
                                                name="title" id="title" required data-trigger>
                                                <option value="">Select Title...</option>
                                                <option value="Mr"
                                                    {{ old('title', $sponsor->title) == 'Mr' ? 'selected' : '' }}>
                                                    Mr</option>
                                                <option value="Mrs"
                                                    {{ old('title', $sponsor->title) == 'Mrs' ? 'selected' : '' }}>
                                                    Mrs</option>
                                                <option value="Ms"
                                                    {{ old('title', $sponsor->title) == 'Ms' ? 'selected' : '' }}>
                                                    Ms</option>
                                                <option value="Dr"
                                                    {{ old('title', $sponsor->title) == 'Dr' ? 'selected' : '' }}>
                                                    Dr</option>
                                                <option value="Miss"
                                                    {{ old('title', $sponsor->title) == 'Miss' ? 'selected' : '' }}>
                                                    Miss</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="first_name">First
                                                Name</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="first_name"
                                                    class="form-control @error('first_name') is-invalid @enderror"
                                                    id="first_name" placeholder="First name"
                                                    value="{{ old('first_name', $sponsor->first_name) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="last_name">Last
                                                Name</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="last_name"
                                                    class="form-control @error('last_name') is-invalid @enderror"
                                                    id="last_name" placeholder="Last name"
                                                    value="{{ old('last_name', $sponsor->last_name) }}" required>
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
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    placeholder="example@domain.com"
                                                    value="{{ old('email', $sponsor->email) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="phone">Phone</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-phone input-icon"></i>
                                                <input type="text" name="phone"
                                                    class="form-control @error('phone') is-invalid @enderror" id="phone"
                                                    placeholder="75 234 098"
                                                    value="{{ old('phone', $sponsor->formatted_phone) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="telephone">Telephone</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-phone input-icon"></i>
                                                <input type="text" name="telephone"
                                                    class="form-control @error('telephone') is-invalid @enderror"
                                                    id="telephone" placeholder="395 0555"
                                                    value="{{ old('telephone', $sponsor->formatted_telephone) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="date_of_birth">Date of
                                                Birth</label>
                                            <div class="input-icon-group flatpickr-wrapper" data-toggle="sponsor-edit-dob-picker">
                                                <i class="fas fa-calendar input-icon"></i>
                                                <input type="text" name="date_of_birth"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    id="date_of_birth" data-input
                                                    value="{{ old('date_of_birth', $sponsor->formatted_date_of_birth) }}"
                                                    placeholder="dd/mm/yyyy" maxlength="10">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required-field" for="gender">Gender</label>
                                            <select class="form-select @error('gender') is-invalid @enderror"
                                                name="gender" id="gender" data-trigger required>
                                                <option value="">Select gender...</option>
                                                <option value="M"
                                                    {{ old('gender', $sponsor->gender) == 'M' ? 'selected' : '' }}>
                                                    M</option>
                                                <option value="F"
                                                    {{ old('gender', $sponsor->gender) == 'F' ? 'selected' : '' }}>
                                                    F</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="help-text">
                                    <div class="help-title">Identification & Status</div>
                                    <div class="help-content">ID number, nationality, relation to student, and current
                                        status.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="id_number">ID/Passport
                                                No:</label>
                                            <div class="input-icon-group">
                                                <i class="fas fa-id-badge input-icon"></i>
                                                <input type="text" name="id_number" class="form-control" id="id_number"
                                                    placeholder="988 8248 87"
                                                    value="{{ old('id_number', $sponsor->formatted_id_number) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="nationality">Nationality</label>
                                            <select name="nationality"
                                                class="form-select @error('nationality') is-invalid @enderror"
                                                id="nationality" data-trigger>
                                                <option value="">Select Nationality...</option>
                                                @foreach ($nationalities as $nationality)
                                                    <option value="{{ $nationality->name }}"
                                                        {{ old('nationality', $sponsor->nationality) == $nationality->name ? 'selected' : '' }}>
                                                        {{ $nationality->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="relation">Relation</label>
                                            <select name="relation"
                                                class="form-select @error('relation') is-invalid @enderror" id="relation"
                                                data-trigger>
                                                <option value="">Select relation...</option>
                                                <option value="Mother"
                                                    {{ old('relation', $sponsor->relation) == 'Mother' ? 'selected' : '' }}>
                                                    Mother</option>
                                                <option value="Grandmother"
                                                    {{ old('relation', $sponsor->relation) == 'Grandmother' ? 'selected' : '' }}>
                                                    Grandmother</option>
                                                <option value="Father"
                                                    {{ old('relation', $sponsor->relation) == 'Father' ? 'selected' : '' }}>
                                                    Father</option>
                                                <option value="Grandfather"
                                                    {{ old('relation', $sponsor->relation) == 'Grandfather' ? 'selected' : '' }}>
                                                    Grandfather</option>
                                                <option value="Brother"
                                                    {{ old('relation', $sponsor->relation) == 'Brother' ? 'selected' : '' }}>
                                                    Brother</option>
                                                <option value="Sister"
                                                    {{ old('relation', $sponsor->relation) == 'Sister' ? 'selected' : '' }}>
                                                    Sister</option>
                                                <option value="Uncle"
                                                    {{ old('relation', $sponsor->relation) == 'Uncle' ? 'selected' : '' }}>
                                                    Uncle</option>
                                                <option value="Auntie"
                                                    {{ old('relation', $sponsor->relation) == 'Auntie' ? 'selected' : '' }}>
                                                    Auntie</option>
                                                <option value="Relative"
                                                    {{ old('relation', $sponsor->relation) == 'Relative' ? 'selected' : '' }}>
                                                    Relative</option>
                                                <option value="Other"
                                                    {{ old('relation', $sponsor->relation) == 'Other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="status">Status</label>
                                            <select name="status"
                                                class="form-select @error('status') is-invalid @enderror" id="status"
                                                data-trigger>
                                                <option value="">Select status...</option>
                                                <option value="Current"
                                                    {{ old('status', $sponsor->status) == 'Current' ? 'selected' : '' }}>
                                                    Current</option>
                                                <option value="Deleted"
                                                    {{ old('status', $sponsor->status) == 'Deleted' ? 'selected' : '' }}>
                                                    Deleted</option>
                                                <option value="Past"
                                                    {{ old('status', $sponsor->status) == 'Past' ? 'selected' : '' }}>
                                                    Past</option>
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
                                            <label class="form-label" for="sponsor_filter_id">Custom
                                                Filter</label>
                                            <select name="sponsor_filter_id" class="form-select" id="sponsor_filter_id"
                                                data-trigger>
                                                <option value="">Select Filter...</option>
                                                @foreach ($filters as $filter)
                                                    <option value="{{ $filter->id }}"
                                                        {{ old('sponsor_filter_id', $sponsor->sponsor_filter_id) == $filter->id ? 'selected' : '' }}>
                                                        {{ $filter->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="profession">Profession</label>
                                            <select name="profession"
                                                class="form-select @error('profession') is-invalid @enderror"
                                                id="profession" data-trigger>
                                                <option value="">Select Profession...</option>
                                                <option value="Accountant"
                                                    {{ old('profession', $sponsor->profession) == 'Accountant' ? 'selected' : '' }}>
                                                    Accountant</option>
                                                <option value="Administrative Assistant"
                                                    {{ old('profession', $sponsor->profession) == 'Administrative Assistant' ? 'selected' : '' }}>
                                                    Administrative Assistant</option>
                                                <option value="Architect"
                                                    {{ old('profession', $sponsor->profession) == 'Architect' ? 'selected' : '' }}>
                                                    Architect</option>
                                                <option value="Artist/Designer"
                                                    {{ old('profession', $sponsor->profession) == 'Artist/Designer' ? 'selected' : '' }}>
                                                    Artist/Designer</option>
                                                <option value="Attorney/Lawyer"
                                                    {{ old('profession', $sponsor->profession) == 'Attorney/Lawyer' ? 'selected' : '' }}>
                                                    Attorney/Lawyer</option>
                                                <option value="Business Owner/Entrepreneur"
                                                    {{ old('profession', $sponsor->profession) == 'Business Owner/Entrepreneur' ? 'selected' : '' }}>
                                                    Business Owner/Entrepreneur</option>
                                                <option value="Carpenter"
                                                    {{ old('profession', $sponsor->profession) == 'Carpenter' ? 'selected' : '' }}>
                                                    Carpenter</option>
                                                <option value="Chef/Cook"
                                                    {{ old('profession', $sponsor->profession) == 'Chef/Cook' ? 'selected' : '' }}>
                                                    Chef/Cook</option>
                                                <option value="Clergy/Religious Leader"
                                                    {{ old('profession', $sponsor->profession) == 'Clergy/Religious Leader' ? 'selected' : '' }}>
                                                    Clergy/Religious Leader</option>
                                                <option value="Consultant"
                                                    {{ old('profession', $sponsor->profession) == 'Consultant' ? 'selected' : '' }}>
                                                    Consultant</option>
                                                <option value="Customer Service Representative"
                                                    {{ old('profession', $sponsor->profession) == 'Customer Service Representative' ? 'selected' : '' }}>
                                                    Customer Service Representative</option>
                                                <option value="Dentist"
                                                    {{ old('profession', $sponsor->profession) == 'Dentist' ? 'selected' : '' }}>
                                                    Dentist</option>
                                                <option value="Doctor/Physician"
                                                    {{ old('profession', $sponsor->profession) == 'Doctor/Physician' ? 'selected' : '' }}>
                                                    Doctor/Physician</option>
                                                <option value="Electrician"
                                                    {{ old('profession', $sponsor->profession) == 'Electrician' ? 'selected' : '' }}>
                                                    Electrician</option>
                                                <option value="Engineer"
                                                    {{ old('profession', $sponsor->profession) == 'Engineer' ? 'selected' : '' }}>
                                                    Engineer</option>
                                                <option value="Farmer/Agricultural Worker"
                                                    {{ old('profession', $sponsor->profession) == 'Farmer/Agricultural Worker' ? 'selected' : '' }}>
                                                    Farmer/Agricultural Worker</option>
                                                <option value="Financial Advisor"
                                                    {{ old('profession', $sponsor->profession) == 'Financial Advisor' ? 'selected' : '' }}>
                                                    Financial Advisor</option>
                                                <option value="Firefighter"
                                                    {{ old('profession', $sponsor->profession) == 'Firefighter' ? 'selected' : '' }}>
                                                    Firefighter</option>
                                                <option value="Graphic Designer"
                                                    {{ old('profession', $sponsor->profession) == 'Graphic Designer' ? 'selected' : '' }}>
                                                    Graphic Designer</option>
                                                <option value="Healthcare Worker - Nurse"
                                                    {{ old('profession', $sponsor->profession) == 'Healthcare Worker - Nurse' ? 'selected' : '' }}>
                                                    Healthcare Worker - Nurse</option>
                                                <option value="Healthcare Worker - Pharmacist"
                                                    {{ old('profession', $sponsor->profession) == 'Healthcare Worker - Pharmacist' ? 'selected' : '' }}>
                                                    Healthcare Worker - Pharmacist</option>
                                                <option value="Healthcare Worker - Therapist"
                                                    {{ old('profession', $sponsor->profession) == 'Healthcare Worker - Therapist' ? 'selected' : '' }}>
                                                    Healthcare Worker - Therapist</option>
                                                <option value="Healthcare Worker - Paramedic"
                                                    {{ old('profession', $sponsor->profession) == 'Healthcare Worker - Paramedic' ? 'selected' : '' }}>
                                                    Healthcare Worker - Paramedic</option>
                                                <option value="Homemaker/Stay-at-Home Parent"
                                                    {{ old('profession', $sponsor->profession) == 'Homemaker/Stay-at-Home Parent' ? 'selected' : '' }}>
                                                    Homemaker/Stay-at-Home Parent</option>
                                                <option value="Human Resources Professional"
                                                    {{ old('profession', $sponsor->profession) == 'Human Resources Professional' ? 'selected' : '' }}>
                                                    Human Resources Professional</option>
                                                <option value="Information Technology (IT) Professional"
                                                    {{ old('profession', $sponsor->profession) == 'Information Technology (IT) Professional' ? 'selected' : '' }}>
                                                    Information Technology (IT) Professional</option>
                                                <option value="Journalist/Writer"
                                                    {{ old('profession', $sponsor->profession) == 'Journalist/Writer' ? 'selected' : '' }}>
                                                    Journalist/Writer</option>
                                                <option value="Law Enforcement Officer - Police Officer"
                                                    {{ old('profession', $sponsor->profession) == 'Law Enforcement Officer - Police Officer' ? 'selected' : '' }}>
                                                    Law Enforcement Officer - Police Officer</option>
                                                <option value="Law Enforcement Officer - Detective"
                                                    {{ old('profession', $sponsor->profession) == 'Law Enforcement Officer - Detective' ? 'selected' : '' }}>
                                                    Law Enforcement Officer - Detective</option>
                                                <option value="Law Enforcement Officer - Corrections Officer"
                                                    {{ old('profession', $sponsor->profession) == 'Law Enforcement Officer - Corrections Officer' ? 'selected' : '' }}>
                                                    Law Enforcement Officer - Corrections Officer</option>
                                                <option value="Mechanic"
                                                    {{ old('profession', $sponsor->profession) == 'Mechanic' ? 'selected' : '' }}>
                                                    Mechanic</option>
                                                <option value="Military Personnel"
                                                    {{ old('profession', $sponsor->profession) == 'Military Personnel' ? 'selected' : '' }}>
                                                    Military Personnel</option>
                                                <option value="Professor/Teacher/Educator - Elementary School Teacher"
                                                    {{ old('profession', $sponsor->profession) == 'Professor/Teacher/Educator - Elementary School Teacher' ? 'selected' : '' }}>
                                                    Professor/Teacher/Educator - Elementary School Teacher</option>
                                                <option value="Professor/Teacher/Educator - High School Teacher"
                                                    {{ old('profession', $sponsor->profession) == 'Professor/Teacher/Educator - High School Teacher' ? 'selected' : '' }}>
                                                    Professor/Teacher/Educator - High School Teacher</option>
                                                <option value="Professor/Teacher/Educator - College Professor"
                                                    {{ old('profession', $sponsor->profession) == 'Professor/Teacher/Educator - College Professor' ? 'selected' : '' }}>
                                                    Professor/Teacher/Educator - College Professor</option>
                                                <option value="Real Estate Agent/Broker"
                                                    {{ old('profession', $sponsor->profession) == 'Real Estate Agent/Broker' ? 'selected' : '' }}>
                                                    Real Estate Agent/Broker</option>
                                                <option value="Researcher/Scientist"
                                                    {{ old('profession', $sponsor->profession) == 'Researcher/Scientist' ? 'selected' : '' }}>
                                                    Researcher/Scientist</option>
                                                <option value="Retail Worker"
                                                    {{ old('profession', $sponsor->profession) == 'Retail Worker' ? 'selected' : '' }}>
                                                    Retail Worker</option>
                                                <option value="Salesperson"
                                                    {{ old('profession', $sponsor->profession) == 'Salesperson' ? 'selected' : '' }}>
                                                    Salesperson</option>
                                                <option value="Social Worker/Counselor"
                                                    {{ old('profession', $sponsor->profession) == 'Social Worker/Counselor' ? 'selected' : '' }}>
                                                    Social Worker/Counselor</option>
                                                <option value="Software Developer/Programmer"
                                                    {{ old('profession', $sponsor->profession) == 'Software Developer/Programmer' ? 'selected' : '' }}>
                                                    Software Developer/Programmer</option>
                                                <option value="Technician - Laboratory Technician"
                                                    {{ old('profession', $sponsor->profession) == 'Technician - Laboratory Technician' ? 'selected' : '' }}>
                                                    Technician - Laboratory Technician</option>
                                                <option value="Technician - Engineering Technician"
                                                    {{ old('profession', $sponsor->profession) == 'Technician - Engineering Technician' ? 'selected' : '' }}>
                                                    Technician - Engineering Technician</option>
                                                <option value="Transportation Worker - Driver"
                                                    {{ old('profession', $sponsor->profession) == 'Transportation Worker - Driver' ? 'selected' : '' }}>
                                                    Transportation Worker - Driver (Taxi/Bus/Truck)</option>
                                                <option value="Transportation Worker - Pilot"
                                                    {{ old('profession', $sponsor->profession) == 'Transportation Worker - Pilot' ? 'selected' : '' }}>
                                                    Transportation Worker - Pilot</option>
                                                <option value="Transportation Worker - Air Traffic Controller"
                                                    {{ old('profession', $sponsor->profession) == 'Transportation Worker - Air Traffic Controller' ? 'selected' : '' }}>
                                                    Transportation Worker - Air Traffic Controller</option>
                                                <option value="Unemployed"
                                                    {{ old('profession', $sponsor->profession) == 'Unemployed' ? 'selected' : '' }}>
                                                    Unemployed</option>
                                                <option value="Retired"
                                                    {{ old('profession', $sponsor->profession) == 'Retired' ? 'selected' : '' }}>
                                                    Retired</option>
                                                <option value="Other"
                                                    {{ old('profession', $sponsor->profession) == 'Other' ? 'selected' : '' }}>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="work_place">Work Place</label>
                                            <input type="text" name="work_place"
                                                class="form-control @error('work_place') is-invalid @enderror"
                                                id="work_place" placeholder="Company/Organization name"
                                                value="{{ old('work_place', $sponsor->work_place) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="text-muted">
                                        Last updated by: {{ $sponsor->last_updated_by ?? 'Support' }}
                                        Date: {{ $sponsor->updated_at ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="{{ route('sponsors.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>Back
                                </a>
                                @can('manage-sponsors')
                                        <a href="{{ route('sponsors.delete-sponsor', $sponsor->id) }}"
                                            onclick="return confirm('Are you sure you want to delete this sponsor? This action cannot be undone.');"
                                            class="btn btn-danger">
                                            <i class="fas fa-trash"></i>Delete
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text">
                                                <i class="fas fa-save me-1"></i>Update Sponsor
                                            </span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
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
                <div class="help-text">
                    <div class="help-title">Children List</div>
                    <div class="help-content">View all children currently linked to this parent/sponsor.</div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-6 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select id="children-class-filter" class="form-select">
                                        <option value="">All Classes</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select id="children-status-filter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Current">Current</option>
                                        <option value="Past">Past</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light"
                                            id="children-clear-filters">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table id="student-parent" class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Firstname</th>
                                    <th>Lastname</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sponsor->students->where('status', 'Current') as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->first_name }}</td>
                                        <td>{{ $student->last_name }}</td>
                                        <td>{{ $student->class->name ?? '' }}</td>
                                        <td>{{ $student->status }}</td>
                                        <td><a href="{{ route('students.show', $student) }}"><i
                                                    class="bx bx-link-alt"></i></a></td>
                                    </tr>
                                @endforeach
                                <tr id="children-no-data-row" style="display: none;">
                                    <td colspan="6">
                                        <div class="text-center text-muted" style="padding: 40px 0;">
                                            <i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i>
                                            <p class="mt-3 mb-0" style="font-size: 15px;">No children found</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="messages1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Portal Login History</div>
                    <div class="help-content">Track all portal login activities for this parent/sponsor account.</div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-6 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <select id="login-category-filter" class="form-select">
                                        <option value="">All Categories</option>
                                        <option value="Login">Login</option>
                                        <option value="Logout">Logout</option>
                                    </select>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light"
                                            id="login-clear-filters">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <table id="parent-logins" class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Category</th>
                                    <th>IP Address</th>
                                    <th>Description</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="login-no-data-row">
                                    <td colspan="6">
                                        <div class="text-center text-muted" style="padding: 40px 0;">
                                            <i class="fas fa-clock-rotate-left"
                                                style="font-size: 48px; opacity: 0.3;"></i>
                                            <p class="mt-3 mb-0" style="font-size: 15px;">No login history found</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="settings1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Additional Information</div>
                    <div class="help-content">Manage address, family situation, and other important notes about the
                        parent/sponsor.</div>
                </div>

                <form action="{{ route('sponsors.update-or-create', $sponsor->id) }}" method="POST">
                    @csrf
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="address">Address</label>
                                    <input placeholder="Gaborone, Phase 2, Plot 23456" class="form-control"
                                        value="{{ old('address', $sponsor->otherInformation->address ?? '') }}"
                                        name="address" type="text" id="address">
                                </div>
                            </div>

                            @php
                                $statuses = [
                                    'Married',
                                    'Together',
                                    'Living Together',
                                    'Divorced',
                                    'Separated',
                                    'Deceased',
                                    'Not living together',
                                ];
                            @endphp

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="family_situation">Family Assessment</label>
                                    <select class="form-select" name="family_situation" id="family_situation">
                                        <option value="">Select family status...</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}"
                                                {{ $sponsor->otherInformation->family_situation ?? '' == $status ? 'selected' : '' }}>
                                                {{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="issues_to_note">Issues to Note</label>
                                    <textarea class="form-control" name="issues_to_note" id="issues" cols="30" rows="6"
                                        placeholder="Add any important notes or issues to be aware of...">{{ $sponsor->otherInformation->issues_to_note ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        @can('manage-sponsors')
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                        @endcan
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="communication" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Communication History</div>
                    <div class="help-content">View all SMS and email communications sent to this parent/sponsor.</div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-6 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select id="comm-service-filter" class="form-select">
                                        <option value="">All Services</option>
                                        <option value="SMS">SMS</option>
                                        <option value="Email">Email</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select id="comm-status-filter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="sent">Sent</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light"
                                            id="comm-clear-filters">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <table id="communication-history" class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Service</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Body</th>
                                    <th>Segments(SMS Count)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sponsor->messages as $message)
                                    <tr>
                                        <td>{{ $message->created_at ?? '' }}</td>
                                        <td>SMS</td>
                                        <td>{{ $school_data->school_name ?? '' }}</td>
                                        <td>{{ $sponsor->phone ?? '' }}</td>
                                        <td>{{ $message->body ?? '' }}</td>
                                        <td>{{ $message->sms_count ?? '' }}</td>
                                        <td>Delivered</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="text-center text-muted" style="padding: 40px 0;">
                                                <i class="fas fa-envelope" style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-3 mb-0" style="font-size: 15px;">No communication history
                                                    found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="roles1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Settings</div>
                    <div class="help-content">Configure additional settings for this parent/sponsor account.</div>
                </div>
                <div class="text-center text-muted" style="padding: 60px 0;">
                    <i class="fas fa-sliders" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mt-3 mb-0" style="font-size: 15px;">No Configuration Available</p>
                </div>
            </div>
        </div>
    </div><!-- end tab-content -->
    </div><!-- end form-container -->
    </div><!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            // Common DataTable configuration
            const dataTableConfig = {
                searching: false,
                lengthChange: false,
                pageLength: 10,
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
            function initDataTable(selector, config) {
                config = config || dataTableConfig;
                if ($(selector).length && !$.fn.DataTable.isDataTable(selector)) {
                    return $(selector).DataTable(config);
                }
                return null;
            }

            initDataTable('#student-parent');
            initDataTable('#parent-logins');
            var commTable = initDataTable('#communication-history');

            // Children filters
            function updateChildrenNoDataRow() {
                var visibleRows = $('#student-parent tbody tr:not(#children-no-data-row):visible').length;
                if (visibleRows === 0) {
                    $('#children-no-data-row').show();
                } else {
                    $('#children-no-data-row').hide();
                }
            }

            $('#children-class-filter, #children-status-filter').on('change', function() {
                var classVal = $('#children-class-filter').val().toLowerCase();
                var statusVal = $('#children-status-filter').val().toLowerCase();

                $('#student-parent tbody tr:not(#children-no-data-row)').each(function() {
                    var row = $(this);
                    var classText = row.find('td:eq(3)').text().toLowerCase();
                    var statusText = row.find('td:eq(4)').text().toLowerCase();

                    var classMatch = !classVal || classText.includes(classVal);
                    var statusMatch = !statusVal || statusText.includes(statusVal);

                    if (classMatch && statusMatch) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
                updateChildrenNoDataRow();
            });

            $('#children-clear-filters').on('click', function() {
                $('#children-class-filter').val('');
                $('#children-status-filter').val('');
                $('#student-parent tbody tr:not(#children-no-data-row)').show();
                updateChildrenNoDataRow();
            });

            // Initialize children no-data row visibility
            updateChildrenNoDataRow();

            // Login history filters
            $('#login-category-filter').on('change', function() {
                var categoryVal = $(this).val().toLowerCase();

                $('#parent-logins tbody tr').each(function() {
                    var row = $(this);
                    var categoryText = row.find('td:eq(2)').text().toLowerCase();

                    if (!categoryVal || categoryText.includes(categoryVal)) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            });

            $('#login-clear-filters').on('click', function() {
                $('#login-category-filter').val('');
                $('#parent-logins tbody tr').show();
            });

            // Communication history filters
            $('#comm-service-filter, #comm-status-filter').on('change', function() {
                var serviceVal = $('#comm-service-filter').val().toLowerCase();
                var statusVal = $('#comm-status-filter').val().toLowerCase();

                commTable.rows().every(function() {
                    var data = this.data();
                    var serviceText = $(data[1]).text().toLowerCase();
                    var statusText = $(data[6]).text().toLowerCase();

                    var serviceMatch = !serviceVal || serviceText.includes(serviceVal);
                    var statusMatch = !statusVal || statusText.includes(statusVal);

                    if (serviceMatch && statusMatch) {
                        $(this.node()).show();
                    } else {
                        $(this.node()).hide();
                    }
                });
            });

            $('#comm-clear-filters').on('click', function() {
                $('#comm-service-filter').val('');
                $('#comm-status-filter').val('');
                commTable.rows().every(function() {
                    $(this.node()).show();
                });
            });
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
            const selectElements = document.querySelectorAll('select[data-trigger]');
            selectElements.forEach(element => {
                new Choices(element, {
                    searchEnabled: element.options.length > 10,
                    searchFields: ['label', 'value'],
                    itemSelectText: '',
                    shouldSort: false,
                    position: 'auto'
                });
            });
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let phone = e.target.value.replace(/\D/g, '');
                    phone = phone.substring(0, 8);
                    if (phone.length > 2) {
                        if (phone.length > 5) {
                            phone = phone.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1 $2 $3');
                        } else {
                            phone = phone.replace(/(\d{2})(\d{1,3})/, '$1 $2');
                        }
                    }
                    e.target.value = phone;
                });

                phoneInput.addEventListener('blur', function(e) {
                    const phone = e.target.value.replace(/\D/g, '');
                    const isValid = /^[7][1-9][0-9]{6}$/.test(phone);

                    if (!isValid && phone.length > 0) {
                        phoneInput.setCustomValidity(
                            'Please enter a valid Botswana mobile number (must start with 71-79)');
                    } else {
                        phoneInput.setCustomValidity('');
                    }
                });
            }

            const telephoneInput = document.getElementById('telephone');
            if (telephoneInput) {
                telephoneInput.addEventListener('input', function(e) {
                    let phone = e.target.value.replace(/\D/g, '');
                    phone = phone.substring(0, 7);
                    if (phone.length > 3) {
                        phone = phone.replace(/(\d{3})(\d{1,4})/, '$1 $2');
                    }

                    e.target.value = phone;
                });

                telephoneInput.addEventListener('blur', function(e) {
                    const phone = e.target.value.replace(/\D/g, '');
                    if (phone.length > 0) {
                        const isValid = /^[2-6][0-9]{6}$/.test(phone);
                        if (!isValid) {
                            telephoneInput.setCustomValidity(
                                'Please enter a valid Botswana telephone number');
                        } else {
                            telephoneInput.setCustomValidity('');
                        }
                    } else {
                        telephoneInput.setCustomValidity('');
                    }
                });
            }

            const form = document.querySelector('form.needs-validation');
            if (form) {
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

                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();

                        const firstInvalidField = form.querySelector(':invalid');
                        if (firstInvalidField) {
                            firstInvalidField.focus();
                            firstInvalidField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }

                    form.classList.add('was-validated');
                });

                const dobInput = document.getElementById('date_of_birth');
                if (dobInput) {
                    formatDateInput(dobInput);

                    if (typeof flatpickr === 'function') {
                        flatpickr('[data-toggle="sponsor-edit-dob-picker"]', {
                            wrap: true,
                            allowInput: true,
                            dateFormat: 'd/m/Y',
                            maxDate: 'today',
                            disableMobile: true,
                        });
                    }

                    const validateDob = function(e) {
                        const parts = e.target.value.split('/');
                        if (parts.length !== 3) {
                            dobInput.setCustomValidity('Please enter date in dd/mm/yyyy format');
                            return;
                        }
                        const selectedDate = new Date(parts[2], parts[1] - 1, parts[0]);
                        const today = new Date();
                        const minDate = new Date();
                        minDate.setFullYear(today.getFullYear() - 100);
                        const maxDate = new Date();
                        maxDate.setFullYear(today.getFullYear() - 16);

                        if (isNaN(selectedDate.getTime())) {
                            dobInput.setCustomValidity('Please enter a valid date in dd/mm/yyyy format');
                        } else if (selectedDate > maxDate || selectedDate < minDate) {
                            dobInput.setCustomValidity('Age must be between 16 and 100 years');
                        } else {
                            dobInput.setCustomValidity('');
                        }
                    };

                    dobInput.addEventListener('change', validateDob);
                    dobInput.addEventListener('blur', validateDob);
                }

                const emailInput = document.getElementById('email');
                if (emailInput) {
                    emailInput.addEventListener('blur', function(e) {
                        const email = e.target.value;
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                        if (email && !emailRegex.test(email)) {
                            emailInput.setCustomValidity('Please enter a valid email address');
                        } else {
                            emailInput.setCustomValidity('');
                        }
                    });
                }

                const idNumberInput = document.getElementById('id_number');
                if (idNumberInput) {
                    idNumberInput.addEventListener('blur', function(e) {
                        const idNumber = e.target.value.trim();
                        if (idNumber && idNumber.length < 5) {
                            idNumberInput.setCustomValidity('ID number must be at least 5 characters long');
                        } else {
                            idNumberInput.setCustomValidity('');
                        }
                    });
                }
            }
        });

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
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Edit School - {{ $school->name }}
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
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }

        .school-status {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #059669;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 32px;
        }

        .nav-tabs {
            border-bottom: none;
            gap: 0;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            color: #6b7280;
            font-weight: 500;
            padding: 16px 24px;
            margin-bottom: 0;
            border-radius: 0;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #374151;
            background: #f9fafb;
        }

        .nav-tabs .nav-link.active {
            color: #3b82f6;
            background: none;
            border-bottom-color: #3b82f6;
        }

        .tab-content {
            padding-top: 24px;
        }

        .tab-pane {
            min-height: 400px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }

        .text-muted {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #3b82f6;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            color: #374151;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 32px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .form-actions-left {
            display: flex;
            gap: 12px;
        }

        .form-actions-right {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
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
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #fbbf24;
            color: #78350f;
        }

        .btn-warning:hover {
            background: #f59e0b;
            transform: translateY(-1px);
        }

        .tab-icon {
            margin-right: 8px;
            font-size: 16px;
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

        .info-alert {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-alert i {
            color: #3b82f6;
            font-size: 18px;
        }

        .info-alert-content {
            flex: 1;
        }

        .info-alert-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 2px;
        }

        .info-alert-text {
            font-size: 13px;
            color: #1e40af;
        }

        .ownership-type-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .ownership-type-option {
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            text-align: center;
        }

        .ownership-type-option:hover {
            border-color: #d1d5db;
        }

        .ownership-type-option.selected {
            border-color: #3b82f6;
            background: #f8faff;
        }

        .ownership-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ownership-type-icon {
            font-size: 24px;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .ownership-type-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .ownership-type-description {
            color: #6b7280;
            font-size: 12px;
        }

        #missionFields {
            display: none;
            padding: 20px;
            background: #fef7cd;
            border-radius: 6px;
            border: 1px solid #fbbf24;
            margin-top: 20px;
        }

        #missionFields.show {
            display: block;
        }

        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .facility-card {
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            text-align: center;
        }

        .facility-card:hover {
            border-color: #d1d5db;
        }

        .facility-card.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .facility-card input[type="checkbox"] {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 18px;
            height: 18px;
            accent-color: #10b981;
        }

        .facility-icon {
            font-size: 28px;
            color: #10b981;
            margin-bottom: 8px;
        }

        .facility-name {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .facility-description {
            color: #6b7280;
            font-size: 11px;
            line-height: 1.3;
        }

        .school-type-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .school-type-option {
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .school-type-option:hover {
            border-color: #d1d5db;
        }

        .school-type-option.selected {
            border-color: #3b82f6;
            background: #f8faff;
        }

        .school-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .school-type-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .school-type-description {
            color: #6b7280;
            font-size: 13px;
        }

        .last-updated {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 2px;
        }

        .api-config-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .api-config-section .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .api-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .api-status-indicator.configured {
            background: #dcfce7;
            color: #166534;
        }

        .api-status-indicator.not-configured {
            background: #fef3c7;
            color: #92400e;
        }

        .api-status-indicator.partial {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .nav-tabs .nav-link {
                padding: 12px 16px;
                font-size: 13px;
            }

            .tab-icon {
                display: none;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .ownership-type-selector {
                grid-template-columns: 1fr;
            }

            .school-type-selector {
                grid-template-columns: 1fr;
            }

            .facilities-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                gap: 12px;
            }

            .form-actions-left,
            .form-actions-right {
                width: 100%;
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
            <a class="text-muted" href="{{ route('schools.index') }}">Back</a>
        @endslot
        @slot('title')
            Edit School
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle label-icon"></i><strong>Please complete all required fields marked with
                        an asterisk (*) and check all tabs to ensure nothing is missed.</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <div class="page-header">
                    <div>
                        <h2 class="page-title">Edit School</h2>
                        <div class="page-subtitle">{{ $school->name }} - {{ $school->code }}</div>
                        @if ($school->updated_at)
                            <div class="last-updated">Last updated: {{ $school->updated_at->format('F j, Y \a\t g:i A') }}
                            </div>
                        @endif
                    </div>
                    <div class="school-status">
                        <span class="status-badge {{ $school->is_active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                            {{ $school->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <a href="{{ route('schools.show', $school) }}" class="btn btn-secondary">
                            <i class="fas fa-eye"></i>
                            View School
                        </a>
                    </div>
                </div>

                <form action="{{ route('schools.update', $school) }}" method="POST" id="editSchoolForm">
                    @csrf
                    @method('PUT')

                    <!-- Tab Navigation -->
                    <div class="form-tabs">
                        <ul class="nav nav-tabs" id="schoolFormTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab">
                                    <i class="fas fa-info-circle tab-icon"></i>
                                    Basic Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                    type="button" role="tab">
                                    <i class="fas fa-address-card tab-icon"></i>
                                    Contact Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="leadership-tab" data-bs-toggle="tab"
                                    data-bs-target="#leadership" type="button" role="tab">
                                    <i class="fas fa-user-tie tab-icon"></i>
                                    Leadership
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="enrollment-tab" data-bs-toggle="tab"
                                    data-bs-target="#enrollment" type="button" role="tab">
                                    <i class="fas fa-users tab-icon"></i>
                                    Enrollment & Staff
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="facilities-tab" data-bs-toggle="tab"
                                    data-bs-target="#facilities" type="button" role="tab">
                                    <i class="fas fa-building tab-icon"></i>
                                    Facilities & Settings
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                                    type="button" role="tab">
                                    <i class="fas fa-cog tab-icon"></i>
                                    Settings
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="schoolFormTabContent">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">School Information</div>
                                <div class="help-content">
                                    Update the basic information for this school. Changes will be reflected immediately
                                    across the system.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">School Name <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $school->name) }}"
                                        placeholder="e.g., Central Primary School" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Full official name of the school</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">School Code</label>
                                    <input type="text" name="code" class="form-control"
                                        value="{{ old('code', $school->code) }}"
                                        placeholder="Leave blank for auto-generation">
                                    @error('code')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Unique identifier (auto-generated if left blank)</div>
                                </div>
                            </div>

                            <!-- School Type Selection -->
                            <div class="form-group">
                                <label class="form-label">School Type <span class="required">*</span></label>
                                <div class="school-type-selector">
                                    @foreach (\App\Models\School::SCHOOL_TYPES as $key => $label)
                                        <label
                                            class="school-type-option {{ old('school_type', $school->school_type) == $key ? 'selected' : '' }}"
                                            id="{{ $key }}Option">
                                            <input type="radio" name="school_type" value="{{ $key }}"
                                                {{ old('school_type', $school->school_type) == $key ? 'checked' : '' }}
                                                required>
                                            <div class="school-type-title">{{ $label }}</div>
                                            <div class="school-type-description">
                                                @switch($key)
                                                    @case('primary')
                                                        Elementary education (Standards 1-7)
                                                    @break

                                                    @case('junior_secondary')
                                                        Lower secondary education (Forms 1-3)
                                                    @break

                                                    @case('senior_secondary')
                                                        Upper secondary education (Forms 4-5)
                                                    @break

                                                    @case('unified_secondary')
                                                        Combined secondary (Forms 1-5)
                                                    @break
                                                @endswitch
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('school_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ownership Type Selection -->
                            <div class="form-group">
                                <label class="form-label">Ownership Type <span class="required">*</span></label>
                                <div class="ownership-type-selector">
                                    @foreach (\App\Models\School::OWNERSHIP_TYPES as $key => $label)
                                        <label
                                            class="ownership-type-option {{ old('ownership_type', $school->ownership_type) == $key ? 'selected' : '' }}"
                                            id="{{ $key }}OwnershipOption">
                                            <input type="radio" name="ownership_type" value="{{ $key }}"
                                                {{ old('ownership_type', $school->ownership_type) == $key ? 'checked' : '' }}
                                                required>
                                            <div class="ownership-type-icon">
                                                @switch($key)
                                                    @case('government')
                                                        <i class="fas fa-landmark"></i>
                                                    @break

                                                    @case('private')
                                                        <i class="fas fa-building"></i>
                                                    @break

                                                    @case('government_aided')
                                                        <i class="fas fa-church"></i>
                                                    @break
                                                @endswitch
                                            </div>
                                            <div class="ownership-type-title">{{ $label }}</div>
                                            <div class="ownership-type-description">
                                                @switch($key)
                                                    @case('government')
                                                        Fully government funded
                                                    @break

                                                    @case('private')
                                                        Privately owned & operated
                                                    @break

                                                    @case('government_aided')
                                                        Mission/religious school
                                                    @break
                                                @endswitch
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('ownership_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mission School Fields -->
                            <div id="missionFields"
                                class="{{ old('ownership_type', $school->ownership_type) == 'government_aided' ? 'show' : '' }}">
                                <h5 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                                    <i class="fas fa-church"></i> Mission School Information
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Religious Affiliation <span
                                                class="required">*</span></label>
                                        <input type="text" name="religious_affiliation" class="form-control"
                                            value="{{ old('religious_affiliation', $school->religious_affiliation) }}"
                                            placeholder="e.g., Roman Catholic">
                                        @error('religious_affiliation')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Mission Organization</label>
                                        <input type="text" name="mission_organization" class="form-control"
                                            value="{{ old('mission_organization', $school->mission_organization) }}"
                                            placeholder="e.g., Catholic Mission">
                                        @error('mission_organization')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Region <span class="required">*</span></label>
                                    <select name="region_id" class="form-select" required>
                                        <option value="">Select Region...</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('region_id', $school->region_id) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Regional office managing this school</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Operational Status <span class="required">*</span></label>
                                    <select name="operational_status" class="form-select" required>
                                        @foreach (\App\Models\School::OPERATIONAL_STATUSES as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ old('operational_status', $school->operational_status) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('operational_status')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Current operational status</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Date Established</label>
                                    <input type="date" name="established_date" class="form-control"
                                        value="{{ old('established_date', $school->established_date?->format('Y-m-d')) }}">
                                    @error('established_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">When the school was founded</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" name="registration_number" class="form-control"
                                        value="{{ old('registration_number', $school->registration_number) }}"
                                        placeholder="e.g., EDU/2024/001">
                                    @error('registration_number')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Official registration number</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-textarea" rows="4"
                                    placeholder="Brief description about the school...">{{ old('description', $school->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="text-muted">Optional description (max 1000 characters)</div>
                            </div>
                        </div>

                        <!-- Contact Details Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Contact Information</div>
                                <div class="help-content">
                                    Update contact details for this school. Ensure all information is current for official
                                    correspondence.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Physical Address</label>
                                    <textarea name="physical_address" class="form-textarea" rows="3"
                                        placeholder="Street address, building, plot number...">{{ old('physical_address', $school->physical_address) }}</textarea>
                                    @error('physical_address')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Physical location of the school</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Postal Address</label>
                                    <input type="text" name="postal_address" class="form-control"
                                        value="{{ old('postal_address', $school->postal_address) }}"
                                        placeholder="e.g., P.O. Box 123, Gaborone">
                                    @error('postal_address')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Mailing address</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Primary Telephone</label>
                                    <input type="tel" name="telephone_primary" class="form-control"
                                        value="{{ old('telephone_primary', $school->telephone_primary) }}"
                                        placeholder="e.g., 3912345">
                                    @error('telephone_primary')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Main contact number</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Secondary Telephone</label>
                                    <input type="tel" name="telephone_secondary" class="form-control"
                                        value="{{ old('telephone_secondary', $school->telephone_secondary) }}"
                                        placeholder="e.g., 3912346">
                                    @error('telephone_secondary')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Alternative contact number</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Fax Number</label>
                                    <input type="tel" name="fax" class="form-control"
                                        value="{{ old('fax', $school->fax) }}" placeholder="e.g., 3912347">
                                    @error('fax')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Fax number (if available)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $school->email) }}" placeholder="e.g., info@school.bw">
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Official email address</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control"
                                        value="{{ old('website', $school->website) }}"
                                        placeholder="https://www.school.bw">
                                    @error('website')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">School website URL (if available)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Access Email</label>
                                    <input type="email" name="access_email" class="form-control"
                                        value="{{ old('access_email', $school->access_email) }}"
                                        placeholder="e.g., admin@school.bw">
                                    @error('access_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Email for regional/ministry access</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Access Password</label>
                                    <div class="input-group">
                                        <input type="password" name="access_password" id="access_password"
                                            class="form-control"
                                            value="{{ $school->metadata['access_password_plain'] ?? '' }}"
                                            placeholder="Enter new password (leave blank to keep current)">
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePassword('access_password')">
                                            <i class="fas fa-eye" id="access_password_icon"></i>
                                        </button>
                                    </div>
                                    @error('access_password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Password for regional/ministry access</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="access_password_confirmation"
                                            id="access_password_confirmation" class="form-control"
                                            value="{{ $school->metadata['access_password_plain'] ?? '' }}"
                                            placeholder="Confirm new password">
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="togglePassword('access_password_confirmation')">
                                            <i class="fas fa-eye" id="access_password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                    @error('access_password_confirmation')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Re-enter password to confirm</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary" onclick="testAccessCredentials()">
                                        <i class="bx bx-log-in me-1"></i>
                                        Test Login Access
                                    </button>
                                </div>
                                <div class="text-muted mt-2">Test the access credentials to ensure regional/ministry
                                    officials can log in</div>
                            </div>
                        </div>

                        <!-- Leadership Tab -->
                        <div class="tab-pane fade" id="leadership" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">School Leadership</div>
                                <div class="help-content">
                                    Update information about the school's leadership team. Keep this current for effective
                                    communication.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Principal Name</label>
                                    <input type="text" name="principal_name" class="form-control"
                                        value="{{ old('principal_name', $school->principal_name) }}"
                                        placeholder="e.g., Mr. John Doe">
                                    @error('principal_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Name of the school principal</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Principal Email</label>
                                    <input type="email" name="principal_email" class="form-control"
                                        value="{{ old('principal_email', $school->principal_email) }}"
                                        placeholder="e.g., principal@school.bw">
                                    @error('principal_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Principal's email address</div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Deputy Principal Name</label>
                                    <input type="text" name="deputy_principal_name" class="form-control"
                                        value="{{ old('deputy_principal_name', $school->deputy_principal_name) }}"
                                        placeholder="e.g., Ms. Jane Smith">
                                    @error('deputy_principal_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Name of the deputy principal</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Deputy Principal Email</label>
                                    <input type="email" name="deputy_principal_email" class="form-control"
                                        value="{{ old('deputy_principal_email', $school->deputy_principal_email) }}"
                                        placeholder="e.g., deputy@school.bw">
                                    @error('deputy_principal_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Deputy principal's email address</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Partnership Details</label>
                                <textarea name="partnership_details" class="form-textarea" rows="4"
                                    placeholder="Details about partnerships, sister schools, or collaborative programs...">{{ old('partnership_details', $school->partnership_details) }}</textarea>
                                @error('partnership_details')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="text-muted">Information about school partnerships (optional)</div>
                            </div>
                        </div>

                        <!-- Enrollment & Staff Tab -->
                        <div class="tab-pane fade" id="enrollment" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Enrollment & Staffing</div>
                                <div class="help-content">
                                    Keep enrollment and staffing numbers up to date. This data is used for resource
                                    allocation and reporting.
                                </div>
                            </div>

                            @if ($school->enrollment_capacity && $school->current_enrollment > $school->enrollment_capacity)
                                <div class="info-alert">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div class="info-alert-content">
                                        <div class="info-alert-title">Enrollment Exceeds Capacity</div>
                                        <div class="info-alert-text">
                                            Current enrollment ({{ number_format($school->current_enrollment) }}) exceeds
                                            the school's capacity ({{ number_format($school->enrollment_capacity) }}).
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <h5
                                style="font-size: 16px; font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-graduation-cap"></i> Student Enrollment
                            </h5>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Current Enrollment <span class="required">*</span></label>
                                    <input type="number" name="current_enrollment" class="form-control"
                                        value="{{ old('current_enrollment', $school->current_enrollment) }}"
                                        min="0" required>
                                    @error('current_enrollment')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Total number of enrolled students</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Enrollment Capacity</label>
                                    <input type="number" name="enrollment_capacity" class="form-control"
                                        value="{{ old('enrollment_capacity', $school->enrollment_capacity) }}"
                                        min="1">
                                    @error('enrollment_capacity')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Maximum student capacity</div>
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-chalkboard-teacher"></i> Teaching Staff
                            </h5>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Total Teachers <span class="required">*</span></label>
                                    <input type="number" name="total_teachers" class="form-control"
                                        value="{{ old('total_teachers', $school->total_teachers) }}" min="0"
                                        required>
                                    @error('total_teachers')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Total number of teaching staff</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Qualified Teachers <span class="required">*</span></label>
                                    <input type="number" name="qualified_teachers" class="form-control"
                                        value="{{ old('qualified_teachers', $school->qualified_teachers) }}"
                                        min="0" required>
                                    @error('qualified_teachers')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Teachers with required certifications</div>
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-door-open"></i> Infrastructure
                            </h5>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Total Classrooms</label>
                                    <input type="number" name="total_classrooms" class="form-control"
                                        value="{{ old('total_classrooms', $school->total_classrooms) }}" min="0">
                                    @error('total_classrooms')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Number of classroom facilities</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Total Streams</label>
                                    <input type="number" name="total_streams" class="form-control"
                                        value="{{ old('total_streams', $school->total_streams) }}" min="0">
                                    @error('total_streams')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Number of class sections/streams</div>
                                </div>
                            </div>
                        </div>

                        <!-- Facilities & Settings Tab -->
                        <div class="tab-pane fade" id="facilities" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Facilities & Configuration</div>
                                <div class="help-content">
                                    Update available facilities and operational settings for this school.
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-check-square"></i> Available Facilities
                            </h5>

                            <div class="facilities-grid">
                                <div class="facility-card {{ $school->has_boarding_facilities ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_boarding_facilities" value="1"
                                        {{ old('has_boarding_facilities', $school->has_boarding_facilities) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                    <div class="facility-name">Boarding</div>
                                    <div class="facility-description">Dormitories & accommodation</div>
                                </div>

                                <div class="facility-card {{ $school->has_library ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_library" value="1"
                                        {{ old('has_library', $school->has_library) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="facility-name">Library</div>
                                    <div class="facility-description">Reading room & resources</div>
                                </div>

                                <div class="facility-card {{ $school->has_computer_lab ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_computer_lab" value="1"
                                        {{ old('has_computer_lab', $school->has_computer_lab) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="facility-name">Computer Lab</div>
                                    <div class="facility-description">ICT facilities</div>
                                </div>

                                <div class="facility-card {{ $school->has_science_lab ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_science_lab" value="1"
                                        {{ old('has_science_lab', $school->has_science_lab) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <div class="facility-name">Science Lab</div>
                                    <div class="facility-description">Laboratory equipment</div>
                                </div>

                                <div class="facility-card {{ $school->has_sports_facilities ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_sports_facilities" value="1"
                                        {{ old('has_sports_facilities', $school->has_sports_facilities) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-running"></i>
                                    </div>
                                    <div class="facility-name">Sports</div>
                                    <div class="facility-description">Fields & equipment</div>
                                </div>

                                <div class="facility-card {{ $school->has_special_education_units ? 'selected' : '' }}">
                                    <input type="checkbox" name="has_special_education_units" value="1"
                                        {{ old('has_special_education_units', $school->has_special_education_units) ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div class="facility-name">Special Ed</div>
                                    <div class="facility-description">Special needs support</div>
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-cog"></i> School Settings
                            </h5>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $school->is_active) ? 'checked' : '' }}>
                                    <label for="is_active">Active School</label>
                                </div>
                                <div class="text-muted">School will be visible and accessible in the system</div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_operational" id="is_operational" value="1"
                                        {{ old('is_operational', $school->is_operational) ? 'checked' : '' }}>
                                    <label for="is_operational">Currently Operational</label>
                                </div>
                                <div class="text-muted">School is currently functioning and accepting students</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-textarea" rows="4"
                                    placeholder="Any additional information or special notes about this school...">{{ old('notes', $school->notes) }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="text-muted">Internal notes and additional information</div>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane fade" id="settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">API Configuration</div>
                                <div class="help-content">
                                    Configure API settings for integration with external school systems. These settings
                                    enable automated data synchronization and remote access. All fields are optional and can
                                    be configured independently.
                                </div>
                            </div>

                            <div class="api-config-section">
                                <div class="section-title">
                                    <i class="fas fa-cog"></i>
                                    API Settings
                                    @if ($school->isApiConfigured())
                                        <span class="api-status-indicator configured">
                                            <i class="fas fa-check-circle"></i>
                                            Configured
                                        </span>
                                    @elseif ($school->hasPartialApiConfig())
                                        <span class="api-status-indicator partial">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Partial Config
                                        </span>
                                    @else
                                        <span class="api-status-indicator not-configured">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Not Configured
                                        </span>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">API Endpoint</label>
                                            <input type="url" name="api_endpoint" class="form-control"
                                                value="{{ old('api_endpoint', $school->api_endpoint ?? $school->website) }}"
                                                placeholder="https://school.example.com">
                                            <small class="text-muted">Leave empty to use school website URL. This field is
                                                optional.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Authentication Type</label>
                                            <select name="api_auth_type" class="form-select">
                                                <option value="">Select authentication type (optional)</option>
                                                <option value="bearer"
                                                    {{ old('api_auth_type', $school->api_auth_type) == 'bearer' ? 'selected' : '' }}>
                                                    Bearer Token
                                                </option>
                                                <option value="basic"
                                                    {{ old('api_auth_type', $school->api_auth_type) == 'basic' ? 'selected' : '' }}>
                                                    Basic Auth
                                                </option>
                                                <option value="api_key"
                                                    {{ old('api_auth_type', $school->api_auth_type) == 'api_key' ? 'selected' : '' }}>
                                                    API Key Header
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">API Key/Token</label>
                                            <input type="password" name="api_key" class="form-control"
                                                value="{{ old('api_key', $school->api_key_decrypted) }}"
                                                placeholder="Enter API key or token (encrypted storage)">
                                            @if ($school->isApiConfigured())
                                                <small class="text-success">✓ API key is configured</small>
                                            @endif
                                            <small class="text-muted">This field is optional.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary" onclick="testApiConfiguration()"
                                            {{ !$school->isApiConfigured() ? 'disabled' : '' }}>
                                            <i class="bx bx-plug me-1"></i>
                                            Test API Connection
                                        </button>
                                    </div>
                                    <div class="text-muted mt-2">Test the API configuration to ensure connectivity and
                                        authentication work properly</div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <div class="form-actions-left">
                                @if (auth()->user()->canDeleteSchools())
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                        Delete School
                                    </button>
                                @endif
                            </div>
                            <div class="form-actions-right">
                                <a href="{{ route('schools.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    Update School
                                </button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    @if (auth()->user()->canDeleteSchools())
        <form id="deleteForm" action="{{ route('schools.destroy', $school) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schoolTypeOptions = document.querySelectorAll('.school-type-option');
            const schoolTypeRadios = document.querySelectorAll('input[name="school_type"]');

            function updateSchoolTypeSelection() {
                const selected = document.querySelector('input[name="school_type"]:checked');
                schoolTypeOptions.forEach(option => {
                    option.classList.toggle('selected', option.querySelector('input').checked);
                });
            }

            schoolTypeRadios.forEach(radio => {
                radio.addEventListener('change', updateSchoolTypeSelection);
            });

            schoolTypeOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    if (e.target.type !== 'radio') {
                        this.querySelector('input[type="radio"]').click();
                    }
                });
            });

            const ownershipOptions = document.querySelectorAll('.ownership-type-option');
            const ownershipRadios = document.querySelectorAll('input[name="ownership_type"]');
            const missionFields = document.getElementById('missionFields');

            function updateOwnershipSelection() {
                const selected = document.querySelector('input[name="ownership_type"]:checked');
                ownershipOptions.forEach(option => {
                    option.classList.toggle('selected', option.querySelector('input').checked);
                });

                if (selected && selected.value === 'government_aided') {
                    missionFields.classList.add('show');
                    document.querySelector('input[name="religious_affiliation"]').setAttribute('required',
                        'required');
                } else {
                    missionFields.classList.remove('show');
                    document.querySelector('input[name="religious_affiliation"]').removeAttribute('required');
                }
            }

            ownershipRadios.forEach(radio => {
                radio.addEventListener('change', updateOwnershipSelection);
            });

            ownershipOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    if (e.target.type !== 'radio') {
                        this.querySelector('input[type="radio"]').click();
                    }
                });
            });

            const facilityCards = document.querySelectorAll('.facility-card');
            facilityCards.forEach(card => {
                const checkbox = card.querySelector('input[type="checkbox"]');

                card.addEventListener('click', function(e) {
                    if (e.target.type !== 'checkbox') {
                        checkbox.checked = !checkbox.checked;
                    }
                    this.classList.toggle('selected', checkbox.checked);
                });

                checkbox.addEventListener('change', function() {
                    card.classList.toggle('selected', this.checked);
                });
            });

            updateSchoolTypeSelection();
            updateOwnershipSelection();

            // Add event listeners for API configuration fields
            const apiFields = ['input[name="api_endpoint"]', 'select[name="api_auth_type"]',
                'input[name="api_key"]'
            ];
            apiFields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    field.addEventListener('input', updateApiTestButton);
                    field.addEventListener('change', updateApiTestButton);
                }
            });

            // Initialize API test button state
            updateApiTestButton();

            document.getElementById('editSchoolForm').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const currentEnrollment = parseInt(document.querySelector(
                    'input[name="current_enrollment"]').value) || 0;
                const enrollmentCapacity = parseInt(document.querySelector(
                    'input[name="enrollment_capacity"]').value) || 0;
                const totalTeachers = parseInt(document.querySelector('input[name="total_teachers"]')
                    .value) || 0;
                const qualifiedTeachers = parseInt(document.querySelector(
                    'input[name="qualified_teachers"]').value) || 0;

                if (enrollmentCapacity > 0 && currentEnrollment > enrollmentCapacity) {
                    if (!confirm(
                            'Warning: Current enrollment exceeds capacity. The school may be overcrowded. Continue?'
                        )) {
                        e.preventDefault();
                        return false;
                    }
                }

                if (qualifiedTeachers > totalTeachers) {
                    e.preventDefault();
                    alert('Qualified teachers cannot exceed total teachers');
                    return false;
                }

                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
                submitBtn.disabled = true;
            });
        });

        function confirmDelete() {
            if (confirm('Are you sure you want to delete this school? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'fas fa-eye-slash';
                icon.style.color = '#6b7280';
            } else {
                passwordField.type = 'password';
                icon.className = 'fas fa-eye';
                icon.style.color = '#374151';
            }
        }

        function showModal(title, message, type = 'info') {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;

            const colors = {
                'success': '#16a34a',
                'error': '#ef4444',
                'warning': '#f59e0b',
                'info': '#3b82f6'
            };

            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-times-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };

            const color = colors[type] || colors.info;
            const icon = icons[type] || icons.info;

            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    max-width: 400px;
                    width: 90%;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                ">
                    <div style="
                        display: flex;
                        align-items: center;
                        margin-bottom: 20px;
                        color: ${color};
                    ">
                        <i class="fas ${icon}" style="font-size: 24px; margin-right: 10px;"></i>
                        <h4 style="margin: 0; color: ${color};">${title}</h4>
                    </div>
                    <p style="margin-bottom: 20px;">${message}</p>
                    <div style="
                        display: flex;
                        justify-content: flex-end;
                    ">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" style="
                            background: ${color};
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 500;
                        ">OK</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
        }

        function updateApiTestButton() {
            const apiEndpoint = document.querySelector('input[name="api_endpoint"]').value;
            const apiKey = document.querySelector('input[name="api_key"]').value;
            const testBtn = document.querySelector('button[onclick="testApiConfiguration()"]');

            if (apiEndpoint && apiKey) {
                testBtn.disabled = false;
                testBtn.classList.remove('btn-secondary');
                testBtn.classList.add('btn-primary');
            } else {
                testBtn.disabled = true;
                testBtn.classList.remove('btn-primary');
                testBtn.classList.add('btn-secondary');
            }
        }

        function testApiConfiguration() {
            const apiEndpoint = document.querySelector('input[name="api_endpoint"]').value;
            const apiAuthType = document.querySelector('select[name="api_auth_type"]').value;
            const apiKey = document.querySelector('input[name="api_key"]').value;

            if (!apiEndpoint) {
                showModal('Missing API Endpoint', 'Please fill in the API endpoint field before testing.', 'warning');
                return;
            }

            if (!apiKey) {
                showModal('Missing API Key', 'Please fill in the API key field before testing.', 'warning');
                return;
            }

            const testBtn = document.querySelector('button[onclick="testApiConfiguration()"]');
            const originalText = testBtn.innerHTML;
            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing...';
            testBtn.disabled = true;

            const baseUrl = "{{ route('schools.api.test-connection', ['school' => $school->id]) }}";

            fetch(baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        api_endpoint: apiEndpoint,
                        api_auth_type: apiAuthType,
                        api_key: apiKey
                    })
                })
                .then(response => response.json())
                .then(data => {
                    testBtn.innerHTML = originalText;
                    testBtn.disabled = false;

                    if (data.success) {
                        showModal('API Connection Successful!',
                            `${data.message}<br><br>Response Time: ${data.response_time ? (data.response_time * 1000).toFixed(0) + 'ms' : 'N/A'}`,
                            'success');
                    } else {
                        showModal('API Connection Failed', data.message, 'error');
                    }
                })
                .catch(error => {
                    testBtn.innerHTML = originalText;
                    testBtn.disabled = false;

                    console.error('API test error:', error);
                    showModal('Connection Error',
                        'Failed to test API configuration. Please check your network connection and try again.',
                        'error');
                });
        }

        function testAccessCredentials() {
            const email = document.querySelector('input[name="access_email"]').value;
            const password = document.querySelector('input[name="access_password"]').value;
            const confirmPassword = document.querySelector('input[name="access_password_confirmation"]').value;

            if (!email) {
                showModal('Missing Email', 'Please fill in the access email field before testing.', 'warning');
                return;
            }

            if (!password) {
                showModal('Missing Password', 'Please fill in the access password field before testing.', 'warning');
                return;
            }

            if (password && !confirmPassword) {
                showModal('Confirm Password', 'Please confirm the password before testing.', 'warning');
                return;
            }

            if (password && password !== confirmPassword) {
                showModal('Password Mismatch', 'Password and confirmation password do not match.', 'error');
                return;
            }

            if (password && password.length < 8) {
                showModal('Password Too Short', 'Password must be at least 8 characters long.', 'error');
                return;
            }

            const testBtn = document.querySelector('button[onclick="testAccessCredentials()"]');
            const originalText = testBtn.innerHTML;
            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing...';
            testBtn.disabled = true;

            const baseUrl = "{{ route('schools.test-credentials', ['school' => 'tempSchoolId']) }}";
            const finalUrl = baseUrl.replace('tempSchoolId', {{ $school->id }});

            fetch(finalUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    testBtn.innerHTML = originalText;
                    testBtn.disabled = false;

                    if (data.success) {
                        let detailsHtml = '';
                        const userData = data.user || data.user_details || data.user_info;
                        if (userData) {
                            detailsHtml = `
                            <div style="text-align: left; margin-top: 10px;">
                                <strong>User Found:</strong><br>
                                Name: ${userData.name || 'N/A'}<br>
                                Position: ${userData.position || 'N/A'}<br>
                                Department: ${userData.department || 'N/A'}<br>
                                Status: ${userData.status || 'N/A'}
                            </div>
                        `;
                        } else if (data.remote_test) {
                            detailsHtml = `
                            <div style="text-align: left; margin-top: 10px; padding: 10px; background: #fef3c7; border-radius: 6px;">
                                <i class="fas fa-info-circle" style="color: #d97706; margin-right: 5px;"></i>
                                <strong>Note:</strong> Credentials were validated by the school's system, but no user details were returned. 
                                This may indicate the school's API is not fully configured for user information retrieval.
                            </div>
                        `;
                        }

                        const successDialog = document.createElement('div');
                        successDialog.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.5);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            z-index: 9999;
                        `;

                        successDialog.innerHTML = `
                            <div style="
                                background: white;
                                padding: 30px;
                                border-radius: 8px;
                                max-width: 500px;
                                width: 90%;
                                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                            ">
                                <div style="
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 20px;
                                    color: #16a34a;
                                ">
                                    <i class="fas fa-check-circle" style="font-size: 24px; margin-right: 10px;"></i>
                                    <h3 style="margin: 0; color: #16a34a;">Credentials Valid!</h3>
                                </div>
                                <p style="margin-bottom: 15px;">${data.message}</p>
                                ${detailsHtml}
                                <div style="
                                    margin-top: 15px;
                                    padding: 10px;
                                    background: #f0fdf4;
                                    border-radius: 6px;
                                    border-left: 4px solid #16a34a;
                                ">
                                    <i class="fas fa-check-circle" style="color: #16a34a; margin-right: 5px;"></i>
                                    These credentials can be used for regional access.
                                </div>
                                <div style="
                                    display: flex;
                                    justify-content: flex-end;
                                    margin-top: 20px;
                                ">
                                    <button onclick="this.parentElement.parentElement.parentElement.remove()" style="
                                        background: #10b981;
                                        color: white;
                                        border: none;
                                        padding: 10px 20px;
                                        border-radius: 6px;
                                        cursor: pointer;
                                        font-weight: 500;
                                    ">OK</button>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(successDialog);
                    } else {
                        let iconType = 'error';
                        let additionalInfo = '';

                        if (data.user_exists === false) {
                            iconType = 'warning';
                            additionalInfo = `
                            <div style="margin-top: 10px; padding: 10px; background: #fef3c7; border-radius: 6px;">
                                <i class="fas fa-exclamation-triangle" style="color: #d97706;"></i>
                                No user account exists with this email in the school system.
                            </div>
                        `;
                        } else if (data.password_valid === false) {
                            additionalInfo = `
                            <div style="margin-top: 10px; padding: 10px; background: #fee2e2; border-radius: 6px;">
                                <i class="fas fa-times-circle" style="color: #dc2626;"></i>
                                User exists but the password is incorrect.
                            </div>
                        `;
                        } else if (data.account_active === false) {
                            iconType = 'warning';
                            additionalInfo = `
                            <div style="margin-top: 10px; padding: 10px; background: #fef3c7; border-radius: 6px;">
                                <i class="fas fa-user-slash" style="color: #d97706;"></i>
                                User exists but the account is inactive (Status: ${data.user_status || 'Unknown'})
                            </div>
                        `;
                        } else if (!data.remote_test) {
                            iconType = 'error';
                            additionalInfo = `
                            <div style="margin-top: 10px; padding: 10px; background: #fee2e2; border-radius: 6px;">
                                <i class="fas fa-plug" style="color: #dc2626;"></i>
                                Could not connect to the school's system. Please check:
                                <ul style="text-align: left; margin-top: 5px;">
                                    <li>School website URL is correct</li>
                                    <li>School system is online</li>
                                    <li>API endpoint is accessible</li>
                                </ul>
                            </div>
                        `;
                        }

                        const errorDialog = document.createElement('div');
                        errorDialog.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.5);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            z-index: 9999;
                        `;

                        const iconColor = iconType === 'warning' ? '#f59e0b' : '#ef4444';
                        const buttonColor = iconType === 'warning' ? '#f59e0b' : '#ef4444';
                        const iconClass = iconType === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';

                        errorDialog.innerHTML = `
                            <div style="
                                background: white;
                                padding: 30px;
                                border-radius: 8px;
                                max-width: 500px;
                                width: 90%;
                                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                            ">
                                <div style="
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 20px;
                                    color: ${iconColor};
                                ">
                                    <i class="fas ${iconClass}" style="font-size: 24px; margin-right: 10px;"></i>
                                    <h3 style="margin: 0; color: ${iconColor};">Test Failed</h3>
                                </div>
                                <p style="margin-bottom: 15px;">${data.message}</p>
                                ${additionalInfo}
                                <div style="
                                    display: flex;
                                    justify-content: flex-end;
                                    margin-top: 20px;
                                ">
                                    <button onclick="this.parentElement.parentElement.parentElement.remove()" style="
                                        background: ${buttonColor};
                                        color: white;
                                        border: none;
                                        padding: 10px 20px;
                                        border-radius: 6px;
                                        cursor: pointer;
                                        font-weight: 500;
                                    ">OK</button>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(errorDialog);
                    }
                })
                .catch(error => {
                    testBtn.innerHTML = originalText;
                    testBtn.disabled = false;

                    console.error('Test error:', error);

                    Swal.fire({
                        title: 'Connection Error',
                        html: `
                        <div>
                            <p>Failed to test credentials. This could be due to:</p>
                            <ul style="text-align: left; margin-top: 10px;">
                                <li>Network connectivity issues</li>
                                <li>School system is offline</li>
                                <li>Invalid school website URL</li>
                            </ul>
                        </div>
                    `,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        // ========================================
        // TAB PERSISTENCE & MANAGEMENT
        // ========================================
        (function() {
            'use strict';

            // ========================================
            // CONFIGURATION & CONSTANTS
            // ========================================
            const SCHOOL_CONFIG = {
                id: {{ $school->id }},
                name: @json($school->name)
            };

            // ========================================
            // TAB PERSISTENCE & MANAGEMENT
            // ========================================
            function clearTabPersistence() {
                const tabStorageKey = `schoolEditTabActive_${SCHOOL_CONFIG.id}`;
                try {
                    localStorage.removeItem(tabStorageKey);
                } catch (error) {
                    console.warn('Failed to clear tab state from localStorage:', error);
                }
            }

            function setActiveTab(tabId) {
                if (typeof tabId === 'string' && tabId.trim() !== '') {
                    activateTab(tabId);
                }
            }

            function getActiveTab() {
                const tabStorageKey = `schoolEditTabActive_${SCHOOL_CONFIG.id}`;
                try {
                    return localStorage.getItem(tabStorageKey) || 'basic-info';
                } catch (error) {
                    console.warn('Failed to read active tab from localStorage:', error);
                    return 'basic-info';
                }
            }

            function resetToBasicInfoTab() {
                const tabStorageKey = `schoolEditTabActive_${SCHOOL_CONFIG.id}`;
                try {
                    localStorage.removeItem(tabStorageKey);
                } catch (error) {
                    console.warn('Failed to clear tab state from localStorage:', error);
                }
                activateTab('basic-info');
            }

            function initializeTabPersistence() {
                const tabButtons = document.querySelectorAll('#schoolFormTabs .nav-link');
                const tabPanes = document.querySelectorAll('#schoolFormTabContent .tab-pane');

                // Function to activate a specific tab
                function activateTab(tabId) {
                    // Validate tabId
                    if (!tabId || typeof tabId !== 'string') {
                        console.warn('Invalid tabId provided to activateTab:', tabId);
                        return false;
                    }

                    // Remove active class from all tabs and panes
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.setAttribute('aria-selected', 'false');
                    });

                    tabPanes.forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Activate the selected tab
                    const targetTab = document.querySelector(`#${tabId}-tab`);
                    const targetPane = document.querySelector(`#${tabId}`);

                    if (targetTab && targetPane) {
                        targetTab.classList.add('active');
                        targetTab.setAttribute('aria-selected', 'true');
                        targetPane.classList.add('show', 'active');

                        // Save to localStorage with school-specific key
                        const tabStorageKey = `schoolEditTabActive_${SCHOOL_CONFIG.id}`;
                        try {
                            localStorage.setItem(tabStorageKey, tabId);
                        } catch (error) {
                            console.warn('Failed to save tab state to localStorage:', error);
                        }

                        // Trigger Bootstrap tab event
                        const event = new Event('shown.bs.tab');
                        targetTab.dispatchEvent(event);

                        return true;
                    } else {
                        console.warn(`Tab elements not found for tabId: ${tabId}`);
                        return false;
                    }
                }

                // Add click event listeners to all tab buttons
                tabButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        const tabId = this.id.replace('-tab', '');
                        console.log(`Tab clicked: ${tabId}`);
                        const success = activateTab(tabId);
                        if (success) {
                            console.log(`Tab ${tabId} activated and saved to localStorage`);
                        }
                    });
                });

                // Restore active tab from localStorage on page load
                // Use school-specific key to avoid conflicts between different schools
                const tabStorageKey = `schoolEditTabActive_${SCHOOL_CONFIG.id}`;
                let savedTab = null;

                try {
                    savedTab = localStorage.getItem(tabStorageKey);
                } catch (error) {
                    console.warn('Failed to read tab state from localStorage:', error);
                }

                if (savedTab && savedTab !== 'basic-info') {
                    console.log(`Restoring saved tab: ${savedTab}`);
                    // Small delay to ensure DOM is ready
                    setTimeout(() => {
                        const success = activateTab(savedTab);
                        if (!success) {
                            console.log('Failed to restore tab, falling back to basic-info');
                            // If restoration fails, ensure basic-info tab is active
                            const basicInfoTab = document.querySelector('#basic-info-tab');
                            const basicInfoPane = document.querySelector('#basic-info');
                            if (basicInfoTab && basicInfoPane) {
                                basicInfoTab.classList.add('active');
                                basicInfoTab.setAttribute('aria-selected', 'true');
                                basicInfoPane.classList.add('show', 'active');
                            }
                        } else {
                            console.log(`Successfully restored tab: ${savedTab}`);
                        }
                    }, 100);
                } else {
                    console.log('No saved tab found, using default basic-info tab');
                }
            }

            // ========================================
            // WINDOW OBJECT EXPORTS
            // ========================================
            window.clearTabPersistence = clearTabPersistence;
            window.setActiveTab = setActiveTab;
            window.getActiveTab = getActiveTab;
            window.resetToBasicInfoTab = resetToBasicInfoTab;

            // Initialize tab persistence when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeTabPersistence);
            } else {
                initializeTabPersistence();
            }

        })();
    </script>
@endsection

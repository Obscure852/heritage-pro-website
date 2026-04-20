@extends('layouts.master')

@section('title')
    Add New School
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
            justify-content: flex-end;
            padding-top: 32px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
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

        .btn-light {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-light:hover {
            background: #e5e7eb;
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

        .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 2px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
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
                flex-direction: column-reverse;
                gap: 12px;
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
            New School
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle label-icon"></i><strong>Please complete all required fields marked with
                        an
                        asterisk (*) and check all tabs to ensure nothing is missed.</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <form action="{{ route('schools.store') }}" method="POST" id="createSchoolForm">
                    @csrf

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
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="schoolFormTabContent">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">School Information</div>
                                <div class="help-content">
                                    Define the basic information for this school. This will be used to identify and organize
                                    the school within the system.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">School Name <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                        placeholder="e.g., Central Primary School" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Full official name of the school</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">School Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}"
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
                                        <label class="school-type-option" id="{{ $key }}Option">
                                            <input type="radio" name="school_type" value="{{ $key }}"
                                                {{ old('school_type') == $key ? 'checked' : '' }} required>
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
                                        <label class="ownership-type-option" id="{{ $key }}OwnershipOption">
                                            <input type="radio" name="ownership_type" value="{{ $key }}"
                                                {{ old('ownership_type') == $key ? 'checked' : '' }} required>
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
                                class="{{ old('ownership_type') == 'government_aided' ? 'show' : '' }}">
                                <h5 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                                    <i class="fas fa-church"></i> Mission School Information
                                </h5>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Religious Affiliation <span
                                                class="required">*</span></label>
                                        <input type="text" name="religious_affiliation" class="form-control"
                                            value="{{ old('religious_affiliation') }}"
                                            placeholder="e.g., Roman Catholic">
                                        @error('religious_affiliation')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Mission Organization</label>
                                        <input type="text" name="mission_organization" class="form-control"
                                            value="{{ old('mission_organization') }}"
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
                                                {{ (old('region_id') ?? $selectedRegionId) == $region->id ? 'selected' : '' }}>
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
                                                {{ old('operational_status', 'fully_operational') == $key ? 'selected' : '' }}>
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
                                        value="{{ old('established_date') }}">
                                    @error('established_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">When the school was founded</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" name="registration_number" class="form-control"
                                        value="{{ old('registration_number') }}" placeholder="e.g., EDU/2024/001">
                                    @error('registration_number')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Official registration number</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-textarea" rows="4"
                                    placeholder="Brief description about the school...">{{ old('description') }}</textarea>
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
                                    Provide contact details for this school. This information will be used for official
                                    correspondence and directory listings.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Physical Address</label>
                                    <textarea name="physical_address" class="form-textarea" rows="3"
                                        placeholder="Street address, building, plot number...">{{ old('physical_address') }}</textarea>
                                    @error('physical_address')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Physical location of the school</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Postal Address</label>
                                    <input type="text" name="postal_address" class="form-control"
                                        value="{{ old('postal_address') }}" placeholder="e.g., P.O. Box 123, Gaborone">
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
                                        value="{{ old('telephone_primary') }}" placeholder="e.g., 3912345">
                                    @error('telephone_primary')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Main contact number</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Secondary Telephone</label>
                                    <input type="tel" name="telephone_secondary" class="form-control"
                                        value="{{ old('telephone_secondary') }}" placeholder="e.g., 3912346">
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
                                        value="{{ old('fax') }}" placeholder="e.g., 3912347">
                                    @error('fax')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Fax number (if available)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email') }}" placeholder="e.g., info@school.bw">
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
                                        value="{{ old('website') }}" placeholder="https://www.school.bw">
                                    @error('website')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">School website URL (if available)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Access Email</label>
                                    <input type="email" name="access_email" class="form-control"
                                        value="{{ old('access_email') }}" placeholder="e.g., admin@school.bw">
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
                                            class="form-control" value="{{ old('access_password') }}"
                                            placeholder="Enter secure password">
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
                                            value="{{ old('access_password_confirmation') }}"
                                            placeholder="Confirm password">
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


                        </div>

                        <!-- Leadership Tab -->
                        <div class="tab-pane fade" id="leadership" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">School Leadership</div>
                                <div class="help-content">
                                    Enter information about the school's leadership team. This helps in communication and
                                    administrative coordination.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Principal Name</label>
                                    <input type="text" name="principal_name" class="form-control"
                                        value="{{ old('principal_name') }}" placeholder="e.g., Mr. John Doe">
                                    @error('principal_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Name of the school principal</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Principal Email</label>
                                    <input type="email" name="principal_email" class="form-control"
                                        value="{{ old('principal_email') }}" placeholder="e.g., principal@school.bw">
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
                                        value="{{ old('deputy_principal_name') }}" placeholder="e.g., Ms. Jane Smith">
                                    @error('deputy_principal_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Name of the deputy principal</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Deputy Principal Email</label>
                                    <input type="email" name="deputy_principal_email" class="form-control"
                                        value="{{ old('deputy_principal_email') }}" placeholder="e.g., deputy@school.bw">
                                    @error('deputy_principal_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Deputy principal's email address</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Partnership Details</label>
                                <textarea name="partnership_details" class="form-textarea" rows="4"
                                    placeholder="Details about partnerships, sister schools, or collaborative programs...">{{ old('partnership_details') }}</textarea>
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
                                    Provide current enrollment numbers and staffing information. This data is crucial for
                                    resource allocation and planning.
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-graduation-cap"></i> Student Enrollment
                            </h5>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Current Enrollment <span class="required">*</span></label>
                                    <input type="number" name="current_enrollment" class="form-control"
                                        value="{{ old('current_enrollment', 0) }}" min="0" required>
                                    @error('current_enrollment')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Total number of enrolled students</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Enrollment Capacity</label>
                                    <input type="number" name="enrollment_capacity" class="form-control"
                                        value="{{ old('enrollment_capacity') }}" min="1">
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
                                        value="{{ old('total_teachers', 0) }}" min="0" required>
                                    @error('total_teachers')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Total number of teaching staff</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Qualified Teachers <span class="required">*</span></label>
                                    <input type="number" name="qualified_teachers" class="form-control"
                                        value="{{ old('qualified_teachers', 0) }}" min="0" required>
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
                                        value="{{ old('total_classrooms') }}" min="0">
                                    @error('total_classrooms')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted">Number of classroom facilities</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Total Streams</label>
                                    <input type="number" name="total_streams" class="form-control"
                                        value="{{ old('total_streams') }}" min="0">
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
                                    Select available facilities and configure operational settings for this school.
                                </div>
                            </div>

                            <h5
                                style="font-size: 16px; font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-check-square"></i> Available Facilities
                            </h5>

                            <div class="facilities-grid">
                                <div class="facility-card">
                                    <input type="checkbox" name="has_boarding_facilities" value="1"
                                        {{ old('has_boarding_facilities') ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                    <div class="facility-name">Boarding</div>
                                    <div class="facility-description">Dormitories & accommodation</div>
                                </div>

                                <div class="facility-card">
                                    <input type="checkbox" name="has_library" value="1"
                                        {{ old('has_library') ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="facility-name">Library</div>
                                    <div class="facility-description">Reading room & resources</div>
                                </div>

                                <div class="facility-card">
                                    <input type="checkbox" name="has_computer_lab" value="1"
                                        {{ old('has_computer_lab') ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="facility-name">Computer Lab</div>
                                    <div class="facility-description">ICT facilities</div>
                                </div>

                                <div class="facility-card">
                                    <input type="checkbox" name="has_science_lab" value="1"
                                        {{ old('has_science_lab') ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <div class="facility-name">Science Lab</div>
                                    <div class="facility-description">Laboratory equipment</div>
                                </div>

                                <div class="facility-card">
                                    <input type="checkbox" name="has_sports_facilities" value="1"
                                        {{ old('has_sports_facilities') ? 'checked' : '' }}>
                                    <div class="facility-icon">
                                        <i class="fas fa-running"></i>
                                    </div>
                                    <div class="facility-name">Sports</div>
                                    <div class="facility-description">Fields & equipment</div>
                                </div>

                                <div class="facility-card">
                                    <input type="checkbox" name="has_special_education_units" value="1"
                                        {{ old('has_special_education_units') ? 'checked' : '' }}>
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
                                        {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label for="is_active">Active School</label>
                                </div>
                                <div class="text-muted">School will be visible and accessible in the system</div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_operational" id="is_operational" value="1"
                                        {{ old('is_operational', '1') ? 'checked' : '' }}>
                                    <label for="is_operational">Currently Operational</label>
                                </div>
                                <div class="text-muted">School is currently functioning and accepting students</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-textarea" rows="4"
                                    placeholder="Any additional information or special notes about this school...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="text-muted">Internal notes and additional information</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="{{ route('schools.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="button" class="btn btn-light" onclick="saveDraft()">
                            <i class="fas fa-save"></i>
                            Save Draft
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-check"></i>
                            Create School
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle school type selection
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

            // Handle ownership type selection
            const ownershipOptions = document.querySelectorAll('.ownership-type-option');
            const ownershipRadios = document.querySelectorAll('input[name="ownership_type"]');
            const missionFields = document.getElementById('missionFields');

            function updateOwnershipSelection() {
                const selected = document.querySelector('input[name="ownership_type"]:checked');
                ownershipOptions.forEach(option => {
                    option.classList.toggle('selected', option.querySelector('input').checked);
                });

                // Toggle mission fields
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

            // Handle facility selection
            const facilityCards = document.querySelectorAll('.facility-card');

            facilityCards.forEach(card => {
                const checkbox = card.querySelector('input[type="checkbox"]');

                // Set initial state
                if (checkbox.checked) {
                    card.classList.add('selected');
                }

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

            // Initial updates
            updateSchoolTypeSelection();
            updateOwnershipSelection();

            // Form validation
            document.getElementById('createSchoolForm').addEventListener('submit', function(e) {
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
                    e.preventDefault();
                    alert('Current enrollment cannot exceed enrollment capacity');
                    return false;
                }

                if (qualifiedTeachers > totalTeachers) {
                    e.preventDefault();
                    alert('Qualified teachers cannot exceed total teachers');
                    return false;
                }

                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
                submitBtn.disabled = true;
            });
        });

        // Save draft functionality
        function saveDraft() {
            const form = document.getElementById('createSchoolForm');
            const formData = new FormData(form);

            const draft = {};
            for (let [key, value] of formData.entries()) {
                draft[key] = value;
            }

            localStorage.setItem('schoolDraft', JSON.stringify(draft));
            alert('Draft saved successfully!');
        }

        window.addEventListener('load', function() {
            const draft = localStorage.getItem('schoolDraft');
            if (draft && confirm('A draft was found. Do you want to restore it?')) {
                const data = JSON.parse(draft);
                for (let key in data) {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field) {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = data[key] === field.value || data[key] === '1';
                            if (field.checked) {
                                field.dispatchEvent(new Event('change'));
                            }
                        } else {
                            field.value = data[key];
                        }
                    }
                }
            }
        });

        // Toggle password visibility
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
    </script>
@endsection

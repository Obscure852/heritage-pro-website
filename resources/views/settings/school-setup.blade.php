@extends('layouts.master')
@section('title')
    School Information
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 0;
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

        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            background: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-tabs-custom .nav-item {
            margin-bottom: -1px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6b7280;
            padding: 14px 20px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            border-bottom-color: transparent;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link i {
            color: inherit;
        }

        .tab-content {
            padding: 24px;
        }

        .form-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control-sm,
        .form-select-sm {
            padding: 8px 12px;
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .readonly-input {
            background-color: #f3f4f6 !important;
            color: #6b7280;
            cursor: default;
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
            background: #f8fafc;
        }

        .file-input-label.has-file {
            border-color: #10b981;
            border-style: solid;
            background: #ecfdf5;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
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

        .file-input-text .title {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .file-input-text .subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-fixed-width {
            width: auto;
            min-width: 180px;
            justify-content: center;
            text-align: center;
            padding: 10px 16px;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-fixed-width .btn-text,
        .btn-fixed-width .btn-spinner {
            justify-content: center;
            white-space: nowrap;
        }

        .btn-fixed-width .btn-text i,
        .btn-fixed-width .btn-spinner i {
            font-size: 14px;
        }

        .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            font-size: 14px;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-text i {
            font-size: 14px;
        }

        .btn-loading .btn-spinner {
            display: none;
            align-items: center;
        }

        .btn-loading.loading .btn-text {
            display: none !important;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
        }

        .btn-loading.loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .license-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .license-card h5 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .branding-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .branding-card h6 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .logo-preview {
            margin-top: 16px;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            display: inline-block;
        }

        .term-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .rollover-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .rollover-card h5 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .config-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .config-card h5 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .template-download-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 18px;
            height: 100%;
        }

        .template-download-card h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .template-download-card p {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.45;
            margin-bottom: 14px;
        }

        .template-download-card .badge {
            margin-bottom: 10px;
        }

        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
        }

        .modal-backdrop.show {
            opacity: 0.4 !important;
        }

        .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
        }

        .list-group-item {
            border-color: #e5e7eb;
        }

        .year-nav-btn {
            cursor: pointer;
            font-size: 1.5rem;
            color: #4e73df;
            transition: all 0.2s ease;
        }

        .year-nav-btn:hover {
            color: #2563eb;
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .nav-tabs-custom {
                padding: 0 16px;
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .nav-tabs-custom .nav-link {
                padding: 12px 14px;
                font-size: 13px;
                white-space: nowrap;
            }

            .tab-content {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="#">School Setup</a>
        @endslot
        @slot('title')
            School Information
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

    @if (session('results'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-information label-icon"></i><strong>{{ session('results') }}</strong>
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

    @php
        $templateSchoolType = \App\Models\SchoolSetup::normalizeType($schoolSetup->type ?? null) ?? \App\Models\SchoolSetup::TYPE_JUNIOR;
        $currentStudentTemplateVariants = match ($templateSchoolType) {
            \App\Models\SchoolSetup::TYPE_PRE_F3 => [
                [
                    'label' => 'Current School - Elementary',
                    'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                    'description' => 'Current PRE_F3 mode: use for REC and STD 1 to STD 7 student imports.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
                [
                    'label' => 'Current School - Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Current PRE_F3 mode: use for F1 to F3 student imports with PSLE columns.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR => [
                [
                    'label' => 'Current School - Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Current Middle & High School mode: use for F1 to F3 student imports with PSLE columns.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
                [
                    'label' => 'Current School - High School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                    'description' => 'Current Middle & High School mode: use for F4 to F5 student imports with JCE columns.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_K12 => [
                [
                    'label' => 'Current School - Elementary',
                    'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                    'description' => 'Current Pre-F5 mode: use for REC and STD 1 to STD 7 student imports.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
                [
                    'label' => 'Current School - Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Current Pre-F5 mode: use for F1 to F3 student imports with PSLE columns.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
                [
                    'label' => 'Current School - High School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                    'description' => 'Current Pre-F5 mode: use for F4 to F5 student imports with JCE columns.',
                    'badge' => 'Current',
                    'badge_class' => 'bg-primary',
                ],
            ],
            default => [[
                'label' => 'Current School',
                'school_type' => $templateSchoolType,
                'description' => 'Uses the template for the school mode currently configured on this system.',
                'badge' => 'Current',
                'badge_class' => 'bg-primary',
            ]],
        };
        $studentTemplateVariants = array_merge($currentStudentTemplateVariants, [
            [
                'label' => 'Primary',
                'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                'description' => 'Student template for REC and STD 1 to STD 7 only.',
                'badge' => 'Single Mode',
                'badge_class' => 'bg-light text-dark',
            ],
            [
                'label' => 'Junior',
                'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                'description' => 'Student template for F1 to F3 with PSLE columns.',
                'badge' => 'Single Mode',
                'badge_class' => 'bg-light text-dark',
            ],
            [
                'label' => 'Senior',
                'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                'description' => 'Student template for F4 to F5 with JCE columns.',
                'badge' => 'Single Mode',
                'badge_class' => 'bg-light text-dark',
            ],
        ]);
    @endphp

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-school me-2"></i>School Configuration</h3>
            <p>Manage school information, branding, terms, and system settings</p>
        </div>

        <div class="settings-body">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist" id="schoolSetupTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab" data-tab-id="home1">
                        <span class="d-block d-sm-none"><i class="fas fa-info-circle"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-info-circle me-1"></i> School Information
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#license" role="tab" data-tab-id="license">
                        <span class="d-block d-sm-none"><i class="fas fa-key"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-key me-1"></i> License
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab" data-tab-id="profile1">
                        <span class="d-block d-sm-none"><i class="fas fa-palette"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-palette me-1"></i> Branding
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab" data-tab-id="messages1">
                        <span class="d-block d-sm-none"><i class="fas fa-calendar-alt"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-calendar-alt me-1"></i> Term Setup
                        </span>
                    </a>
                </li>
                @can('view-system-admin')
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab" data-tab-id="settings1">
                            <span class="d-block d-sm-none"><i class="fas fa-sync-alt"></i></span>
                            <span class="d-none d-sm-block">
                                <i class="fas fa-sync-alt me-1"></i> Year Rollover
                            </span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings2" role="tab" data-tab-id="settings2">
                        <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-cog me-1"></i> Settings
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#templates1" role="tab" data-tab-id="templates1">
                        <span class="d-block d-sm-none"><i class="fas fa-download"></i></span>
                        <span class="d-none d-sm-block">
                            <i class="fas fa-download me-1"></i> Templates
                        </span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- School Information Tab -->
                <div class="tab-pane active" id="home1" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">School Profile</div>
                        <div class="help-content">
                            Update your school's basic information, contact details, and regional settings.
                        </div>
                    </div>

                    <form class="needs-validation" method="POST" action="{{ route('setup.store') }}">
                        @csrf
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-building me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">School Name <span class="text-danger">*</span></label>
                                    <input type="text" name="school_name"
                                        class="form-control form-control-sm @error('school_name') is-invalid @enderror"
                                        placeholder="School Name"
                                        value="{{ old('school_name', $schoolSetup->school_name ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">School ID <small class="text-muted">(Unique identifier)</small></label>
                                    <div class="input-group">
                                        <input type="text" name="school_id" id="school_id"
                                            class="form-control form-control-sm readonly-input"
                                            value="{{ old('school_id', $schoolSetup->school_id ?? '') }}" readonly>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="copySchoolId()" title="Copy School ID">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ownership</label>
                                    <select name="ownership"
                                        class="form-select form-select-sm @error('ownership') is-invalid @enderror">
                                        <option value="">Choose ownership type...</option>
                                        <option value="Public"
                                            {{ old('ownership', $schoolSetup->ownership ?? '') == 'Public' ? 'selected' : '' }}>
                                            Public</option>
                                        <option value="Private"
                                            {{ old('ownership', $schoolSetup->ownership ?? '') == 'Private' ? 'selected' : '' }}>
                                            Private</option>
                                        <option value="Government Aided"
                                            {{ old('ownership', $schoolSetup->ownership ?? '') == 'Government Aided' ? 'selected' : '' }}>
                                            Government Aided</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slogan</label>
                                    <input type="text" name="slogan"
                                        class="form-control form-control-sm @error('slogan') is-invalid @enderror"
                                        placeholder="Slogan"
                                        value="{{ old('slogan', $schoolSetup->slogan ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type</label>
                                    <select disabled name="type" class="form-select form-select-sm">
                                        <option value="">Choose ...</option>
                                        <option value="Primary"
                                            {{ $schoolSetup->type == 'Primary' ? 'selected' : '' }}>Primary</option>
                                        <option value="Junior"
                                            {{ $schoolSetup->type == 'Junior' ? 'selected' : '' }}>Junior</option>
                                        <option value="Senior"
                                            {{ $schoolSetup->type == 'Senior' ? 'selected' : '' }}>Senior</option>
                                        <option value="PRE_F3"
                                            {{ $schoolSetup->type == 'PRE_F3' ? 'selected' : '' }}>PRE_F3</option>
                                        <option value="JUNIOR_SENIOR"
                                            {{ $schoolSetup->type == 'JUNIOR_SENIOR' ? 'selected' : '' }}>Middle &amp; High School</option>
                                        <option value="K12"
                                            {{ $schoolSetup->type == 'K12' ? 'selected' : '' }}>K12</option>
                                        <option value="Unified"
                                            {{ $schoolSetup->type == 'Unified' ? 'selected' : '' }}>Unified</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Boarding</label>
                                    <select name="boarding"
                                        class="form-select form-select-sm @error('boarding') is-invalid @enderror">
                                        <option value="">Choose...</option>
                                        <option value="0"
                                            {{ old('boarding', $schoolSetup->boarding ?? '') === false || old('boarding', $schoolSetup->boarding ?? '') == '0' ? 'selected' : '' }}>
                                            Day School</option>
                                        <option value="1"
                                            {{ old('boarding', $schoolSetup->boarding ?? '') === true || old('boarding', $schoolSetup->boarding ?? '') == '1' ? 'selected' : '' }}>
                                            Boarding School</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-phone-alt me-2"></i>Contact Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telephone <span class="text-danger">*</span></label>
                                    <input type="text" name="telephone"
                                        class="form-control form-control-sm @error('telephone') is-invalid @enderror"
                                        placeholder="Telephone"
                                        value="{{ old('telephone', $schoolSetup->telephone ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fax <span class="text-danger">*</span></label>
                                    <input type="text" name="fax"
                                        class="form-control form-control-sm @error('fax') is-invalid @enderror"
                                        placeholder="Fax"
                                        value="{{ old('fax', $schoolSetup->fax ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="text" name="email_address"
                                        class="form-control form-control-sm @error('email_address') is-invalid @enderror"
                                        placeholder="Email Address"
                                        value="{{ old('email_address', $schoolSetup->email_address ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website <span class="text-danger">*</span></label>
                                    <input type="text" name="website"
                                        class="form-control form-control-sm @error('website') is-invalid @enderror"
                                        placeholder="Website"
                                        value="{{ old('website', $schoolSetup->website ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Physical Address <span class="text-danger">*</span></label>
                                    <input type="text" name="physical_address"
                                        class="form-control form-control-sm @error('physical_address') is-invalid @enderror"
                                        placeholder="Physical Address"
                                        value="{{ old('physical_address', $schoolSetup->physical_address ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Postal Address <span class="text-danger">*</span></label>
                                    <input type="text" name="postal_address"
                                        class="form-control form-control-sm @error('postal_address') is-invalid @enderror"
                                        placeholder="Postal Address"
                                        value="{{ old('postal_address', $schoolSetup->postal_address ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Regional Office</label>
                                    <select class="form-select form-select-sm" name="region">
                                        <option value="">Choose ...</option>
                                        @if ($regional_offices->count() > 0)
                                            @foreach ($regional_offices as $office)
                                                <option value="{{ $office->region }}"
                                                    {{ $office->region == $schoolSetup->region ? 'selected' : '' }}>
                                                    {{ $office->region ?? '' }} - {{ $office->location ?? '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-signature me-2"></i>Communication Settings</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SMS Signature</label>
                                    <input type="text" name="school_sms_signature"
                                        class="form-control form-control-sm @error('school_sms_signature') is-invalid @enderror"
                                        placeholder="From: School Name"
                                        value="{{ old('school_sms_signature', $schoolSetup->school_sms_signature ?? '') }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email Signature</label>
                                    <textarea name="school_email_signature"
                                        class="form-control form-control-sm @error('school_email_signature') is-invalid @enderror"
                                        rows="3" placeholder="Heritage EMS">{{ old('school_email_signature', $schoolSetup->school_email_signature ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Update School Information</span>
                                <span class="btn-spinner">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- License Tab -->
                <div class="tab-pane" id="license" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">License Management</div>
                        <div class="help-content">
                            View and manage your school's software license. Contact support if you need to renew.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            @if (Auth::user()->hasRoles('Administrator') && Auth::user()->email === 'obscure852@gmail.com')
                                <div class="form-section">
                                    <h6 class="form-section-title"><i class="fas fa-key me-2"></i>License Configuration</h6>
                                    <form action="{{ route('setup.create-school-license') }}" method="POST">
                                        @csrf
                                        @if (isset($latestLicense))
                                            <input type="hidden" name="id" value="{{ $latestLicense->id }}">
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label">License Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm"
                                                value="{{ $schoolSetup->school_name ?? '' }}" readonly>
                                            <input type="hidden" name="name" value="{{ $schoolSetup->school_name ?? '' }}">
                                        </div>

                                        @if (isset($latestLicense))
                                            <div class="mb-3">
                                                <label class="form-label">License Key</label>
                                                <input type="text" class="form-control form-control-sm" name="key"
                                                    value="{{ old('key', $latestLicense->key ?? '') }}" readonly>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control form-control-sm" name="start_date"
                                                    value="{{ old('start_date', isset($latestLicense->start_date) ? $latestLicense->start_date->format('Y-m-d') : '') }}"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control form-control-sm" name="end_date"
                                                    value="{{ old('end_date', isset($latestLicense->end_date) ? $latestLicense->end_date->format('Y-m-d') : '') }}"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="year" required>
                                                    @php
                                                        $currentYear = date('Y');
                                                        $endYear = $currentYear + 4;
                                                    @endphp
                                                    @for ($yr = $currentYear; $yr <= $endYear; $yr++)
                                                        <option value="{{ $yr }}"
                                                            {{ old('year', $latestLicense->year ?? $currentYear) == $yr ? 'selected' : '' }}>
                                                            {{ $yr }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Grace Period (Days)</label>
                                                <input type="number" class="form-control form-control-sm" name="grace_period_days"
                                                    value="{{ old('grace_period_days', config('license.grace_period_days', 14)) }}"
                                                    min="0" max="90">
                                                <small class="text-muted">Days after expiration before degradation</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                                    {{ old('active', $latestLicense->active ?? false ? '1' : '') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="active">Active</label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> {{ isset($latestLicense) ? 'Update License' : 'Create License' }}</span>
                                            <span class="btn-spinner">
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="license-card">
                                    <h5><i class="fas fa-key me-2"></i>License Information</h5>
                                    @if (isset($latestLicense) && $latestLicense)
                                        <div class="mb-2">
                                            <strong>License:</strong> {{ $latestLicense->name }}
                                        </div>

                                        <div class="mb-2">
                                            <strong>Status:</strong>
                                            @if (isset($licenseData) && isset($licenseData['valid']) && $licenseData['valid'])
                                                <span class="badge bg-success">Active</span>
                                            @elseif(isset($licenseData) && isset($licenseData['in_grace_period']) && $licenseData['in_grace_period'])
                                                <span class="badge bg-warning">Grace Period</span>
                                            @elseif(now()->lessThan($latestLicense->start_date))
                                                <span class="badge bg-info">Not Started</span>
                                            @else
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <strong>Duration:</strong>
                                            {{ \Carbon\Carbon::parse($latestLicense->start_date)->format('M d, Y') }}
                                            <strong> - </strong>
                                            {{ \Carbon\Carbon::parse($latestLicense->end_date)->format('M d, Y') }}
                                        </div>

                                        @if (now()->lessThan($latestLicense->start_date))
                                            <div class="mb-3">
                                                <strong>License Starts:</strong>
                                                {{ \Carbon\Carbon::parse($latestLicense->start_date)->format('M d, Y') }}
                                                (in {{ now()->diffInDays($latestLicense->start_date) }} days)
                                            </div>
                                        @endif

                                        @if (isset($licenseData) && isset($licenseData['in_grace_period']) && $licenseData['in_grace_period'] && isset($licenseData['grace_ends']))
                                            <div class="mb-3">
                                                <strong>Grace Period Ends:</strong>
                                                {{ \Carbon\Carbon::parse($licenseData['grace_ends'])->format('M d, Y') }}
                                                ({{ now()->diffInDays($licenseData['grace_ends']) }} days remaining)
                                            </div>
                                        @endif

                                        @if (isset($licenseData) && isset($licenseData['is_expiring_soon']) && $licenseData['is_expiring_soon'])
                                            <div class="alert alert-info small py-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                License will expire in {{ $licenseData['days_remaining'] }} days.
                                                Please contact your administrator to renew.
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            No license found. Please contact your administrator.
                                        </div>
                                    @endif

                                    @if (isset($latestLicense) && $latestLicense &&
                                        ((isset($licenseData) && isset($licenseData['in_grace_period']) && $licenseData['in_grace_period']) ||
                                        (isset($licenseData) && isset($licenseData['is_expiring_soon']) && $licenseData['is_expiring_soon'])))
                                        <div class="mt-3">
                                            <a href="mailto:support@heritagepro.co" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-envelope me-1"></i> Contact Support
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Branding Tab -->
                <div class="tab-pane" id="profile1" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">School Branding</div>
                        <div class="help-content">
                            Upload your school's logo and crop it to the standard 500 x 500 size before saving.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="branding-card">
                                <h6><i class="fas fa-image me-2"></i>School Logo</h6>
                                <div style="color: red;" id="avatarLogo"></div>

                                <form action="{{ route('setup.upload-logo') }}" method="POST" enctype="multipart/form-data" id="logoUploadForm">
                                    @csrf
                                    <div class="mb-3">
                                        <div class="custom-file-input">
                                            <input type="file" name="logo" id="image-logo"
                                                accept="image/png,image/jpeg,image/jpg,image/gif">
                                            <label for="image-logo" class="file-input-label" id="logoFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload logo</div>
                                                    <div class="subtitle">PNG, JPG or GIF. Crop to 500 x 500 before upload.</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-upload me-1"></i> Upload Logo</span>
                                        <span class="btn-spinner">
                                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                            Uploading...
                                        </span>
                                    </button>
                                </form>

                                <div id="logoCropPreview" class="mt-3"></div>

                                @if (!empty($schoolSetup->logo_path))
                                    <div class="logo-preview">
                                        <img height="100" src="{{ URL::asset($schoolSetup->logo_path) }}" alt="School Logo">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Login Page Image -->
                    <div class="row mt-4">
                        <div class="col-lg-8">
                            <div class="branding-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login Page Image</h6>
                                    @if ($schoolSetup->login_image_path)
                                        <span class="badge {{ $schoolSetup->use_custom_login_image ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $schoolSetup->use_custom_login_image ? 'Custom' : 'Default' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Default</span>
                                    @endif
                                </div>

                                <div class="help-text mb-3">
                                    <div class="help-title">Login Image Requirements</div>
                                    <div class="help-content">
                                        Select an image and crop it to the standard 1000 x 600 landscape size before upload.
                                    </div>
                                </div>

                                <div style="color: red;" id="loginImageError"></div>

                                <form action="{{ route('setup.upload-login-image') }}" method="POST" enctype="multipart/form-data" id="loginImageForm">
                                    @csrf
                                    <div class="mb-3">
                                        <div class="custom-file-input">
                                            <input type="file" name="login_image" id="image-login" accept="image/jpeg,image/png">
                                            <label for="image-login" class="file-input-label" id="loginFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload login image</div>
                                                    <div class="subtitle">JPG or PNG. Crop to 1000 x 600 before upload.</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-upload me-1"></i> Upload Login Image</span>
                                        <span class="btn-spinner">
                                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                            Uploading...
                                        </span>
                                    </button>
                                </form>

                                <div id="loginImageCropPreview" class="mt-3"></div>

                                @if (!empty($schoolSetup->login_image_path))
                                    <div class="logo-preview mt-3">
                                        <img style="max-width: 300px; height: auto; border-radius: 3px;" src="{{ URL::asset($schoolSetup->login_image_path) }}" alt="Login Page Image">
                                    </div>

                                    <div class="mt-3 pt-3" style="border-top: 1px solid #e5e7eb;">
                                        <form action="{{ route('setup.toggle-login-image') }}" method="POST" class="d-flex align-items-center gap-3">
                                            @csrf
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="loginImageToggle"
                                                    name="enable"
                                                    value="1"
                                                    {{ $schoolSetup->use_custom_login_image ? 'checked' : '' }}
                                                    onchange="this.closest('form').submit()">
                                                <label class="form-check-label fw-medium" for="loginImageToggle">
                                                    Use custom login image
                                                </label>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Term Setup Tab -->
                <div class="tab-pane" id="messages1" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">Academic Terms</div>
                        <div class="help-content">
                            Configure term start and end dates for the academic year. Closed terms cannot be modified.
                        </div>
                    </div>

                    <div class="term-card">
                        <div id="termsContainer">
                            <form action="{{ route('terms.update') }}" method="POST" id="termsForm">
                                @csrf
                                <input type="hidden" name="term_year" value="{{ $terms->first()->year ?? date('Y') }}">
                                @foreach ($terms as $term)
                                    <div class="row">
                                        <input type="hidden" name="term_ids[{{ $term->term }}]" value="{{ $term->id }}">

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">
                                                Term {{ $term->term }} Start Date ({{ $term->year }})
                                                <span class="text-danger">*</span>
                                                @if ($term->closed)
                                                    <span class="badge bg-danger">Closed</span>
                                                @endif
                                            </label>
                                            @if ($term->closed)
                                                <input class="form-control form-control-sm" type="date"
                                                    value="{{ $term->start_date->format('Y-m-d') }}" disabled>
                                                <input type="hidden" name="term{{ $term->term }}_start_date"
                                                    value="{{ $term->start_date->format('Y-m-d') }}">
                                            @else
                                                <input class="form-control form-control-sm" type="date"
                                                    name="term{{ $term->term }}_start_date"
                                                    id="term{{ $term->term }}_start_date"
                                                    value="{{ old('term' . $term->term . '_start_date', $term->start_date->format('Y-m-d')) }}">
                                            @endif
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">
                                                Term {{ $term->term }} End Date
                                                <span class="text-danger">*</span>
                                            </label>
                                            @if ($term->closed)
                                                <input class="form-control form-control-sm" type="date"
                                                    value="{{ $term->end_date->format('Y-m-d') }}" disabled>
                                                <input type="hidden" name="term{{ $term->term }}_end_date"
                                                    value="{{ $term->end_date->format('Y-m-d') }}">
                                            @else
                                                <input class="form-control form-control-sm" type="date"
                                                    name="term{{ $term->term }}_end_date"
                                                    id="term{{ $term->term }}_end_date"
                                                    value="{{ old('term' . $term->term . '_end_date', $term->end_date->format('Y-m-d')) }}">
                                            @endif
                                        </div>

                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">Extend Days</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="term{{ $term->term }}_extension_days"
                                                    id="term{{ $term->term }}_extension_days" min="0" max="180"
                                                    value="{{ old('term' . $term->term . '_extension_days', $term->extension_days) }}"
                                                    {{ $term->closed ? 'disabled' : '' }}>
                                                @if (!$term->closed)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="incrementExtensionDays('term{{ $term->term }}_extension_days')">
                                                        +
                                                    </button>
                                                @endif
                                                @if ($term->closed)
                                                    <input type="hidden" name="term{{ $term->term }}_extension_days"
                                                        value="{{ $term->extension_days }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <span class="year-nav-btn" id="prevYearBtn" title="Previous Year">
                                                <i class="bx bx-chevron-left"></i>
                                            </span>
                                            @can('view-system-admin')
                                                <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#termRolloverHistoryModal">
                                                    <i class="fas fa-history me-1"></i> Term Rollover History
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                                    data-bs-target="#termRolloverModal">
                                                    <i class="fas fa-sync-alt me-1"></i> Term Rollover
                                                </button>
                                            @endcan
                                            <button class="btn btn-primary btn-loading" type="submit">
                                                <span class="btn-text"><i class="fas fa-save me-1"></i> Update Term Dates</span>
                                                <span class="btn-spinner">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                    Saving...
                                                </span>
                                            </button>
                                            <span class="year-nav-btn" id="nextYearBtn" title="Next Year">
                                                <i class="bx bx-chevron-right"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Year Rollover Tab -->
                @can('view-system-admin')
                    <div class="tab-pane" id="settings1" role="tabpanel">
                        <div class="help-text">
                            <div class="help-title">Year-End Rollover</div>
                            <div class="help-content">
                                Transfer data from one academic year to the next. This includes grades, classes, subjects, and student allocations.
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5 class="section-title">Year-End Rollover</h5>
                                <form id="rolloverForm">
                                    @csrf
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkGrades" checked disabled>
                                        <label class="form-check-label" for="checkGrades">
                                            <strong>Grades</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Promotes grading setup to the next academic
                                            year</small>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkClasses" checked disabled>
                                        <label class="form-check-label" for="checkClasses">
                                            <strong>Classes</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Carries class structures into the new year</small>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkClassAllocations" checked
                                            disabled>
                                        <label class="form-check-label" for="checkClassAllocations">
                                            <strong>Students Class Allocations</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Keeps students attached to their class groups</small>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkSubjects" checked disabled>
                                        <label class="form-check-label" for="checkSubjects">
                                            <strong>Subjects</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Copies subject definitions to the next year</small>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkOptionalClasses" checked
                                            disabled>
                                        <label class="form-check-label" for="checkOptionalClasses">
                                            <strong>Optional Classes</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Preserves optional class structures</small>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="checkOptionalClassesAllocations"
                                            checked disabled>
                                        <label class="form-check-label" for="checkOptionalClassesAllocations">
                                            <strong>Optional Classes Allocations</strong>
                                        </label>
                                        <small class="text-muted d-block ms-4">Retains optional class allocations per student</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                                data-bs-target="#yearRolloverReverseModal">
                                                <i class="fas fa-history me-1"></i> Year Rollover History
                                            </button>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#yearRolloverModal">
                                                <i class="fas fa-sync-alt me-1"></i> Year Rollover
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan

                <!-- Settings Tab -->
                <div class="tab-pane" id="settings2" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">System Configuration</div>
                        <div class="help-content">
                            Manage system cache, storage links, and database backups. Use these options carefully.
                        </div>
                    </div>

                    <div class="config-card">
                        <h5><i class="fas fa-cog me-2"></i>System Config and Backups</h5>
                        <p class="text-muted mb-4">Please be cautious about which action you perform.</p>

                        <div class="d-flex flex-column gap-3" style="max-width: 250px;">
                            <form action="{{ route('setup.storage-symlink') }}" method="GET"
                                onsubmit="return confirmAction('Create Storage Link')">
                                <button type="submit" class="btn btn-primary btn-fixed-width btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-link me-1"></i> Create Storage Link</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                            </form>

                            <form action="{{ route('setup.config-cache') }}" method="GET"
                                onsubmit="return confirmAction('Clear Config Cache')">
                                <button type="submit" class="btn btn-primary btn-fixed-width btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-cog me-1"></i> Clear Config Cache</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                            </form>

                            <form action="{{ route('setup.clear-caches') }}" method="GET"
                                onsubmit="return confirmAction('Clear Caches')">
                                <button type="submit" class="btn btn-primary btn-fixed-width btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-sync-alt me-1"></i> Clear Caches</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                            </form>

                            <form action="{{ route('setup.create-backup') }}" method="GET"
                                onsubmit="return confirmAction('DB Backup')">
                                <button type="submit" class="btn btn-primary btn-fixed-width btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-database me-1"></i> DB Backup</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                            </form>

                            <form action="{{ route('setup.clear-logs') }}" method="GET"
                                onsubmit="return confirmAction('Clear Logs')">
                                <button type="submit" class="btn btn-danger btn-fixed-width btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-trash-alt me-1"></i> Clear Logs</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="templates1" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">Import Templates</div>
                        <div class="help-content">
                            Download the correct import files before preparing bulk uploads. Student templates differ by level,
                            and combined modes expose the appropriate level-specific downloads here.
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-file-excel me-2"></i>Core Import Templates</h6>
                        <div class="template-grid">
                            <div class="template-download-card">
                                <span class="badge bg-light text-dark">Staff</span>
                                <h6>Staff Template</h6>
                                <p>Use this when importing teaching and support staff records.</p>
                                <a href="{{ route('setup.download-import', ['filename' => 'import-staff.xlsx']) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download Staff
                                </a>
                            </div>

                            <div class="template-download-card">
                                <span class="badge bg-light text-dark">Sponsors</span>
                                <h6>Sponsors Template</h6>
                                <p>Use this for parent and guardian records before importing students.</p>
                                <a href="{{ route('setup.download-import', ['filename' => 'import-sponsors.xlsx']) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download Sponsors
                                </a>
                            </div>

                            <div class="template-download-card">
                                <span class="badge bg-light text-dark">Admissions</span>
                                <h6>Admissions Template</h6>
                                <p>Use this for admission applicants and intake records.</p>
                                <a href="{{ route('setup.download-import', ['filename' => 'import-admissions.xlsx']) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download Admissions
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-users me-2"></i>Student Template Variants</h6>
                        <div class="mb-3 text-muted" style="font-size: 13px;">
                            Current school mode:
                            <strong>{{ match ($templateSchoolType) {
                                \App\Models\SchoolSetup::TYPE_PRE_F3 => 'Pre-F3',
                                \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR => 'Middle & High School',
                                \App\Models\SchoolSetup::TYPE_K12 => 'Pre-F5 (K12)',
                                default => $templateSchoolType,
                            } }}</strong>
                        </div>

                        <div class="template-grid">
                            @foreach ($studentTemplateVariants as $variant)
                                <div class="template-download-card">
                                    <span class="badge {{ $variant['badge_class'] }}">{{ $variant['badge'] }}</span>
                                    <h6>{{ $variant['label'] }} Student Template</h6>
                                    <p>{{ $variant['description'] }}</p>
                                    <a href="{{ route('setup.download-import', ['filename' => 'import-students.xlsx', 'school_type' => $variant['school_type']]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-1"></i> Download {{ $variant['label'] }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Term Rollover Modal -->
    <div class="modal fade" id="termRolloverModal" tabindex="-1" aria-labelledby="termRolloverModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="fas fa-sync-alt text-primary me-2"></i>Term Rollover
                        </h5>
                        <small class="text-muted">Transition records to the next term</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="rolloverFormTerm">
                        @csrf
                        {{-- Loading indicator --}}
                        <div id="termLoadingIndicator" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0" id="termLoadingText">Processing rollover, please wait...</p>
                        </div>

                        {{-- Success indicator --}}
                        <div id="termSuccessIndicator" class="text-center py-4 d-none">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-4x"></i>
                            </div>
                            <h5 class="text-success">Term Rollover Completed!</h5>
                            <p class="text-muted mb-0" id="termSuccessMessage">The selected term has been rolled over successfully.</p>
                        </div>

                        {{-- Term selector --}}
                        <div id="termTermSelector">
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-3">
                                    <div class="row align-items-center g-2">
                                        <div class="col">
                                            <label for="fromTermSelect" class="form-label small fw-semibold text-muted mb-1">
                                                <i class="fas fa-sign-out-alt me-1"></i>From
                                            </label>
                                            <select class="form-select form-select-sm" name="fromTermId" id="fromTermSelect" required>
                                                <option value="">Select term...</option>
                                                @if (!empty($openTerms))
                                                    @foreach ($openTerms as $term)
                                                        <option value="{{ $term->id }}">Term {{ $term->term }}, {{ $term->year }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-auto text-center px-2">
                                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; margin-top: 20px;">
                                                <i class="fas fa-arrow-right text-white small"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="toTermSelect" class="form-label small fw-semibold text-muted mb-1">
                                                <i class="fas fa-sign-in-alt me-1"></i>To
                                            </label>
                                            <select class="form-select form-select-sm" name="toTermId" id="toTermSelect" required>
                                                <option value="">Select term...</option>
                                                @if (!empty($openTerms))
                                                    @foreach ($openTerms as $term)
                                                        <option value="{{ $term->id }}">Term {{ $term->term }}, {{ $term->year }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="termErrorMessage" class="alert alert-danger mt-2 d-none"></div>

                        {{-- Preview section --}}
                        <div id="termPreviewSection" class="d-none">
                            <div class="row g-2 mb-3" id="termPreviewSummary"></div>

                            <div class="accordion accordion-flush" id="termPreviewAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#termPreviewGrades">
                                            <i class="fas fa-layer-group me-2 text-primary"></i>Grades
                                            <span class="badge bg-primary ms-2" id="termPreviewGradeCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="termPreviewGrades" class="accordion-collapse collapse" data-bs-parent="#termPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Grade</th><th>Action</th></tr>
                                                    </thead>
                                                    <tbody id="termPreviewGradesBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#termPreviewClasses">
                                            <i class="fas fa-chalkboard me-2 text-primary"></i>Classes
                                            <span class="badge bg-primary ms-2" id="termPreviewClassCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="termPreviewClasses" class="accordion-collapse collapse" data-bs-parent="#termPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Class</th><th>Grade</th><th>Students</th></tr>
                                                    </thead>
                                                    <tbody id="termPreviewClassesBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="termPreviewOptionalSubjectsItem" style="display:none;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#termPreviewOptionals">
                                            <i class="fas fa-book-open me-2 text-primary"></i>Optional Subjects
                                            <span class="badge bg-primary ms-2" id="termPreviewOptionalCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="termPreviewOptionals" class="accordion-collapse collapse" data-bs-parent="#termPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Name</th><th>Subject</th><th>Grade</th><th>Students</th></tr>
                                                    </thead>
                                                    <tbody id="termPreviewOptionalsBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Results section --}}
                        <div id="termResultsSection" class="d-none">
                            <div class="row g-2 mb-3" id="termResultsCards"></div>
                            <div id="termResultsAutoCreatedAlert" class="alert alert-warning d-none">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong id="termResultsAutoCreatedCount">0</strong> missing grade-subject record(s) were auto-created during rollover.
                                Please verify these in the Grades management area.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="termCancelButton">Cancel</button>
                    <button type="button" class="btn btn-primary btn-loading" id="termRolloverButton">
                        <span class="btn-text"><i class="fas fa-search me-1"></i> Preview Rollover</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Loading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Rollover Modal -->
    <div class="modal fade" id="yearRolloverModal" tabindex="-1" aria-labelledby="yearRolloverModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="fas fa-level-up-alt text-primary me-2"></i>Year Rollover
                        </h5>
                        <small class="text-muted">Advance students to the next academic year</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="yearRolloverForm">
                        @csrf
                        {{-- Loading indicator --}}
                        <div id="yearLoadingIndicator" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0" id="yearLoadingText">Processing year rollover, please wait...</p>
                        </div>

                        {{-- Success indicator --}}
                        <div id="yearSuccessIndicator" class="text-center py-4 d-none">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-4x"></i>
                            </div>
                            <h5 class="text-success">Year Rollover Completed!</h5>
                            <p class="text-muted mb-0" id="yearSuccessMessage">The academic year has been rolled over successfully.</p>
                        </div>

                        {{-- Term selector --}}
                        <div id="yearTermSelector">
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-3">
                                    <div class="row align-items-center g-2">
                                        <div class="col">
                                            <label for="yearFromTermSelect" class="form-label small fw-semibold text-muted mb-1">
                                                <i class="fas fa-sign-out-alt me-1"></i>From
                                            </label>
                                            <select class="form-select form-select-sm" name="fromTermId" id="yearFromTermSelect" required>
                                                <option value="">Select term...</option>
                                                @if (!empty($openTerms))
                                                    @foreach ($openTerms as $term)
                                                        <option value="{{ $term->id }}">Term {{ $term->term }}, {{ $term->year }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-auto text-center px-2">
                                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; margin-top: 20px;">
                                                <i class="fas fa-arrow-right text-white small"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="yearToTermSelect" class="form-label small fw-semibold text-muted mb-1">
                                                <i class="fas fa-sign-in-alt me-1"></i>To
                                            </label>
                                            <select class="form-select form-select-sm" name="toTermId" id="yearToTermSelect" required>
                                                <option value="">Select term...</option>
                                                @if (!empty($openTerms))
                                                    @foreach ($openTerms as $term)
                                                        <option value="{{ $term->id }}">Term {{ $term->term }}, {{ $term->year }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="yearErrorMessage" class="alert alert-danger mt-2 d-none"></div>

                        {{-- Preview section --}}
                        <div id="yearPreviewSection" class="d-none">
                            <div class="row g-2 mb-3" id="yearPreviewSummary"></div>

                            <div class="accordion accordion-flush" id="yearPreviewAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#previewGrades">
                                            <i class="fas fa-layer-group me-2 text-primary"></i>Grades
                                            <span class="badge bg-primary ms-2" id="previewGradeCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="previewGrades" class="accordion-collapse collapse" data-bs-parent="#yearPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Grade</th><th>Promotes To</th><th>Action</th></tr>
                                                    </thead>
                                                    <tbody id="previewGradesBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#previewClasses">
                                            <i class="fas fa-chalkboard me-2 text-primary"></i>Classes
                                            <span class="badge bg-primary ms-2" id="previewClassCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="previewClasses" class="accordion-collapse collapse" data-bs-parent="#yearPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Class</th><th>Grade</th><th>Students</th><th>Promoted Name</th><th>Action</th></tr>
                                                    </thead>
                                                    <tbody id="previewClassesBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="previewOptionalSubjectsItem" style="display:none;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#previewOptionals">
                                            <i class="fas fa-book-open me-2 text-primary"></i>Optional Subjects
                                            <span class="badge bg-primary ms-2" id="previewOptionalCount">0</span>
                                        </button>
                                    </h2>
                                    <div id="previewOptionals" class="accordion-collapse collapse" data-bs-parent="#yearPreviewAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr><th>Name</th><th>Subject</th><th>Grade</th><th>Students</th><th>Promoted Name</th><th>Status</th></tr>
                                                    </thead>
                                                    <tbody id="previewOptionalsBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Results section --}}
                        <div id="yearResultsSection" class="d-none">
                            <div class="row g-2 mb-3" id="yearResultsCards"></div>
                            <div id="yearResultsAutoCreatedAlert" class="alert alert-warning d-none">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong id="yearResultsAutoCreatedCount">0</strong> missing grade-subject record(s) were auto-created during rollover.
                                Please verify these in the Grades management area.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="yearCancelButton">Cancel</button>
                    <button type="button" class="btn btn-primary btn-loading" id="yearRolloverButton">
                        <span class="btn-text"><i class="fas fa-search me-1"></i> Preview Rollover</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Loading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Rollover History Modal -->
    <div class="modal" id="yearRolloverReverseModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-history me-2"></i>Year Rollover History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($histories as $history)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-{{ $history->status === 'completed' ? 'success' : ($history->status === 'reversed' ? 'danger' : 'warning') }} me-2">
                                                {{ ucfirst($history->status) }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $history->created_at->format('M d, Y h:i A') }}
                                                <strong>User:</strong> {{ $history->performer->fullName }}
                                                <strong>Role:</strong> {{ $history->performer->position ?? '' }}
                                            </small>
                                        </div>
                                        <div class="small">
                                            <strong>From:</strong> Term {{ $history->fromTerm->term }}, {{ $history->fromTerm->year }}
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                            <strong>To:</strong> Term {{ $history->toTerm->term }}, {{ $history->toTerm->year }}
                                        </div>
                                    </div>
                                    @if ($history->status === 'completed')
                                        <button type="button"
                                            class="btn {{ $history->id === $latestHistory->id ? 'btn-warning' : 'btn-secondary' }} btn-sm undo-rollover"
                                            data-id="{{ $history->id }}"
                                            {{ $history->id !== $latestHistory->id ? 'disabled' : '' }}
                                            onclick="confirmRolloverReversal({{ $history->id }})">
                                            <i class="fas fa-undo-alt me-1"></i> Undo
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Reverse Rollover Confirmation Modal -->
    <div class="modal" id="confirmReverseModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Confirm Reversal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reverse this rollover? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="executeRolloverReversal()">
                        <i class="fas fa-undo-alt me-1"></i> Confirm Reversal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Term Rollover History Modal -->
    <div class="modal" id="termRolloverHistoryModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-history me-2"></i>Term Rollover History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($termRolloverhistories as $history)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-{{ $history->status === 'completed' ? 'success' : ($history->status === 'reversed' ? 'danger' : 'warning') }} me-2">
                                                {{ ucfirst($history->status) }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $history->created_at->format('M d, Y h:i A') }}
                                                <strong>User:</strong> {{ $history->performer->fullName }}
                                                <strong>Role:</strong> {{ $history->performer->position ?? '' }}
                                            </small>
                                        </div>
                                        <div class="small">
                                            <strong>From:</strong> Term {{ $history->fromTerm->term }}, {{ $history->fromTerm->year }}
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                            <strong>To:</strong> Term {{ $history->toTerm->term }}, {{ $history->toTerm->year }}
                                        </div>
                                    </div>
                                    @if ($history->status === 'completed')
                                        <button type="button"
                                            class="btn {{ $history->id === $termRolloverLatestHistory->id ? 'btn-warning' : 'btn-secondary' }} btn-sm undo-term-rollover"
                                            data-id="{{ $history->id }}"
                                            {{ $history->id !== $termRolloverLatestHistory->id ? 'disabled' : '' }}
                                            onclick="confirmTermRolloverReversal({{ $history->id }})">
                                            <i class="fas fa-undo-alt me-1"></i> Undo
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Term Reverse Rollover Confirmation Modal -->
    <div class="modal" id="confirmTermRolloverReverseModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Confirm Term Reversal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reverse this term rollover? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="executeTermRolloverReversal()">
                        <i class="fas fa-undo-alt me-1"></i> Confirm Reversal
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('components.crop-modal')
@endsection

@section('script')
    <script>
        // Form submit loading animation handler
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn && !submitBtn.classList.contains('loading')) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert .btn-close').forEach(function(btn) {
                    btn.click();
                });
            }, 5000);
        });

        let selectedRolloverHistoryId = null;
        const yearRolloverReverseModal = document.getElementById('yearRolloverReverseModal');
        const confirmReverseModal = document.getElementById('confirmReverseModal');

        let selectedTermRolloverHistoryId = null;
        const termRolloverReverseModal = document.getElementById('termRolloverHistoryModal');
        const confirmTermRolloverReverseModal = document.getElementById('confirmTermRolloverReverseModal');

        function confirmRolloverReversal(historyId) {
            selectedRolloverHistoryId = historyId;
            bootstrap.Modal.getInstance(yearRolloverReverseModal).hide();
            new bootstrap.Modal(confirmReverseModal).show();
        }

        function executeRolloverReversal() {
            if (!selectedRolloverHistoryId) return;

            const undoButton = document.querySelector(`.undo-rollover[data-id='${selectedRolloverHistoryId}']`);
            undoButton.disabled = true;
            undoButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const baseUrl = "{{ route('setup.reverse-year-rollover', ['historyId' => 'tempHistoryId']) }}";
            const reverseYearRolloverUrl = baseUrl.replace('tempHistoryId', selectedRolloverHistoryId);

            fetch(reverseYearRolloverUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                toastr.success('Rollover has been successfully reversed');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                toastr.error(error.message || 'An error occurred during reversal');
                undoButton.disabled = false;
                undoButton.innerHTML = '<i class="fas fa-undo-alt me-1"></i>Undo';
            })
            .finally(() => {
                bootstrap.Modal.getInstance(confirmReverseModal).hide();
            });
        }

        function confirmTermRolloverReversal(historyId) {
            selectedTermRolloverHistoryId = historyId;
            bootstrap.Modal.getInstance(termRolloverReverseModal).hide();
            new bootstrap.Modal(confirmTermRolloverReverseModal).show();
        }

        function executeTermRolloverReversal() {
            if (!selectedTermRolloverHistoryId) return;

            const undoButton = document.querySelector(`.undo-term-rollover[data-id='${selectedTermRolloverHistoryId}']`);
            undoButton.disabled = true;
            undoButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const termBaseUrl = "{{ route('setup.reverse-term-rollover', ['historyId' => 'tempHistoryId']) }}";
            const reverseTermRolloverUrl = termBaseUrl.replace('tempHistoryId', selectedTermRolloverHistoryId);

            fetch(reverseTermRolloverUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                toastr.success('Rollover has been successfully reversed');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                toastr.error(error.message || 'An error occurred during reversal');
                undoButton.disabled = false;
                undoButton.innerHTML = '<i class="fas fa-undo-alt me-1"></i>Undo';
            })
            .finally(() => {
                bootstrap.Modal.getInstance(confirmTermRolloverReverseModal).hide();
            });
        }

        function confirmAction(action) {
            return confirm(`Are you sure you want to ${action}?`);
        }

        function closeTerm(termId) {
            if (confirm('Are you sure you want to close this term?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('setup.close-term', '') }}/" + termId;

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = '{{ csrf_token() }}';
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const logoUploadForm = document.getElementById('logoUploadForm');
            const loginImageForm = document.getElementById('loginImageForm');

            function clearInlineError(errorElement) {
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            function showInlineError(errorElement, message) {
                if (errorElement) {
                    errorElement.textContent = message;
                }
            }

            function clearCropPreview(previewElement) {
                if (!previewElement) {
                    return;
                }

                if (previewElement.dataset.objectUrl) {
                    URL.revokeObjectURL(previewElement.dataset.objectUrl);
                    delete previewElement.dataset.objectUrl;
                }

                previewElement.innerHTML = '';
            }

            function renderCropPreview(previewElement, file, title, imageStyle) {
                if (!previewElement) {
                    return;
                }

                clearCropPreview(previewElement);

                const previewUrl = URL.createObjectURL(file);
                previewElement.dataset.objectUrl = previewUrl;
                previewElement.innerHTML = `
                    <div class="p-3 border rounded text-center">
                        <p class="text-muted mb-2"><strong>${title}</strong></p>
                        <img src="${previewUrl}" alt="${title}" class="img-fluid" style="${imageStyle}">
                    </div>
                `;
            }

            function buildCroppedFileName(originalName, suffix) {
                const fallbackName = 'image';
                const baseName = (originalName || fallbackName).replace(/\.[^.]+$/, '').trim() || fallbackName;
                return `${baseName}-${suffix}`;
            }

            function initialiseCropUpload(config) {
                if (!config.input || !config.label || !config.errorElement) {
                    return;
                }

                config.input.addEventListener('change', function() {
                    clearInlineError(config.errorElement);
                    clearCropPreview(config.previewElement);
                    config.label.classList.remove('has-file');
                });

                CropHelper.init(config.input, function(blob, meta) {
                    const croppedFile = new File(
                        [blob],
                        buildCroppedFileName(meta && meta.sourceFile ? meta.sourceFile.name : '', config.outputFileName),
                        {
                            type: blob.type || config.outputMimeType,
                            lastModified: Date.now()
                        }
                    );

                    CropHelper.attachFileToInput(config.input, croppedFile);

                    clearInlineError(config.errorElement);
                    config.label.classList.add('has-file');
                    renderCropPreview(config.previewElement, croppedFile, config.previewTitle, config.previewImageStyle);
                    CropHelper.hideModal();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: config.successMessage,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }, config.cropOptions);
            }

            CropHelper.bindAjaxFallback(logoUploadForm, 'logo', document.getElementById('image-logo'), {
                onSuccess: function(payload) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: payload.message || 'Logo uploaded successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    window.setTimeout(function() {
                        window.location.reload();
                    }, 500);
                },
                onError: function(error) {
                    showInlineError(document.getElementById('avatarLogo'), error.message || 'Failed to upload the cropped logo.');
                }
            });

            CropHelper.bindAjaxFallback(loginImageForm, 'login_image', document.getElementById('image-login'), {
                onSuccess: function(payload) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: payload.message || 'Login image uploaded successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    window.setTimeout(function() {
                        window.location.reload();
                    }, 500);
                },
                onError: function(error) {
                    showInlineError(document.getElementById('loginImageError'), error.message || 'Failed to upload the cropped login image.');
                }
            });

            initialiseCropUpload({
                input: document.getElementById('image-logo'),
                label: document.getElementById('logoFileLabel'),
                errorElement: document.getElementById('avatarLogo'),
                previewElement: document.getElementById('logoCropPreview'),
                outputMimeType: 'image/png',
                outputFileName: 'cropped.png',
                previewTitle: 'Cropped Logo Preview (500 x 500)',
                previewImageStyle: 'max-height: 180px; object-fit: contain;',
                successMessage: 'Logo cropped. Click upload to save it.',
                cropOptions: {
                    title: 'Crop School Logo',
                    aspectRatio: 1,
                    outputWidth: 500,
                    outputHeight: 500,
                    outputMimeType: 'image/png',
                    allowedTypes: ['image/jpeg', 'image/png', 'image/gif'],
                    fileTypeErrorMessage: 'Please select a PNG, JPG or GIF image.',
                    maxFileSize: 10 * 1024 * 1024,
                    maxFileSizeErrorMessage: 'Please select an image smaller than 10 MB before cropping.'
                }
            });

            initialiseCropUpload({
                input: document.getElementById('image-login'),
                label: document.getElementById('loginFileLabel'),
                errorElement: document.getElementById('loginImageError'),
                previewElement: document.getElementById('loginImageCropPreview'),
                outputMimeType: 'image/jpeg',
                outputFileName: 'cropped.jpg',
                previewTitle: 'Cropped Login Image Preview (1000 x 600)',
                previewImageStyle: 'max-width: 100%; height: auto; border-radius: 3px;',
                successMessage: 'Login image cropped. Click upload to save it.',
                cropOptions: {
                    title: 'Crop Login Image',
                    aspectRatio: 5 / 3,
                    outputWidth: 1000,
                    outputHeight: 600,
                    outputMimeType: 'image/jpeg',
                    outputQuality: 0.92,
                    allowedTypes: ['image/jpeg', 'image/png'],
                    fileTypeErrorMessage: 'Please select a JPG or PNG image.',
                    maxFileSize: 12 * 1024 * 1024,
                    maxFileSizeErrorMessage: 'Please select an image smaller than 12 MB before cropping.'
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            // Term Rollover functionality (two-step: preview → execute)
            const termRolloverButton = document.getElementById('termRolloverButton');
            const termLoadingIndicator = document.getElementById('termLoadingIndicator');
            const termLoadingText = document.getElementById('termLoadingText');
            const termSuccessIndicator = document.getElementById('termSuccessIndicator');
            const termSuccessMessage = document.getElementById('termSuccessMessage');
            const fromTermSelect = document.getElementById('fromTermSelect');
            const toTermSelect = document.getElementById('toTermSelect');
            const termErrorMessage = document.getElementById('termErrorMessage');
            const termTermSelector = document.getElementById('termTermSelector');
            const termPreviewSection = document.getElementById('termPreviewSection');
            const termResultsSection = document.getElementById('termResultsSection');
            const termCancelButton = document.getElementById('termCancelButton');

            let termRolloverState = 'initial'; // initial | previewed | executing | complete

            function showTermError(message) {
                termErrorMessage.innerHTML = message;
                termErrorMessage.classList.remove('d-none');
            }

            function hideTermError() {
                termErrorMessage.innerHTML = '';
                termErrorMessage.classList.add('d-none');
            }

            function setTermButtonLoading(show, text) {
                const btnText = termRolloverButton.querySelector('.btn-text');
                const btnSpinner = termRolloverButton.querySelector('.btn-spinner');
                if (show) {
                    btnText.classList.add('d-none');
                    btnSpinner.classList.remove('d-none');
                    termRolloverButton.disabled = true;
                } else {
                    btnText.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');
                    termRolloverButton.disabled = false;
                }
            }

            function setTermButtonState(icon, label, btnClass) {
                const btnText = termRolloverButton.querySelector('.btn-text');
                btnText.innerHTML = `<i class="fas fa-${icon} me-1"></i> ${label}`;
                termRolloverButton.className = `btn btn-${btnClass} btn-loading`;
            }

            function buildTermSummaryCards(container, data) {
                container.innerHTML = '';
                const items = [
                    { key: 'grades', label: 'Grades', icon: 'layer-group', color: 'primary' },
                    { key: 'classes', label: 'Classes', icon: 'chalkboard', color: 'info' },
                    { key: 'subjects', label: 'Subjects', icon: 'book', color: 'success' },
                    { key: 'houses', label: 'Houses', icon: 'home', color: 'warning' },
                    { key: 'gradingScales', label: 'Grading Scales', icon: 'balance-scale', color: 'danger' },
                    { key: 'gradingMatrices', label: 'Matrices', icon: 'th', color: 'dark' },
                ];
                if (data.optionalSubjects !== undefined && data.optionalSubjects > 0) {
                    items.splice(3, 0, { key: 'optionalSubjects', label: 'Optional Subjects', icon: 'book-open', color: 'secondary' });
                }
                items.forEach(item => {
                    const val = data[item.key] ?? 0;
                    const col = document.createElement('div');
                    col.className = 'col';
                    col.innerHTML = `
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body py-2 px-2">
                                <div class="text-${item.color} mb-1"><i class="fas fa-${item.icon}"></i></div>
                                <div class="fw-bold fs-5">${val}</div>
                                <div class="text-muted" style="font-size:11px;">${item.label}</div>
                            </div>
                        </div>`;
                    container.appendChild(col);
                });
            }

            function populateTermPreview(preview) {
                buildTermSummaryCards(document.getElementById('termPreviewSummary'), preview.summary);

                const gradesBody = document.getElementById('termPreviewGradesBody');
                gradesBody.innerHTML = '';
                document.getElementById('termPreviewGradeCount').textContent = preview.grades.length;
                preview.grades.forEach(g => {
                    gradesBody.innerHTML += `<tr><td>${g.name}</td><td><span class="badge bg-info">Copy</span></td></tr>`;
                });

                const classesBody = document.getElementById('termPreviewClassesBody');
                classesBody.innerHTML = '';
                document.getElementById('termPreviewClassCount').textContent = preview.classes.length;
                preview.classes.forEach(c => {
                    classesBody.innerHTML += `<tr><td>${c.name}</td><td>${c.grade}</td><td>${c.studentCount}</td></tr>`;
                });

                const optItem = document.getElementById('termPreviewOptionalSubjectsItem');
                if (preview.optionalSubjects && preview.optionalSubjects.length > 0) {
                    optItem.style.display = '';
                    const optBody = document.getElementById('termPreviewOptionalsBody');
                    optBody.innerHTML = '';
                    document.getElementById('termPreviewOptionalCount').textContent = preview.optionalSubjects.length;
                    preview.optionalSubjects.forEach(os => {
                        optBody.innerHTML += `<tr><td>${os.name}</td><td>${os.subject}</td><td>${os.grade}</td><td>${os.studentCount}</td></tr>`;
                    });
                } else {
                    optItem.style.display = 'none';
                }

                termPreviewSection.classList.remove('d-none');
            }

            function populateTermResults(details) {
                buildTermSummaryCards(document.getElementById('termResultsCards'), details);

                const autoCreated = details.autoCreatedGradeSubjects || 0;
                const alertEl = document.getElementById('termResultsAutoCreatedAlert');
                if (autoCreated > 0) {
                    document.getElementById('termResultsAutoCreatedCount').textContent = autoCreated;
                    alertEl.classList.remove('d-none');
                } else {
                    alertEl.classList.add('d-none');
                }

                termResultsSection.classList.remove('d-none');
            }

            function resetTermModal() {
                termRolloverState = 'initial';
                hideTermError();
                setTermButtonLoading(false);
                setTermButtonState('search', 'Preview Rollover', 'primary');
                termRolloverButton.disabled = false;
                fromTermSelect.disabled = false;
                toTermSelect.disabled = false;
                fromTermSelect.value = '';
                toTermSelect.value = '';
                termLoadingIndicator.classList.add('d-none');
                termSuccessIndicator.classList.add('d-none');
                termTermSelector.classList.remove('d-none');
                termPreviewSection.classList.add('d-none');
                termResultsSection.classList.add('d-none');
                termCancelButton.textContent = 'Cancel';
            }

            function termFetchJson(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(body)
                }).then(response => response.json().then(data => {
                    if (!response.ok) throw { status: response.status, data };
                    return data;
                }));
            }

            async function handleTermPreview(fromTermId, toTermId) {
                try {
                    hideTermError();
                    setTermButtonLoading(true, 'Loading...');
                    termLoadingText.textContent = 'Checking grades and generating preview...';
                    termLoadingIndicator.classList.remove('d-none');

                    const checkData = await termFetchJson("{{ route('setup.check-grades') }}", { fromTermId, toTermId });
                    if (!checkData.canRollover) {
                        termLoadingIndicator.classList.add('d-none');
                        showTermError(checkData.message || 'Cannot perform term rollover.');
                        setTermButtonLoading(false);
                        return;
                    }

                    const previewData = await termFetchJson("{{ route('setup.preview-term-rollover') }}", { fromTermId, toTermId });
                    if (!previewData.success) {
                        termLoadingIndicator.classList.add('d-none');
                        showTermError(previewData.message || 'Failed to generate preview.');
                        setTermButtonLoading(false);
                        return;
                    }

                    termLoadingIndicator.classList.add('d-none');
                    populateTermPreview(previewData.preview);

                    termRolloverState = 'previewed';
                    fromTermSelect.disabled = true;
                    toTermSelect.disabled = true;
                    setTermButtonLoading(false);
                    setTermButtonState('play', 'Confirm & Execute Rollover', 'warning');

                } catch (error) {
                    console.error('Term preview error:', error);
                    termLoadingIndicator.classList.add('d-none');
                    setTermButtonLoading(false);
                    showTermError(error.data?.message || 'An error occurred while generating the preview.');
                }
            }

            async function handleTermExecute(fromTermId, toTermId) {
                const confirmed = confirm('Are you sure you want to execute the term rollover? This will modify the database.');
                if (!confirmed) return;

                try {
                    termRolloverState = 'executing';
                    hideTermError();
                    setTermButtonLoading(true, 'Processing...');
                    termPreviewSection.classList.add('d-none');
                    termTermSelector.classList.add('d-none');
                    termLoadingText.textContent = 'Executing term rollover, please wait...';
                    termLoadingIndicator.classList.remove('d-none');

                    const data = await termFetchJson("{{ route('setup.term-rollover') }}", { fromTermId, toTermId });

                    termLoadingIndicator.classList.add('d-none');

                    termRolloverState = 'complete';
                    termSuccessMessage.textContent = data.message || 'The term has been rolled over successfully.';
                    termSuccessIndicator.classList.remove('d-none');

                    if (data.details && typeof data.details === 'object') {
                        populateTermResults(data.details);
                    }

                    setTermButtonLoading(false);
                    setTermButtonState('check', 'Complete', 'success');
                    termRolloverButton.disabled = true;
                    termCancelButton.textContent = 'Close';

                } catch (error) {
                    console.error('Term rollover error:', error);
                    termLoadingIndicator.classList.add('d-none');
                    termTermSelector.classList.remove('d-none');
                    termPreviewSection.classList.remove('d-none');
                    setTermButtonLoading(false);
                    setTermButtonState('play', 'Confirm & Execute Rollover', 'warning');
                    termRolloverState = 'previewed';
                    showTermError(error.data?.message || 'An unexpected error occurred during the rollover process.');
                }
            }

            termRolloverButton.addEventListener('click', function(event) {
                event.preventDefault();

                const fromTermId = fromTermSelect.value;
                const toTermId = toTermSelect.value;

                if (!fromTermId || !toTermId) {
                    showTermError('Please select both "From" and "To" terms.');
                    return;
                }
                if (fromTermId === toTermId) {
                    showTermError('The "From" and "To" terms cannot be the same.');
                    return;
                }

                if (termRolloverState === 'initial') {
                    handleTermPreview(fromTermId, toTermId);
                } else if (termRolloverState === 'previewed') {
                    handleTermExecute(fromTermId, toTermId);
                }
            });

            document.getElementById('termRolloverModal').addEventListener('hidden.bs.modal', function() {
                resetTermModal();
            });
        });

        // Year Rollover functionality
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) return;

            const yearRolloverButton = document.getElementById('yearRolloverButton');
            const yearLoadingIndicator = document.getElementById('yearLoadingIndicator');
            const yearLoadingText = document.getElementById('yearLoadingText');
            const yearSuccessIndicator = document.getElementById('yearSuccessIndicator');
            const yearSuccessMessage = document.getElementById('yearSuccessMessage');
            const yearFromTermSelect = document.getElementById('yearFromTermSelect');
            const yearToTermSelect = document.getElementById('yearToTermSelect');
            const yearErrorMessage = document.getElementById('yearErrorMessage');
            const yearTermSelector = document.getElementById('yearTermSelector');
            const yearPreviewSection = document.getElementById('yearPreviewSection');
            const yearResultsSection = document.getElementById('yearResultsSection');
            const yearCancelButton = document.getElementById('yearCancelButton');

            let yearRolloverState = 'initial'; // initial | previewed | executing | complete

            function showYearError(message) {
                yearErrorMessage.innerHTML = message;
                yearErrorMessage.classList.remove('d-none');
            }

            function hideYearError() {
                yearErrorMessage.innerHTML = '';
                yearErrorMessage.classList.add('d-none');
            }

            function setButtonLoading(show, text) {
                const btnText = yearRolloverButton.querySelector('.btn-text');
                const btnSpinner = yearRolloverButton.querySelector('.btn-spinner');
                if (show) {
                    btnText.classList.add('d-none');
                    btnSpinner.classList.remove('d-none');
                    if (text) btnSpinner.querySelector('span:last-child') || (btnSpinner.childNodes[btnSpinner.childNodes.length - 1].textContent = text);
                    yearRolloverButton.disabled = true;
                } else {
                    btnText.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');
                    yearRolloverButton.disabled = false;
                }
            }

            function setButtonState(icon, label, btnClass) {
                const btnText = yearRolloverButton.querySelector('.btn-text');
                btnText.innerHTML = `<i class="fas fa-${icon} me-1"></i> ${label}`;
                yearRolloverButton.className = `btn btn-${btnClass} btn-loading`;
            }

            function buildSummaryCards(container, data, icons) {
                container.innerHTML = '';
                const items = [
                    { key: 'grades', label: 'Grades', icon: 'layer-group', color: 'primary' },
                    { key: 'classes', label: 'Classes', icon: 'chalkboard', color: 'info' },
                    { key: 'subjects', label: 'Subjects', icon: 'book', color: 'success' },
                    { key: 'houses', label: 'Houses', icon: 'home', color: 'warning' },
                ];
                if (data.optionalSubjects !== undefined && data.optionalSubjects > 0) {
                    items.splice(3, 0, { key: 'optionalSubjects', label: 'Optional Subjects', icon: 'book-open', color: 'secondary' });
                }
                items.forEach(item => {
                    const val = data[item.key] ?? 0;
                    const col = document.createElement('div');
                    col.className = 'col';
                    col.innerHTML = `
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body py-2 px-2">
                                <div class="text-${item.color} mb-1"><i class="fas fa-${item.icon}"></i></div>
                                <div class="fw-bold fs-5">${val}</div>
                                <div class="text-muted" style="font-size:11px;">${item.label}</div>
                            </div>
                        </div>`;
                    container.appendChild(col);
                });
            }

            function populatePreview(preview) {
                // Summary cards
                buildSummaryCards(document.getElementById('yearPreviewSummary'), preview.summary);

                // Grades table
                const gradesBody = document.getElementById('previewGradesBody');
                gradesBody.innerHTML = '';
                document.getElementById('previewGradeCount').textContent = preview.grades.length;
                preview.grades.forEach(g => {
                    const badge = g.action === 'graduating'
                        ? '<span class="badge bg-secondary">Graduating</span>'
                        : '<span class="badge bg-success">Promote</span>';
                    gradesBody.innerHTML += `<tr><td>${g.name}</td><td>${g.promotion || '—'}</td><td>${badge}</td></tr>`;
                });

                // Classes table
                const classesBody = document.getElementById('previewClassesBody');
                classesBody.innerHTML = '';
                document.getElementById('previewClassCount').textContent = preview.classes.length;
                preview.classes.forEach(c => {
                    let badge = '';
                    if (c.action === 'graduating') badge = '<span class="badge bg-secondary">Graduating</span>';
                    else if (c.action === 'promote') badge = '<span class="badge bg-success">Promote</span>';
                    else badge = '<span class="badge bg-info">Shell</span>';
                    classesBody.innerHTML += `<tr><td>${c.name}</td><td>${c.grade}</td><td>${c.studentCount}</td><td>${c.promotedName || '—'}</td><td>${badge}</td></tr>`;
                });

                // Optional subjects
                const optItem = document.getElementById('previewOptionalSubjectsItem');
                if (preview.optionalSubjects && preview.optionalSubjects.length > 0) {
                    optItem.style.display = '';
                    const optBody = document.getElementById('previewOptionalsBody');
                    optBody.innerHTML = '';
                    document.getElementById('previewOptionalCount').textContent = preview.optionalSubjects.length;
                    preview.optionalSubjects.forEach(os => {
                        const statusBadge = os.gradeSubjectExists
                            ? '<span class="badge bg-success">Ready</span>'
                            : '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Auto-create</span>';
                        optBody.innerHTML += `<tr><td>${os.name}</td><td>${os.subject}</td><td>${os.grade}</td><td>${os.studentCount}</td><td>${os.promotedName || '—'}</td><td>${statusBadge}</td></tr>`;
                    });
                } else {
                    optItem.style.display = 'none';
                }

                yearPreviewSection.classList.remove('d-none');
            }

            function populateResults(details) {
                const container = document.getElementById('yearResultsCards');
                buildSummaryCards(container, details);

                const autoCreated = details.autoCreatedGradeSubjects || 0;
                const alertEl = document.getElementById('yearResultsAutoCreatedAlert');
                if (autoCreated > 0) {
                    document.getElementById('yearResultsAutoCreatedCount').textContent = autoCreated;
                    alertEl.classList.remove('d-none');
                } else {
                    alertEl.classList.add('d-none');
                }

                yearResultsSection.classList.remove('d-none');
            }

            function resetYearModal() {
                yearRolloverState = 'initial';
                hideYearError();
                setButtonLoading(false);
                setButtonState('search', 'Preview Rollover', 'primary');
                yearRolloverButton.disabled = false;
                yearFromTermSelect.disabled = false;
                yearToTermSelect.disabled = false;
                yearFromTermSelect.value = '';
                yearToTermSelect.value = '';
                yearLoadingIndicator.classList.add('d-none');
                yearSuccessIndicator.classList.add('d-none');
                yearTermSelector.classList.remove('d-none');
                yearPreviewSection.classList.add('d-none');
                yearResultsSection.classList.add('d-none');
                yearCancelButton.textContent = 'Cancel';
            }

            async function fetchJson(url, body) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(body)
                });
                const data = await response.json();
                if (!response.ok) throw { status: response.status, data };
                return data;
            }

            async function handlePreview(fromTermId, toTermId) {
                try {
                    hideYearError();
                    setButtonLoading(true, 'Loading...');
                    yearLoadingText.textContent = 'Checking grades and generating preview...';
                    yearLoadingIndicator.classList.remove('d-none');

                    // Step 1: Check grades
                    const checkData = await fetchJson("{{ route('setup.check-grades') }}", { fromTermId, toTermId });
                    if (!checkData.canRollover) {
                        yearLoadingIndicator.classList.add('d-none');
                        showYearError(checkData.message || 'Cannot perform year rollover.');
                        setButtonLoading(false);
                        return;
                    }

                    // Step 2: Fetch preview
                    const previewData = await fetchJson("{{ route('setup.preview-year-rollover') }}", { fromTermId, toTermId });
                    if (!previewData.success) {
                        yearLoadingIndicator.classList.add('d-none');
                        showYearError(previewData.message || 'Failed to generate preview.');
                        setButtonLoading(false);
                        return;
                    }

                    yearLoadingIndicator.classList.add('d-none');
                    populatePreview(previewData.preview);

                    // Transition to previewed state
                    yearRolloverState = 'previewed';
                    yearFromTermSelect.disabled = true;
                    yearToTermSelect.disabled = true;
                    setButtonLoading(false);
                    setButtonState('play', 'Confirm & Execute Rollover', 'warning');

                } catch (error) {
                    console.error('Preview error:', error);
                    yearLoadingIndicator.classList.add('d-none');
                    setButtonLoading(false);
                    showYearError(error.data?.message || 'An error occurred while generating the preview.');
                }
            }

            async function handleExecute(fromTermId, toTermId) {
                const confirmed = confirm('Are you sure you want to execute the year rollover? This will modify the database.');
                if (!confirmed) return;

                try {
                    yearRolloverState = 'executing';
                    hideYearError();
                    setButtonLoading(true, 'Processing...');
                    yearPreviewSection.classList.add('d-none');
                    yearTermSelector.classList.add('d-none');
                    yearLoadingText.textContent = 'Executing year rollover, please wait...';
                    yearLoadingIndicator.classList.remove('d-none');

                    const data = await fetchJson("{{ route('setup.year-rollover') }}", { fromTermId, toTermId });

                    yearLoadingIndicator.classList.add('d-none');

                    if (!data.canRollover) {
                        showYearError(data.message || 'Rollover failed.');
                        setButtonLoading(false);
                        setButtonState('play', 'Confirm & Execute Rollover', 'warning');
                        yearRolloverState = 'previewed';
                        yearTermSelector.classList.remove('d-none');
                        yearPreviewSection.classList.remove('d-none');
                        return;
                    }

                    // Success
                    yearRolloverState = 'complete';
                    yearSuccessMessage.textContent = data.message || 'The academic year has been rolled over successfully.';
                    yearSuccessIndicator.classList.remove('d-none');

                    if (data.details && typeof data.details === 'object') {
                        populateResults(data.details);
                    }

                    setButtonLoading(false);
                    setButtonState('check', 'Complete', 'success');
                    yearRolloverButton.disabled = true;
                    yearCancelButton.textContent = 'Close';

                } catch (error) {
                    console.error('Rollover error:', error);
                    yearLoadingIndicator.classList.add('d-none');
                    yearTermSelector.classList.remove('d-none');
                    yearPreviewSection.classList.remove('d-none');
                    setButtonLoading(false);
                    setButtonState('play', 'Confirm & Execute Rollover', 'warning');
                    yearRolloverState = 'previewed';
                    showYearError(error.data?.message || 'An unexpected error occurred during the rollover process.');
                }
            }

            yearRolloverButton.addEventListener('click', function(event) {
                event.preventDefault();

                const fromTermId = yearFromTermSelect.value;
                const toTermId = yearToTermSelect.value;

                if (!fromTermId || !toTermId) {
                    showYearError('Please select both "From" and "To" terms.');
                    return;
                }
                if (fromTermId === toTermId) {
                    showYearError('The "From" and "To" terms cannot be the same.');
                    return;
                }

                if (yearRolloverState === 'initial') {
                    handlePreview(fromTermId, toTermId);
                } else if (yearRolloverState === 'previewed') {
                    handleExecute(fromTermId, toTermId);
                }
            });

            const yearRolloverModalElement = document.getElementById('yearRolloverModal');
            yearRolloverModalElement.addEventListener('hidden.bs.modal', function() {
                resetYearModal();
            });
        });

        // Tab persistence
        document.addEventListener('DOMContentLoaded', function() {
            const tabList = document.getElementById('schoolSetupTabs');
            const tabs = tabList.querySelectorAll('.nav-link');
            const storageKey = 'schoolSetupActiveTab';

            function setActiveTab(tabId) {
                let tabFound = false;

                tabs.forEach(tab => {
                    const tabPaneId = tab.getAttribute('href');
                    const tabPane = tabPaneId ? document.querySelector(tabPaneId) : null;

                    if (tab.getAttribute('data-tab-id') === tabId && tabPane) {
                        tab.classList.add('active');
                        tabPane.classList.add('active', 'show');
                        tabFound = true;
                    } else {
                        tab.classList.remove('active');
                        if (tabPane) {
                            tabPane.classList.remove('active', 'show');
                        }
                    }
                });

                if (!tabFound && tabs.length > 0) {
                    setFirstAvailableTab();
                }
            }

            function setFirstAvailableTab() {
                for (let tab of tabs) {
                    const tabPaneId = tab.getAttribute('href');
                    const tabPane = tabPaneId ? document.querySelector(tabPaneId) : null;

                    if (tabPane) {
                        tabs.forEach(t => {
                            t.classList.remove('active');
                            const paneId = t.getAttribute('href');
                            const pane = paneId ? document.querySelector(paneId) : null;
                            if (pane) {
                                pane.classList.remove('active', 'show');
                            }
                        });

                        tab.classList.add('active');
                        tabPane.classList.add('active', 'show');

                        const tabId = tab.getAttribute('data-tab-id');
                        if (tabId) {
                            localStorage.setItem(storageKey, tabId);
                        }
                        break;
                    }
                }
            }

            try {
                const storedTabId = localStorage.getItem(storageKey);
                if (storedTabId) {
                    const storedTab = Array.from(tabs).find(tab => tab.getAttribute('data-tab-id') === storedTabId);
                    if (storedTab) {
                        const tabPaneId = storedTab.getAttribute('href');
                        const tabPane = tabPaneId ? document.querySelector(tabPaneId) : null;
                        if (tabPane) {
                            setActiveTab(storedTabId);
                        } else {
                            setFirstAvailableTab();
                        }
                    } else {
                        setFirstAvailableTab();
                    }
                } else {
                    setFirstAvailableTab();
                }
            } catch (error) {
                console.error('Error restoring tab state:', error);
                setFirstAvailableTab();
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    const tabId = this.getAttribute('data-tab-id');
                    if (tabId) {
                        try {
                            localStorage.setItem(storageKey, tabId);
                        } catch (error) {
                            console.error('Error saving tab state:', error);
                        }
                    }
                });
            });
        });

        function incrementExtensionDays(selectId) {
            const select = document.getElementById(selectId);
            const currentValue = parseInt(select.value, 10);
            if (currentValue < 180) {
                select.value = currentValue + 1;
            }
        }

        // Year Toggle for Term Setup
        (function() {
            let currentYear = parseInt({{ $terms->first()->year ?? date('Y') }});
            let availableYears = @json($availableYears).map(y => parseInt(y));
            const termsContainer = document.getElementById('termsContainer');

            function renderTerms(terms) {
                let html = `<form action="{{ route('terms.update') }}" method="POST" id="termsForm">
                    @csrf`;

                terms.forEach(term => {
                    const startDate = term.start_date ? term.start_date.split('T')[0] : '';
                    const endDate = term.end_date ? term.end_date.split('T')[0] : '';
                    const isClosed = term.closed == 1;

                    html += `
                    <div class="row">
                        <input type="hidden" name="term_ids[${term.term}]" value="${term.id}">
                        <input type="hidden" name="term_year" value="${term.year}">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                Term ${term.term} Start Date (${term.year})
                                <span class="text-danger">*</span>
                                ${isClosed ? '<span class="badge bg-danger">Closed</span>' : ''}
                            </label>
                            ${isClosed ? `
                                <input class="form-control form-control-sm" type="date" value="${startDate}" disabled>
                                <input type="hidden" name="term${term.term}_start_date" value="${startDate}">
                            ` : `
                                <input class="form-control form-control-sm" type="date"
                                    name="term${term.term}_start_date"
                                    id="term${term.term}_start_date"
                                    value="${startDate}">
                            `}
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                Term ${term.term} End Date
                                <span class="text-danger">*</span>
                            </label>
                            ${isClosed ? `
                                <input class="form-control form-control-sm" type="date" value="${endDate}" disabled>
                                <input type="hidden" name="term${term.term}_end_date" value="${endDate}">
                            ` : `
                                <input class="form-control form-control-sm" type="date"
                                    name="term${term.term}_end_date"
                                    id="term${term.term}_end_date"
                                    value="${endDate}">
                            `}
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Extend Days</label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-sm"
                                    name="term${term.term}_extension_days"
                                    id="term${term.term}_extension_days" min="0"
                                    max="180"
                                    value="${term.extension_days || 0}"
                                    ${isClosed ? 'disabled' : ''}>
                                ${!isClosed ? `
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="incrementExtensionDays('term${term.term}_extension_days')">
                                        +
                                    </button>
                                ` : `
                                    <input type="hidden" name="term${term.term}_extension_days" value="${term.extension_days || 0}">
                                `}
                            </div>
                        </div>
                    </div>`;
                });

                html += `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <span class="year-nav-btn" id="prevYearBtn" title="Previous Year">
                                    <i class="bx bx-chevron-left"></i>
                                </span>
                                @can('view-system-admin')
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                        data-bs-target="#termRolloverHistoryModal">
                                        <i class="fas fa-history me-1"></i> Term History
                                    </button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#termRolloverModal">
                                        <i class="fas fa-sync-alt me-1"></i> Term Rollover
                                    </button>
                                @endcan
                                <button class="btn btn-primary btn-loading" type="submit">
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Update Term Dates</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                                <span class="year-nav-btn" id="nextYearBtn" title="Next Year">
                                    <i class="bx bx-chevron-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>`;

                termsContainer.innerHTML = html;
            }

            async function fetchTermsByYear(year) {
                try {
                    const baseUrl = "{{ route('terms.byYear', ['year' => 'tempYear']) }}";
                    const response = await fetch(baseUrl.replace('tempYear', year));
                    const data = await response.json();

                    if (data.terms && data.terms.length > 0) {
                        currentYear = year;
                        availableYears = data.availableYears.map(y => parseInt(y));
                        renderTerms(data.terms);
                        attachArrowListeners();
                    } else {
                        toastr.warning('No terms found for year ' + year);
                    }
                } catch (error) {
                    console.error('Error fetching terms:', error);
                    toastr.error('Failed to load terms');
                }
            }

            function attachArrowListeners() {
                const prevBtn = document.getElementById('prevYearBtn');
                const nextBtn = document.getElementById('nextYearBtn');

                prevBtn.addEventListener('click', function() {
                    const prevYear = currentYear - 1;
                    if (availableYears.includes(prevYear)) {
                        fetchTermsByYear(prevYear);
                    }
                });

                nextBtn.addEventListener('click', function() {
                    const nextYear = currentYear + 1;
                    if (availableYears.includes(nextYear)) {
                        fetchTermsByYear(nextYear);
                    }
                });

                const minYear = Math.min(...availableYears);
                const maxYear = Math.max(...availableYears);
                prevBtn.style.opacity = currentYear <= minYear ? '0.3' : '1';
                prevBtn.style.pointerEvents = currentYear <= minYear ? 'none' : 'auto';
                nextBtn.style.opacity = currentYear >= maxYear ? '0.3' : '1';
                nextBtn.style.pointerEvents = currentYear >= maxYear ? 'none' : 'auto';
            }

            attachArrowListeners();
        })();

        function copySchoolId() {
            const schoolIdInput = document.getElementById('school_id');
            const schoolId = schoolIdInput.value;

            if (schoolId) {
                navigator.clipboard.writeText(schoolId).then(function() {
                    const copyButton = schoolIdInput.nextElementSibling;
                    const originalIcon = copyButton.innerHTML;
                    copyButton.innerHTML = '<i class="fas fa-check"></i>';
                    copyButton.classList.remove('btn-outline-secondary');
                    copyButton.classList.add('btn-success');

                    setTimeout(function() {
                        copyButton.innerHTML = originalIcon;
                        copyButton.classList.remove('btn-success');
                        copyButton.classList.add('btn-outline-secondary');
                    }, 2000);

                    if (typeof toastr !== 'undefined') {
                        toastr.success('School ID copied to clipboard!');
                    }
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    schoolIdInput.select();
                    schoolIdInput.setSelectionRange(0, 99999);
                    document.execCommand('copy');

                    const copyButton = schoolIdInput.nextElementSibling;
                    const originalIcon = copyButton.innerHTML;
                    copyButton.innerHTML = '<i class="fas fa-check"></i>';
                    copyButton.classList.remove('btn-outline-secondary');
                    copyButton.classList.add('btn-success');

                    setTimeout(function() {
                        copyButton.innerHTML = originalIcon;
                        copyButton.classList.remove('btn-success');
                        copyButton.classList.add('btn-outline-secondary');
                    }, 2000);

                    if (typeof toastr !== 'undefined') {
                        toastr.success('School ID copied to clipboard!');
                    }
                });
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('No School ID available to copy');
                }
            }
        }
    </script>
@endsection

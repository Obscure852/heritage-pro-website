@extends('layouts.master')
@section('title')
    Edit Admission Information
@endsection
@section('css')
    <style>
        :root {
            --pathway-triple: #4f46e5;
            --pathway-triple-bg: #eef2ff;
            --pathway-double: #d97706;
            --pathway-double-bg: #fffbeb;
            --pathway-single: #059669;
            --pathway-single-bg: #ecfdf5;
            --pathway-unclassified: #64748b;
            --pathway-unclassified-bg: #f1f5f9;
        }

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

        .status-current { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-enrolled { background: #dbeafe; color: #1e40af; }
        .status-left { background: #fee2e2; color: #991b1b; }
        .status-to-join { background: #e9d5ff; color: #6b21a8; }
        .status-new-online { background: #cffafe; color: #0e7490; }
        .status-offer-accepted { background: #d1fae5; color: #065f46; }
        .status-deleted { background: #f3f4f6; color: #4b5563; }

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

        /* Enhanced Recommendation Card */
        .recommendation-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 6px solid var(--pathway-unclassified);
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .recommendation-card.rec-triple { border-left-color: var(--pathway-triple); background: var(--pathway-triple-bg); }
        .recommendation-card.rec-double { border-left-color: var(--pathway-double); background: var(--pathway-double-bg); }
        .recommendation-card.rec-single { border-left-color: var(--pathway-single); background: var(--pathway-single-bg); }
        .recommendation-card.rec-unclassified { border-left-color: var(--pathway-unclassified); background: var(--pathway-unclassified-bg); }

        .recommendation-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }

        .rec-triple .recommendation-icon { background: var(--pathway-triple); }
        .rec-double .recommendation-icon { background: var(--pathway-double); }
        .rec-single .recommendation-icon { background: var(--pathway-single); }
        .rec-unclassified .recommendation-icon { background: var(--pathway-unclassified); }

        .recommendation-content {
            flex: 1;
        }

        .recommendation-card .label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .recommendation-card .pathway-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            color: white;
        }

        .rec-triple .pathway-badge { background: var(--pathway-triple); }
        .rec-double .pathway-badge { background: var(--pathway-double); }
        .rec-single .pathway-badge { background: var(--pathway-single); }
        .rec-unclassified .pathway-badge { background: var(--pathway-unclassified); }

        .recommendation-card .class-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.7);
            color: #374151;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .recommendation-card .reason {
            color: #4b5563;
            font-size: 13px;
            margin-top: 8px;
            line-height: 1.5;
        }

        .recommendation-grades {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .recommendation-grade-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.06);
            color: #374151;
        }

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 32px;
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

        /* Color-coded Grade Form */
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

        .subject-group-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e5e7eb;
        }

        .subject-group-core .subject-group-title {
            color: #4f46e5;
            border-bottom-color: #c7d2fe;
        }

        .subject-group-other .subject-group-title {
            color: #6b7280;
            border-bottom-color: #e5e7eb;
        }

        /* Grade select color borders */
        .grade-select-wrapper {
            position: relative;
        }

        .grade-select-wrapper .form-select.grade-a { border-left: 3px solid #059669; }
        .grade-select-wrapper .form-select.grade-b { border-left: 3px solid #3b82f6; }
        .grade-select-wrapper .form-select.grade-c { border-left: 3px solid #eab308; }
        .grade-select-wrapper .form-select.grade-d { border-left: 3px solid #f97316; }
        .grade-select-wrapper .form-select.grade-e,
        .grade-select-wrapper .form-select.grade-u { border-left: 3px solid #ef4444; }
        .grade-select-wrapper .form-select.grade-m { border-left: 3px solid #8b5cf6; }

        .grade-indicator {
            display: none;
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%);
        }

        .grade-select-wrapper.has-grade .grade-indicator {
            display: inline-flex;
        }

        .grade-badge-sm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .grade-badge-sm.grade-a { background: #d1fae5; color: #059669; }
        .grade-badge-sm.grade-b { background: #dbeafe; color: #3b82f6; }
        .grade-badge-sm.grade-c { background: #fef9c3; color: #a16207; }
        .grade-badge-sm.grade-d { background: #ffedd5; color: #ea580c; }
        .grade-badge-sm.grade-e,
        .grade-badge-sm.grade-u { background: #fee2e2; color: #ef4444; }
        .grade-badge-sm.grade-m { background: #ede9fe; color: #8b5cf6; }

        .core-subject-highlight {
            background: #fafafe;
            border: 1px solid #e0e7ff;
            border-radius: 3px;
            padding: 3px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            margin-top: 8px;
        }

        .checkbox-group .form-check {
            margin: 0;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .existing-attachments {
            background: #f9fafb;
            border-radius: 3px;
            padding: 16px;
        }

        .existing-attachments ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .existing-attachments li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 3px;
            margin-bottom: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .existing-attachments li:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .existing-attachments li:last-child {
            margin-bottom: 0;
        }

        .existing-attachments li i {
            font-size: 24px;
            color: #6b7280;
        }

        .existing-attachments li a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .existing-attachments li a:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.5;
            margin-bottom: 16px;
        }

        /* Enhanced Enrollment Modal */
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: white;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .modal .recommendation-card {
            margin-bottom: 16px;
        }

        .class-option-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-bottom: 12px;
        }

        .class-option-card {
            border: 2px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .class-option-card:hover {
            border-color: #93c5fd;
            background: #f0f9ff;
        }

        .class-option-card.selected {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .class-option-card .class-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .class-option-card .recommended-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
        }

        .alt-classes-toggle {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 0;
            border: none;
            background: none;
            transition: color 0.2s;
        }

        .alt-classes-toggle:hover {
            color: #3b82f6;
        }

        .alt-classes-section {
            display: none;
            border-top: 1px solid #f3f4f6;
            padding-top: 12px;
            margin-top: 8px;
        }

        .alt-classes-section.visible {
            display: block;
        }

        .alt-classes-section .class-option-card {
            border-style: dashed;
        }

        .last-updated {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
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

            .recommendation-card {
                flex-direction: column;
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
            Edit Admission
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

    @if (session('warning'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>{{ session('warning') }}</strong>
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
            <div>
                <h1 class="page-title">{{ $admission->full_name ?? 'Admission Student' }}</h1>
                <small class="text-muted">Edit admission information</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                @php
                    $statusClass = 'status-pending';
                    $statusLower = strtolower(str_replace(' ', '-', $admission->status ?? ''));
                    if (in_array($statusLower, ['current', 'pending', 'enrolled', 'left', 'to-join', 'new-online', 'offer-accepted', 'deleted'])) {
                        $statusClass = 'status-' . $statusLower;
                    }
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $admission->status }}</span>
            </div>
        </div>

        @php
            $showSeniorAdmissionsFeature = \App\Models\SchoolSetup::isSeniorSchool();
            $placementRecommendation = $placementRecommendation ?? null;
            $recPathway = strtolower($placementRecommendation['pathway'] ?? 'unclassified');
            $canSeniorEnroll = $showSeniorAdmissionsFeature
                && $placementRecommendation
                && ($placementRecommendation['pathway'] ?? null) !== 'unclassified'
                && !in_array($admission->status, ['Enrolled', 'Deleted'], true)
                && $classes->isNotEmpty();
            $canLegacyEnroll = !$showSeniorAdmissionsFeature && $admission->status == 'Offer Accepted';
            $canEnrollAdmission = $canSeniorEnroll || $canLegacyEnroll;
            $recPathwayIcons = [
                'triple' => 'fas fa-flask',
                'double' => 'fas fa-vials',
                'single' => 'fas fa-atom',
                'unclassified' => 'fas fa-question-circle',
            ];
        @endphp

        <div class="form-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                        <i class="fas fa-user"></i>
                        <span class="d-none d-sm-inline">Profile Information</span>
                        <span class="d-inline d-sm-none">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="d-none d-sm-inline">Academic Information</span>
                        <span class="d-inline d-sm-none">Academic</span>
                    </a>
                </li>
                @can('admissions-health')
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                            <i class="fas fa-heartbeat"></i>
                            <span class="d-none d-sm-inline">Medical Information</span>
                            <span class="d-inline d-sm-none">Medical</span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                        <i class="fas fa-paperclip"></i>
                        <span class="d-none d-sm-inline">Attachments</span>
                        <span class="d-inline d-sm-none">Files</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#roles1" role="tab">
                        <i class="fas fa-cog"></i>
                        <span class="d-none d-sm-inline">Settings</span>
                        <span class="d-inline d-sm-none">Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- Profile Information Tab -->
            <div class="tab-pane active" id="home1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Profile Information</div>
                    <div class="help-content">
                        Update the student's personal and application details. Fields marked with <span class="text-danger">*</span> are required.
                    </div>
                </div>

                @if ($showSeniorAdmissionsFeature && $placementRecommendation)
                    <div class="recommendation-card rec-{{ $recPathway }}">
                        <div class="recommendation-icon">
                            <i class="{{ $recPathwayIcons[$recPathway] ?? 'fas fa-question-circle' }}"></i>
                        </div>
                        <div class="recommendation-content">
                            <div class="label">Recommended Science Pathway</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="pathway-badge">{{ $placementRecommendation['label'] }}</span>
                                @if (!empty($placementRecommendation['class_type']))
                                    <span class="class-type-badge">{{ $placementRecommendation['class_type'] }}</span>
                                @endif
                            </div>
                            <div class="reason">{{ $placementRecommendation['reason'] ?? '' }}</div>
                            @php
                                $seniorAcademic = $admission->seniorAdmissionAcademic;
                            @endphp
                            @if ($seniorAcademic)
                                <div class="recommendation-grades">
                                    @if ($seniorAcademic->science)
                                        <span class="recommendation-grade-chip">Science: <strong>{{ $seniorAcademic->science }}</strong></span>
                                    @endif
                                    @if ($seniorAcademic->mathematics)
                                        <span class="recommendation-grade-chip">Maths: <strong>{{ $seniorAcademic->mathematics }}</strong></span>
                                    @endif
                                    @if ($seniorAcademic->overall)
                                        <span class="recommendation-grade-chip">Overall: <strong>{{ $seniorAcademic->overall }}</strong></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <form class="needs-validation" method="post" action="{{ route('admissions.admissions-update', $admission->id) }}" novalidate>
                    @csrf
                    <input type="hidden" name="last_updated_by" value="{{ auth()->user()->id }}" required>

                    <h3 class="section-title">Personal Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                            <div class="input-icon-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                    name="first_name" id="first_name" placeholder="First name"
                                    value="{{ old('first_name', $admission->first_name) }}" required>
                            </div>
                            @error('first_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="middle_name">Middle Name</label>
                            <div class="input-icon-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                    name="middle_name" id="middle_name" placeholder="Middle name"
                                    value="{{ old('middle_name', $admission->middle_name) }}">
                            </div>
                            @error('middle_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                            <div class="input-icon-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    name="last_name" id="last_name" placeholder="Last name"
                                    value="{{ old('last_name', $admission->last_name) }}" required>
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
                                <input type="text" name="id_number" class="form-control @error('id_number') is-invalid @enderror"
                                    id="id_number" value="{{ old('id_number', $admission->formatted_id_number) }}" required>
                            </div>
                            @error('id_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                            <div class="input-icon-group flatpickr-wrapper" data-toggle="flatpickr-dob">
                                <i class="fas fa-calendar input-icon"></i>
                                <input type="text" data-input class="form-control @error('date_of_birth') is-invalid @enderror"
                                    id="date_of_birth" name="date_of_birth"
                                    value="{{ old('date_of_birth', $admission->formatted_date_of_birth) }}"
                                    placeholder="dd/mm/yyyy" required>
                            </div>
                            @error('date_of_birth')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror"
                                name="gender" id="gender" data-trigger required>
                                <option value="">Select Gender</option>
                                <option value="M" {{ old('gender', $admission->gender) === 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('gender', $admission->gender) === 'F' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nationality <span class="text-danger">*</span></label>
                            <select class="form-select @error('nationality') is-invalid @enderror"
                                data-trigger name="nationality" required>
                                <option value="">Select Nationality</option>
                                @foreach ($nationalities as $nationality)
                                    <option value="{{ $nationality->name }}"
                                        {{ old('nationality', $admission->nationality) == $nationality->name ? 'selected' : '' }}>
                                        {{ $nationality->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            @php
                                $sponsorIsRequired = blank($admission->connect_id);
                            @endphp
                            <label class="form-label">Parent/Sponsor
                                @if ($sponsorIsRequired)
                                    <span class="text-danger">*</span>
                                @endif
                                @if ($admission->sponsor_id)
                                    <a href="{{ route('sponsors.sponsor-edit', $admission->sponsor_id) }}" class="ms-1" title="View Sponsor">
                                        <i class="bx bx-link-alt"></i>
                                    </a>
                                @endif
                                @if ($admission->connect_id)
                                    <small class="d-block text-muted mt-1">Connect ID: {{ $admission->connect_id }}</small>
                                @endif
                            </label>
                            <select class="form-select @error('sponsor_id') is-invalid @enderror"
                                data-trigger name="sponsor_id" @if ($sponsorIsRequired) required @endif>
                                <option value="">Select Parent/Sponsor</option>
                                @foreach ($sponsors as $sponsor)
                                    <option value="{{ $sponsor->id }}"
                                        {{ old('sponsor_id', $admission->sponsor_id) == $sponsor->id ? 'selected' : '' }}>
                                        {{ $sponsor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sponsor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number
                                @if (blank($admission->connect_id))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <div class="input-icon-group">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" value="{{ old('phone', $admission->formatted_phone) }}" @if (blank($admission->connect_id)) required @endif>
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h3 class="section-title">Application Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" data-trigger required>
                                <option value="">Select status</option>
                                <option value="Current" {{ old('status', $admission->status) == 'Current' ? 'selected' : '' }}>Current</option>
                                <option value="Offer Accepted" {{ old('status', $admission->status) == 'Offer Accepted' ? 'selected' : '' }}>Offer Accepted</option>
                                <option value="New online" {{ old('status', $admission->status) == 'New online' ? 'selected' : '' }}>New Online</option>
                                <option value="Enrolled" {{ old('status', $admission->status) == 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                                <option value="Pending" {{ old('status', $admission->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Left" {{ old('status', $admission->status) == 'Left' ? 'selected' : '' }}>Left</option>
                                <option value="To Join" {{ old('status', $admission->status) == 'To Join' ? 'selected' : '' }}>To Join</option>
                                <option value="Deleted" {{ old('status', $admission->status) == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="grade_applying_for">Grade Applying For <span class="text-danger">*</span></label>
                            <select class="form-select @error('grade_applying_for') is-invalid @enderror"
                                name="grade_applying_for" id="grade_applying_for" required>
                                <option value="">Select grade</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->name }}"
                                        {{ old('grade_applying_for', $admission->grade_applying_for) == $grade->name ? 'selected' : '' }}>
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
                            <select name="year" class="form-select @error('year') is-invalid @enderror" data-trigger required>
                                <option value="">Select Year</option>
                                @php
                                    $currentYear = date('Y');
                                    $endYear = $currentYear + 3;
                                @endphp
                                @for ($year = $currentYear; $year <= $endYear; $year++)
                                    <option value="{{ $year }}" {{ old('year', $admission->year) == $year ? 'selected' : '' }}>
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
                                <input type="text" data-input class="form-control @error('application_date') is-invalid @enderror"
                                    id="application_date" name="application_date"
                                    value="{{ old('application_date', $admission->application_date ? \Carbon\Carbon::parse($admission->application_date)->format('d/m/Y') : '') }}"
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
                                        {{ old('term_id', $admission->term_id) == $term->id ? 'selected' : '' }}>
                                        Term {{ $term->term }}, {{ $term->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if ($canEnrollAdmission)
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#enrollAdmissionModal{{ $admission->id }}">
                                <i class="bx bx-user-plus"></i> Enroll Now
                            </button>
                        </div>
                    @endif

                    <div class="form-actions">
                        <a href="{{ route('admissions.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        @can('manage-admissions')
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            @endif
                        @endcan
                    </div>

                    <div class="last-updated">
                        Last Edited By: {{ App\Models\Admission::lastUpdatedBy($admission->id) ?? 'Support' }}
                        on {{ $admission->updated_at ?? '' }}
                    </div>
                </form>
            </div>

            <!-- Academic Information Tab -->
            <div class="tab-pane" id="profile1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Academic Information</div>
                    <div class="help-content">
                        @if ($showSeniorAdmissionsFeature)
                            Record the student's F3 junior-school subject grades using the Senior admissions academic layout.
                            @if ($placementRecommendation)
                                <span class="d-block mt-2">
                                    Current recommendation:
                                    <strong>{{ $placementRecommendation['label'] }}</strong>.
                                    {{ $placementRecommendation['reason'] ?? '' }}
                                </span>
                            @endif
                        @else
                            Record the student's academic grades from their previous school.
                        @endif
                    </div>
                </div>

                @if ($showSeniorAdmissionsFeature)
                    @php
                        $coreSubjects = [
                            'overall' => 'Overall',
                            'science' => 'Science',
                            'mathematics' => 'Mathematics',
                            'english' => 'English',
                        ];
                        $otherSubjects = [
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
                            'private_agriculture' => 'Private Agriculture',
                        ];
                        $overallGradeOptions = ['A', 'B', 'C', 'D', 'M'];
                        $subjectGradeOptions = ['A', 'B', 'C', 'D', 'E', 'U'];
                    @endphp

                    <form class="jce-grade-form" method="post" action="{{ route('admissions.create-senior-admission-academics') }}">
                        @csrf
                        <input type="hidden" name="admission_id" value="{{ $admission->id }}">

                        {{-- Core Subjects --}}
                        <div class="subject-group-core mb-4">
                            <div class="subject-group-title"><i class="fas fa-star me-1"></i> Core Subjects</div>
                            <div class="row g-3">
                                @foreach ($coreSubjects as $key => $subject)
                                    @php
                                        $gradeOptions = $key === 'overall' ? $overallGradeOptions : $subjectGradeOptions;
                                        $currentVal = old($key, data_get($admission->seniorAdmissionAcademic, $key));
                                    @endphp
                                    <div class="col-md-3 mb-3">
                                        <label for="{{ $key }}" class="form-label">{{ $subject }}</label>
                                        <div class="grade-select-wrapper {{ $currentVal ? 'has-grade' : '' }}">
                                            <select name="{{ $key }}" id="{{ $key }}" class="form-select grade-colorize @error($key) is-invalid @enderror {{ $currentVal ? 'grade-' . strtolower($currentVal) : '' }}">
                                                <option value="">Select Grade</option>
                                                @foreach ($gradeOptions as $grade)
                                                    <option value="{{ $grade }}" {{ $currentVal == $grade ? 'selected' : '' }}>
                                                        {{ $grade }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error($key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Other Subjects --}}
                        <div class="subject-group-other">
                            <div class="subject-group-title"><i class="fas fa-book me-1"></i> Other Subjects</div>
                            <div class="row g-3">
                                @foreach ($otherSubjects as $key => $subject)
                                    @php
                                        $gradeOptions = $subjectGradeOptions;
                                        $currentVal = old($key, data_get($admission->seniorAdmissionAcademic, $key));
                                    @endphp
                                    <div class="col-md-4 mb-3">
                                        <label for="{{ $key }}" class="form-label">{{ $subject }}</label>
                                        <div class="grade-select-wrapper {{ $currentVal ? 'has-grade' : '' }}">
                                            <select name="{{ $key }}" id="{{ $key }}" class="form-select grade-colorize @error($key) is-invalid @enderror {{ $currentVal ? 'grade-' . strtolower($currentVal) : '' }}">
                                                <option value="">Select Grade</option>
                                                @foreach ($gradeOptions as $grade)
                                                    <option value="{{ $grade }}" {{ $currentVal == $grade ? 'selected' : '' }}>
                                                        {{ $grade }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error($key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-secondary" href="{{ route('admissions.admissions-view', $admission->id) }}">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            @can('manage-admissions')
                                @if (!session('is_past_term'))
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Academic Grades</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </form>
                @else
                    <form class="needs-validation" method="post" action="{{ route('admissions.create-admission-academics') }}" novalidate>
                        @csrf
                        <input type="hidden" name="admission_id" value="{{ $admission->id }}">

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="science">Science</label>
                                <input type="text" class="form-control @error('science') is-invalid @enderror"
                                    name="science" id="science" placeholder="A*"
                                    value="{{ old('science', optional($admission->admissionAcademics)->science ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="mathematics">Mathematics</label>
                                <input type="text" class="form-control @error('mathematics') is-invalid @enderror"
                                    name="mathematics" id="mathematics" placeholder="A*"
                                    value="{{ old('mathematics', optional($admission->admissionAcademics)->mathematics ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="english">English</label>
                                <input type="text" class="form-control @error('english') is-invalid @enderror"
                                    name="english" id="english" placeholder="C"
                                    value="{{ old('english', optional($admission->admissionAcademics)->english ?? '') }}">
                            </div>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-secondary" href="{{ route('admissions.admissions-view', $admission->id) }}">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            @can('manage-admissions')
                                @if (!session('is_past_term'))
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </form>
                @endif
            </div>

            <!-- Medical Information Tab -->
            @can('admissions-health')
                <div class="tab-pane" id="messages1" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">Medical Information</div>
                        <div class="help-content">
                            Record the student's health history, allergies, and any medical conditions.
                        </div>
                    </div>

                    <form class="needs-validation" method="post" action="{{ route('admissions.create-admission-medicals') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="admission_id" value="{{ $admission->id }}">

                        <div class="form-group mb-3">
                            <label for="health_history" class="form-label">Health History</label>
                            <textarea name="health_history" id="health_history" class="form-control" rows="3">{{ old('health_history', optional($admission->admissionMedicals)->health_history ?? '') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Immunization Records</label>
                            <div class="custom-file-input">
                                <input type="file" name="immunization_records" id="immunization_records" accept=".pdf,.jpg,.jpeg,.png">
                                <label for="immunization_records" class="file-input-label">
                                    <div class="file-input-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <div class="file-input-text">
                                        <span class="file-label">Choose File</span>
                                        <span class="file-hint" id="immunizationHint">PDF, JPG, PNG (Max 3MB)</span>
                                        <span class="file-selected d-none" id="immunizationFileName"></span>
                                    </div>
                                </label>
                            </div>
                            @error('immunization_records')
                                <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                            @if (optional($admission->admissionMedicals)->immunization_records != null)
                                @php
                                    $filePath = optional($admission->admissionMedicals)->immunization_records;
                                    $fileUrl = asset('storage/' . $filePath);
                                    $fileName = basename($filePath);
                                @endphp
                                <div class="existing-file">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Current file:</span>
                                    <a href="{{ $fileUrl }}" target="_blank" download>{{ $fileName }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Allergies & Food Preferences</label>
                            <div class="checkbox-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="peanuts" value="Peanuts"
                                        {{ optional($admission->admissionMedicals)->peanuts != false ? 'checked' : '' }} id="peanuts">
                                    <label class="form-check-label" for="peanuts">Peanuts</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="red_meat" value="Red meat"
                                        {{ optional($admission->admissionMedicals)->red_meat != false ? 'checked' : '' }} id="red_meat">
                                    <label class="form-check-label" for="red_meat">Red Meat</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="vegetarian" value="Vegetarian"
                                        {{ optional($admission->admissionMedicals)->vegetarian != false ? 'checked' : '' }} id="vegetarian">
                                    <label class="form-check-label" for="vegetarian">Vegetarian</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="other_allergies" class="form-label">Other Allergies</label>
                            <textarea name="other_allergies" id="other_allergies" class="form-control" rows="2">{{ old('other_allergies', optional($admission->admissionMedicals)->other_allergies ?? '') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Limb Disabilities</label>
                            <div class="checkbox-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="left_leg" value="Left Leg"
                                        {{ optional($admission->admissionMedicals)->left_leg != false ? 'checked' : '' }} id="left_leg">
                                    <label class="form-check-label" for="left_leg">Left Leg</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="right_leg" value="Right Leg"
                                        {{ optional($admission->admissionMedicals)->right_leg != false ? 'checked' : '' }} id="right_leg">
                                    <label class="form-check-label" for="right_leg">Right Leg</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="left_hand" value="Left Arm"
                                        {{ optional($admission->admissionMedicals)->left_hand != false ? 'checked' : '' }} id="left_hand">
                                    <label class="form-check-label" for="left_hand">Left Arm</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="right_hand" value="Right Arm"
                                        {{ optional($admission->admissionMedicals)->right_hand != false ? 'checked' : '' }} id="right_hand">
                                    <label class="form-check-label" for="right_hand">Right Arm</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="other_disabilities" class="form-label">Other Disabilities</label>
                            <textarea name="other_disabilities" id="other_disabilities" class="form-control" rows="2">{{ old('other_disabilities', optional($admission->admissionMedicals)->other_disabilities ?? '') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="medical_conditions" class="form-label">Medical Conditions</label>
                            <textarea name="medical_conditions" id="medical_conditions" class="form-control" rows="3">{{ old('medical_conditions', optional($admission->admissionMedicals)->medical_conditions ?? '') }}</textarea>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-secondary" href="{{ route('admissions.index') }}">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endcan

            <!-- Attachments Tab -->
            <div class="tab-pane" id="settings1" role="tabpanel">
                <div class="help-text">
                    <div class="help-title">Online Application Attachments</div>
                    <div class="help-content">
                        View documents uploaded during the online application process.
                    </div>
                </div>

                @if ($admission->onlineAttachments->count() > 0)
                    <div class="existing-attachments">
                        <ul>
                            @foreach ($admission->onlineAttachments as $attachment)
                                @php
                                    $fileName = basename($attachment->file_path);
                                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                                    $iconClass = '';

                                    switch ($extension) {
                                        case 'pdf':
                                            $iconClass = 'bx bxs-file-pdf text-danger';
                                            break;
                                        case 'jpg':
                                        case 'jpeg':
                                            $iconClass = 'bx bxs-file-jpg text-warning';
                                            break;
                                        case 'png':
                                            $iconClass = 'bx bxs-file-png text-info';
                                            break;
                                        case 'gif':
                                            $iconClass = 'bx bxs-file-gif text-success';
                                            break;
                                        default:
                                            $iconClass = 'bx bxs-file text-secondary';
                                            break;
                                    }

                                    $attachmentTitle = '';
                                    switch ($attachment->attachment_type) {
                                        case 'identification':
                                            $attachmentTitle = 'Identification';
                                            break;
                                        case 'report':
                                            $attachmentTitle = 'Report Card';
                                            break;
                                        case 'application_fee_receipt':
                                            $attachmentTitle = 'Application Fee Receipt';
                                            break;
                                    }
                                @endphp
                                <li>
                                    <i class="{{ $iconClass }}"></i>
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" download>{{ $attachmentTitle }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bx bx-folder-open"></i>
                        <p>No online application documents for this student</p>
                    </div>
                @endif
            </div>

            <!-- Settings Tab -->
            <div class="tab-pane" id="roles1" role="tabpanel">
                <div class="empty-state">
                    <i class="bx bx-cog"></i>
                    <p>No Configuration</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div class="modal fade" id="enrollAdmissionModal{{ $admission->id }}" tabindex="-1"
        aria-labelledby="enrollAdmissionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollAdmissionModalLabel">Enroll Admission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admissions.enrol-admission', $admission->id) }}" method="POST">
                        @csrf
                        @if ($showSeniorAdmissionsFeature && $placementRecommendation)
                            <div class="recommendation-card rec-{{ $recPathway }}" style="margin-bottom: 16px;">
                                <div class="recommendation-icon">
                                    <i class="{{ $recPathwayIcons[$recPathway] ?? 'fas fa-question-circle' }}"></i>
                                </div>
                                <div class="recommendation-content">
                                    <div class="label">Recommended Science Pathway</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="pathway-badge">{{ $placementRecommendation['label'] }}</span>
                                        @if (!empty($placementRecommendation['class_type']))
                                            <small class="text-muted">Preferred class type: {{ $placementRecommendation['class_type'] }}</small>
                                        @endif
                                    </div>
                                    <div class="reason">{{ $placementRecommendation['reason'] ?? '' }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label for="class_id" class="form-label">Select Class:</label>

                            {{-- Recommended classes as selectable cards --}}
                            <div class="class-option-cards" id="classOptionCards{{ $admission->id }}">
                                @foreach (($recommendedClasses ?? $classes) as $index => $class)
                                    <label class="class-option-card {{ $index === 0 ? 'selected' : '' }}" data-class-id="{{ $class->id }}">
                                        <span class="class-name">{{ $class->name }}</span>
                                        @if ($showSeniorAdmissionsFeature && ($hasRecommendedClassMatch ?? false) && $index === 0)
                                            <span class="recommended-tag"><i class="fas fa-check"></i> Recommended</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            {{-- Hidden select that holds actual value --}}
                            <select class="form-select d-none" name="klass_id" id="class_id">
                                @foreach (($recommendedClasses ?? $classes) as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                                @foreach (($alternativeClasses ?? collect()) as $class)
                                    <option value="{{ $class->id }}" data-alt-class="1" hidden>{{ $class->name }}</option>
                                @endforeach
                            </select>

                            @if ($showSeniorAdmissionsFeature && ($hasRecommendedClassMatch ?? false))
                                <small class="text-muted d-block mt-2">Matching classes are shown first based on the recommendation above. Use Show all classes to place this student into any other F4 class.</small>
                            @elseif ($showSeniorAdmissionsFeature && $placementRecommendation)
                                <small class="text-muted d-block mt-2">No exact class type match was found, so all available F4 classes are shown.</small>
                            @endif
                            @if ($showSeniorAdmissionsFeature)
                                <small class="text-muted d-block mt-2">Max Students is advisory only. Choosing a class manually overrides the recommended pathway for this admission.</small>
                            @endif
                        </div>

                        @if ($showSeniorAdmissionsFeature && ($hasRecommendedClassMatch ?? false) && isset($alternativeClasses) && $alternativeClasses->isNotEmpty())
                            <button type="button" class="alt-classes-toggle" id="altClassesToggle{{ $admission->id }}">
                                <i class="fas fa-chevron-down"></i>
                                <span>Show all classes</span>
                            </button>
                            <div class="alt-classes-section" id="altClassesSection{{ $admission->id }}">
                                <div class="class-option-cards">
                                    @foreach ($alternativeClasses as $class)
                                        <label class="class-option-card" data-class-id="{{ $class->id }}" data-alt="1">
                                            <span class="class-name">{{ $class->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @can('manage-admissions')
                            @if (!session('is_past_term'))
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm btn-loading">
                                        <span class="btn-text">Enroll</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Enrolling...
                                        </span>
                                    </button>
                                </div>
                            @endif
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function saveActiveTab(tabId) {
            localStorage.setItem('activeAdmissionTab', tabId);
        }

        function getActiveTab() {
            return localStorage.getItem('activeAdmissionTab') || 'home1';
        }

        function activateTab(tabId) {
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(content => {
                content.classList.remove('active');
            });

            const selectedTab = document.querySelector(`[href="#${tabId}"]`);
            const selectedContent = document.getElementById(tabId);

            if (selectedTab && selectedContent) {
                selectedTab.classList.add('active');
                selectedContent.classList.add('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
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

            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    const tabId = this.getAttribute('href').substring(1);
                    saveActiveTab(tabId);
                });
            });

            const savedTab = getActiveTab();
            activateTab(savedTab);

            // Class option cards click handler
            const classSelect = document.getElementById('class_id');
            const admissionId = '{{ $admission->id }}';

            document.querySelectorAll('.class-option-card').forEach(card => {
                card.addEventListener('click', function() {
                    // Deselect all
                    document.querySelectorAll('.class-option-card').forEach(c => c.classList.remove('selected'));
                    // Select this one
                    this.classList.add('selected');
                    // Update hidden select
                    const classId = this.dataset.classId;
                    if (classSelect) {
                        classSelect.value = classId;
                        // Unhide alt option if needed
                        const altOption = classSelect.querySelector(`option[value="${classId}"]`);
                        if (altOption) altOption.hidden = false;
                    }
                });
            });

            // Alternative classes toggle
            const altToggle = document.getElementById('altClassesToggle' + admissionId);
            const altSection = document.getElementById('altClassesSection' + admissionId);
            if (altToggle && altSection) {
                altToggle.addEventListener('click', function() {
                    altSection.classList.toggle('visible');
                    const icon = this.querySelector('i');
                    if (altSection.classList.contains('visible')) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                        this.querySelector('span').textContent = 'Hide alternative classes';
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                        this.querySelector('span').textContent = 'Show all classes';
                    }
                });
            }

            // Grade colorization for academic tab
            document.querySelectorAll('.grade-colorize').forEach(function(select) {
                function updateColor() {
                    const val = select.value;
                    const wrapper = select.closest('.grade-select-wrapper');
                    // Remove all grade classes
                    select.className = select.className.replace(/\bgrade-[a-z]\b/g, '');
                    if (wrapper) wrapper.classList.remove('has-grade');

                    if (val) {
                        select.classList.add('grade-' + val.toLowerCase());
                        if (wrapper) wrapper.classList.add('has-grade');
                    }
                }

                select.addEventListener('change', updateColor);
                updateColor();
            });

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

            // Immunization file input handler
            const immunizationInput = document.getElementById('immunization_records');
            if (immunizationInput) {
                const immunizationHint = document.getElementById('immunizationHint');
                const immunizationFileName = document.getElementById('immunizationFileName');

                immunizationInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const fileSize = file.size / 1024 / 1024; // Convert to MB

                        if (fileSize > 3) {
                            alert('File size exceeds 3MB. Please choose a smaller file.');
                            this.value = '';
                            immunizationHint.classList.remove('d-none');
                            immunizationFileName.classList.add('d-none');
                            return;
                        }

                        immunizationHint.classList.add('d-none');
                        immunizationFileName.classList.remove('d-none');
                        immunizationFileName.textContent = file.name;
                    } else {
                        immunizationHint.classList.remove('d-none');
                        immunizationFileName.classList.add('d-none');
                        immunizationFileName.textContent = '';
                    }
                });
            }
        });
    </script>
@endsection

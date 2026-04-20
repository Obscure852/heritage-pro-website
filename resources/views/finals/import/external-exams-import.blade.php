@extends('layouts.master')
@section('title')
    External Exam Management
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
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
            line-height: 1.5;
            margin: 0;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        /* Form Controls */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control-sm,
        .form-select-sm {
            padding: 8px 12px;
            font-size: 13px;
        }

        .modal-backdrop.show {
            opacity: 0.4 !important;
        }

        .readonly-input {
            background-color: white !important;
            color: black;
            cursor: default;
        }

        .btn-fixed-width {
            width: 150px;
        }

        .row,
        .col-12,
        .card,
        .card-body {
            overflow: visible;
        }

        .step-indicator {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .step-active {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
        }

        .step-complete {
            background: #28a745;
            color: white;
        }

        .step-pending {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        .progress-thin {
            height: 4px;
            border-radius: 2px;
        }

        .template-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            text-align: center;
            padding: 2rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .template-card:hover,
        .template-card.dragover {
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .cleanup-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 3px;
        }

        .target-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 3px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .target-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .target-card.high-achievement {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #f8fbff 0%, #e3f2fd 100%);
        }

        .target-card.pass-rate {
            border-color: #10b981;
            background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
        }

        .target-card.non-failure {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffef8 0%, #fff3cd 100%);
        }

        .target-input {
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
            border: 2px solid #e9ecef;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.9);
        }

        .target-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .exam-type-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(0, 0, 0, 0.1);
            color: rgba(0, 0, 0, 0.7);
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .current-targets-display {
            background: linear-gradient(135deg, #e8f5e8 0%, #d5f5d5 100%);
            border: 1px solid #10b981;
            border-radius: 3px;
            padding: 1rem;
        }

        .guidelines-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }

        .guideline-item {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }

        .guideline-item:last-child {
            border-bottom: none;
        }

        .section-card {
            transition: all 0.2s ease;
            border-radius: 3px;
        }

        .section-card .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }

        .btn {
            padding: 10px 16px;
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
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
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

        .btn-outline-primary {
            background: transparent;
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            background: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-outline-secondary:hover {
            background: #f3f4f6;
            color: #374151;
            transform: translateY(-1px);
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

        /* Table Styling */
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

        /* Input Group */
        .input-group .form-control,
        .input-group .form-select {
            border-radius: 3px;
        }

        .input-group-text {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #6b7280;
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
            border-color: #4e73df;
            background: #f0f9ff;
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
            color: #4e73df;
            font-weight: 500;
        }

        /* Required Field Indicator */
        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        @media (max-width: 768px) {
            .admissions-header {
                padding: 16px;
            }

            .admissions-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
@endsection
@section('content')
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
        @foreach ($errors->all() as $error)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 style="margin:0;">External Exam Management</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage performance targets and import exam results for
                        {{ $school_data->name ?? 'your school' }}</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white bg-opacity-25">{{ $school_data->type ?? 'Unknown' }} School</span>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">External Exam Results</div>
                <div class="help-content">
                    Manage performance targets, import exam results, and convert PDF result files to Excel format.
                    Use the tabs below to navigate between different functions.
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist" id="examTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#targets" role="tab"
                                data-tab-id="targets">
                                <i class="fas fa-bullseye me-2 text-muted"></i>Performance Targets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#import" role="tab" data-tab-id="import">
                                <i class="fas fa-file-import me-2 text-muted"></i>Import Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#conversion" role="tab"
                                data-tab-id="conversion">
                                <i class="fas fa-exchange-alt me-2 text-muted"></i>BEC Results Converter
                            </a>
                        </li>
                        @if ($showSubjectMappingTab ?? false)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#subject-mapping" role="tab"
                                    data-tab-id="subject-mapping">
                                    <i class="fas fa-project-diagram me-2 text-muted"></i>Subject Mapping
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="targets" role="tabpanel">
                            <div class="card border-1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <form class="needs-validation" method="POST"
                                                action="{{ route('finals.performance-targets.store-target') }}"
                                                id="targetsForm" novalidate>
                                                @csrf
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-6">
                                                        <label for="academic_year" class="form-label fw-semibold">Academic
                                                            Year</label>
                                                        <select class="form-select form-select-sm" name="academic_year"
                                                            id="academic_year" required onchange="loadExistingTargets()">
                                                            <option value="">Select year...</option>
                                                            @for ($year = date('Y') + 2; $year >= date('Y') - 3; $year--)
                                                                <option value="{{ $year }}"
                                                                    {{ $year == date('Y') ? 'selected' : '' }}>
                                                                    {{ $year }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="exam_type_targets" class="form-label fw-semibold">Exam
                                                            Type</label>
                                                        <select class="form-select form-select-sm" name="exam_type"
                                                            id="exam_type_targets" required onchange="updateTargetLabels()">
                                                            @foreach ($examTypeOptions as $examTypeValue => $examTypeLabel)
                                                                <option value="{{ $examTypeValue }}"
                                                                    {{ $defaultExamType === $examTypeValue ? 'selected' : '' }}>
                                                                    {{ $examTypeLabel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Current Targets Display (if exists) -->
                                                <div id="currentTargetsDisplay" class="current-targets-display mb-4"
                                                    style="display: none;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1"><i class="bi bi-info-circle me-2"></i>Current
                                                                Targets</h6>
                                                            <small class="text-muted">Last updated: <span
                                                                    id="lastUpdated"></span></small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            onclick="loadCurrentValues()">
                                                            <i class="bi bi-arrow-down me-1"></i>Load Current
                                                        </button>
                                                    </div>
                                                    <div class="row mt-2" id="currentTargetsValues">
                                                        <!-- Will be populated by JavaScript -->
                                                    </div>
                                                </div>

                                                <!-- Target Input Cards -->
                                                <div class="row g-4 mb-4">
                                                    <div class="col-md-4">
                                                        <div class="target-card high-achievement">
                                                            <div class="exam-type-badge" id="highAchievementBadge">High
                                                            </div>
                                                            <div class="mb-3">
                                                                <h6 class="text-primary mb-1" id="highAchievementLabel">
                                                                    High Achievement</h6>
                                                                <small class="text-muted"
                                                                    id="highAchievementDesc">Students achieving top
                                                                    grades</small>
                                                            </div>
                                                            <div class="input-group input-group-lg">
                                                                <input type="number" class="form-control target-input"
                                                                    name="high_achievement_target"
                                                                    id="high_achievement_target" min="0"
                                                                    max="100" step="0.1" placeholder="25.0"
                                                                    required>
                                                                <span
                                                                    class="input-group-text bg-white border-start-0">%</span>
                                                            </div>
                                                            <small class="text-muted mt-2 d-block"
                                                                id="highAchievementGrades">Merit + A + B grades</small>
                                                        </div>
                                                    </div>

                                                    <!-- Pass Rate Target -->
                                                    <div class="col-md-4">
                                                        <div class="target-card pass-rate">
                                                            <div class="exam-type-badge" id="passRateBadge">Pass</div>
                                                            <div class="mb-3">
                                                                <h6 class="text-success mb-1" id="passRateLabel">Pass Rate
                                                                </h6>
                                                                <small class="text-muted" id="passRateDesc">Students
                                                                    achieving passing grades</small>
                                                            </div>
                                                            <div class="input-group input-group-lg">
                                                                <input type="number" class="form-control target-input"
                                                                    name="pass_rate_target" id="pass_rate_target"
                                                                    min="0" max="100" step="0.1"
                                                                    placeholder="65.0" required>
                                                                <span
                                                                    class="input-group-text bg-white border-start-0">%</span>
                                                            </div>
                                                            <small class="text-muted mt-2 d-block"
                                                                id="passRateGrades">Merit + A + B + C grades</small>
                                                        </div>
                                                    </div>

                                                    <!-- Non-Failure Target -->
                                                    <div class="col-md-4">
                                                        <div class="target-card non-failure">
                                                            <div class="exam-type-badge" id="nonFailureBadge">Non-Fail
                                                            </div>
                                                            <div class="mb-3">
                                                                <h6 class="text-warning mb-1" id="nonFailureLabel">
                                                                    Non-Failure Rate</h6>
                                                                <small class="text-muted" id="nonFailureDesc">Students not
                                                                    completely failing</small>
                                                            </div>
                                                            <div class="input-group input-group-lg">
                                                                <input type="number" class="form-control target-input"
                                                                    name="non_failure_target" id="non_failure_target"
                                                                    min="0" max="100" step="0.1"
                                                                    placeholder="85.0" required>
                                                                <span
                                                                    class="input-group-text bg-white border-start-0">%</span>
                                                            </div>
                                                            <small class="text-muted mt-2 d-block"
                                                                id="nonFailureGrades">Merit + A + B + C + D grades</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Notes -->
                                                <div class="mb-4">
                                                    <label for="targets_notes" class="form-label fw-semibold">
                                                        <i class="bi bi-chat-text me-2"></i>Notes (Optional)
                                                    </label>
                                                    <textarea class="form-control form-control-sm" name="notes" id="targets_notes" rows="3"
                                                        placeholder="Add any notes about these performance targets..."></textarea>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="row">
                                                    <div class="col-md-6"></div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                onclick="resetTargetsForm()">
                                                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm"
                                                                onclick="loadRecommendedTargets()">
                                                                <i class="bi bi-lightbulb me-2"></i>Load Targers
                                                            </button>
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm btn-loading">
                                                                <span class="btn-text"><i
                                                                        class="fas fa-save me-2"></i>Save Targets</span>
                                                                <span class="btn-spinner d-none">
                                                                    <span class="spinner-border spinner-border-sm me-2"
                                                                        role="status" aria-hidden="true"></span>
                                                                    Saving...
                                                                </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="import" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Main Import Form -->
                                        <div class="col-lg-8">
                                            <form action="{{ route('finals.import.upload-external-results') }}"
                                                method="post" enctype="multipart/form-data" id="externalExamForm">
                                                @csrf
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="exam_type" class="form-label">Exam Type</label>
                                                            <select class="form-select form-select-sm" name="exam_type"
                                                                id="exam_type" required>
                                                                @foreach ($examTypeOptions as $examTypeValue => $examTypeLabel)
                                                                    <option value="{{ $examTypeValue }}"
                                                                        {{ old('exam_type', $defaultExamType) === $examTypeValue ? 'selected' : '' }}>
                                                                        {{ $examTypeLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="graduation_term_id" class="form-label">Graduation
                                                                Term <span class="text-danger">*</span></label>
                                                            <select
                                                                class="form-select form-select-sm @error('graduation_term_id') is-invalid @enderror"
                                                                name="graduation_term_id" id="graduation_term_id"
                                                                required>
                                                                <option value="">Select term...</option>
                                                                @foreach ($terms as $term)
                                                                    <option value="{{ $term->id }}"
                                                                        {{ old('graduation_term_id') == $term->id ? 'selected' : '' }}>
                                                                        {{ $term->term }} {{ $term->year }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('graduation_term_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="exam_session" class="form-label">Exam
                                                                Session</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="exam_session" id="exam_session"
                                                                placeholder="November 2024" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="exam_year" class="form-label">Year</label>
                                                            <select class="form-select form-select-sm" name="exam_year"
                                                                id="exam_year" required>
                                                                <option value="">Year...</option>
                                                                @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                                                    <option value="{{ $year }}"
                                                                        {{ $year == date('Y') ? 'selected' : '' }}>
                                                                        {{ $year }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="centre_code" class="form-label">Centre
                                                                Code</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="centre_code" id="centre_code"
                                                                value="{{ $school_data->centre_code ?? '' }}"
                                                                placeholder="JC0006">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="centre_name" class="form-label">Centre
                                                                Name</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="centre_name" id="centre_name"
                                                                placeholder="Heritage Junior School"
                                                                value="{{ $school_data->name ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- File Upload -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi bi-file-earmark-excel me-2"></i>Excel File Upload
                                                    </label>
                                                    <div class="custom-file-input">
                                                        <input type="file" name="file" id="exam_file"
                                                            accept=".xlsx,.xls" required>
                                                        <label for="exam_file" class="file-input-label">
                                                            <div class="file-input-icon">
                                                                <i class="fas fa-file-excel"></i>
                                                            </div>
                                                            <div class="file-input-text">
                                                                <span class="file-label">Choose Excel File</span>
                                                                <span class="file-hint" id="fileHint">.xlsx or .xls
                                                                    format (up to 10MB)</span>
                                                                <span class="file-selected d-none" id="fileName"></span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Notes -->
                                                <div class="mb-4">
                                                    <label for="import_notes" class="form-label"><i
                                                            class="bi bi-chat-text me-2"></i>Import Notes
                                                        (Optional)</label>
                                                    <textarea class="form-control form-control-sm" name="import_notes" id="import_notes" rows="3"
                                                        placeholder="Optional notes..."></textarea>
                                                </div>

                                                <!-- Cleanup Warning -->
                                                <div class="cleanup-warning p-3 mb-4" id="cleanupSection">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="cleanup_before_import" id="cleanup_before_import"
                                                            value="1">
                                                        <label class="form-check-label fw-semibold text-warning"
                                                            for="cleanup_before_import">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            Delete all existing external exam data before importing
                                                        </label>
                                                        <small class="d-block text-muted ps-1 mt-1">
                                                            This will permanently remove all external exams, results, and
                                                            subject results from the database.
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="row">
                                                    <div class="col-md-6"></div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                onclick="resetForm()">
                                                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                                            </button>
                                                            @can('view-system-admin')
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm btn-loading"
                                                                    id="examImportBtn">
                                                                    <span class="btn-text"><i
                                                                            class="fas fa-save me-2"></i>Import Exam
                                                                        Results</span>
                                                                    <span class="btn-spinner d-none">
                                                                        <span class="spinner-border spinner-border-sm me-2"
                                                                            role="status" aria-hidden="true"></span>
                                                                        Importing...
                                                                    </span>
                                                                </button>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Sidebar with Help -->
                                        <div class="col-lg-4">
                                            <!-- Template Downloads -->
                                            <div class="section-card card mb-4">
                                                <div class="card-header bg-light border-0">
                                                    <h6 class="mb-0"><i class="bi bi-download me-2"></i>Excel Templates
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="d-grid gap-2">
                                                        @foreach ($templateDownloads as $templateDownload)
                                                            <a class="text-success" href="{{ $templateDownload['path'] }}" download>
                                                                <i class="fas fa-file-excel me-2"></i>{{ $templateDownload['label'] }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Column Reference -->
                                            <div class="section-card card">
                                                <div class="card-header bg-light border-0">
                                                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Column Reference
                                                    </h6>
                                                </div>
                                                <div id="columnReference">
                                                    <div class="card-body p-3">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Required Columns</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><small><span class="badge bg-danger">*</span>
                                                                                Exam Number</small></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><small><span class="badge bg-danger">*</span>
                                                                                First Name</small></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><small><span class="badge bg-danger">*</span>
                                                                                Last Name</small></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><small><span class="badge bg-danger">*</span>
                                                                                Class</small></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><small><span class="badge bg-danger">*</span>
                                                                                Overall Grade</small></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="mt-2">
                                                            @foreach ($subjectColumnReferences as $subjectColumnReference)
                                                                <small class="text-muted d-block">
                                                                    <strong>{{ $subjectColumnReference }}</strong>
                                                                </small>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="conversion" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Main Converter Form -->
                                        <div class="col-lg-8">
                                            <form action="{{ route('finals.import.convert-to-excel') }}" method="POST"
                                                enctype="multipart/form-data" id="converterForm">
                                                @csrf
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="converter_exam_type" class="form-label">Exam
                                                                Type</label>
                                                            <select
                                                                class="form-select form-select-sm @error('exam_type') is-invalid @enderror"
                                                                id="converter_exam_type" name="exam_type" required>
                                                                @foreach ($examTypeOptions as $optionValue => $optionLabel)
                                                                    <option value="{{ $optionValue }}"
                                                                        {{ old('exam_type', $defaultExamType) == $optionValue ? 'selected' : '' }}>
                                                                        {{ $optionLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('exam_type')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="converter_exam_session" class="form-label">Exam
                                                                Session</label>
                                                            <input type="text"
                                                                class="form-control form-control-sm @error('exam_session') is-invalid @enderror"
                                                                id="converter_exam_session" name="exam_session"
                                                                placeholder="e.g., November 2024"
                                                                value="{{ old('exam_session') }}" required>
                                                            @error('exam_session')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label for="converter_exam_year" class="form-label">Exam
                                                                Year</label>
                                                            <input type="number"
                                                                class="form-control form-control-sm @error('exam_year') is-invalid @enderror"
                                                                id="converter_exam_year" name="exam_year" min="2020"
                                                                max="{{ date('Y') + 1 }}"
                                                                value="{{ old('exam_year', date('Y')) }}" required>
                                                            @error('exam_year')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- File Upload -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi bi-file-earmark-pdf me-2"></i>PDF File Upload
                                                    </label>
                                                    <div class="custom-file-input">
                                                        <input type="file" name="pdf_file" id="pdf_file"
                                                            accept=".pdf" required>
                                                        <label for="pdf_file" class="file-input-label"
                                                            id="pdfFileDropZone">
                                                            <div class="file-input-icon"
                                                                style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </div>
                                                            <div class="file-input-text">
                                                                <span class="file-label">Choose PDF File</span>
                                                                <span class="file-hint" id="pdfFileHint">.pdf format (up
                                                                    to 10MB)</span>
                                                                <span class="file-selected d-none"
                                                                    id="pdfFileName"></span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    @error('pdf_file')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="row">
                                                    <div class="col-md-6"></div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                onclick="resetConverterForm()">
                                                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                                            </button>
                                                            @can('view-system-admin')
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-sm btn-loading"
                                                                    id="convertBtn">
                                                                    <span class="btn-text"><i
                                                                            class="fas fa-file-excel me-2"></i>Convert to
                                                                        Excel</span>
                                                                    <span class="btn-spinner d-none">
                                                                        <span class="spinner-border spinner-border-sm me-2"
                                                                            role="status" aria-hidden="true"></span>
                                                                        Converting...
                                                                    </span>
                                                                </button>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Sidebar with Help -->
                                        <div class="col-lg-4">
                                            <!-- Instructions -->
                                            <div class="section-card card mb-4">
                                                <div class="card-header bg-light border-0">
                                                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>How to Use
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <ol class="small mb-0 ps-3">
                                                        <li class="mb-1">Confirm the exam type for your school</li>
                                                        <li class="mb-1">Enter the exam session and year</li>
                                                        <li class="mb-1">Upload the official PDF results file</li>
                                                        <li class="mb-1">Click "Convert to Excel"</li>
                                                        <li class="mb-1">Download and use in the Import tab</li>
                                                    </ol>
                                                </div>
                                            </div>

                                            <!-- Supported Formats -->
                                            <div class="section-card card mb-4">
                                                <div class="card-header bg-light border-0">
                                                    <h6 class="mb-0"><i
                                                            class="bi bi-file-earmark-check me-2"></i>Supported Formats
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <ul class="small mb-0 ps-3">
                                                        @foreach ($supportedFormats as $supportedFormat)
                                                            <li>{{ $supportedFormat }}</li>
                                                        @endforeach
                                                    </ul>
                                                    <div class="alert alert-info mt-3 mb-0 py-2 px-3">
                                                        <small><strong>Note:</strong> The converter automatically maps
                                                            subject codes/grades to the Excel import columns for your
                                                            school type.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sample Download -->
                                            <div class="section-card card">
                                                <div class="card-header bg-light border-0">
                                                    <h6 class="mb-0"><i class="bi bi-download me-2"></i>Sample Files
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3 text-center">
                                                    <p class="small mb-2">Download sample to understand the expected
                                                        format:</p>
                                                    <a href="{{ route('finals.import.download-sample', ['exam_type' => $defaultExamType]) }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download me-1"></i>Download Sample Excel
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($showSubjectMappingTab ?? false)
                            <div class="tab-pane" id="subject-mapping" role="tabpanel">
                                <div class="card">
                                    <div class="card-body">
                                        @if (!($mappingTableReady ?? false))
                                            <div class="alert alert-warning mb-3">
                                                Subject mapping is temporarily unavailable because the required mapping table has not been migrated yet.
                                                Please run migrations, then refresh this page.
                                            </div>
                                        @endif

                                        @if (empty($subjectMappingCatalog))
                                            <div class="alert alert-info mb-0">
                                                Subject mapping is currently available for Senior BGCSE imports.
                                            </div>
                                        @elseif (!($mappingTableReady ?? false))
                                            <div class="alert alert-light mb-0">
                                                Mapping catalog is ready, but saving mappings is disabled until migrations are applied.
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1"><i class="fas fa-link me-2"></i>Configure Senior Subject Mapping</h6>
                                                    <p class="text-muted mb-0">
                                                        Map BEC subject columns to your internal Senior subjects. This allows import even when your subject names differ.
                                                    </p>
                                                </div>
                                                <span class="badge bg-primary">{{ $mappingExamType }}</span>
                                            </div>

                                            <form method="POST" action="{{ route('finals.import.subject-mappings.store') }}" id="subjectMappingsForm">
                                                @csrf
                                                <input type="hidden" name="school_type" value="{{ $mappingSchoolType ?? \App\Models\SchoolSetup::TYPE_SENIOR }}">
                                                <input type="hidden" name="exam_type" value="{{ $mappingExamType }}">

                                                <div class="table-responsive">
                                                    <table class="table table-sm align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th style="min-width: 110px;">BEC Code</th>
                                                                <th style="min-width: 220px;">BEC Subject</th>
                                                                <th style="min-width: 190px;">Excel Column</th>
                                                                <th style="min-width: 260px;">Map To Internal Subject</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($subjectMappingCatalog as $item)
                                                                @php
                                                                    $mappedId = $existingSubjectMappings[$item['source_key']] ?? ($suggestedSubjectMappings[$item['source_key']] ?? null);
                                                                @endphp
                                                                <tr>
                                                                    <td><code>{{ $item['source_code'] }}</code></td>
                                                                    <td>{{ $item['source_label'] }}</td>
                                                                    <td><code>{{ $item['source_key'] }}</code></td>
                                                                    <td>
                                                                        <select class="form-select form-select-sm"
                                                                            name="mappings[{{ $item['source_key'] }}]">
                                                                            <option value="">Use Default Mapping</option>
                                                                            @foreach ($availableSubjects as $subject)
                                                                                <option value="{{ $subject->id }}"
                                                                                    {{ (string) $mappedId === (string) $subject->id ? 'selected' : '' }}>
                                                                                    {{ $subject->name }}{{ $subject->abbrev ? ' (' . $subject->abbrev . ')' : '' }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="d-flex justify-content-end mt-3">
                                                    <button type="submit" class="btn btn-primary btn-sm btn-loading" id="saveSubjectMappingsBtn">
                                                        <span class="btn-text"><i class="fas fa-save me-2"></i>Save Subject Mappings</span>
                                                        <span class="btn-spinner d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Saving...
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const gradingSystems = {
            'JCE': {
                grades: ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'],
                categories: {
                    high_achievement: {
                        label: 'MAB %',
                        grades: ['Merit', 'A', 'B']
                    },
                    pass_rate: {
                        label: 'MABC %',
                        grades: ['Merit', 'A', 'B', 'C']
                    },
                    non_failure: {
                        label: 'MABCD %',
                        grades: ['Merit', 'A', 'B', 'C', 'D']
                    }
                },
                defaults: {
                    high_achievement: 25.0,
                    pass_rate: 65.0,
                    non_failure: 85.0
                }
            },
            'BGCSE': {
                grades: ['A', 'B', 'C', 'D', 'E', 'U'],
                categories: {
                    high_achievement: {
                        label: 'AB %',
                        grades: ['A', 'B']
                    },
                    pass_rate: {
                        label: 'ABC %',
                        grades: ['A', 'B', 'C']
                    },
                    non_failure: {
                        label: 'ABCD %',
                        grades: ['A', 'B', 'C', 'D']
                    }
                },
                defaults: {
                    high_achievement: 30.0,
                    pass_rate: 70.0,
                    non_failure: 90.0
                }
            },
            'PSLE': {
                grades: ['A', 'B', 'C', 'D', 'E'],
                categories: {
                    high_achievement: {
                        label: 'AB %',
                        grades: ['A', 'B']
                    },
                    pass_rate: {
                        label: 'ABC %',
                        grades: ['A', 'B', 'C']
                    },
                    non_failure: {
                        label: 'ABCD %',
                        grades: ['A', 'B', 'C', 'D']
                    }
                },
                defaults: {
                    high_achievement: 35.0,
                    pass_rate: 75.0,
                    non_failure: 95.0
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const tabList = document.getElementById('examTabs');
            const tabs = tabList.querySelectorAll('.nav-link');
            const storageKey = 'examManagementActiveTab';

            function setActiveTab(tabId) {
                let matched = false;
                tabs.forEach(tab => {
                    if (tab.getAttribute('data-tab-id') === tabId) {
                        matched = true;
                        tab.classList.add('active');
                        document.querySelector(tab.getAttribute('href')).classList.add('active', 'show');
                    } else {
                        tab.classList.remove('active');
                        document.querySelector(tab.getAttribute('href')).classList.remove('active', 'show');
                    }
                });

                return matched;
            }

            const storedTabId = localStorage.getItem(storageKey);
            if (!storedTabId || !setActiveTab(storedTabId)) {
                setActiveTab(tabs[0].getAttribute('data-tab-id'));
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    const tabId = this.getAttribute('data-tab-id');
                    localStorage.setItem(storageKey, tabId);
                });
            });

            updateTargetLabels();
            loadExistingTargets();

            const targetsForm = document.getElementById('targetsForm');
            if (targetsForm) {
                targetsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitPerformanceTargets();
                });
            }

            document.querySelector('[data-tab-id="targets"]').addEventListener('shown.bs.tab', function() {
                document.getElementById('targetGuidelines').style.display = 'block';
            });

            document.querySelector('[data-tab-id="import"]').addEventListener('shown.bs.tab', function() {
                document.getElementById('targetGuidelines').style.display = 'none';
            });

            document.querySelector('[data-tab-id="conversion"]').addEventListener('shown.bs.tab', function() {
                document.getElementById('targetGuidelines').style.display = 'none';
            });

            const mappingTab = document.querySelector('[data-tab-id="subject-mapping"]');
            if (mappingTab) {
                mappingTab.addEventListener('shown.bs.tab', function() {
                    document.getElementById('targetGuidelines').style.display = 'none';
                });
            }

            initFileUpload();

            const subjectMappingsForm = document.getElementById('subjectMappingsForm');
            if (subjectMappingsForm) {
                subjectMappingsForm.addEventListener('submit', function() {
                    const saveBtn = document.getElementById('saveSubjectMappingsBtn');
                    if (saveBtn) {
                        saveBtn.classList.add('loading');
                        saveBtn.disabled = true;
                    }
                });
            }
        });

        function updateTargetLabels() {
            const examType = document.getElementById('exam_type_targets').value;

            if (!examType || !gradingSystems[examType]) return;

            const system = gradingSystems[examType];
            document.getElementById('highAchievementLabel').textContent = system.categories.high_achievement.label;
            document.getElementById('passRateLabel').textContent = system.categories.pass_rate.label;
            document.getElementById('nonFailureLabel').textContent = system.categories.non_failure.label;

            document.getElementById('highAchievementGrades').textContent = system.categories.high_achievement.grades.join(
                ' + ') + ' grades';
            document.getElementById('passRateGrades').textContent = system.categories.pass_rate.grades.join(' + ') +
                ' grades';
            document.getElementById('nonFailureGrades').textContent = system.categories.non_failure.grades.join(' + ') +
                ' grades';

            document.getElementById('highAchievementBadge').textContent = examType;
            document.getElementById('passRateBadge').textContent = examType;
            document.getElementById('nonFailureBadge').textContent = examType;

            document.getElementById('high_achievement_target').placeholder = system.defaults.high_achievement.toString();
            document.getElementById('pass_rate_target').placeholder = system.defaults.pass_rate.toString();
            document.getElementById('non_failure_target').placeholder = system.defaults.non_failure.toString();

            loadExistingTargets();
        }

        function loadRecommendedTargets() {
            const examType = document.getElementById('exam_type_targets').value;

            if (!examType || !gradingSystems[examType]) {
                alert('Please select an exam type first.');
                return;
            }

            const defaults = gradingSystems[examType].defaults;

            document.getElementById('high_achievement_target').value = defaults.high_achievement;
            document.getElementById('pass_rate_target').value = defaults.pass_rate;
            document.getElementById('non_failure_target').value = defaults.non_failure;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Recommended Targets Loaded',
                    text: `Loaded ${examType} recommended targets based on typical performance standards.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function resetTargetsForm() {
            document.getElementById('targetsForm').reset();
            document.getElementById('currentTargetsDisplay').style.display = 'none';
            updateTargetLabels();
        }

        function submitPerformanceTargets() {
            const form = document.getElementById('targetsForm');
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(data.message);
                        }

                        updateCurrentTargetsDisplay(data.data);
                    } else {
                        throw new Error(data.message || 'Failed to save targets');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to save performance targets',
                            icon: 'error'
                        });
                    } else {
                        alert(error.message || 'Failed to save performance targets');
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        function loadExistingTargets() {
            const year = document.getElementById('academic_year').value;
            const examType = document.getElementById('exam_type_targets').value;

            if (!year || !examType) {
                document.getElementById('currentTargetsDisplay').style.display = 'none';
                return;
            }

            fetch(`{{ route('finals.performance-targets.get-target') }}?academic_year=${year}&exam_type=${examType}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.exists) {
                        updateCurrentTargetsDisplay(data.data);
                    } else {
                        document.getElementById('currentTargetsDisplay').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading existing targets:', error);
                    document.getElementById('currentTargetsDisplay').style.display = 'none';
                });
        }

        function updateCurrentTargetsDisplay(data) {
            document.getElementById('lastUpdated').textContent = data.updated_at;

            const valuesContainer = document.getElementById('currentTargetsValues');
            valuesContainer.innerHTML = `
            <div class="col-md-4">
                <strong>${data.high_achievement_label}:</strong> ${data.high_achievement_target}%
            </div>
            <div class="col-md-4">
                <strong>${data.pass_rate_label}:</strong> ${data.pass_rate_target}%
            </div>
            <div class="col-md-4">
                <strong>${data.non_failure_label}:</strong> ${data.non_failure_target}%
            </div>
        `;

            document.getElementById('currentTargetsDisplay').style.display = 'block';
            window.currentTargetsData = data;
        }

        function loadCurrentValues() {
            if (window.currentTargetsData) {
                const data = window.currentTargetsData;
                document.getElementById('high_achievement_target').value = data.high_achievement_target;
                document.getElementById('pass_rate_target').value = data.pass_rate_target;
                document.getElementById('non_failure_target').value = data.non_failure_target;
                document.getElementById('targets_notes').value = data.notes || '';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Current Targets Loaded',
                        text: 'Current targets have been loaded into the form.',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'No Current Targets',
                        text: 'No current targets found to load.',
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
        }

        function initFileUpload() {
            const fileInput = document.getElementById('exam_file');
            const fileHint = document.getElementById('fileHint');
            const fileName = document.getElementById('fileName');
            const examForm = document.getElementById('externalExamForm');
            const examImportBtn = document.getElementById('examImportBtn');

            if (!fileInput) return;

            fileInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    handleFileSelect(e.target.files[0]);
                } else {
                    fileHint.classList.remove('d-none');
                    fileName.classList.add('d-none');
                    fileName.textContent = '';
                }
            });

            function handleFileSelect(file) {
                const name = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileExtension = name.split('.').pop().toLowerCase();

                if (!['xlsx', 'xls'].includes(fileExtension)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Invalid File Type',
                            text: 'Please select an Excel file (.xlsx or .xls)',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Please select an Excel file (.xlsx or .xls)');
                    }
                    fileInput.value = '';
                    fileHint.classList.remove('d-none');
                    fileName.classList.add('d-none');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'File Too Large',
                            text: 'File size must be less than 10MB.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('File size must be less than 10MB.');
                    }
                    fileInput.value = '';
                    fileHint.classList.remove('d-none');
                    fileName.classList.add('d-none');
                    return;
                }

                fileHint.classList.add('d-none');
                fileName.classList.remove('d-none');
                fileName.textContent = `${name} (${fileSize} MB)`;
                updateProgress(1);
            }

            examForm.addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'File Required',
                            text: 'Please select an Excel file to upload.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Please select an Excel file to upload.');
                    }
                    return false;
                }

                // Confirm all students are on the list
                if (!confirm('Confirm that all the students are on the list please')) {
                    e.preventDefault();
                    return false;
                }

                const cleanupChecked = document.getElementById('cleanup_before_import').checked;
                if (cleanupChecked) {
                    if (!confirm(
                            '⚠️ This will permanently delete ALL existing external exam data!\n\nAre you sure you want to proceed?'
                            )) {
                        e.preventDefault();
                        return false;
                    }
                }

                examImportBtn.classList.add('loading');
                examImportBtn.disabled = true;
                updateProgress(2);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Processing Import',
                        text: 'Please wait while we process your external exam results...',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });
        }

        function resetForm() {
            document.getElementById('externalExamForm').reset();
            const fileHint = document.getElementById('fileHint');
            const fileName = document.getElementById('fileName');
            const examImportBtn = document.getElementById('examImportBtn');

            fileHint.classList.remove('d-none');
            fileName.classList.add('d-none');
            fileName.textContent = '';

            examImportBtn.classList.remove('loading');
            examImportBtn.disabled = false;
            updateProgress(0);
        }

        function updateProgress(step) {
            const steps = document.querySelectorAll('.step-indicator');
            const progressBars = document.querySelectorAll('.progress-bar');

            steps.forEach((stepEl, index) => {
                if (index < step) {
                    stepEl.className = 'step-indicator step-complete';
                    stepEl.innerHTML = '<i class="bi bi-check"></i>';
                } else if (index === step) {
                    stepEl.className = 'step-indicator step-active';
                    if (step === 0) stepEl.textContent = '1';
                    if (step === 1) stepEl.innerHTML = '<i class="bi bi-upload"></i>';
                    if (step === 2) stepEl.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                }
            });

            progressBars.forEach((bar, index) => {
                if (index < step) {
                    bar.style.width = '100%';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // PDF file input handling for converter
            const pdfFileInput = document.getElementById('pdf_file');
            const pdfFileHint = document.getElementById('pdfFileHint');
            const pdfFileName = document.getElementById('pdfFileName');

            if (pdfFileInput) {
                pdfFileInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        handlePdfFileSelect(e.target.files[0]);
                    } else {
                        pdfFileHint.classList.remove('d-none');
                        pdfFileName.classList.add('d-none');
                        pdfFileName.textContent = '';
                    }
                });
            }

            function handlePdfFileSelect(file) {
                const name = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileExtension = name.split('.').pop().toLowerCase();

                if (fileExtension !== 'pdf') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Invalid File Type',
                            text: 'Please select a PDF file (.pdf)',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Please select a PDF file (.pdf)');
                    }
                    pdfFileInput.value = '';
                    pdfFileHint.classList.remove('d-none');
                    pdfFileName.classList.add('d-none');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'File Too Large',
                            text: 'File size must be less than 10MB.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('File size must be less than 10MB.');
                    }
                    pdfFileInput.value = '';
                    pdfFileHint.classList.remove('d-none');
                    pdfFileName.classList.add('d-none');
                    return;
                }

                pdfFileHint.classList.add('d-none');
                pdfFileName.classList.remove('d-none');
                pdfFileName.textContent = `${name} (${fileSize} MB)`;
            }

            // Converter form button loading
            const converterForm = document.getElementById('converterForm');
            if (converterForm) {
                converterForm.addEventListener('submit', function() {
                    const btn = document.getElementById('convertBtn');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;

                        // Reset button after timeout since file download doesn't reload the page
                        setTimeout(function() {
                            btn.classList.remove('loading');
                            btn.disabled = false;
                        }, 5000);
                    }
                });
            }
        });

        function resetConverterForm() {
            document.getElementById('converterForm').reset();
            const pdfFileHint = document.getElementById('pdfFileHint');
            const pdfFileName = document.getElementById('pdfFileName');
            const convertBtn = document.getElementById('convertBtn');

            pdfFileHint.classList.remove('d-none');
            pdfFileName.classList.add('d-none');
            pdfFileName.textContent = '';

            convertBtn.classList.remove('loading');
            convertBtn.disabled = false;
        }
    </script>
@endsection

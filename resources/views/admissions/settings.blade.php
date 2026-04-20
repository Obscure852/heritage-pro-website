@extends('layouts.master')
@section('title')
    F4 Admissions Import
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
        }

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

        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
            padding: 0 24px;
            background: #f9fafb;
        }

        .nav-tabs-custom .nav-item {
            margin-bottom: -2px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
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
            font-weight: 600;
        }

        .nav-tabs-custom .nav-link i {
            font-size: 15px;
        }

        .tab-content {
            padding: 24px;
        }

        /* Workflow Stepper */
        .workflow-stepper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            padding: 24px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            border: 1px solid #e0e7ff;
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }

        .workflow-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .workflow-step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
        }

        .workflow-step-label {
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
            text-align: center;
            line-height: 1.3;
            max-width: 90px;
        }

        .workflow-step-connector {
            position: absolute;
            top: 43px;
            left: calc(50% + 24px);
            right: calc(-50% + 24px);
            height: 2px;
            background: linear-gradient(90deg, #93c5fd, #a5b4fc);
            z-index: 0;
        }

        .workflow-step:last-child .workflow-step-connector {
            display: none;
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

        /* Enhanced File Upload Dropzone */
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
            gap: 16px;
            padding: 24px 20px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .file-input-label.has-file {
            border-color: #10b981;
            border-style: solid;
            background: #ecfdf5;
        }

        .file-input-label.drag-over {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .file-input-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .file-input-label.has-file .file-input-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .title {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .file-input-text .subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .file-name {
            font-size: 12px;
            color: #10b981;
            margin-top: 4px;
            font-weight: 500;
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

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-spinner {
            display: none;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex;
            align-items: center;
        }

        .format-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .format-card-header {
            background: #f9fafb;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .format-card-body {
            padding: 16px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 11px;
            padding: 10px 8px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 8px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
        }

        .guidelines-card {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 16px;
        }

        .guidelines-card h6 {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .guidelines-card ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .guidelines-card li {
            color: #92400e;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .info-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 16px;
        }

        .info-card h6 {
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-card p,
        .info-card small {
            color: #1e40af;
        }

        .warning-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 12px;
        }

        .warning-card .warning-title {
            color: #991b1b;
            font-weight: 600;
            font-size: 13px;
        }

        .warning-card .warning-text {
            color: #991b1b;
            font-size: 12px;
        }

        /* Criteria Pathway Cards */
        .criteria-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 20px;
            border-left: 4px solid #e5e7eb;
            overflow: hidden;
        }

        .criteria-card.pathway-triple { border-left-color: var(--pathway-triple); }
        .criteria-card.pathway-double { border-left-color: var(--pathway-double); }
        .criteria-card.pathway-single { border-left-color: var(--pathway-single); }

        .criteria-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .criteria-card.pathway-triple .criteria-card-header { background: var(--pathway-triple-bg); }
        .criteria-card.pathway-double .criteria-card-header { background: var(--pathway-double-bg); }
        .criteria-card.pathway-single .criteria-card-header { background: var(--pathway-single-bg); }

        .criteria-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .criteria-card-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .criteria-pathway-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }

        .criteria-card.pathway-triple .criteria-pathway-badge { background: var(--pathway-triple); }
        .criteria-card.pathway-double .criteria-pathway-badge { background: var(--pathway-double); }
        .criteria-card.pathway-single .criteria-pathway-badge { background: var(--pathway-single); }

        .fallback-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .criteria-card-body {
            padding: 20px;
        }

        .criteria-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .criteria-card-footer {
            padding: 14px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* Target Summary Progress Cards */
        .target-progress-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px 20px;
            margin-bottom: 12px;
            background: white;
        }

        .target-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .target-progress-label {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .target-progress-values {
            font-size: 13px;
            color: #6b7280;
        }

        .target-progress-values strong {
            color: #111827;
        }

        .target-progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .target-progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.4s ease;
        }

        .target-progress-bar-fill.bar-triple { background: var(--pathway-triple); }
        .target-progress-bar-fill.bar-double { background: var(--pathway-double); }
        .target-progress-bar-fill.bar-single { background: var(--pathway-single); }
        .target-progress-bar-fill.bar-over { background: #f59e0b; }

        .target-diff-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .target-diff-badge.positive { background: #dcfce7; color: #166534; }
        .target-diff-badge.negative { background: #fee2e2; color: #991b1b; }
        .target-diff-badge.zero { background: #f1f5f9; color: #64748b; }

        /* How It Works Tab */
        .guide-section {
            margin-bottom: 28px;
        }

        .guide-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guide-section-title .guide-step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .guide-text {
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        .guide-text strong {
            color: #111827;
        }

        .guide-example {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            padding: 14px 16px;
            margin: 12px 0;
            font-size: 13px;
            color: #334155;
            line-height: 1.6;
        }

        .guide-example .example-title {
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .guide-pathway-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .guide-pathway-header {
            padding: 10px 16px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guide-pathway-body {
            padding: 12px 16px;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
        }

        .guide-pathway-body ul {
            margin: 6px 0 0 0;
            padding-left: 18px;
        }

        .guide-pathway-body li {
            margin-bottom: 4px;
        }

        .guide-pathway-card.gp-triple .guide-pathway-header { background: var(--pathway-triple-bg); color: var(--pathway-triple); }
        .guide-pathway-card.gp-double .guide-pathway-header { background: var(--pathway-double-bg); color: var(--pathway-double); }
        .guide-pathway-card.gp-single .guide-pathway-header { background: var(--pathway-single-bg); color: var(--pathway-single); }
        .guide-pathway-card.gp-unclassified .guide-pathway-header { background: #f1f5f9; color: #64748b; }

        .guide-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin: 12px 0;
        }

        .guide-table th {
            background: #f8fafc;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .guide-table td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            color: #4b5563;
        }

        .guide-callout {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            margin: 12px 0;
        }

        .guide-callout.callout-info {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1e40af;
        }

        .guide-callout.callout-success {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #065f46;
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

            .workflow-stepper {
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
            }

            .workflow-step-connector {
                display: none;
            }

            .criteria-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Admissions
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @php
        $sampleRow = [
            'connect_id' => '987765',
            'firstname' => 'AARON',
            'lastname' => 'ANDILE KGOSI',
            'gender' => 'M',
            'nationality' => 'Motswana',
            'date_of_birth' => '15/06/2009',
            'status' => 'Current',
            'grade' => 'D',
            'grade_applying_for' => 'F4',
            'english' => 'D',
            'setswana' => 'E',
            'science' => 'D',
            'mathematics' => 'E',
            'agriculture' => '',
            'social_studies' => 'D',
            'moral_education' => '',
            'design_and_technology' => '',
            'home_economics' => '',
            'office_procedures' => '',
            'accounting' => '',
            'french' => '',
            'art' => '',
            'music' => '',
            'physical_education' => '',
            'religious_education' => '',
            'private_agriculture' => '',
        ];
        $placementCriteriaMap = collect($placementCriteria ?? [])->keyBy('pathway');
        $placementGradeOptions = array_keys(\App\Services\SeniorAdmissionPlacementService::GRADE_ORDER);
        $pathwayRows = [
            'triple' => ['label' => 'Triple Science', 'class_type' => \App\Models\Klass::TYPE_TRIPLE_AWARD],
            'double' => ['label' => 'Double Science', 'class_type' => \App\Models\Klass::TYPE_DOUBLE_AWARD],
            'single' => ['label' => 'Single Science Award', 'class_type' => \App\Models\Klass::TYPE_SINGLE_AWARD],
        ];
    @endphp

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
                    <i class="mdi mdi-alert label-icon"></i><strong>{{ session('warning') }}</strong>
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

    <div class="settings-container">
        <div class="settings-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3><i class="fas fa-file-import me-2"></i>Admissions Import Hub</h3>
                    <p>Import F4 admissions with F3 junior-school subject grades into the selected term</p>
                </div>
                <div>
                    <a href="{{ route('admissions.placement', ['term_id' => old('term_id', $currentTerm?->id)]) }}" class="btn btn-secondary">
                        <i class="fas fa-layer-group me-1"></i> Placement Recommendations
                    </a>
                </div>
            </div>
        </div>

        <div class="settings-body">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist" id="importTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#seniorAdmissionsImport" role="tab">
                        <i class="fas fa-user-plus me-2"></i>
                        <span>F4 Admissions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#placementCriteria" role="tab">
                        <i class="fas fa-flask me-2"></i>
                        <span>Placement Criteria</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#templateGuide" role="tab">
                        <i class="fas fa-table me-2"></i>
                        <span>Template Guide</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#howItWorks" role="tab">
                        <i class="fas fa-question-circle me-2"></i>
                        <span>How Placement Works</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="seniorAdmissionsImport" role="tabpanel">
                    {{-- Workflow Stepper --}}
                    <div class="workflow-stepper">
                        @php
                            $steps = [
                                ['num' => 1, 'label' => 'Select Term'],
                                ['num' => 2, 'label' => 'Prepare Workbook'],
                                ['num' => 3, 'label' => 'Verify Connect IDs'],
                                ['num' => 4, 'label' => 'Clear Existing (Optional)'],
                                ['num' => 5, 'label' => 'Upload & Import'],
                                ['num' => 6, 'label' => 'Review Results'],
                            ];
                        @endphp
                        @foreach ($steps as $step)
                            <div class="workflow-step">
                                <div class="workflow-step-circle">{{ $step['num'] }}</div>
                                <div class="workflow-step-label">{{ $step['label'] }}</div>
                                @if (!$loop->last)
                                    <div class="workflow-step-connector"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-upload me-2"></i>Import F4 Admissions</h6>
                                <form action="{{ route('admissions.import-senior') }}" method="POST" enctype="multipart/form-data" id="seniorAdmissionsForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="term_id" class="form-label">Select Term <span class="text-danger">*</span></label>
                                        <select class="form-select @error('term_id') is-invalid @enderror" id="term_id" name="term_id" required>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}" {{ (string) old('term_id', $currentTerm?->id) === (string) $term->id ? 'selected' : '' }}>
                                                    Term {{ $term->term }}, {{ $term->year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                        <div class="custom-file-input">
                                            <input type="file" name="file" id="senior_admissions_upload_file" accept=".xlsx,.xls,.csv" required>
                                            <label for="senior_admissions_upload_file" class="file-input-label" id="seniorAdmissionsFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click or drag file to upload</div>
                                                    <div class="subtitle">.xlsx, .xls or .csv (max 10MB)</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="file-name" id="seniorAdmissionsFileName"></div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="warning-card">
                                            <div class="form-check">
                                                <input id="deleteExistingTermAdmissions" class="form-check-input" name="delete_existing_term_admissions" type="checkbox" value="1" {{ old('delete_existing_term_admissions') ? 'checked' : '' }}>
                                                <label class="form-check-label warning-title" for="deleteExistingTermAdmissions">
                                                    Delete existing admissions for the selected term first
                                                </label>
                                            </div>
                                            <div class="warning-text mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This permanently removes admissions in the chosen term only before the import runs.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary btn-loading" id="seniorAdmissionsBtn">
                                            <span class="btn-text"><i class="fas fa-upload me-1"></i> Import F4 Admissions</span>
                                            <span class="btn-spinner">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                Processing...
                                            </span>
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Import Guidelines</h6>
                                <h6 class="text-success mb-2" style="font-size: 13px;"><i class="fas fa-check me-1"></i>Key Requirements:</h6>
                                <ul class="list-unstyled mb-3" style="font-size: 13px;">
                                    <li class="mb-1"><strong>Grade applying for:</strong> Must be <code>F4</code> on every row</li>
                                    <li class="mb-1"><strong>Connect ID:</strong> Stored on the admission and used later when sponsors are added</li>
                                    <li class="mb-1"><strong>Status and nationality:</strong> Imported directly into the admission record</li>
                                    <li class="mb-1"><strong>Junior grades:</strong> Stored separately as F3 results, not in the legacy admissions academic fields</li>
                                    <li class="mb-1"><strong>Duplicates:</strong> Same student in the same selected term is skipped and reported</li>
                                </ul>

                                <div class="info-card mb-3">
                                    <h6><i class="fas fa-lightbulb me-1"></i>Selected Term Behavior</h6>
                                    <small>Every imported student is attached to the term chosen above. The selected term controls both <code>term_id</code> and the admission year.</small>
                                </div>

                                <div class="guidelines-card">
                                    <h6><i class="fas fa-clipboard-check me-1"></i>Accepted Values</h6>
                                    <ul>
                                        <li>Overall grade: <code>A</code>, <code>B</code>, <code>C</code>, <code>D</code>, <code>M</code></li>
                                        <li>Subject grades: <code>A</code>, <code>B</code>, <code>C</code>, <code>D</code>, <code>E</code>, <code>U</code></li>
                                        <li>Gender: <code>M</code> or <code>F</code></li>
                                        <li>Date format: Excel date or <code>DD/MM/YYYY</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="placementCriteria" role="tabpanel">
                    <div class="form-section" style="background: white;">
                        <h6 class="form-section-title"><i class="fas fa-sliders-h me-2"></i>Science Placement Criteria</h6>
                        <form action="{{ route('admissions.store-placement-criteria') }}" method="POST">
                            @csrf
                            <input type="hidden" name="summary_term_id" value="{{ $summaryTermId }}">

                            {{-- Criteria Cards --}}
                            @foreach ($pathwayRows as $pathway => $meta)
                                @php
                                    $row = $placementCriteriaMap->get($pathway, []);
                                    $isFallbackPathway = $pathway === 'single';
                                @endphp
                                <div class="criteria-card pathway-{{ $pathway }}">
                                    <div class="criteria-card-header">
                                        <div>
                                            <div class="criteria-card-title">{{ $meta['label'] }}</div>
                                            <div class="criteria-card-subtitle">Class Type: {{ $meta['class_type'] }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($isFallbackPathway)
                                                <span class="fallback-badge"><i class="fas fa-shield-alt"></i> Fallback Pathway</span>
                                            @endif
                                            <span class="criteria-pathway-badge">{{ $meta['label'] }}</span>
                                        </div>
                                    </div>
                                    <div class="criteria-card-body">
                                        @if ($isFallbackPathway)
                                            <div class="text-muted mb-3" style="font-size: 13px;">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Single Science Award is the fallback pathway. Students who do not meet Triple or Double Science criteria are automatically placed here. No grade range configuration is needed.
                                            </div>
                                            <input type="hidden" name="criteria[{{ $pathway }}][science_best_grade]" value="">
                                            <input type="hidden" name="criteria[{{ $pathway }}][science_worst_grade]" value="">
                                            <input type="hidden" name="criteria[{{ $pathway }}][mathematics_best_grade]" value="">
                                            <input type="hidden" name="criteria[{{ $pathway }}][mathematics_worst_grade]" value="">
                                        @else
                                            <div class="criteria-grid">
                                                <div>
                                                    <label class="form-label">Science Best Grade</label>
                                                    <select class="form-select @error("criteria.{$pathway}.science_best_grade") is-invalid @enderror" name="criteria[{{ $pathway }}][science_best_grade]">
                                                        <option value="">Select</option>
                                                        @foreach ($placementGradeOptions as $grade)
                                                            <option value="{{ $grade }}" {{ old("criteria.{$pathway}.science_best_grade", data_get($row, 'science_best_grade')) === $grade ? 'selected' : '' }}>
                                                                {{ $grade }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("criteria.{$pathway}.science_best_grade")
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="form-label">Science Worst Grade</label>
                                                    <select class="form-select @error("criteria.{$pathway}.science_worst_grade") is-invalid @enderror" name="criteria[{{ $pathway }}][science_worst_grade]">
                                                        <option value="">Select</option>
                                                        @foreach ($placementGradeOptions as $grade)
                                                            <option value="{{ $grade }}" {{ old("criteria.{$pathway}.science_worst_grade", data_get($row, 'science_worst_grade')) === $grade ? 'selected' : '' }}>
                                                                {{ $grade }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("criteria.{$pathway}.science_worst_grade")
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="form-label">Mathematics Best Grade</label>
                                                    <select class="form-select @error("criteria.{$pathway}.mathematics_best_grade") is-invalid @enderror" name="criteria[{{ $pathway }}][mathematics_best_grade]">
                                                        <option value="">Select</option>
                                                        @foreach ($placementGradeOptions as $grade)
                                                            <option value="{{ $grade }}" {{ old("criteria.{$pathway}.mathematics_best_grade", data_get($row, 'mathematics_best_grade')) === $grade ? 'selected' : '' }}>
                                                                {{ $grade }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("criteria.{$pathway}.mathematics_best_grade")
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="form-label">Mathematics Worst Grade</label>
                                                    <select class="form-select @error("criteria.{$pathway}.mathematics_worst_grade") is-invalid @enderror" name="criteria[{{ $pathway }}][mathematics_worst_grade]">
                                                        <option value="">Select</option>
                                                        @foreach ($placementGradeOptions as $grade)
                                                            <option value="{{ $grade }}" {{ old("criteria.{$pathway}.mathematics_worst_grade", data_get($row, 'mathematics_worst_grade')) === $grade ? 'selected' : '' }}>
                                                                {{ $grade }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("criteria.{$pathway}.mathematics_worst_grade")
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Science Ceiling & Promotion --}}
                                        <div class="criteria-grid mt-3">
                                            <div>
                                                <label class="form-label">Science Ceiling Grade</label>
                                                <select class="form-select ceiling-grade-select @error("criteria.{$pathway}.science_ceiling_grade") is-invalid @enderror" name="criteria[{{ $pathway }}][science_ceiling_grade]" data-pathway="{{ $pathway }}">
                                                    <option value="">None</option>
                                                    @foreach ($placementGradeOptions as $grade)
                                                        <option value="{{ $grade }}" {{ old("criteria.{$pathway}.science_ceiling_grade", data_get($row, 'science_ceiling_grade')) === $grade ? 'selected' : '' }}>
                                                            {{ $grade }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Students with Science better than this get promoted. Leave empty for no ceiling.</small>
                                                @error("criteria.{$pathway}.science_ceiling_grade")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="promotion-pathway-wrapper" data-pathway="{{ $pathway }}" style="{{ old("criteria.{$pathway}.science_ceiling_grade", data_get($row, 'science_ceiling_grade')) ? '' : 'display:none;' }}">
                                                <label class="form-label">Promote To</label>
                                                <select class="form-select @error("criteria.{$pathway}.promotion_pathway") is-invalid @enderror" name="criteria[{{ $pathway }}][promotion_pathway]">
                                                    <option value="">None</option>
                                                    @foreach ($pathwayRows as $pw => $pwMeta)
                                                        @if ($pw !== $pathway)
                                                            <option value="{{ $pw }}" {{ old("criteria.{$pathway}.promotion_pathway", data_get($row, 'promotion_pathway')) === $pw ? 'selected' : '' }}>
                                                                {{ $pwMeta['label'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Where promoted students go.</small>
                                                @error("criteria.{$pathway}.promotion_pathway")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="criteria-card-footer">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label mb-0" style="font-size: 13px;">Target Count:</label>
                                            <input type="number" min="0" class="form-control @error("criteria.{$pathway}.target_count") is-invalid @enderror" name="criteria[{{ $pathway }}][target_count]" value="{{ old("criteria.{$pathway}.target_count", data_get($row, 'target_count', 0)) }}" style="width: 100px;">
                                            @error("criteria.{$pathway}.target_count")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $isChecked = old("criteria.{$pathway}.is_active", data_get($row, 'is_active', true));
                                            @endphp
                                            <input type="checkbox" class="form-check-input" name="criteria[{{ $pathway }}][is_active]" value="1" {{ $isChecked ? 'checked' : '' }} id="active_{{ $pathway }}">
                                            <label class="form-label mb-0" style="font-size: 13px;" for="active_{{ $pathway }}">Active</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Save Criteria</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>

                        <div class="d-flex justify-content-end mt-3">
                            <form action="{{ route('admissions.reset-placement-criteria') }}" method="POST" onsubmit="return confirm('Reset the placement criteria to the default science placement rules?');">
                                @csrf
                                <input type="hidden" name="summary_term_id" value="{{ $summaryTermId }}">
                                <button type="submit" class="btn btn-secondary btn-loading">
                                    <span class="btn-text"><i class="fas fa-undo me-1"></i> Reset To Defaults</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Resetting...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="form-section" style="background: white;">
                        <h6 class="form-section-title"><i class="fas fa-chart-bar me-2"></i>Target Summary</h6>
                        <form action="{{ route('admissions.settings') }}" method="GET" class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label for="summary_term_id" class="form-label">Summary Term</label>
                                <select class="form-select" id="summary_term_id" name="summary_term_id">
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" {{ (string) $summaryTermId === (string) $term->id ? 'selected' : '' }}>
                                            Term {{ $term->term }}, {{ $term->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-secondary btn-loading">
                                    <span class="btn-text"><i class="fas fa-sync-alt me-1"></i> Refresh Summary</span>
                                    <span class="btn-spinner">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Loading...
                                    </span>
                                </button>
                            </div>
                        </form>

                        {{-- Target Progress Cards --}}
                        @forelse ($placementSummary as $summaryRow)
                            @php
                                $target = $summaryRow['target_count'];
                                $current = $summaryRow['current_count'];
                                $diff = $summaryRow['difference'];
                                $pct = $target ? min(100, round(($current / $target) * 100)) : 0;
                                $isOver = $target && $current > $target;
                                $pathwayKey = strtolower(str_replace(' ', '', $summaryRow['label']));
                                $barClass = 'bar-single';
                                if (str_contains(strtolower($summaryRow['label']), 'triple')) $barClass = 'bar-triple';
                                elseif (str_contains(strtolower($summaryRow['label']), 'double')) $barClass = 'bar-double';
                                if ($isOver) $barClass = 'bar-over';
                            @endphp
                            <div class="target-progress-card">
                                <div class="target-progress-header">
                                    <span class="target-progress-label">{{ $summaryRow['label'] }}</span>
                                    <div class="target-progress-values">
                                        <strong>{{ $current }}</strong> / {{ $target ?? 'N/A' }} students
                                        @if (!is_null($diff))
                                            <span class="target-diff-badge {{ $diff > 0 ? 'positive' : ($diff < 0 ? 'negative' : 'zero') }}">
                                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($target)
                                    <div class="target-progress-bar">
                                        <div class="target-progress-bar-fill {{ $barClass }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">No summary data available for the selected term.</div>
                        @endforelse
                    </div>
                </div>

                <div class="tab-pane" id="templateGuide" role="tabpanel">
                    <div class="format-card">
                        <div class="format-card-header">
                            <i class="fas fa-table me-2"></i>Expected Data Format
                        </div>
                        <div class="format-card-body">
                            <p class="text-muted mb-3" style="font-size: 13px;">Your Excel file should include these exact headers in the same row heading format:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            @foreach ($templateHeaders as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach ($templateHeaders as $header)
                                                <td>{{ $sampleRow[$header] }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                Required fields: <code>connect_id</code>, <code>firstname</code>, <code>lastname</code>, <code>gender</code>, <code>nationality</code>, <code>date_of_birth</code>, <code>status</code>, <code>grade</code>, <code>grade_applying_for</code>.
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-database me-2"></i>Import Output</h6>
                                <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                    <li class="mb-2"><strong>Admissions table:</strong> student identity, term, status, nationality, and generated import ID number</li>
                                    <li class="mb-2"><strong>Senior results table:</strong> overall grade and all F3 junior-school subject grades</li>
                                    <li class="mb-2"><strong>Application date:</strong> set to the import date</li>
                                    <li class="mb-0"><strong>Phone:</strong> kept null for imported admissions</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-shield-alt me-2"></i>Safety Checks</h6>
                                <div class="warning-card mb-3">
                                    <div class="warning-title">Delete before import is term-scoped</div>
                                    <div class="warning-text mt-1">Only admissions in the selected term are cleared. Other terms are left untouched.</div>
                                </div>
                                <div class="info-card">
                                    <h6><i class="fas fa-eye me-1"></i>Admissions View</h6>
                                    <small>Imported F3 results appear directly in the Senior admissions <code>Academic Information</code> tab and now drive science-placement recommendations.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- How Placement Works Tab --}}
                <div class="tab-pane" id="howItWorks" role="tabpanel">

                    {{-- 1. Overview --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">1</span> What Is Placement?
                        </div>
                        <div class="guide-text">
                            Placement is the process of sorting incoming F4 admissions into science pathway classes based on their F3 junior-school grades. The system reads each student's <strong>Science</strong> and <strong>Mathematics</strong> grades, compares them against your configured criteria, and recommends a pathway: <strong>Triple Science</strong>, <strong>Double Science</strong>, or <strong>Single Science Award</strong>. If a student does not have a Science grade, the system automatically uses their <strong>Private Agriculture</strong> grade as a substitute. Once recommended, you allocate students into actual F4 classes that match their pathway.
                        </div>
                        <div class="guide-text">
                            Placement does <strong>not</strong> assign optional science subjects (Biology, Chemistry, Physics). Those are handled separately through the Academics Manager &rarr; Optional Subjects after students are placed into their base classes.
                        </div>
                    </div>

                    {{-- 2. End-to-End Workflow --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">2</span> End-to-End Workflow
                        </div>
                        <div class="guide-text">
                            The placement process follows these steps in order. Every step must be completed before the next one will work correctly.
                        </div>
                        <table class="guide-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">Step</th>
                                    <th style="width: 160px;">Action</th>
                                    <th>Where</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1</strong></td>
                                    <td>Set placement criteria</td>
                                    <td>Settings &rarr; Placement Criteria tab</td>
                                    <td>Define the Science and Mathematics grade bands for Triple and Double pathways. Set target counts for each pathway. Single Science has no grade band &mdash; it receives everyone who does not match Triple or Double.</td>
                                </tr>
                                <tr>
                                    <td><strong>2</strong></td>
                                    <td>Import F4 admissions</td>
                                    <td>Settings &rarr; F4 Admissions tab</td>
                                    <td>Upload the Excel workbook with student identity data and F3 junior-school subject grades (Science, Mathematics, Overall, and all other subjects). Each row must have <code>grade_applying_for = F4</code>.</td>
                                </tr>
                                <tr>
                                    <td><strong>3</strong></td>
                                    <td>Create F4 classes</td>
                                    <td>Academics Manager &rarr; Class Allocations</td>
                                    <td>Create F4 classes for the same term and assign each class a <strong>type</strong>: Triple Award, Double Award, or Single Award. The placement page uses these types to match students to the correct classes. Set a <strong>max students</strong> value on each class if you want a planning threshold and warning indicator.</td>
                                </tr>
                                <tr>
                                    <td><strong>4</strong></td>
                                    <td>Review recommendations</td>
                                    <td>Placement Recommendations page</td>
                                    <td>Open the Placement Recommendations page for the term. Students are grouped by pathway, ranked by Science &rarr; Mathematics &rarr; Overall grade, and pre-selected up to the target count. Review the groupings and adjust selections if needed.</td>
                                </tr>
                                <tr>
                                    <td><strong>5</strong></td>
                                    <td>Allocate students</td>
                                    <td>Placement Recommendations page</td>
                                    <td>Click "Allocate" on each pathway section to enroll selected students into classes. Leave rows on Auto-assign to distribute them across matching class types, or use Choose Class to override individual students into any F4 class for the term.</td>
                                </tr>
                                <tr>
                                    <td><strong>6</strong></td>
                                    <td>Assign optional subjects</td>
                                    <td>Academics Manager &rarr; Optional Subjects</td>
                                    <td>After placement, assign Biology, Chemistry, and Physics to students through the Academics Manager &rarr; Optional Subjects. This is a separate step from base-class placement.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- 3. The Three Pathways --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">3</span> The Three Science Pathways
                        </div>
                        <div class="guide-text">
                            Every student with complete Science and Mathematics grades is placed into exactly one of three pathways. The pathways are evaluated in priority order: Triple first, then Double, then Single as fallback.
                        </div>

                        <div class="guide-pathway-card gp-triple">
                            <div class="guide-pathway-header">
                                <i class="fas fa-flask"></i> Triple Science
                            </div>
                            <div class="guide-pathway-body">
                                <strong>Priority 1 (checked first).</strong> For the strongest science students. By default, requires Science grade A&ndash;B <strong>and</strong> Mathematics grade A&ndash;B. Students take all three optional science subjects (Biology, Chemistry, Physics).
                                <ul>
                                    <li><strong>Class type:</strong> Triple Award</li>
                                    <li><strong>Default criteria:</strong> Science A&ndash;B, Mathematics A&ndash;B</li>
                                    <li><strong>Configurable:</strong> Yes &mdash; change the grade bands on the Placement Criteria tab</li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-pathway-card gp-double">
                            <div class="guide-pathway-header">
                                <i class="fas fa-vials"></i> Double Science
                            </div>
                            <div class="guide-pathway-body">
                                <strong>Priority 2 (checked after Triple).</strong> For students who are strong in science but did not meet Triple criteria. By default, requires Science grade C&ndash;D <strong>and</strong> Mathematics grade C&ndash;D. Students take two optional science subjects.
                                <ul>
                                    <li><strong>Class type:</strong> Double Award</li>
                                    <li><strong>Default criteria:</strong> Science C&ndash;D, Mathematics C&ndash;D</li>
                                    <li><strong>Configurable:</strong> Yes &mdash; change the grade bands on the Placement Criteria tab</li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-pathway-card gp-single">
                            <div class="guide-pathway-header">
                                <i class="fas fa-atom"></i> Single Science Award
                            </div>
                            <div class="guide-pathway-body">
                                <strong>Fallback pathway.</strong> Students who do not match Triple or Double criteria are automatically placed here. There is no grade band to configure &mdash; this pathway catches everyone else. Students take one science subject.
                                <ul>
                                    <li><strong>Class type:</strong> Single Award</li>
                                    <li><strong>No grade band:</strong> Receives all students not matched by Triple or Double</li>
                                    <li><strong>Promotion (optional):</strong> You can set a Science Ceiling Grade. Students with a science grade better than the ceiling get promoted to Double (or Triple) instead of staying in Single. See the Promotion section below.</li>
                                </ul>

                        <div class="guide-callout">
                                    <strong><i class="fas fa-exclamation-triangle me-1"></i> Schools without Single Science classes:</strong>
                                    If your school does not create Single Award classes for a term, the Single Science category will not appear on the Placement Recommendations page. Students who would normally be placed in Single Science will instead appear under <strong>Double Science</strong>. This is intentional &mdash; if you only run Triple and Double, all remaining students go into Double.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. How the Recommendation Algorithm Works --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">4</span> How the Recommendation Algorithm Works
                        </div>
                        <div class="guide-text">
                            For each student, the system reads their F3 Science grade (or Private Agriculture grade if Science is missing) and Mathematics grade, then follows this exact decision process:
                        </div>
                        <table class="guide-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">Step</th>
                                    <th>Check</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1</strong></td>
                                    <td>Is Science grade missing (and no Private Agriculture grade) <strong>or</strong> Mathematics grade missing?</td>
                                    <td><strong>Unclassified</strong> &mdash; cannot recommend any pathway without both grades. If Science is missing, the system checks for a Private Agriculture grade first.</td>
                                </tr>
                                <tr>
                                    <td><strong>2</strong></td>
                                    <td>Does Science fall within Triple's grade band <strong>and</strong> does Mathematics fall within Triple's grade band?</td>
                                    <td><strong>Triple Science</strong> &mdash; student meets the highest-priority criteria.</td>
                                </tr>
                                <tr>
                                    <td><strong>3</strong></td>
                                    <td>Does Science fall within Double's grade band <strong>and</strong> does Mathematics fall within Double's grade band?</td>
                                    <td><strong>Double Science</strong> &mdash; student meets the second-priority criteria.</td>
                                </tr>
                                <tr>
                                    <td><strong>4</strong></td>
                                    <td>No match found in Triple or Double.</td>
                                    <td><strong>Single Science Award</strong> (fallback) &mdash; unless promotion applies (see below).</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="guide-example">
                            <div class="example-title">Example with default criteria (Triple A&ndash;B / A&ndash;B, Double C&ndash;D / C&ndash;D)</div>
                            <strong>Student A:</strong> Science = A, Mathematics = B &rarr; Both fall within A&ndash;B. Result: <strong>Triple Science</strong>.<br>
                            <strong>Student B:</strong> Science = C, Mathematics = D &rarr; Both fall within C&ndash;D. Result: <strong>Double Science</strong>.<br>
                            <strong>Student C:</strong> Science = B, Mathematics = D &rarr; Science matches Triple (A&ndash;B) but Maths does not. Science does not match Double (C&ndash;D) because B is better than C. Result: <strong>Single Science Award</strong> (fallback).<br>
                            <strong>Student D:</strong> Science = E, Mathematics = U &rarr; Neither matches Triple or Double. Result: <strong>Single Science Award</strong> (fallback).<br>
                            <strong>Student E:</strong> Science = <em>missing</em>, Private Agriculture = C, Mathematics = A &rarr; Uses Private Agriculture C as the science grade. Result: <strong>Double Science</strong> (shown with a "P.Agric" badge).<br>
                            <strong>Student F:</strong> Science = <em>missing</em>, Private Agriculture = <em>missing</em>, Mathematics = A &rarr; Result: <strong>Unclassified</strong> (cannot place without Science or Private Agriculture).
                        </div>

                        <div class="guide-callout">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i> Notice Student C above:</strong>
                            With the narrow default bands (Triple A&ndash;B, Double C&ndash;D), a student with Science B and Maths D falls through to Single because they do not fully match either band. This is usually not what you want. <strong>Fix this by widening your Double criteria</strong> &mdash; for example, set Double to Science A&ndash;D and Maths A&ndash;D. Triple is checked first, so strong students still go to Triple, while everyone else with A&ndash;D grades lands in Double instead of falling to Single.
                        </div>

                        <div class="guide-callout callout-info">
                            <strong><i class="fas fa-info-circle me-1"></i> "Grade falls within a band" means:</strong>
                            A grade band like A&ndash;C means the student's grade must be A, B, or C. The grade order from best to worst is: <strong>A &rarr; B &rarr; C &rarr; D &rarr; E &rarr; U</strong>. "Best Grade" is the highest (e.g. A) and "Worst Grade" is the lowest allowed (e.g. C).
                        </div>
                    </div>

                    {{-- 5. Science Ceiling and Promotion --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">5</span> Science Ceiling and Promotion
                        </div>
                        <div class="guide-text">
                            Sometimes a student falls into the Single Science fallback but has a strong Science grade that makes them suitable for a higher pathway. The <strong>Science Ceiling Grade</strong> and <strong>Promote To</strong> settings handle this.
                        </div>
                        <div class="guide-text">
                            <strong>How it works:</strong> On any pathway card in the Placement Criteria tab, you can set a "Science Ceiling Grade". If a student lands in that pathway and their Science grade is <strong>better than</strong> the ceiling, they get automatically promoted to the pathway specified in "Promote To".
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Example</div>
                            <strong>Setup:</strong> Single Science has Science Ceiling = C, Promote To = Double Science.<br><br>
                            <strong>Student with Science = B, Mathematics = E:</strong><br>
                            1. Science B does not match Triple (A&ndash;B math required, E does not qualify).<br>
                            2. Science B does not match Double (C&ndash;D science required, B is better than C).<br>
                            3. Falls to Single Science (fallback).<br>
                            4. Ceiling check: Is B better than C? <strong>Yes.</strong> Student is <strong>promoted to Double Science</strong>.<br><br>
                            <strong>Student with Science = D, Mathematics = E:</strong><br>
                            1. No Triple/Double match.<br>
                            2. Falls to Single Science.<br>
                            3. Ceiling check: Is D better than C? <strong>No</strong> (D is worse than C). Student <strong>stays in Single Science</strong>.
                        </div>

                        <div class="guide-callout callout-info">
                            <strong><i class="fas fa-info-circle me-1"></i> When to use this:</strong>
                            Use Science Ceiling + Promotion when you have a gap between your Triple and Double grade bands. For example, if Triple requires Science A&ndash;B and Double requires Science D&ndash;E, students with Science C would fall to Single. Setting Single's ceiling to D with promotion to Double would push those C-grade students into Double instead.
                        </div>
                    </div>

                    {{-- 6. Unclassified Students --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">6</span> Unclassified Students
                        </div>
                        <div class="guide-text">
                            A student is marked <strong>Unclassified</strong> when their Science grade (and Private Agriculture grade), Mathematics grade, or both are missing from the imported data. The system automatically uses Private Agriculture as a substitute when Science is missing, so a student is only Unclassified if <strong>both</strong> Science and Private Agriculture are missing, or if Mathematics is missing.
                        </div>
                        <div class="guide-text">
                            <strong>How to fix Unclassified students:</strong>
                        </div>
                        <ol style="font-size: 14px; color: #374151; line-height: 1.7;">
                            <li>Go to the <strong>Admissions</strong> list and open the student's admission record.</li>
                            <li>Navigate to the <strong>Academic Information</strong> tab.</li>
                            <li>Enter the missing Science and/or Mathematics grade.</li>
                            <li>Save the record and return to the Placement Recommendations page.</li>
                            <li>The student will now appear under their recommended pathway instead of Unclassified.</li>
                        </ol>
                        <div class="guide-callout">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i> Unclassified students cannot be allocated.</strong> Their checkboxes are disabled and no Allocate button appears. You must complete their grades first.
                        </div>
                    </div>

                    {{-- 7. Target Counts and Pre-Selection --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">7</span> Target Counts and Pre-Selection
                        </div>
                        <div class="guide-text">
                            Each pathway has a <strong>Target Count</strong> configured on the Placement Criteria tab. This number controls how many students are <strong>pre-selected</strong> (checkboxes ticked by default) on the Placement Recommendations page.
                        </div>
                        <div class="guide-text">
                            <strong>Target counts are not caps.</strong> They do not prevent more students from being allocated. They only determine which students have their checkboxes checked by default. You can manually check or uncheck any student before clicking Allocate.
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Example</div>
                            Triple Science has a target count of 30. The system finds 45 students who qualify for Triple Science. On the placement page:<br>
                            &bull; Students ranked 1&ndash;30 have their checkboxes pre-selected (marked "Recommended").<br>
                            &bull; Students ranked 31&ndash;45 are unchecked (marked "Overflow").<br>
                            &bull; You can manually check any overflow student or uncheck any recommended student before allocating.
                        </div>
                    </div>

                    {{-- 8. Student Ranking --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">8</span> How Students Are Ranked Within a Pathway
                        </div>
                        <div class="guide-text">
                            Within each pathway, students are sorted from strongest to weakest using these tiebreakers in order:
                        </div>
                        <table class="guide-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Priority</th>
                                    <th>Criterion</th>
                                    <th>Direction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1st</strong></td>
                                    <td>Science grade (or Private Agriculture if used as substitute)</td>
                                    <td>A is ranked highest, U is lowest</td>
                                </tr>
                                <tr>
                                    <td><strong>2nd</strong></td>
                                    <td>Mathematics grade</td>
                                    <td>A is ranked highest, U is lowest</td>
                                </tr>
                                <tr>
                                    <td><strong>3rd</strong></td>
                                    <td>Overall grade</td>
                                    <td>A is ranked highest, M is lowest</td>
                                </tr>
                                <tr>
                                    <td><strong>4th</strong></td>
                                    <td>Last name</td>
                                    <td>Alphabetical (A&ndash;Z)</td>
                                </tr>
                                <tr>
                                    <td><strong>5th</strong></td>
                                    <td>First name</td>
                                    <td>Alphabetical (A&ndash;Z)</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="guide-text">
                            The rank number shown in the <strong>#</strong> column on the placement page reflects this sort order. Students with better grades appear higher in the list and get pre-selected first when the target count is applied.
                        </div>
                    </div>

                    {{-- 9. F4 Classes and Class Types --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">9</span> F4 Classes and Class Types
                        </div>
                        <div class="guide-text">
                            Before you can allocate students, you must create F4 classes in the <strong>Academics Manager &rarr; Class Allocations</strong> module for the same term. Each class must have a <strong>type</strong> set to one of:
                        </div>
                        <table class="guide-table">
                            <thead>
                                <tr>
                                    <th>Class Type</th>
                                    <th>Receives Students From</th>
                                    <th>Example Class Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Triple Award</strong></td>
                                    <td>Triple Science pathway</td>
                                    <td>F4 Triple A, F4 Triple B</td>
                                </tr>
                                <tr>
                                    <td><strong>Double Award</strong></td>
                                    <td>Double Science pathway</td>
                                    <td>F4 Double A, F4 Double B</td>
                                </tr>
                                <tr>
                                    <td><strong>Single Award</strong></td>
                                    <td>Single Science pathway</td>
                                    <td>F4 Single A</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="guide-text">
                            <strong>Class capacity:</strong> You can set a <strong>Max Students</strong> value on each class. The placement page shows capacity bars so you can see how full each class is. Auto-assign prefers classes with space remaining first, but if all matching classes are already full the system can still place students and will show a warning. You can edit class capacities directly from the placement page.
                        </div>

                        <div class="guide-callout">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i> If you do not create Single Award classes:</strong>
                            The Single Science pathway will not appear on the Placement Recommendations page. Students who would have been Single will automatically appear under <strong>Double Science</strong> instead. This is the correct behavior for schools that only run two pathways (Triple and Double).
                        </div>

                        <div class="guide-callout callout-info">
                            <strong><i class="fas fa-info-circle me-1"></i> If a pathway has no classes at all:</strong>
                            Students in that pathway still appear on the Placement Recommendations page. A warning banner tells you which class types are missing and links you to the Class Allocations page so you can create them. You can also use <strong>Choose Class</strong> to place individual students into any other F4 class for the term.
                        </div>
                    </div>

                    {{-- 10. Auto-Assign vs Choose Class --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">10</span> Auto-Assign vs Choose Class
                        </div>
                        <div class="guide-text">
                            When you click "Allocate" on a pathway section, each selected student can either stay on Auto-assign or be switched to Choose Class. You can mix both approaches in the same allocation run.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="guide-pathway-card gp-triple">
                                    <div class="guide-pathway-header">
                                        <i class="fas fa-magic"></i> Auto-Assign (Default)
                                    </div>
                                    <div class="guide-pathway-body">
                                        The system distributes students evenly across all classes of the matching type using a zigzag pattern (first class, second class, ..., last class, last class, second-to-last, ..., first class). This produces balanced class sizes.
                                        <ul>
                                            <li>Uses the pathway's matching class type first</li>
                                            <li>Prefers classes with remaining space before using already-full classes</li>
                                            <li>Shows a warning if placement goes into a class already at or above Max Students</li>
                                            <li>No action needed per student &mdash; just check the box and click Allocate</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="guide-pathway-card gp-double">
                                    <div class="guide-pathway-header">
                                        <i class="fas fa-sliders-h"></i> Choose Class
                                    </div>
                                    <div class="guide-pathway-body">
                                        Click <strong>Choose Class</strong> next to a student to show a dropdown of all F4 classes for the selected term. Select the exact class you want and that student will go there instead of following Auto-assign.
                                        <ul>
                                            <li>Works as a true override for that student only</li>
                                            <li>Can place a student into a different class type when staff decide that is appropriate</li>
                                            <li>Other selected students without a chosen class stay on Auto-assign</li>
                                            <li>Still warns if the chosen class is already at or above Max Students</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 11. What Happens When You Click Allocate --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">11</span> What Happens When You Click Allocate
                        </div>
                        <div class="guide-text">
                            For each selected student, the system does the following:
                        </div>
                        <ol style="font-size: 14px; color: #374151; line-height: 1.7;">
                            <li>Validates the student has not already been enrolled or deleted.</li>
                            <li>Confirms the student has complete Science and Mathematics grades (not unclassified).</li>
                            <li>If no class is chosen, Auto-assign picks a class from the pathway's matching class type. If a class is chosen, that explicit class selection is used as an override.</li>
                            <li>Creates a new <strong>Student</strong> record from the admission data (name, gender, date of birth, nationality, sponsor).</li>
                            <li>Generates a unique 8-digit student ID number and password.</li>
                            <li>Enrolls the student in the selected class for the term (creates <code>student_term</code> and <code>klass_student</code> records).</li>
                            <li>Shows a warning if the class used was already at or above its Max Students threshold.</li>
                            <li>Updates the admission status to <strong>Enrolled</strong>.</li>
                        </ol>
                        <div class="guide-callout callout-success">
                            <strong><i class="fas fa-check-circle me-1"></i> After allocation:</strong>
                            Enrolled students disappear from the Placement Recommendations page (only pending admissions are shown). The student now appears in the Class Allocations page under their assigned class. The admission record's status changes to "Enrolled" in the Admissions list.
                        </div>
                    </div>

                    {{-- 12. Active / Inactive Pathways --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">12</span> Active and Inactive Pathways
                        </div>
                        <div class="guide-text">
                            Each pathway on the Placement Criteria tab has an <strong>Active</strong> checkbox. When a pathway is unchecked (inactive):
                        </div>
                        <ul style="font-size: 14px; color: #374151; line-height: 1.7;">
                            <li>The pathway's grade band is <strong>skipped</strong> during the recommendation process.</li>
                            <li>Students who would have matched that pathway will fall through to the next active pathway or to Single Science (fallback).</li>
                            <li>The pathway group still appears on the Placement Recommendations page, but it will be empty.</li>
                        </ul>
                        <div class="guide-example">
                            <div class="example-title">Example</div>
                            If you uncheck "Active" on Triple Science, a student with Science A and Mathematics A will <strong>not</strong> be recommended for Triple. Instead, they will be checked against Double criteria. If they do not match Double either, they fall to Single Science.
                        </div>
                    </div>

                    {{-- 13. Common Scenarios --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">13</span> Common Scenarios
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Scenario: School runs only Triple and Double (no Single)</div>
                            1. Create only Triple Award and Double Award F4 classes.<br>
                            2. Do <strong>not</strong> create any Single Award classes.<br>
                            3. On the Placement Recommendations page, only Triple Science, Double Science, and Unclassified groups appear.<br>
                            4. Students who would normally be Single are automatically included in the Double Science group.<br>
                            5. Allocate Triple, then Double. All students are covered.
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Scenario: A student has strong Science but weak Maths</div>
                            Student gets Science = A, Mathematics = E.<br>
                            1. Triple requires A&ndash;B in both &rarr; Maths E does not qualify. Fail.<br>
                            2. Double requires C&ndash;D in both &rarr; Science A is better than C. Fail.<br>
                            3. Falls to Single Science (fallback).<br>
                            4. If Single has a Science Ceiling of C with promotion to Double: Science A is better than C &rarr; <strong>promoted to Double</strong>.<br>
                            5. If no ceiling is set: stays in Single.
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Scenario: Student was imported with missing grades</div>
                            1. Student appears under <strong>Unclassified</strong> with disabled checkbox.<br>
                            2. Open the student's admission from the Admissions list.<br>
                            3. Go to the Academic Information tab and enter the missing Science/Mathematics grade.<br>
                            4. Return to Placement Recommendations &rarr; the student now appears in the correct pathway.
                        </div>

                        <div class="guide-example">
                            <div class="example-title">Scenario: All classes for a pathway are full</div>
                            1. During auto-assign, the system still prefers classes with room first.<br>
                            2. If every matching class is already at or above Max Students, the student can still be placed and a capacity warning is shown after allocation.<br>
                            3. Staff can increase the max students, create a new class of that type, or use <strong>Choose Class</strong> to place the student into a different F4 class if needed.
                        </div>
                    </div>

                    {{-- 14. Grade Scales Reference --}}
                    <div class="guide-section">
                        <div class="guide-section-title">
                            <span class="guide-step-num">14</span> Grade Scales Reference
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="guide-text"><strong>Subject Grades</strong> (Science, Mathematics, and all other subjects):</div>
                                <table class="guide-table">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Rank (1 = best)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>A</td><td>1</td></tr>
                                        <tr><td>B</td><td>2</td></tr>
                                        <tr><td>C</td><td>3</td></tr>
                                        <tr><td>D</td><td>4</td></tr>
                                        <tr><td>E</td><td>5</td></tr>
                                        <tr><td>U</td><td>6</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="guide-text"><strong>Overall Grades</strong> (used as a tiebreaker in ranking only):</div>
                                <table class="guide-table">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Rank (1 = best)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>A</td><td>1</td></tr>
                                        <tr><td>B</td><td>2</td></tr>
                                        <tr><td>C</td><td>3</td></tr>
                                        <tr><td>D</td><td>4</td></tr>
                                        <tr><td>M</td><td>5</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="guide-callout callout-info">
                            <strong><i class="fas fa-info-circle me-1"></i> Note:</strong> Overall grades use a different scale (A&ndash;M) than subject grades (A&ndash;U). The overall grade is only used for ranking students within a pathway. It does <strong>not</strong> affect which pathway a student is recommended to.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('senior_admissions_upload_file');
            const fileLabel = document.getElementById('seniorAdmissionsFileLabel');
            const fileName = document.getElementById('seniorAdmissionsFileName');
            const form = document.getElementById('seniorAdmissionsForm');
            const button = document.getElementById('seniorAdmissionsBtn');
            const deleteCheckbox = document.getElementById('deleteExistingTermAdmissions');
            const storageKey = 'activeAdmissionsImportTab';

            if (fileInput && fileLabel) {
                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        fileLabel.classList.add('has-file');
                        if (fileName) {
                            fileName.textContent = this.files[0].name;
                        }
                    } else {
                        fileLabel.classList.remove('has-file');
                        if (fileName) {
                            fileName.textContent = '';
                        }
                    }
                });

                // Drag and drop enhancement
                fileLabel.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    fileLabel.classList.add('drag-over');
                });

                fileLabel.addEventListener('dragleave', function() {
                    fileLabel.classList.remove('drag-over');
                });

                fileLabel.addEventListener('drop', function(e) {
                    e.preventDefault();
                    fileLabel.classList.remove('drag-over');
                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });
            }

            if (deleteCheckbox && button) {
                deleteCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        const confirmMsg = 'WARNING: This will permanently delete all admissions for the selected term before importing. Do you want to continue?';
                        if (!confirm(confirmMsg)) {
                            this.checked = false;
                            return;
                        }

                        button.querySelector('.btn-text').innerHTML = '<i class="fas fa-trash-alt me-1"></i> Clear Term & Import';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-danger');
                    } else {
                        button.querySelector('.btn-text').innerHTML = '<i class="fas fa-upload me-1"></i> Import F4 Admissions';
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-primary');
                    }
                });
            }

            if (form && button) {
                form.addEventListener('submit', function(e) {
                    if (fileInput && !fileInput.files.length) {
                        e.preventDefault();
                        alert('Please select an Excel file to import.');
                        return;
                    }

                    button.classList.add('loading');
                    button.disabled = true;
                });

                form.addEventListener('reset', function() {
                    if (fileLabel) {
                        fileLabel.classList.remove('has-file');
                    }

                    if (fileName) {
                        fileName.textContent = '';
                    }

                    if (deleteCheckbox) {
                        deleteCheckbox.checked = false;
                    }

                    button.querySelector('.btn-text').innerHTML = '<i class="fas fa-upload me-1"></i> Import F4 Admissions';
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-primary');
                    button.classList.remove('loading');
                    button.disabled = false;
                });
            }

            const activeTab = localStorage.getItem(storageKey);
            if (activeTab) {
                const tabElement = document.querySelector(`a[href="#${activeTab}"]`);
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }

            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(e) {
                    const tabId = e.target.getAttribute('href').substring(1);
                    localStorage.setItem(storageKey, tabId);
                });
            });

            document.querySelectorAll('.ceiling-grade-select').forEach(function(select) {
                select.addEventListener('change', function() {
                    const pathway = this.dataset.pathway;
                    const wrapper = document.querySelector('.promotion-pathway-wrapper[data-pathway="' + pathway + '"]');
                    if (wrapper) {
                        wrapper.style.display = this.value ? '' : 'none';
                    }
                });
            });

            // Generic loading state for all forms with .btn-loading buttons
            document.querySelectorAll('form').forEach(function(f) {
                if (f.id === 'seniorAdmissionsForm') return; // already handled above
                f.addEventListener('submit', function() {
                    var btn = f.querySelector('button[type="submit"].btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection

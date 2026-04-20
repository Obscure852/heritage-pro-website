@extends('layouts.master')
@section('title')
    Data Importing
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

        .quick-start-guide {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border: 1px solid #93c5fd;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .quick-start-guide h5 {
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .quick-start-guide hr {
            border-color: #93c5fd;
            opacity: 0.5;
        }

        .quick-start-guide p {
            color: #1e40af;
            font-size: 13px;
            margin-bottom: 4px;
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

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
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

        .file-name {
            font-size: 12px;
            color: #10b981;
            margin-top: 4px;
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
            Data Importing
        @endslot
        @slot('title')
            Import
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
        $studentImportMode = \App\Models\SchoolSetup::normalizeType($school_data->type ?? null) ?? \App\Models\SchoolSetup::TYPE_JUNIOR;
        $studentImportRequiredHeadings = ['connect_id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'nationality', 'id_number', 'status', 'grade'];
        $studentTemplateDefinitions = match ($studentImportMode) {
            \App\Models\SchoolSetup::TYPE_PRIMARY => [
                [
                    'label' => 'Elementary',
                    'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                    'description' => 'Use for REC and STD 1 to STD 7 student imports.',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_JUNIOR => [
                [
                    'label' => 'Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Use for F1 to F3 student imports with PSLE columns.',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_SENIOR => [
                [
                    'label' => 'High School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                    'description' => 'Use for F4 to F5 student imports with JCE columns.',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_PRE_F3 => [
                [
                    'label' => 'Elementary',
                    'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                    'description' => 'Use for REC and STD 1 to STD 7 student imports.',
                ],
                [
                    'label' => 'Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Use for F1 to F3 student imports with PSLE columns.',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR => [
                [
                    'label' => 'Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Use for F1 to F3 student imports with PSLE columns.',
                ],
                [
                    'label' => 'High School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                    'description' => 'Use for F4 to F5 student imports with JCE columns.',
                ],
            ],
            \App\Models\SchoolSetup::TYPE_K12 => [
                [
                    'label' => 'Elementary',
                    'school_type' => \App\Models\SchoolSetup::TYPE_PRIMARY,
                    'description' => 'Use for REC and STD 1 to STD 7 student imports.',
                ],
                [
                    'label' => 'Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Use for F1 to F3 student imports with PSLE columns.',
                ],
                [
                    'label' => 'High School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_SENIOR,
                    'description' => 'Use for F4 to F5 student imports with JCE columns.',
                ],
            ],
            default => [
                [
                    'label' => 'Middle School',
                    'school_type' => \App\Models\SchoolSetup::TYPE_JUNIOR,
                    'description' => 'Use for F1 to F3 student imports with PSLE columns.',
                ],
            ],
        };
        $studentTemplateVariants = array_map(function (array $variant): array {
            $template = \App\Exports\ImportTemplateExport::students($variant['school_type']);
            $variant['headings'] = $template->headings();
            $variant['sample_row'] = $template->array()[0] ?? [];
            $variant['download_url'] = route('setup.download-import', [
                'filename' => 'import-students.xlsx',
                'school_type' => $variant['school_type'],
            ]);

            return $variant;
        }, $studentTemplateDefinitions);
    @endphp

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-file-import me-2"></i>Data Import Hub</h3>
            <p>Import staff, parents, students, and admissions data from Excel files</p>
        </div>

        <div class="settings-body">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist" id="importTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#staffImport" role="tab">
                        <i class="fas fa-users me-2"></i>
                        <span>Staff</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#parentsImport" role="tab">
                        <i class="fas fa-user-friends me-2"></i>
                        <span>Parents</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#studentsImport" role="tab">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#admissionsImport" role="tab">
                        <i class="fas fa-user-plus me-2"></i>
                        <span>Admissions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#sampleFiles" role="tab">
                        <i class="fas fa-download me-2"></i>
                        <span>Templates</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- STAFF IMPORT TAB -->
                <div class="tab-pane active" id="staffImport" role="tabpanel">
                    <div class="quick-start-guide">
                        <h5><i class="fas fa-info-circle me-2"></i>Quick Start Guide</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Step 1:</strong> Download template or prepare Excel file with format below</p>
                                <p><strong>Step 2:</strong> Fill in staff data (required fields marked with <span class="text-danger">*</span>)</p>
                                <p><strong>Step 3:</strong> Save as .xlsx or .xls format</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Step 4:</strong> Choose import options below</p>
                                <p><strong>Step 5:</strong> Upload your file and wait for processing</p>
                                <p class="mb-0"><strong>Step 6:</strong> Review the import results</p>
                            </div>
                        </div>
                    </div>

                    <div class="format-card">
                        <div class="format-card-header">
                            <i class="fas fa-table me-2"></i>Expected Data Format
                        </div>
                        <div class="format-card-body">
                            <p class="text-muted mb-3" style="font-size: 13px;">Your Excel file should have these exact headers:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Firstname <span class="text-danger">*</span></th>
                                            <th>Middlename</th>
                                            <th>Lastname <span class="text-danger">*</span></th>
                                            <th>Email <span class="text-danger">*</span></th>
                                            <th>Date Of Birth <span class="text-danger">*</span></th>
                                            <th>Gender <span class="text-danger">*</span></th>
                                            <th>Position <span class="text-danger">*</span></th>
                                            <th>Area of work <span class="text-danger">*</span></th>
                                            <th>Phone</th>
                                            <th>Id Number <span class="text-danger">*</span></th>
                                            <th>Nationality <span class="text-danger">*</span></th>
                                            <th>City</th>
                                            <th>Address</th>
                                            <th>Active</th>
                                            <th>Status <span class="text-danger">*</span></th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Shawn</td>
                                            <td>B.</td>
                                            <td>Bolz</td>
                                            <td>bolz@gmail.com</td>
                                            <td>03/09/1965</td>
                                            <td>M</td>
                                            <td>Teacher</td>
                                            <td>Teaching</td>
                                            <td>72975334</td>
                                            <td>357718812</td>
                                            <td>Motswana</td>
                                            <td>Francistown</td>
                                            <td>Block 3</td>
                                            <td>True</td>
                                            <td>Current</td>
                                            <td>2024</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                <span class="text-danger">* Required fields</span> |
                                Date format: DD/MM/YYYY | Gender: M/F | Active: True/False | Status: Current/Former
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-upload me-2"></i>Import Staff</h6>
                                <form action="{{ route('setup.import-staff') }}" method="post" enctype="multipart/form-data" id="staffForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                        <div class="custom-file-input">
                                            <input type="file" name="file" id="staff_upload_file" accept=".xlsx,.xls" required>
                                            <label for="staff_upload_file" class="file-input-label" id="staffFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload Excel file</div>
                                                    <div class="subtitle">.xlsx or .xls (Max 10MB)</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="file-name" id="staffFileName"></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="warning-card">
                                            <div class="form-check">
                                                <input id="deleteStaff" class="form-check-input" name="deleteStaff" type="checkbox">
                                                <label class="form-check-label warning-title" for="deleteStaff">
                                                    Delete existing staff data first
                                                </label>
                                            </div>
                                            <div class="warning-text mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This will permanently delete all current staff records.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-loading" id="staffBtn">
                                            <span class="btn-text"><i class="fas fa-upload me-1"></i> Import Staff</span>
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
                                <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Staff Import Guidelines</h6>
                                <h6 class="text-success mb-2" style="font-size: 13px;"><i class="fas fa-check me-1"></i>Required Fields:</h6>
                                <ul class="list-unstyled mb-3" style="font-size: 13px;">
                                    <li class="mb-1"><strong>Firstname, Lastname:</strong> Staff member's name</li>
                                    <li class="mb-1"><strong>Email:</strong> Must be unique and valid</li>
                                    <li class="mb-1"><strong>Date Of Birth:</strong> Format DD/MM/YYYY</li>
                                    <li class="mb-1"><strong>Gender:</strong> M (Male) or F (Female)</li>
                                    <li class="mb-1"><strong>Position:</strong> Job title</li>
                                    <li class="mb-1"><strong>Area of work:</strong> Department</li>
                                    <li class="mb-1"><strong>Id Number:</strong> Must be unique</li>
                                    <li class="mb-1"><strong>Nationality, Status:</strong> Required</li>
                                </ul>
                                <div class="guidelines-card">
                                    <h6><i class="fas fa-lightbulb me-1"></i>Common Values:</h6>
                                    <small><strong>Positions:</strong> Principal, Teacher, HOD, Secretary, Librarian<br>
                                    <strong>Areas:</strong> Administration, Teaching, Support, Finance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PARENTS IMPORT TAB -->
                <div class="tab-pane" id="parentsImport" role="tabpanel">
                    <div class="quick-start-guide">
                        <h5><i class="fas fa-info-circle me-2"></i>Quick Start Guide</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Step 1:</strong> Prepare Excel file with parents/sponsors data</p>
                                <p><strong>Step 2:</strong> Include Connect_id to link with students</p>
                                <p><strong>Step 3:</strong> Fill all required fields marked with <span class="text-danger">*</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Step 4:</strong> Choose import options</p>
                                <p><strong>Step 5:</strong> Upload and process</p>
                                <p class="mb-0"><strong>Step 6:</strong> Review results</p>
                            </div>
                        </div>
                    </div>

                    <div class="format-card">
                        <div class="format-card-header">
                            <i class="fas fa-table me-2"></i>Expected Data Format
                        </div>
                        <div class="format-card-body">
                            <p class="text-muted mb-3" style="font-size: 13px;">Your Excel file should have these exact headers:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Connect_id <span class="text-danger">*</span></th>
                                            <th>Title <span class="text-danger">*</span></th>
                                            <th>Firstname <span class="text-danger">*</span></th>
                                            <th>Lastname <span class="text-danger">*</span></th>
                                            <th>Email <span class="text-danger">*</span></th>
                                            <th>Gender <span class="text-danger">*</span></th>
                                            <th>Date of Birth <span class="text-danger">*</span></th>
                                            <th>Nationality <span class="text-danger">*</span></th>
                                            <th>Relation <span class="text-danger">*</span></th>
                                            <th>Status <span class="text-danger">*</span></th>
                                            <th>Id_number <span class="text-danger">*</span></th>
                                            <th>Phone</th>
                                            <th>Profession</th>
                                            <th>Work_place</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>345632</td>
                                            <td>Mrs</td>
                                            <td>Jane</td>
                                            <td>Smith</td>
                                            <td>jane.smith@example.com</td>
                                            <td>F</td>
                                            <td>07/04/1976</td>
                                            <td>Motswana</td>
                                            <td>Mother</td>
                                            <td>Current</td>
                                            <td>357718812</td>
                                            <td>71869865</td>
                                            <td>Nurse</td>
                                            <td>Sedilega Private Hospital</td>
                                            <td>2023</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                <span class="text-danger">* Required fields</span> |
                                Date format: DD/MM/YYYY | Gender: M/F | Relation: Mother/Father/Guardian
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-upload me-2"></i>Import Parents/Sponsors</h6>
                                <form action="{{ route('setup.import-sponsors') }}" method="post" enctype="multipart/form-data" id="sponsorsForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                        <div class="custom-file-input">
                                            <input type="file" name="file" id="sponsors_upload_file" accept=".xlsx,.xls" required>
                                            <label for="sponsors_upload_file" class="file-input-label" id="sponsorsFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload Excel file</div>
                                                    <div class="subtitle">.xlsx or .xls (Max 10MB)</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="file-name" id="sponsorsFileName"></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="warning-card">
                                            <div class="form-check">
                                                <input id="deleteSponsors" class="form-check-input" name="deleteSponsors" type="checkbox">
                                                <label class="form-check-label warning-title" for="deleteSponsors">
                                                    Delete existing sponsors data first
                                                </label>
                                            </div>
                                            <div class="warning-text mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This will permanently delete all current sponsor records.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-loading" id="sponsorsBtn">
                                            <span class="btn-text"><i class="fas fa-upload me-1"></i> Import Sponsors</span>
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
                                <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Sponsors Import Guidelines</h6>
                                <h6 class="text-success mb-2" style="font-size: 13px;"><i class="fas fa-check me-1"></i>Key Requirements:</h6>
                                <ul class="list-unstyled mb-3" style="font-size: 13px;">
                                    <li class="mb-1"><strong>Connect_id:</strong> Links to student records</li>
                                    <li class="mb-1"><strong>Title:</strong> Mr, Mrs, Ms, Dr</li>
                                    <li class="mb-1"><strong>Email:</strong> Must be unique</li>
                                    <li class="mb-1"><strong>Relation:</strong> Mother, Father, Guardian</li>
                                    <li class="mb-1"><strong>Id_number:</strong> Must be unique</li>
                                </ul>
                                <div class="info-card">
                                    <h6><i class="fas fa-lightbulb me-1"></i>Important Note:</h6>
                                    <small>The Connect_id must match existing student records to establish parent-child relationships.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STUDENTS IMPORT TAB -->
                <div class="tab-pane" id="studentsImport" role="tabpanel">
                    <div class="quick-start-guide">
                        <h5><i class="fas fa-info-circle me-2"></i>Quick Start Guide</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Step 1:</strong> Select the appropriate term for import</p>
                                <p><strong>Step 2:</strong> Download the correct level-specific student template</p>
                                <p><strong>Step 3:</strong> Prepare the Excel file with the required fields and grades</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Step 4:</strong> Choose import options</p>
                                <p><strong>Step 5:</strong> Upload and process</p>
                                <p class="mb-0"><strong>Step 6:</strong> Review results and verify class assignments</p>
                            </div>
                        </div>
                    </div>

                    @foreach ($studentTemplateVariants as $variant)
                        <div class="format-card">
                            <div class="format-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <i class="fas fa-table me-2"></i>Expected Data Format ({{ $variant['label'] }})
                                </div>
                                <a href="{{ $variant['download_url'] }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download {{ $variant['label'] }} Template
                                </a>
                            </div>
                            <div class="format-card-body">
                                <p class="text-muted mb-2" style="font-size: 13px;">{{ $variant['description'] }}</p>
                                <div class="table-responsive" style="max-width: 100%; overflow-x: auto;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                @foreach ($variant['headings'] as $heading)
                                                    <th>
                                                        {{ $heading }}
                                                        @if (in_array($heading, $studentImportRequiredHeadings, true))
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @foreach ($variant['headings'] as $index => $heading)
                                                    <td>{{ $variant['sample_row'][$index] ?? '' }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    <span class="text-danger">* Required fields</span> |
                                    Date format: DD/MM/YYYY | Gender: M/F | Boarding: use <code>boarding</code> or leave blank
                                    @if ($variant['school_type'] === \App\Models\SchoolSetup::TYPE_JUNIOR)
                                        | PSLE columns apply to F1-F3 only
                                    @elseif ($variant['school_type'] === \App\Models\SchoolSetup::TYPE_SENIOR)
                                        | JCE columns apply to F4-F5 only
                                    @else
                                        | No external exam columns are required
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-upload me-2"></i>Import Students</h6>
                                <form action="{{ route('setup.import-students') }}" method="post" enctype="multipart/form-data" id="studentsForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="term_id" class="form-label">Select Term <span class="text-danger">*</span></label>
                                        <select class="form-select" name="term_id" id="term_id" required>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">Term {{ $term->term }}, {{ $term->year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                        <div class="custom-file-input">
                                            <input type="file" name="file" id="students_upload_file" accept=".xlsx,.xls" required>
                                            <label for="students_upload_file" class="file-input-label" id="studentsFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload Excel file</div>
                                                    <div class="subtitle">.xlsx or .xls (Max 10MB)</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="file-name" id="studentsFileName"></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="warning-card">
                                            <div class="form-check">
                                                <input id="deleteStudents" class="form-check-input" name="deleteStudents" type="checkbox">
                                                <label class="form-check-label warning-title" for="deleteStudents">
                                                    Delete existing students data first
                                                </label>
                                            </div>
                                            <div class="warning-text mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This will permanently delete all current student records.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-loading" id="studentsBtn">
                                            <span class="btn-text"><i class="fas fa-upload me-1"></i> Import Students</span>
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
                                <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Students Import Guidelines</h6>
                                <h6 class="text-success mb-2" style="font-size: 13px;"><i class="fas fa-check me-1"></i>Key Requirements:</h6>
                                <ul class="list-unstyled mb-3" style="font-size: 13px;">
                                    <li class="mb-1"><strong>connect_id:</strong> Links to sponsor/parent</li>
                                    <li class="mb-1"><strong>id_number:</strong> Must be unique</li>
                                    <li class="mb-1"><strong>grade:</strong> Must match existing grades</li>
                                    <li class="mb-1"><strong>class:</strong> Will be auto-created if missing</li>
                                </ul>
                                <div class="guidelines-card">
                                    <h6><i class="fas fa-book me-1"></i>Grade Information:</h6>
                                    <small>
                                        @if ($studentImportMode === \App\Models\SchoolSetup::TYPE_JUNIOR)
                                            <strong>Junior School:</strong> PSLE grades (A-E)<br>
                                            <strong>Subjects:</strong> Overall, Agriculture, Math, English, Science, Social Studies, Setswana, CAPA, Religious & Moral Education
                                        @elseif ($studentImportMode === \App\Models\SchoolSetup::TYPE_PRE_F3)
                                            <strong>Pre-F3:</strong> Download the correct level template before importing.<br>
                                            <strong>Elementary:</strong> Use the Elementary template for REC to STD 7.<br>
                                            <strong>Middle School:</strong> Use the Middle School template for F1 to F3 with PSLE columns.
                                        @elseif ($studentImportMode === \App\Models\SchoolSetup::TYPE_JUNIOR_SENIOR)
                                            <strong>Middle &amp; High School:</strong> Download the correct level template before importing.<br>
                                            <strong>Middle School:</strong> Use the Middle School template for F1 to F3 with PSLE columns.<br>
                                            <strong>High School:</strong> Use the High School template for F4 to F5 with JCE columns.
                                        @elseif ($studentImportMode === \App\Models\SchoolSetup::TYPE_K12)
                                            <strong>Pre-F5 (K12):</strong> Download the correct level template before importing.<br>
                                            <strong>Elementary:</strong> Use the Elementary template for REC to STD 7.<br>
                                            <strong>Middle School:</strong> Use the Middle School template for F1 to F3 with PSLE columns.<br>
                                            <strong>High School:</strong> Use the High School template for F4 to F5 with JCE columns.
                                        @elseif ($studentImportMode === \App\Models\SchoolSetup::TYPE_SENIOR)
                                            <strong>Senior School:</strong> JCE grades (A-U)<br>
                                            <strong>Subjects:</strong> Overall, Math, English, Science, Setswana, DT, HE, Agriculture, etc.
                                        @else
                                            <strong>Elementary:</strong> Use the elementary student template for REC and STD 1 to STD 7 only.
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ADMISSIONS IMPORT TAB -->
                <div class="tab-pane" id="admissionsImport" role="tabpanel">
                    <div class="quick-start-guide">
                        <h5><i class="fas fa-info-circle me-2"></i>Quick Start Guide</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Step 1:</strong> Prepare Excel file with admission applications</p>
                                <p><strong>Step 2:</strong> Include Connect_id to link with parents</p>
                                <p><strong>Step 3:</strong> Fill all required fields</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Step 4:</strong> Specify grade and year</p>
                                <p><strong>Step 5:</strong> Upload and process</p>
                                <p class="mb-0"><strong>Step 6:</strong> Review admission records</p>
                            </div>
                        </div>
                    </div>

                    <div class="format-card">
                        <div class="format-card-header">
                            <i class="fas fa-table me-2"></i>Expected Data Format
                        </div>
                        <div class="format-card-body">
                            <p class="text-muted mb-3" style="font-size: 13px;">Your Excel file should have these exact headers:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Connect_id <span class="text-danger">*</span></th>
                                            <th>First_name <span class="text-danger">*</span></th>
                                            <th>Last_name <span class="text-danger">*</span></th>
                                            <th>Middle_name</th>
                                            <th>Gender <span class="text-danger">*</span></th>
                                            <th>Date Of Birth <span class="text-danger">*</span></th>
                                            <th>Nationality <span class="text-danger">*</span></th>
                                            <th>Phone <span class="text-danger">*</span></th>
                                            <th>Id Number <span class="text-danger">*</span></th>
                                            <th>Grade <span class="text-danger">*</span></th>
                                            <th>Year <span class="text-danger">*</span></th>
                                            <th>Status <span class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>345632</td>
                                            <td>Cheri</td>
                                            <td>Bolz</td>
                                            <td>B.</td>
                                            <td>F</td>
                                            <td>03/09/2010</td>
                                            <td>Motswana</td>
                                            <td>73879654</td>
                                            <td>367729981</td>
                                            <td>STD 1</td>
                                            <td>2025</td>
                                            <td>Applied</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                <span class="text-danger">* Required fields</span> |
                                Date format: DD/MM/YYYY | Gender: M/F | Status: Applied/Accepted/Rejected
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-upload me-2"></i>Import Admissions</h6>
                                <form action="{{ route('setup.import-admissions') }}" method="post" enctype="multipart/form-data" id="admissionsForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                        <div class="custom-file-input">
                                            <input type="file" name="file" id="admissions_upload_file" accept=".xlsx,.xls" required>
                                            <label for="admissions_upload_file" class="file-input-label" id="admissionsFileLabel">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <div class="title">Click to upload Excel file</div>
                                                    <div class="subtitle">.xlsx or .xls (Max 10MB)</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="file-name" id="admissionsFileName"></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="warning-card">
                                            <div class="form-check">
                                                <input id="deleteAdmissions" class="form-check-input" name="deleteAdmissions" type="checkbox">
                                                <label class="form-check-label warning-title" for="deleteAdmissions">
                                                    Delete existing admissions data first
                                                </label>
                                            </div>
                                            <div class="warning-text mt-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                This will permanently delete all current admission records.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-loading" id="admissionsBtn">
                                            <span class="btn-text"><i class="fas fa-upload me-1"></i> Import Admissions</span>
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
                                <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Admissions Import Guidelines</h6>
                                <h6 class="text-success mb-2" style="font-size: 13px;"><i class="fas fa-check me-1"></i>Key Requirements:</h6>
                                <ul class="list-unstyled mb-3" style="font-size: 13px;">
                                    <li class="mb-1"><strong>Connect_id:</strong> Links to parent/sponsor</li>
                                    <li class="mb-1"><strong>Phone:</strong> Contact number for admission</li>
                                    <li class="mb-1"><strong>Id Number:</strong> Must be unique</li>
                                    <li class="mb-1"><strong>Grade & Year:</strong> Admission level and year</li>
                                </ul>
                                <div class="info-card">
                                    <h6><i class="fas fa-clipboard-list me-1"></i>Status Options:</h6>
                                    <small><strong>Applied:</strong> Application submitted<br>
                                    <strong>Accepted:</strong> Admission approved<br>
                                    <strong>Rejected:</strong> Application declined</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SAMPLE FILES TAB -->
                <div class="tab-pane" id="sampleFiles" role="tabpanel">
                    <div class="help-text">
                        <div class="help-title">Download Templates</div>
                        <div class="help-content">
                            Download these sample Excel files to see the exact format expected for each import type.
                            Use them as templates for your data. Student templates stay level-specific for Elementary, Middle School, and High School.
                        </div>
                    </div>

                    <div class="template-grid">
                        <div class="template-download-card">
                            <span class="badge bg-light text-dark">Staff</span>
                            <h6>Staff Data Template</h6>
                            <p>Sample staff import file with headers and examples.</p>
                            <a href="{{ route('setup.download-import', 'import-staff.xlsx') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-1"></i> Download Staff
                            </a>
                        </div>

                        <div class="template-download-card">
                            <span class="badge bg-light text-dark">Sponsors</span>
                            <h6>Sponsors Data Template</h6>
                            <p>Sample parents and sponsors import file.</p>
                            <a href="{{ route('setup.download-import', 'import-sponsors.xlsx') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-1"></i> Download Sponsors
                            </a>
                        </div>

                        <div class="template-download-card">
                            <span class="badge bg-light text-dark">Admissions</span>
                            <h6>Admissions Data Template</h6>
                            <p>Sample admissions import file for applicant intake data.</p>
                            <a href="{{ route('setup.download-import', 'import-admissions.xlsx') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-1"></i> Download Admissions
                            </a>
                        </div>
                    </div>

                    <div class="form-section mt-4">
                        <h6 class="form-section-title"><i class="fas fa-graduation-cap me-2"></i>Student Templates By Level</h6>
                        <div class="template-grid">
                            @foreach ($studentTemplateVariants as $variant)
                                <div class="template-download-card">
                                    <span class="badge bg-info">{{ $variant['label'] }}</span>
                                    <h6>{{ $variant['label'] }} Student Template</h6>
                                    <p>{{ $variant['description'] }}</p>
                                    <a href="{{ $variant['download_url'] }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-1"></i> Download {{ $variant['label'] }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="guidelines-card mt-4">
                        <h6><i class="fas fa-lightbulb me-1"></i>Best Practices:</h6>
                        <ul class="mb-0">
                            <li>Always download and use these sample files as templates</li>
                            <li>Don't change the column headers - they must match exactly</li>
                            <li>Use the matching student template for Elementary, Middle School, or High School imports</li>
                            <li>Test with small files first (5-10 records)</li>
                            <li>Keep backup copies of your original data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasFlashAlert = @json(session('message') || session('error') || $errors->any());

            // File input handlers
            const fileInputConfigs = [
                { input: 'staff_upload_file', label: 'staffFileLabel', name: 'staffFileName' },
                { input: 'sponsors_upload_file', label: 'sponsorsFileLabel', name: 'sponsorsFileName' },
                { input: 'students_upload_file', label: 'studentsFileLabel', name: 'studentsFileName' },
                { input: 'admissions_upload_file', label: 'admissionsFileLabel', name: 'admissionsFileName' }
            ];

            fileInputConfigs.forEach(config => {
                const input = document.getElementById(config.input);
                const label = document.getElementById(config.label);
                const nameDisplay = document.getElementById(config.name);

                if (input && label) {
                    input.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            label.classList.add('has-file');
                            if (nameDisplay) {
                                nameDisplay.textContent = this.files[0].name;
                            }
                        } else {
                            label.classList.remove('has-file');
                            if (nameDisplay) {
                                nameDisplay.textContent = '';
                            }
                        }
                    });
                }
            });

            // Form submission handlers
            const importForms = [
                { form: 'staffForm', checkbox: 'deleteStaff', button: 'staffBtn', type: 'staff' },
                { form: 'sponsorsForm', checkbox: 'deleteSponsors', button: 'sponsorsBtn', type: 'sponsors' },
                { form: 'studentsForm', checkbox: 'deleteStudents', button: 'studentsBtn', type: 'students' },
                { form: 'admissionsForm', checkbox: 'deleteAdmissions', button: 'admissionsBtn', type: 'admissions' }
            ];

            importForms.forEach(config => {
                const checkbox = document.getElementById(config.checkbox);
                const button = document.getElementById(config.button);
                const form = document.getElementById(config.form);

                if (checkbox && button) {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            const confirmMsg = `WARNING: This will permanently delete all existing ${config.type} records. Are you sure you want to continue?`;
                            if (!confirm(confirmMsg)) {
                                this.checked = false;
                            } else {
                                button.querySelector('.btn-text').innerHTML = `<i class="fas fa-trash-alt me-1"></i> Clear Data & Import`;
                                button.classList.remove('btn-primary');
                                button.classList.add('btn-danger');
                            }
                        } else {
                            button.querySelector('.btn-text').innerHTML = `<i class="fas fa-upload me-1"></i> Import ${config.type.charAt(0).toUpperCase() + config.type.slice(1)}`;
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-primary');
                        }
                    });
                }

                if (form && button) {
                    form.addEventListener('submit', function(e) {
                        const fileInput = this.querySelector('input[type="file"]');
                        if (!fileInput.files.length) {
                            e.preventDefault();
                            alert('Please select an Excel file to import.');
                            return;
                        }

                        button.classList.add('loading');
                        button.disabled = true;
                    });

                    form.addEventListener('reset', function() {
                        const label = this.querySelector('.file-input-label');
                        const nameDisplay = this.querySelector('.file-name');
                        if (label) label.classList.remove('has-file');
                        if (nameDisplay) nameDisplay.textContent = '';

                        button.querySelector('.btn-text').innerHTML = `<i class="fas fa-upload me-1"></i> Import ${config.type.charAt(0).toUpperCase() + config.type.slice(1)}`;
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-primary');
                    });
                }
            });

            // Tab persistence
            const storageKey = 'activeImportTab';

            function setActiveTab(tabId) {
                localStorage.setItem(storageKey, tabId);
            }

            function getActiveTab() {
                return localStorage.getItem(storageKey);
            }

            const activeTab = getActiveTab();
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
                    setActiveTab(tabId);
                });
            });

            if (hasFlashAlert) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
@endsection

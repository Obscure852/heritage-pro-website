@extends('layouts.master')

@section('title')
    Upload SCORM Package - Learning Space
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 3px;
            padding: 24px;
            margin-bottom: 24px;
            color: white;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 12px;
        }

        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .help-text {
            background: #f5f3ff;
            padding: 12px 16px;
            border-left: 4px solid #8b5cf6;
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
            line-height: 1.5;
            margin: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .form-grid,
            .form-grid-3 {
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
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .required {
            color: #dc2626;
        }

        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            padding: 40px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.2s;
            cursor: pointer;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: #8b5cf6;
            background: #f5f3ff;
        }

        .upload-zone i {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .upload-zone.dragover i {
            color: #8b5cf6;
        }

        .upload-zone h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .upload-zone p {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 0;
        }

        .selected-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #ecfdf5;
            border: 1px solid #86efac;
            border-radius: 3px;
            margin-top: 16px;
        }

        .selected-file .file-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8b5cf6;
            font-size: 24px;
        }

        .selected-file .file-name {
            font-weight: 500;
            color: #1f2937;
        }

        .selected-file .file-size {
            font-size: 12px;
            color: #6b7280;
        }

        .info-box {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 3px;
            padding: 16px;
            margin: 0;
        }

        .info-box h6 {
            color: #6d28d9;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #ede9fe;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item .label {
            font-size: 13px;
            color: #6b7280;
        }

        .info-item .value {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
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
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
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

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
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
            Learning Space
        @endslot
        @slot('li_1_url')
            {{ route('lms.courses.edit', $module->course) }}
        @endslot
        @slot('li_2')
            {{ $module->course->title }}
        @endslot
        @slot('li_2_url')
            {{ route('lms.courses.edit', $module->course) }}
        @endslot
        @slot('title')
            Upload SCORM Package
        @endslot
    @endcomponent

    <div class="page-header">
        <h4><i class="fas fa-cube me-2"></i>Upload SCORM Package</h4>
        <p>Add interactive SCORM content to your course module</p>
        <div class="module-badge">
            <i class="fas fa-folder"></i>
            <span>{{ $module->title }}</span>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="help-text">
        <div class="help-title">SCORM Support</div>
        <div class="help-content">
            Upload a SCORM 1.2 or SCORM 2004 compliant package (.zip file). The system will automatically
            detect the version and configure the runtime environment.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-container">
                <form action="{{ route('lms.scorm.store', $module) }}" method="POST" enctype="multipart/form-data" id="scormForm" class="needs-validation" novalidate>
                    @csrf

                    <h3 class="section-title">Package Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="Enter content title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description') }}" placeholder="Brief description of the content">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h3 class="section-title">SCORM Package File</h3>
                    <input type="file" name="package" id="packageInput" accept=".zip" style="display: none;" required>
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-file-archive"></i>
                        <h5>Drop SCORM package here</h5>
                        <p>or click to browse<br>ZIP format, max 500MB</p>
                    </div>

                    <div class="selected-file" id="selectedFile" style="display: none;">
                        <div class="file-icon">
                            <i class="fas fa-file-archive"></i>
                        </div>
                        <div>
                            <div class="file-name" id="fileName"></div>
                            <div class="file-size" id="fileSize"></div>
                        </div>
                    </div>

                    @error('package')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror

                    <h3 class="section-title">Completion Settings</h3>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Mastery Score (%)</label>
                            <input type="number" name="mastery_score" class="form-control @error('mastery_score') is-invalid @enderror"
                                value="{{ old('mastery_score', 80) }}" min="0" max="100" placeholder="e.g., 80">
                            <div class="form-text">Score needed to pass</div>
                            @error('mastery_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time Limit (minutes)</label>
                            <input type="number" name="time_limit_minutes" class="form-control @error('time_limit_minutes') is-invalid @enderror"
                                value="{{ old('time_limit_minutes') }}" min="1" placeholder="No limit">
                            <div class="form-text">Optional time limit</div>
                            @error('time_limit_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Attempts</label>
                            <input type="number" name="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror"
                                value="{{ old('max_attempts') }}" min="1" placeholder="Unlimited">
                            <div class="form-text">Leave empty for unlimited</div>
                            @error('max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                            <span class="btn-text"><i class="fas fa-upload"></i> Upload Package</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Uploading...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-container" style="padding: 0;">
                <div class="info-box">
                    <h6><i class="fas fa-info-circle"></i> Module Info</h6>
                    <div class="info-item">
                        <span class="label">Course</span>
                        <span class="value">{{ Str::limit($module->course->title, 20) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Module</span>
                        <span class="value">{{ Str::limit($module->title, 20) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Formats</span>
                        <span class="value">SCORM 1.2 & 2004</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadZone = document.getElementById('uploadZone');
            const packageInput = document.getElementById('packageInput');
            const selectedFile = document.getElementById('selectedFile');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('scormForm');
            let isFilePickerOpen = false;

            uploadZone.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (isFilePickerOpen) return;
                isFilePickerOpen = true;
                packageInput.click();
                // Reset flag after a short delay (file picker closed)
                setTimeout(() => { isFilePickerOpen = false; }, 500);
            });

            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    packageInput.files = e.dataTransfer.files;
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });

            packageInput.addEventListener('change', () => {
                if (packageInput.files.length) {
                    handleFileSelect(packageInput.files[0]);
                }
            });

            function handleFileSelect(file) {
                if (!file.name.endsWith('.zip')) {
                    alert('Please select a ZIP file.');
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                uploadZone.style.display = 'none';
                selectedFile.style.display = 'flex';
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Form validation and loading state
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalidElement = form.querySelector(':invalid');
                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                        firstInvalidElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } else {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }

                form.classList.add('was-validated');
            });
        });
    </script>
@endsection

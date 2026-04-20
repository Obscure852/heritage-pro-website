@extends('layouts.master')

@section('title')
    Upload H5P Content
@endsection

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .form-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .form-body {
            padding: 24px;
        }

        .help-text {
            background: #ecfeff;
            padding: 12px 16px;
            border-left: 4px solid #06b6d4;
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

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
            border-radius: 3px 3px 0 0 !important;
        }

        .card-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-label .required {
            color: #dc2626;
        }

        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
            outline: none;
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
            color: white;
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
            border-color: #06b6d4;
            background: #ecfeff;
        }

        .upload-zone i {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .upload-zone.dragover i {
            color: #06b6d4;
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
            color: #06b6d4;
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

        .content-types {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
        }

        .content-type-badge {
            padding: 6px 10px;
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 3px;
            font-size: 12px;
            color: #0d9488;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.modules.edit', $module) }}">{{ $module->title }}</a>
        @endslot
        @slot('title')
            Upload H5P Content
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('lms.h5p.store', $module) }}" method="POST" enctype="multipart/form-data" id="h5pForm">
        @csrf

        <div class="form-container">
            <div class="form-header">
                <h3 style="margin:0;">Upload H5P Content</h3>
                <p style="margin:6px 0 0 0; opacity:.9;">Add interactive H5P content to {{ $module->title }}</p>
            </div>

            <div class="form-body">
                <div class="help-text">
                    <div class="help-title">H5P Interactive Content</div>
                    <div class="help-content">
                        Upload an H5P package (.h5p file) to add interactive content. H5P supports various content types
                        including interactive videos, quizzes, presentations, and more.
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Content Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Title <span class="required">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title') }}" placeholder="Enter content title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="Brief description of the content">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">H5P Package File <span class="required">*</span></h5>
                            </div>
                            <div class="card-body">
                                <div class="upload-zone" id="uploadZone">
                                    <i class="fas fa-cube"></i>
                                    <h5>Drop H5P package here</h5>
                                    <p>or click to browse<br>.h5p format, max 500MB</p>
                                    <input type="file" name="package" id="packageInput" accept=".h5p,.zip"
                                        style="display: none;" required>
                                </div>

                                <div class="selected-file" id="selectedFile" style="display: none;">
                                    <div class="file-icon">
                                        <i class="fas fa-cube"></i>
                                    </div>
                                    <div>
                                        <div class="file-name" id="fileName"></div>
                                        <div class="file-size" id="fileSize"></div>
                                    </div>
                                </div>

                                @error('package')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Module Info</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Course:</strong> {{ $module->course->title }}</p>
                                <p class="mb-0"><strong>Module:</strong> {{ $module->title }}</p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Supported Content Types</h5>
                            </div>
                            <div class="card-body">
                                <div class="content-types">
                                    <span class="content-type-badge">Interactive Video</span>
                                    <span class="content-type-badge">Course Presentation</span>
                                    <span class="content-type-badge">Quiz</span>
                                    <span class="content-type-badge">Drag & Drop</span>
                                    <span class="content-type-badge">Fill in Blanks</span>
                                    <span class="content-type-badge">Timeline</span>
                                    <span class="content-type-badge">Flashcards</span>
                                    <span class="content-type-badge">Memory Game</span>
                                </div>
                                <p class="mt-3 mb-0" style="font-size: 12px; color: #6b7280;">
                                    Create H5P content at <a href="https://h5p.org" target="_blank">h5p.org</a> or use an H5P editor.
                                </p>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-1"></i> Upload Content
                            </button>
                            <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const packageInput = document.getElementById('packageInput');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const submitBtn = document.getElementById('submitBtn');

        uploadZone.addEventListener('click', () => packageInput.click());

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
            if (!file.name.endsWith('.h5p') && !file.name.endsWith('.zip')) {
                alert('Please select an H5P or ZIP file.');
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

        document.getElementById('h5pForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';
        });
    </script>
@endsection

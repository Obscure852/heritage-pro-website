@extends('layouts.master')

@section('title')
    Submit Assignment
@endsection

@section('css')
    <style>
        .submit-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 28px;
            border-radius: 3px;
            margin-bottom: 24px;
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
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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

        .file-drop-zone {
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            padding: 40px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.2s;
            cursor: pointer;
        }

        .file-drop-zone:hover,
        .file-drop-zone.dragover {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .file-drop-zone i {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .file-drop-zone.dragover i {
            color: #f59e0b;
        }

        .file-drop-zone h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .file-drop-zone p {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 0;
        }

        .selected-files {
            margin-top: 16px;
        }

        .selected-file {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .selected-file .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .selected-file .file-icon {
            width: 36px;
            height: 36px;
            background: #f3f4f6;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }

        .selected-file .file-name {
            font-weight: 500;
            color: #374151;
        }

        .selected-file .file-size {
            font-size: 12px;
            color: #6b7280;
        }

        .selected-file .remove-file {
            color: #ef4444;
            cursor: pointer;
            padding: 4px 8px;
        }

        .selected-file .remove-file:hover {
            background: #fee2e2;
            border-radius: 3px;
        }

        .existing-files {
            background: #ecfdf5;
            border: 1px solid #86efac;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .existing-files h6 {
            color: #065f46;
            margin-bottom: 12px;
        }

        .deadline-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .deadline-warning i {
            color: #d97706;
            font-size: 20px;
        }

        .deadline-warning .text {
            font-size: 14px;
            color: #92400e;
        }

        .late-warning {
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }

        .late-warning i {
            color: #dc2626;
        }

        .late-warning .text {
            color: #991b1b;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Submit Assignment
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Submission Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="submit-header">
        <h3 style="margin:0;">{{ $assignment->title }}</h3>
        <p style="margin:8px 0 0 0; opacity:0.9;">
            {{ $assignment->contentItem->module->course->title }} / {{ $assignment->contentItem->module->title }}
        </p>
    </div>

    @if ($assignment->due_date)
        @if ($assignment->is_overdue && $assignment->allow_late_submissions)
            <div class="deadline-warning late-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="text">
                    <strong>Late Submission</strong><br>
                    This assignment is past due. A {{ $assignment->late_penalty_percent }}% late penalty will be applied.
                </div>
            </div>
        @elseif (!$assignment->is_overdue)
            <div class="deadline-warning">
                <i class="fas fa-clock"></i>
                <div class="text">
                    <strong>Due {{ $assignment->due_date->diffForHumans() }}</strong><br>
                    {{ $assignment->due_date->format('l, F j, Y \a\t g:i A') }}
                </div>
            </div>
        @endif
    @endif

    <form action="{{ route('lms.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data"
        id="submissionForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                @if (in_array($assignment->submission_type, ['file', 'both']))
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                File Upload
                                @if ($assignment->submission_type === 'file')
                                    <span class="required">*</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @if ($submission && $submission->attachedFiles->count())
                                <div class="existing-files">
                                    <h6><i class="fas fa-check-circle me-1"></i> Previously Uploaded Files</h6>
                                    @foreach ($submission->attachedFiles as $file)
                                        <div class="selected-file">
                                            <div class="file-info">
                                                <div class="file-icon">
                                                    <i class="{{ $file->icon_class }}"></i>
                                                </div>
                                                <div>
                                                    <div class="file-name">{{ $file->original_name }}</div>
                                                    <div class="file-size">{{ $file->formatted_size }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <small class="text-muted">Upload new files to add to your submission</small>
                                </div>
                            @endif

                            <div class="file-drop-zone" id="dropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <h5>Drop files here or click to browse</h5>
                                <p>
                                    Allowed:
                                    {{ implode(', ', $assignment->allowed_file_types ?? ['pdf', 'doc', 'docx']) }}<br>
                                    Max size: {{ $assignment->max_file_size_mb }}MB per file<br>
                                    Max files: {{ $assignment->max_files }}
                                </p>
                                <input type="file" name="files[]" id="fileInput" multiple
                                    accept=".{{ implode(',.', $assignment->allowed_file_types ?? ['pdf', 'doc', 'docx']) }}"
                                    style="display: none;">
                            </div>

                            <div class="selected-files" id="selectedFiles"></div>
                        </div>
                    </div>
                @endif

                @if (in_array($assignment->submission_type, ['text', 'both']))
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                Text Submission
                                @if ($assignment->require_submission_text)
                                    <span class="required">*</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea name="submission_text" class="form-control @error('submission_text') is-invalid @enderror" rows="12"
                                placeholder="Enter your response here..." {{ $assignment->require_submission_text ? 'required' : '' }}>{{ old('submission_text', $submission?->submission_text) }}</textarea>
                            @error('submission_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                @if ($assignment->require_submission_text)
                                    Text submission is required.
                                @else
                                    Text submission is optional.
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Submission Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Assignment:</strong><br>
                            {{ $assignment->title }}
                        </div>
                        <div class="mb-3">
                            <strong>Points:</strong><br>
                            {{ $assignment->max_points }} points possible
                        </div>
                        @if ($assignment->due_date)
                            <div class="mb-3">
                                <strong>Due Date:</strong><br>
                                {{ $assignment->due_date->format('M j, Y g:i A') }}
                            </div>
                        @endif
                        @if ($submission)
                            <div class="mb-3">
                                <strong>Attempt:</strong><br>
                                {{ $submission->attempt_number + 1 }}
                                @if ($assignment->max_attempts)
                                    of {{ $assignment->max_attempts }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg btn-loading" id="submitBtn">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane me-1"></i>
                            {{ $submission ? 'Update Submission' : 'Submit Assignment' }}
                        </span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Submitting...
                        </span>
                    </button>
                    <a href="{{ route('lms.assignments.show', $assignment) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

                <div class="alert alert-info mt-3" style="font-size: 13px;">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Important:</strong> Make sure your work is complete before submitting.
                    @if ($assignment->allow_resubmission)
                        You can update your submission until the deadline.
                    @else
                        You cannot modify your submission after submitting.
                    @endif
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        // Loading animation on form submit
        document.getElementById('submissionForm').addEventListener('submit', function() {
            var submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const selectedFilesContainer = document.getElementById('selectedFiles');
        const maxFiles = {{ $assignment->max_files }};
        const maxSizeMB = {{ $assignment->max_file_size_mb }};
        let selectedFiles = [];

        // Click to open file browser
        dropZone.addEventListener('click', () => fileInput.click());

        // Drag and drop events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // File input change
        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
        });

        function handleFiles(files) {
            for (let file of files) {
                if (selectedFiles.length >= maxFiles) {
                    alert(`Maximum ${maxFiles} files allowed.`);
                    break;
                }

                if (file.size > maxSizeMB * 1024 * 1024) {
                    alert(`File "${file.name}" exceeds ${maxSizeMB}MB limit.`);
                    continue;
                }

                selectedFiles.push(file);
            }

            updateFileDisplay();
            updateFileInput();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFileDisplay();
            updateFileInput();
        }

        function updateFileDisplay() {
            selectedFilesContainer.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'selected-file';
                div.innerHTML = `
                    <div class="file-info">
                        <div class="file-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div>
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${formatFileSize(file.size)}</div>
                        </div>
                    </div>
                    <span class="remove-file" onclick="removeFile(${index})">
                        <i class="fas fa-times"></i>
                    </span>
                `;
                selectedFilesContainer.appendChild(div);
            });
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
@endsection

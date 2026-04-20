@extends('layouts.master')
@section('title') Upload New Version - {{ $document->title }} @endsection
@section('css')
    <link href="{{ URL::asset('/assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet">
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        /* Current document info panel */
        .doc-info-panel {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .doc-info-item {
            display: flex;
            flex-direction: column;
        }

        .doc-info-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .doc-info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        /* Version type radio group */
        .version-type-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .version-type-option {
            flex: 1;
            position: relative;
        }

        .version-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .version-type-option label {
            display: block;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .version-type-option label:hover {
            border-color: #93c5fd;
            background: #f0f7ff;
        }

        .version-type-option input[type="radio"]:checked + label {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .version-type-label {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            display: block;
            margin-bottom: 2px;
        }

        .version-type-desc {
            font-size: 12px;
            color: #6b7280;
        }

        /* Version preview box */
        .version-preview {
            background: #eff6ff;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1e40af;
            font-weight: 500;
        }

        .version-preview i {
            margin-right: 6px;
        }

        /* Dropzone overrides */
        .dropzone {
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            background: #fafafa;
            min-height: 180px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .dropzone:hover {
            border-color: #3b82f6;
            background: #f0f7ff;
        }

        .dropzone.dz-drag-hover {
            border-color: #2563eb;
            background: #e8f0fe;
        }

        .dropzone .dz-message {
            margin: 2em 0;
            color: #6b7280;
            font-size: 15px;
        }

        .dropzone .dz-message .dz-button {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 15px;
            cursor: pointer;
        }

        .dropzone .dz-preview .dz-progress {
            height: 6px;
            border-radius: 3px;
        }

        .dropzone .dz-preview .dz-progress .dz-upload {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .version-type-group {
                flex-direction: column;
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
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('documents.show', $document) }}">{{ Str::limit($document->title, 30) }}</a>
        @endslot
        @slot('title')
            Upload New Version
        @endslot
    @endcomponent

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Upload New Version</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Version Upload</div>
            <div class="help-content">
                Upload a new version of <strong>{{ $document->title }}</strong>. Choose between a minor or major version increment. Any file type is accepted.
            </div>
        </div>

        {{-- Current Document Info --}}
        <div class="doc-info-panel">
            <div class="doc-info-item">
                <span class="doc-info-label">Document</span>
                <span class="doc-info-value">{{ $document->title }}</span>
            </div>
            <div class="doc-info-item">
                <span class="doc-info-label">Current Version</span>
                <span class="doc-info-value">v{{ $document->current_version }}</span>
            </div>
            <div class="doc-info-item">
                <span class="doc-info-label">File Type</span>
                <span class="doc-info-value">{{ strtoupper($document->extension ?: 'Unknown') }}</span>
            </div>
        </div>

        {{-- File Upload --}}
        <h3 class="section-title">File</h3>

        <form id="version-upload-form" action="{{ route('documents.versions.store', $document) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="version-dropzone" class="dropzone">
                <div class="dz-message">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #9ca3af; display: block; margin-bottom: 12px;"></i>
                    Drop file here or click to upload
                    <br><small class="text-muted">Maximum file size: 50MB</small>
                </div>
            </div>
        </form>

        {{-- Version Type --}}
        <h3 class="section-title" style="margin-top: 24px;">Version Type</h3>

        <div class="version-type-group">
            <div class="version-type-option">
                <input type="radio" name="version_type" id="version-minor" value="minor" checked>
                <label for="version-minor">
                    <span class="version-type-label">Minor Update</span>
                    <span class="version-type-desc">{{ $document->current_version }} &rarr; {{ $nextMinor }}</span>
                </label>
            </div>
            <div class="version-type-option">
                <input type="radio" name="version_type" id="version-major" value="major">
                <label for="version-major">
                    <span class="version-type-label">Major Update</span>
                    <span class="version-type-desc">{{ $document->current_version }} &rarr; {{ $nextMajor }}</span>
                </label>
            </div>
        </div>

        {{-- Version Preview --}}
        <div class="version-preview" id="version-preview">
            <i class="fas fa-info-circle"></i>
            This will create version <strong id="preview-version">{{ $nextMinor }}</strong>
        </div>

        {{-- Version Notes --}}
        <h3 class="section-title">Notes</h3>

        <textarea name="version_notes" id="version-notes" class="form-control" rows="3" placeholder="Describe what changed in this version..." maxlength="5000"></textarea>
        <small class="text-muted">Optional but recommended — helps others understand what changed</small>

        {{-- Form Actions --}}
        <div class="form-actions">
            <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                <i class="bx bx-x"></i> Cancel
            </a>
            <button type="button" id="upload-btn" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Upload Version</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Uploading...
                </span>
            </button>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;

        var nextMinor = '{{ $nextMinor }}';
        var nextMajor = '{{ $nextMajor }}';

        var uploadDropzone = new Dropzone('#version-dropzone', {
            url: '{{ route("documents.versions.store", $document) }}',
            paramName: 'file',
            maxFiles: 1,
            maxFilesize: 50,
            addRemoveLinks: true,
            parallelUploads: 1,
            autoProcessQueue: false,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            dictDefaultMessage: '<i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #9ca3af; display: block; margin-bottom: 12px;"></i> Drop file here or click to upload<br><small class="text-muted">Maximum file size: 50MB</small>',
            dictRemoveFile: 'Remove',
            dictFileTooBig: 'File is too large (@{{filesize}}MB). Maximum allowed size: @{{maxFilesize}}MB.',
            dictMaxFilesExceeded: 'Only one file can be uploaded per version.',

            init: function() {
                var dz = this;

                // Enforce single file - remove previous when new file added
                dz.on('addedfile', function(file) {
                    if (dz.files.length > 1) {
                        dz.removeFile(dz.files[0]);
                    }
                });

                // Append version_type and version_notes to the upload request
                dz.on('sending', function(file, xhr, formData) {
                    var versionType = document.querySelector('input[name="version_type"]:checked').value;
                    formData.append('version_type', versionType);

                    var versionNotes = document.getElementById('version-notes').value;
                    if (versionNotes) {
                        formData.append('version_notes', versionNotes);
                    }
                });

                // On success, redirect to show page
                dz.on('success', function(file, response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'New version uploaded successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = '{{ route("documents.show", $document) }}';
                    }, 1500);
                });

                // On error, show SweetAlert and re-enable submit
                dz.on('error', function(file, errorMessage, xhr) {
                    var msg = 'Upload failed.';
                    if (typeof errorMessage === 'object' && errorMessage.message) {
                        msg = errorMessage.message;
                    } else if (typeof errorMessage === 'object' && errorMessage.errors) {
                        var errors = Object.values(errorMessage.errors).flat();
                        msg = errors.join(', ');
                    } else if (typeof errorMessage === 'string') {
                        msg = errorMessage;
                    }
                    Swal.fire('Upload Error', msg, 'error');

                    var uploadBtn = document.getElementById('upload-btn');
                    uploadBtn.classList.remove('loading');
                    uploadBtn.disabled = false;
                });
            }
        });

        // Radio button change handler - update version preview dynamically
        document.querySelectorAll('input[name="version_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var previewEl = document.getElementById('preview-version');
                if (this.value === 'major') {
                    previewEl.textContent = nextMajor;
                } else {
                    previewEl.textContent = nextMinor;
                }
            });
        });

        // Upload button click handler
        document.getElementById('upload-btn').addEventListener('click', function() {
            var queuedFiles = uploadDropzone.getQueuedFiles();
            if (queuedFiles.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No File Selected',
                    text: 'Please drag and drop a file or click the upload area to select a file.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            // Add loading state
            var uploadBtn = document.getElementById('upload-btn');
            uploadBtn.classList.add('loading');
            uploadBtn.disabled = true;

            // Start processing the queue
            uploadDropzone.processQueue();
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
@endsection

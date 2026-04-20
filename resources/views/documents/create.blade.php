@extends('layouts.master')
@section('title') Create Document @endsection
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
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

        /* Select2 sizing to match form-control */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            min-height: 42px;
            padding: 4px 8px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 4px;
        }

        /* Dropzone overrides */
        .dropzone {
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            background: #fafafa;
            min-height: 200px;
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

        .file-count-badge {
            background: #e8f0fe;
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            display: none;
        }

        .file-count-badge.visible {
            display: inline-block;
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
            <a class="text-muted font-size-14" href="{{ $uploadRedirectUrl }}">Documents</a>
        @endslot
        @slot('title')
            Create Document
        @endslot
    @endcomponent

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Create Document</h1>
        </div>

        @if($currentFolder)
            <div class="help-text" style="border-left-color: #2563eb;">
                <div class="help-title"><i class="fas fa-folder-open me-1"></i> Upload Destination</div>
                <div class="help-content">
                    New files will be added to <strong>{{ $currentFolder->name }}</strong>.
                </div>
            </div>
        @endif

        {{-- Storage Quota Info --}}
        @if(isset($userQuota))
            <div id="quota-help-block">
            @if($userQuota->is_unlimited)
                <div class="help-text" style="border-left-color: #10b981;">
                    <div class="help-title"><i class="fas fa-infinity me-1"></i> Unlimited Storage</div>
                    <div class="help-content">You have unlimited document storage.</div>
                </div>
            @elseif($userQuota->usage_percent > 100)
                <div class="help-text" style="background: #fef2f2; border-left-color: #ef4444;">
                    <div class="help-title" style="color: #991b1b;"><i class="fas fa-exclamation-triangle me-1"></i> Storage Quota Exceeded</div>
                    <div class="help-content" style="color: #991b1b;">
                        You have used {{ $usedFormatted }} of {{ $totalFormatted }} ({{ number_format($userQuota->usage_percent, 0) }}%).
                        @if($userQuota->usage_percent > 110)
                            Uploads are blocked. Please delete unused documents or contact an administrator.
                        @else
                            You may still upload but are over your quota. Please free up space soon.
                        @endif
                    </div>
                </div>
            @elseif($userQuota->usage_percent >= 80)
                <div class="help-text" style="background: #fffbeb; border-left-color: #f59e0b;">
                    <div class="help-title" style="color: #92400e;"><i class="fas fa-exclamation-circle me-1"></i> Storage Warning</div>
                    <div class="help-content" style="color: #92400e;">
                        You have {{ $remainingFormatted }} remaining ({{ $usedFormatted }} of {{ $totalFormatted }} used).
                        Consider removing unused documents to free up space.
                    </div>
                </div>
            @else
                <div class="help-text" style="border-left-color: #10b981;">
                    <div class="help-title"><i class="fas fa-hdd me-1"></i> Storage Available</div>
                    <div class="help-content">You have {{ $remainingFormatted }} of {{ $totalFormatted }} remaining.</div>
                </div>
            @endif
            </div>
        @endif

        @php
            $allowedExtensionsLabel = !empty($allowedExtensions ?? [])
                ? collect($allowedExtensions)->map(fn($extension) => strtoupper($extension))->implode(', ')
                : 'No file types configured';
        @endphp

        <div class="help-text" id="allowed-files-help">
            <div class="help-title">Allowed File Types</div>
            <div class="help-content">
                {{ $allowedExtensionsLabel }} — Maximum file size: {{ $uploadMaxSizeMb }}MB per file.
                You can upload multiple files at once. Files keep their own names during upload.
            </div>
        </div>

        <div class="help-text d-none" id="external-source-help" style="border-left-color: #f59e0b; background: #fffbeb;">
            <div class="help-title" style="color: #92400e;">Remote document link</div>
            <div class="help-content" style="color: #92400e;">
                URL-backed documents do not consume upload storage and are redirected to their remote source when opened.
                Anyone who learns the raw remote URL can access it outside document sharing controls.
            </div>
        </div>

        {{-- Metadata Form Section --}}
        <h3 class="section-title">Document Details</h3>

        <div class="form-grid">
            <div class="form-group">
                <label for="source_type" class="form-label">Document Source</label>
                <select id="source_type" class="form-select">
                    <option value="upload">Upload file</option>
                    <option value="external_url">Online URL</option>
                </select>
                <small class="text-muted">Choose whether this document is uploaded here or linked from a remote URL.</small>
            </div>
            <div class="form-group">
                <label for="title" class="form-label" id="title-label">Custom Title</label>
                <input type="text" id="title" class="form-control" placeholder="Optional for a single file upload">
                <small class="text-muted" id="title-help-text">Leave blank to keep the filename. Multiple files keep their own names.</small>
            </div>
        </div>

        <div class="form-grid" style="margin-top: 16px;">
            <div class="form-group" id="external-url-group" style="display: none;">
                <label for="external_url" class="form-label">Online Document URL</label>
                <input type="url" id="external_url" class="form-control" placeholder="https://example.com/document.pdf">
                <small class="text-muted">Paste the public HTTP or HTTPS URL for the remote syllabus or document.</small>
            </div>
            <div class="form-group">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" class="form-select">
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid" style="margin-top: 16px;">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" class="form-control" rows="3" placeholder="Enter a description for the document(s)..."></textarea>
            </div>
        </div>

        <div class="form-grid" style="margin-top: 16px;">
            <div class="form-group">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="date" id="expiry_date" class="form-control">
                <small class="text-muted">Optional. Set a date when this document should expire.</small>
            </div>
            <div class="form-group">
                <label for="tag-select" class="form-label">Tags</label>
                <select id="tag-select" name="tag_ids[]" class="form-select" multiple>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Search existing tags or type to create new ones</small>
            </div>
        </div>

        <div id="upload-section">
            {{-- Dropzone Section --}}
            <h3 class="section-title">
                Files
                <span id="file-count-badge" class="file-count-badge ms-2">0 files queued</span>
            </h3>

            <div id="document-dropzone" class="dropzone"></div>
        </div>

        {{-- Upload Button --}}
        <div class="form-actions">
            <a href="{{ $uploadRedirectUrl }}" class="btn btn-secondary">
                <i class="bx bx-x"></i> Cancel
            </a>
            <button type="button" id="upload-btn" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Upload Documents</span>
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
        const uploadMaxSizeMb = @json($uploadMaxSizeMb);
        const acceptedFilesCsv = @json($acceptedFilesCsv);
        const currentFolderId = @json($currentFolder?->id);
        const uploadRedirectUrl = @json($uploadRedirectUrl);
        const documentShowUrlTemplate = @json(route('documents.show', ['document' => '__DOCUMENT__']));
        const defaultDropzoneMessage = '<i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #9ca3af; display: block; margin-bottom: 12px;"></i> Drag files here or click to browse<br><small class="text-muted">Maximum file size: ' + uploadMaxSizeMb + 'MB per file</small>';
        let currentBatchFiles = [];

        const uploadDropzone = new Dropzone('#document-dropzone', {
            url: '{{ route("documents.store") }}',
            paramName: 'file',
            maxFilesize: uploadMaxSizeMb,
            acceptedFiles: acceptedFilesCsv || null,
            addRemoveLinks: true,
            parallelUploads: 1,
            autoProcessQueue: false,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            dictDefaultMessage: defaultDropzoneMessage,
            dictRemoveFile: 'Remove',
            dictFileTooBig: 'File is too large (@{{filesize}}MB). Maximum allowed size: @{{maxFilesize}}MB.',
            dictInvalidFileType: 'This file type is not allowed.',

            init: function() {
                var dz = this;

                function parseUploadError(errorMessage, xhr) {
                    if (typeof errorMessage === 'object' && errorMessage !== null) {
                        if (errorMessage.message) {
                            return errorMessage.message;
                        }

                        if (errorMessage.errors) {
                            return Object.values(errorMessage.errors).flat().join(', ');
                        }

                        if (errorMessage.error && errorMessage.error.message) {
                            return errorMessage.error.message;
                        }
                    }

                    if (typeof errorMessage === 'string' && errorMessage.trim() !== '') {
                        return errorMessage;
                    }

                    if (xhr && xhr.responseText) {
                        try {
                            var payload = JSON.parse(xhr.responseText);
                            if (payload.message) {
                                return payload.message;
                            }
                            if (payload.errors) {
                                return Object.values(payload.errors).flat().join(', ');
                            }
                            if (payload.error && payload.error.message) {
                                return payload.error.message;
                            }
                        } catch (e) {
                            // Ignore parse error and fall back to generic message
                        }
                    }

                    return 'Upload failed.';
                }

                // Append metadata to each file upload
                dz.on('sending', function(file, xhr, formData) {
                    var titleInput = document.getElementById('title').value.trim();
                    var preserveOriginalName = currentBatchFiles.length > 1;
                    if (!preserveOriginalName && titleInput !== '') {
                        formData.append('title', titleInput);
                    }
                    if (preserveOriginalName) {
                        formData.append('preserve_original_name', '1');
                    }
                    formData.append('description', document.getElementById('description').value);

                    var categoryId = document.getElementById('category_id').value;
                    if (categoryId) {
                        formData.append('category_id', categoryId);
                    }

                    if (currentFolderId) {
                        formData.append('folder_id', currentFolderId);
                    }

                    var expiryDate = document.getElementById('expiry_date').value;
                    if (expiryDate) {
                        formData.append('expiry_date', expiryDate);
                    }

                    // Collect Select2 tag values - separate new tags from existing IDs
                    var selectedTags = $('#tag-select').val() || [];
                    selectedTags.forEach(function(val) {
                        if (String(val).startsWith('new:')) {
                            formData.append('new_tags[]', val.substring(4));
                        } else {
                            formData.append('tag_ids[]', val);
                        }
                    });
                });

                // Per-file success
                dz.on('success', function(file, response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Document uploaded successfully.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    updateFileCount();
                    updateTitleInputState();
                });

                // Per-file error
                dz.on('error', function(file, errorMessage, xhr) {
                    var msg = parseUploadError(errorMessage, xhr);
                    Swal.fire('Upload Error', msg, 'error');
                    updateFileCount();
                    updateTitleInputState();
                });

                // All files in queue processed
                dz.on('queuecomplete', function() {
                    var uploadBtn = document.getElementById('upload-btn');
                    uploadBtn.classList.remove('loading');
                    uploadBtn.disabled = false;

                    var uploadedCount = 0;
                    var failedCount = 0;
                    var skippedCount = 0;

                    currentBatchFiles.forEach(function(file) {
                        if (file.status === Dropzone.SUCCESS) {
                            uploadedCount++;
                        } else if (file.status === Dropzone.ERROR) {
                            failedCount++;
                        } else {
                            skippedCount++;
                        }
                    });

                    if (failedCount === 0 && uploadedCount > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Upload Batch Complete',
                            html:
                                '<strong>Uploaded:</strong> ' + uploadedCount + '<br>' +
                                '<strong>Failed:</strong> ' + failedCount + '<br>' +
                                '<strong>Skipped:</strong> ' + skippedCount + '<br><br>Redirecting to documents...',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function() {
                            window.location.href = uploadRedirectUrl;
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Upload Batch Complete',
                            html:
                                '<strong>Uploaded:</strong> ' + uploadedCount + '<br>' +
                                '<strong>Failed:</strong> ' + failedCount + '<br>' +
                                '<strong>Skipped:</strong> ' + skippedCount,
                            confirmButtonColor: '#3b82f6'
                        });
                    }

                    currentBatchFiles = [];
                    updateTitleInputState();
                });

                // Track file count
                dz.on('addedfile', function() {
                    updateFileCount();
                    updateTitleInputState();
                });

                dz.on('removedfile', function() {
                    updateFileCount();
                    updateTitleInputState();
                });

                // Process next file after each upload completes (since parallelUploads=1)
                dz.on('complete', function(file) {
                    if (dz.getQueuedFiles().length > 0) {
                        dz.processQueue();
                    }
                });
            }
        });

        // Update file count badge
        function updateFileCount() {
            var queued = uploadDropzone.getQueuedFiles().length;
            var badge = document.getElementById('file-count-badge');
            if (queued > 0) {
                badge.textContent = queued + ' file' + (queued > 1 ? 's' : '') + ' queued';
                badge.classList.add('visible');
            } else {
                badge.classList.remove('visible');
            }
        }

        function updateTitleInputState() {
            var titleInput = document.getElementById('title');
            var sourceType = document.getElementById('source_type').value;
            var titleHelpText = document.getElementById('title-help-text');
            var titleLabel = document.getElementById('title-label');

            if (sourceType === 'external_url') {
                titleInput.disabled = false;
                titleInput.placeholder = 'Required for a remote document';
                titleLabel.textContent = 'Title';
                titleHelpText.textContent = 'Required. This is the name shown inside the documents module.';
                return;
            }

            var activeFiles = uploadDropzone.files.filter(function(file) {
                return file.status !== Dropzone.SUCCESS && file.status !== Dropzone.CANCELED;
            }).length;
            var isMultiUpload = activeFiles > 1;

            titleInput.disabled = isMultiUpload;
            titleLabel.textContent = 'Custom Title';
            titleInput.placeholder = isMultiUpload
                ? 'Multiple files keep their original names'
                : 'Optional for a single file upload';
            titleHelpText.textContent = isMultiUpload
                ? 'Multiple files keep their own names during upload.'
                : 'Leave blank to keep the filename. Multiple files keep their own names.';
        }

        function updateSourceMode() {
            var sourceType = document.getElementById('source_type').value;
            var uploadSection = document.getElementById('upload-section');
            var externalUrlGroup = document.getElementById('external-url-group');
            var allowedFilesHelp = document.getElementById('allowed-files-help');
            var externalSourceHelp = document.getElementById('external-source-help');
            var quotaHelpBlock = document.getElementById('quota-help-block');
            var uploadBtn = document.getElementById('upload-btn');
            var externalUrlInput = document.getElementById('external_url');

            if (sourceType === 'external_url') {
                uploadSection.style.display = 'none';
                externalUrlGroup.style.display = 'block';
                externalUrlInput.disabled = false;
                allowedFilesHelp.classList.add('d-none');
                externalSourceHelp.classList.remove('d-none');
                if (quotaHelpBlock) {
                    quotaHelpBlock.classList.add('d-none');
                }
                uploadBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-link"></i> Save Document Link';
                uploadBtn.querySelector('.btn-spinner').lastChild.textContent = 'Saving...';
            } else {
                uploadSection.style.display = '';
                externalUrlGroup.style.display = 'none';
                externalUrlInput.disabled = true;
                allowedFilesHelp.classList.remove('d-none');
                externalSourceHelp.classList.add('d-none');
                if (quotaHelpBlock) {
                    quotaHelpBlock.classList.remove('d-none');
                }
                uploadBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-save"></i> Upload Documents';
                uploadBtn.querySelector('.btn-spinner').lastChild.textContent = 'Uploading...';
            }

            updateTitleInputState();
        }

        function appendCommonMetadata(formData) {
            formData.append('source_type', document.getElementById('source_type').value);
            formData.append('title', document.getElementById('title').value.trim());
            formData.append('description', document.getElementById('description').value);

            var categoryId = document.getElementById('category_id').value;
            if (categoryId) {
                formData.append('category_id', categoryId);
            }

            if (currentFolderId) {
                formData.append('folder_id', currentFolderId);
            }

            var expiryDate = document.getElementById('expiry_date').value;
            if (expiryDate) {
                formData.append('expiry_date', expiryDate);
            }

            var selectedTags = $('#tag-select').val() || [];
            selectedTags.forEach(function(val) {
                if (String(val).startsWith('new:')) {
                    formData.append('new_tags[]', val.substring(4));
                } else {
                    formData.append('tag_ids[]', val);
                }
            });
        }

        function submitExternalUrlDocument() {
            var uploadBtn = document.getElementById('upload-btn');
            var formData = new FormData();
            appendCommonMetadata(formData);
            formData.append('external_url', document.getElementById('external_url').value.trim());

            uploadBtn.classList.add('loading');
            uploadBtn.disabled = true;

            fetch('{{ route("documents.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async function(response) {
                var payload = await response.json().catch(function() { return {}; });
                if (!response.ok) {
                    throw payload;
                }
                return payload;
            })
            .then(function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Document Created',
                    text: response.message || 'Document link saved successfully.',
                    timer: 1200,
                    showConfirmButton: false
                });

                var destination = response.document && response.document.id
                    ? documentShowUrlTemplate.replace('__DOCUMENT__', response.document.id)
                    : uploadRedirectUrl;

                setTimeout(function() {
                    window.location.href = destination;
                }, 1200);
            })
            .catch(function(error) {
                var message = 'Unable to save the document link.';
                if (error && error.errors) {
                    message = Object.values(error.errors).flat().join(', ');
                } else if (error && error.message) {
                    message = error.message;
                }

                Swal.fire('Save Error', message, 'error');
            })
            .finally(function() {
                uploadBtn.classList.remove('loading');
                uploadBtn.disabled = false;
            });
        }

        // Upload button click handler — registered immediately (before Select2)
        document.getElementById('upload-btn').addEventListener('click', function() {
            var sourceType = document.getElementById('source_type').value;
            if (sourceType === 'external_url') {
                submitExternalUrlDocument();
                return;
            }

            var queuedFiles = uploadDropzone.getQueuedFiles();
            if (queuedFiles.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Files Selected',
                    text: 'Please drag and drop files or click the upload area to select files.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            // Add loading state
            var uploadBtn = document.getElementById('upload-btn');
            uploadBtn.classList.add('loading');
            uploadBtn.disabled = true;
            currentBatchFiles = queuedFiles.slice();

            // Start processing the queue
            uploadDropzone.processQueue();
        });

        // Initialize Select2 after it's loaded (it's included after @yield('script') in vendor-scripts)
        $(document).ready(function() {
            document.getElementById('source_type').addEventListener('change', updateSourceMode);
            updateTitleInputState();
            updateSourceMode();
            $('#tag-select').select2({
                placeholder: 'Search or create tags...',
                allowClear: true,
                width: '100%',
                multiple: true,
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') return null;
                    return {
                        id: 'new:' + term,
                        text: term,
                        newTag: true
                    };
                },
                templateResult: function(data) {
                    if (data.newTag) {
                        return $('<span><i class="fas fa-plus-circle text-success me-1"></i> Create: "' + data.text + '"</span>');
                    }
                    return data.text;
                }
            });
        });
    </script>
@endsection

@extends('layouts.master')

@section('title')
    Create Assignment - Learning Space
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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

        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .help-text {
            background: #eff6ff;
            padding: 12px 16px;
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

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .required {
            color: #dc2626;
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

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 16px;
            margin: 0;
        }

        .info-box h6 {
            color: #1e40af;
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
            border-bottom: 1px solid #dbeafe;
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

        /* Attachment Upload Styles */
        .attachment-row {
            display: grid;
            grid-template-columns: 1fr 200px auto;
            gap: 12px;
            align-items: start;
            padding: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .attachment-row {
                grid-template-columns: 1fr;
            }
        }

        .file-drop-zone {
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            padding: 24px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            background: white;
        }

        .file-drop-zone:hover,
        .file-drop-zone.dragover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .file-drop-zone.has-file {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .file-drop-zone .drop-icon {
            font-size: 24px;
            color: #9ca3af;
            margin-bottom: 8px;
        }

        .file-drop-zone.has-file .drop-icon {
            color: #10b981;
        }

        .file-drop-zone .drop-text {
            font-size: 13px;
            color: #6b7280;
        }

        .file-drop-zone .file-name {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
            word-break: break-all;
        }

        .attachment-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }

        .btn-remove-attachment {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 8px 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-remove-attachment:hover {
            background: #fecaca;
        }

        .btn-add-attachment {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px dashed #3b82f6;
            padding: 10px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add-attachment:hover {
            background: #dbeafe;
        }

        .btn-add-attachment:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Learning Space
        @endslot
        @slot('title')
            Create Assignment
        @endslot
    @endcomponent

    <div class="page-header">
        <h4><i class="fas fa-tasks me-2"></i>Create Assignment</h4>
        <p>Add a new assignment to {{ $module->title }}</p>
    </div>

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
        <div class="help-title">Assignment Setup</div>
        <div class="help-content">
            Create an assignment for students to submit work. You can configure submission types, due dates, and optionally
            attach a grading rubric.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-container">
                <form action="{{ route('lms.assignments.store', $module) }}" method="POST" id="assignmentForm"
                    class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf

                    <h3 class="section-title">Assignment Details</h3>
                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="Enter assignment title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum Points <span class="required">*</span></label>
                            <input type="number" name="max_points"
                                class="form-control @error('max_points') is-invalid @enderror"
                                value="{{ old('max_points', 100) }}" min="1" max="1000" required>
                            @error('max_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the assignment">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="5" placeholder="Detailed instructions for students">{{ old('instructions') }}</textarea>
                        <div class="form-text">Provide clear instructions on how to complete the assignment</div>
                    </div>

                    <h3 class="section-title">Submission Settings</h3>
                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Submission Type <span class="required">*</span></label>
                            <select name="submission_type"
                                class="form-select @error('submission_type') is-invalid @enderror" required>
                                <option value="file" {{ old('submission_type') == 'file' ? 'selected' : '' }}>File Upload
                                    Only</option>
                                <option value="text" {{ old('submission_type') == 'text' ? 'selected' : '' }}>Text Entry
                                    Only</option>
                                <option value="both" {{ old('submission_type', 'both') == 'both' ? 'selected' : '' }}>
                                    Both File and Text</option>
                            </select>
                            @error('submission_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="form-check" style="margin-top: 28px;">
                                <input type="checkbox" class="form-check-input" name="require_submission_text"
                                    id="requireText" value="1" {{ old('require_submission_text') ? 'checked' : '' }}>
                                <label class="form-check-label" for="requireText">
                                    Require text submission
                                </label>
                            </div>
                            <div class="form-text">Students must include text with their submission</div>
                        </div>
                    </div>

                    <div id="fileSettings">
                        <div class="form-grid-3" style="margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">Allowed File Types</label>
                                <input type="text" name="allowed_file_types" class="form-control"
                                    value="{{ old('allowed_file_types', 'pdf, doc, docx, ppt, pptx') }}"
                                    placeholder="pdf, doc, docx">
                                <div class="form-text">Comma-separated extensions</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Max File Size (MB)</label>
                                <input type="number" name="max_file_size_mb" class="form-control"
                                    value="{{ old('max_file_size_mb', 10) }}" min="1" max="100" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Max Files</label>
                                <input type="number" name="max_files" class="form-control"
                                    value="{{ old('max_files', 5) }}" min="1" max="20" required>
                            </div>
                        </div>
                    </div>

                    <h3 class="section-title">Dates & Deadlines</h3>
                    <div class="form-grid-3" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Available From</label>
                            <input type="datetime-local" name="available_from" class="form-control"
                                value="{{ old('available_from') }}">
                            <div class="form-text">When students can start</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control"
                                value="{{ old('due_date') }}">
                            <div class="form-text">Submission deadline</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cutoff Date</label>
                            <input type="datetime-local" name="cutoff_date" class="form-control"
                                value="{{ old('cutoff_date') }}">
                            <div class="form-text">No submissions after this</div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="allow_late_submissions"
                                    id="allowLate" value="1" {{ old('allow_late_submissions') ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowLate">
                                    Allow late submissions
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="latePenaltyField" style="display: none;">
                            <label class="form-label">Late Penalty (%)</label>
                            <input type="number" name="late_penalty_percent" class="form-control"
                                value="{{ old('late_penalty_percent', 10) }}" min="0" max="100">
                            <div class="form-text">Percentage deducted for late submissions</div>
                        </div>
                    </div>

                    <h3 class="section-title">Attempts & Resubmission</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Maximum Attempts</label>
                            <input type="number" name="max_attempts" class="form-control"
                                value="{{ old('max_attempts') }}" min="1" placeholder="Unlimited">
                            <div class="form-text">Leave empty for unlimited attempts</div>
                        </div>
                        <div class="form-group">
                            <div class="form-check" style="margin-top: 28px;">
                                <input type="checkbox" class="form-check-input" name="allow_resubmission"
                                    id="allowResubmission" value="1"
                                    {{ old('allow_resubmission', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowResubmission">
                                    Allow resubmission
                                </label>
                            </div>
                            <div class="form-text">Students can update their submission</div>
                        </div>
                    </div>

                    <h3 class="section-title">Grading</h3>
                    <div class="form-group">
                        <label class="form-label">Grading Rubric</label>
                        <select name="rubric_id" class="form-select">
                            <option value="">No Rubric</option>
                            @foreach ($rubrics as $rubric)
                                <option value="{{ $rubric->id }}"
                                    {{ old('rubric_id') == $rubric->id ? 'selected' : '' }}>
                                    {{ $rubric->title }} ({{ $rubric->total_points }} pts)
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Optional rubric for structured grading</div>
                    </div>

                    @if ($rubrics->isEmpty())
                        <div class="alert alert-info mt-3 mb-0" style="font-size: 13px;">
                            <i class="fas fa-info-circle me-1"></i>
                            No rubrics available. You can create one later and attach it to this assignment.
                        </div>
                    @endif

                    <h3 class="section-title">Reference Materials</h3>
                    <div class="help-text" style="margin-bottom: 16px;">
                        <div class="help-content">
                            Attach up to {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }} reference files (max
                            {{ \App\Models\Lms\AssignmentAttachment::MAX_FILE_SIZE_MB }}MB each) that students can
                            download. Supported formats: PDF, Word, Excel, PowerPoint, images, and archives.
                        </div>
                    </div>

                    <div id="attachments-container">
                        {{-- Attachment rows will be added here dynamically --}}
                    </div>

                    <button type="button" class="btn-add-attachment" id="add-attachment-btn">
                        <i class="fas fa-plus"></i> Add Reference File
                    </button>
                    <span class="attachment-hint ms-2" id="attachment-count-hint">
                        0 of {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }} files added
                    </span>

                    <div class="form-actions">
                        <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                            <span class="btn-text"><i class="fas fa-save"></i> Create Assignment</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Creating...
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
                        <span class="label">Status</span>
                        <span class="value">Will be saved as Draft</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('assignmentForm');
            const submitBtn = document.getElementById('submitBtn');

            // Toggle late penalty field
            document.getElementById('allowLate').addEventListener('change', function() {
                document.getElementById('latePenaltyField').style.display = this.checked ? 'block' : 'none';
            });

            // Show on page load if checked
            if (document.getElementById('allowLate').checked) {
                document.getElementById('latePenaltyField').style.display = 'block';
            }

            // Toggle file settings based on submission type
            document.querySelector('select[name="submission_type"]').addEventListener('change', function() {
                document.getElementById('fileSettings').style.display =
                    (this.value === 'text') ? 'none' : 'block';
            });

            // Initial state
            if (document.querySelector('select[name="submission_type"]').value === 'text') {
                document.getElementById('fileSettings').style.display = 'none';
            }

            // Attachment Management
            const maxAttachments = {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }};
            const maxFileSizeMB = {{ \App\Models\Lms\AssignmentAttachment::MAX_FILE_SIZE_MB }};
            const allowedExtensions = @json(\App\Models\Lms\AssignmentAttachment::ALLOWED_MIMES);
            const attachmentsContainer = document.getElementById('attachments-container');
            const addAttachmentBtn = document.getElementById('add-attachment-btn');
            const countHint = document.getElementById('attachment-count-hint');
            let attachmentIndex = 0;

            function updateAttachmentCount() {
                const count = attachmentsContainer.querySelectorAll('.attachment-row').length;
                countHint.textContent = `${count} of ${maxAttachments} files added`;
                addAttachmentBtn.disabled = count >= maxAttachments;
            }

            function createAttachmentRow() {
                const index = attachmentIndex++;
                const row = document.createElement('div');
                row.className = 'attachment-row';
                row.dataset.index = index;

                row.innerHTML = `
                    <div class="file-drop-zone" data-index="${index}">
                        <input type="file" name="attachments[]" id="attachment-file-${index}"
                            accept=".${allowedExtensions.join(',.')}"
                            style="display: none;">
                        <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="drop-text">
                            <span class="file-name">Drop file here or click to browse</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Label (optional)</label>
                        <input type="text" name="attachment_labels[]" class="form-control"
                            placeholder="e.g., Sample Essay">
                    </div>
                    <div>
                        <button type="button" class="btn-remove-attachment" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                // File input change handler
                const fileInput = row.querySelector('input[type="file"]');
                const dropZone = row.querySelector('.file-drop-zone');
                const fileName = row.querySelector('.file-name');

                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const file = this.files[0];
                        const fileSizeMB = file.size / (1024 * 1024);

                        if (fileSizeMB > maxFileSizeMB) {
                            alert(`File size exceeds ${maxFileSizeMB}MB limit.`);
                            this.value = '';
                            return;
                        }

                        fileName.textContent = file.name;
                        dropZone.classList.add('has-file');
                    } else {
                        fileName.textContent = 'Drop file here or click to browse';
                        dropZone.classList.remove('has-file');
                    }
                });

                // Click to browse
                dropZone.addEventListener('click', function() {
                    fileInput.click();
                });

                // Drag and drop
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                dropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');

                    if (e.dataTransfer.files.length > 0) {
                        const file = e.dataTransfer.files[0];
                        const ext = file.name.split('.').pop().toLowerCase();

                        if (!allowedExtensions.includes(ext)) {
                            alert('File type not allowed. Allowed types: ' + allowedExtensions.join(', '));
                            return;
                        }

                        const fileSizeMB = file.size / (1024 * 1024);
                        if (fileSizeMB > maxFileSizeMB) {
                            alert(`File size exceeds ${maxFileSizeMB}MB limit.`);
                            return;
                        }

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });

                // Remove button
                row.querySelector('.btn-remove-attachment').addEventListener('click', function() {
                    row.remove();
                    updateAttachmentCount();
                });

                return row;
            }

            addAttachmentBtn.addEventListener('click', function() {
                if (attachmentsContainer.querySelectorAll('.attachment-row').length < maxAttachments) {
                    attachmentsContainer.appendChild(createAttachmentRow());
                    updateAttachmentCount();
                }
            });

            updateAttachmentCount();

            // Form submission with loading animation
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

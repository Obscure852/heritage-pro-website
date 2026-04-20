@extends('layouts.master')

@section('title')
    Edit Assignment - Learning Space
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-row .status-badge {
            font-size: 13px;
            padding: 6px 16px;
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

        .info-box.green {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .info-box.green h6 {
            color: #065f46;
        }

        .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .header-stats {
            display: flex;
            gap: 48px;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
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

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
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

        /* Attachment Styles */
        .existing-attachment {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .existing-attachment .file-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .existing-attachment .file-info {
            flex: 1;
        }

        .existing-attachment .file-name {
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }

        .existing-attachment .file-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .existing-attachment .file-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

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
            Edit Assignment
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-edit me-2"></i>Edit Assignment</h4>
                <p>{{ $assignment->contentItem->module->title }} &bull;
                    {{ $assignment->contentItem->module->course->title }}</p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <h4>{{ $enrolledCount }}</h4>
                    <small>Enrolled</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $submissionStats->total }}</h4>
                    <small>Submissions</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $submissionStats->graded }}</h4>
                    <small>Graded</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $submissionStats->pending }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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
        <div class="help-title">Edit Assignment Settings</div>
        <div class="help-content">
            Update the assignment title, instructions, submission settings, and deadlines. Changes will be applied
            immediately for students.
        </div>
    </div>

    <div class="status-row">
        <span class="status-badge status-{{ $assignment->status }}">{{ ucfirst($assignment->status) }}</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#enrolledStudentsPanel">
                <i class="fas fa-user-graduate"></i> Enrolled Students ({{ $enrolledCount }})
            </button>
            <a href="{{ route('lms.assignments.enrollments', $assignment) }}" class="btn btn-outline-primary">
                <i class="fas fa-users"></i> Enrollments ({{ $enrolledCount }})
            </a>
            <a href="{{ route('lms.assignments.submissions', $assignment) }}" class="btn btn-outline-primary">
                <i class="fas fa-inbox"></i> View Submissions
            </a>
        </div>
    </div>

    <div class="collapse mb-3" id="enrolledStudentsPanel">
        <div class="form-container" style="padding: 0;">
            <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h6 style="margin: 0; font-weight: 600; color: #1f2937;"><i class="fas fa-user-graduate me-2"></i>Enrolled Students</h6>
                <span class="badge bg-secondary">{{ $enrollments->count() }}</span>
            </div>
            @if ($enrollments->count() > 0)
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach ($enrollments as $enrollment)
                        @if ($enrollment->student)
                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px 24px; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                <span style="color: #6b7280; min-width: 40px;">{{ $enrollment->student->id }}</span>
                                <span style="color: #1f2937;">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p style="color: #6b7280; font-size: 13px; text-align: center; padding: 20px; margin: 0;">No students enrolled in this course yet.</p>
            @endif
        </div>
    </div>

    <div class="form-container">
        <form action="{{ route('lms.assignments.update', $assignment) }}" method="POST" id="assignmentForm"
            class="needs-validation" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h3 class="section-title">Assignment Details</h3>
            <div class="form-grid" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $assignment->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Maximum Points <span class="required">*</span></label>
                    <input type="number" name="max_points" class="form-control @error('max_points') is-invalid @enderror"
                        value="{{ old('max_points', $assignment->max_points) }}" min="1" max="1000" required>
                    @error('max_points')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $assignment->description) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Instructions</label>
                <textarea name="instructions" class="form-control" rows="5">{{ old('instructions', $assignment->instructions) }}</textarea>
                <div class="form-text">Provide clear instructions on how to complete the assignment</div>
            </div>

            <h3 class="section-title">Submission Settings</h3>
            <div class="form-grid" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Submission Type <span class="required">*</span></label>
                    <select name="submission_type" class="form-select" required>
                        <option value="file"
                            {{ old('submission_type', $assignment->submission_type) == 'file' ? 'selected' : '' }}>File
                            Upload Only</option>
                        <option value="text"
                            {{ old('submission_type', $assignment->submission_type) == 'text' ? 'selected' : '' }}>Text
                            Entry Only</option>
                        <option value="both"
                            {{ old('submission_type', $assignment->submission_type) == 'both' ? 'selected' : '' }}>Both
                            File and Text</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="form-check" style="margin-top: 28px;">
                        <input type="checkbox" class="form-check-input" name="require_submission_text" id="requireText"
                            value="1"
                            {{ old('require_submission_text', $assignment->require_submission_text) ? 'checked' : '' }}>
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
                            value="{{ old('allowed_file_types', is_array($assignment->allowed_file_types) ? implode(', ', $assignment->allowed_file_types) : $assignment->allowed_file_types) }}">
                        <div class="form-text">Comma-separated extensions</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max File Size (MB)</label>
                        <input type="number" name="max_file_size_mb" class="form-control"
                            value="{{ old('max_file_size_mb', $assignment->max_file_size_mb) }}" min="1"
                            max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Files</label>
                        <input type="number" name="max_files" class="form-control"
                            value="{{ old('max_files', $assignment->max_files) }}" min="1" max="20"
                            required>
                    </div>
                </div>
            </div>

            <h3 class="section-title">Dates & Deadlines</h3>
            <div class="form-grid-3" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Available From</label>
                    <input type="datetime-local" name="available_from" class="form-control"
                        value="{{ old('available_from', $assignment->available_from?->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">When students can start</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="datetime-local" name="due_date" class="form-control"
                        value="{{ old('due_date', $assignment->due_date?->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Submission deadline</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Cutoff Date</label>
                    <input type="datetime-local" name="cutoff_date" class="form-control"
                        value="{{ old('cutoff_date', $assignment->cutoff_date?->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">No submissions after this</div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="allow_late_submissions" id="allowLate"
                            value="1"
                            {{ old('allow_late_submissions', $assignment->allow_late_submissions) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowLate">
                            Allow late submissions
                        </label>
                    </div>
                </div>
                <div class="form-group" id="latePenaltyField"
                    style="{{ $assignment->allow_late_submissions ? '' : 'display: none;' }}">
                    <label class="form-label">Late Penalty (%)</label>
                    <input type="number" name="late_penalty_percent" class="form-control"
                        value="{{ old('late_penalty_percent', $assignment->late_penalty_percent) }}" min="0"
                        max="100">
                    <div class="form-text">Percentage deducted for late submissions</div>
                </div>
            </div>

            <h3 class="section-title">Attempts & Resubmission</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Maximum Attempts</label>
                    <input type="number" name="max_attempts" class="form-control"
                        value="{{ old('max_attempts', $assignment->max_attempts) }}" min="1"
                        placeholder="Unlimited">
                    <div class="form-text">Leave empty for unlimited attempts</div>
                </div>
                <div class="form-group">
                    <div class="form-check" style="margin-top: 28px;">
                        <input type="checkbox" class="form-check-input" name="allow_resubmission" id="allowResubmission"
                            value="1"
                            {{ old('allow_resubmission', $assignment->allow_resubmission) ? 'checked' : '' }}>
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
                            {{ old('rubric_id', $assignment->rubric_id) == $rubric->id ? 'selected' : '' }}>
                            {{ $rubric->title }} ({{ $rubric->total_points }} pts)
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Optional rubric for structured grading</div>
            </div>

            @if ($assignment->rubric)
                <div class="alert alert-info mt-3 mb-0" style="font-size: 13px;">
                    <strong>Current Rubric:</strong> {{ $assignment->rubric->title }}<br>
                    <small>{{ $assignment->rubric->criteria->count() }} criteria, {{ $assignment->rubric->total_points }}
                        total points</small>
                </div>
            @endif

            <h3 class="section-title">Reference Materials</h3>
            <div class="help-text"
                style="margin-bottom: 16px; background: #eff6ff; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
                <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5; margin: 0;">
                    Attach up to {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }} reference files (max
                    {{ \App\Models\Lms\AssignmentAttachment::MAX_FILE_SIZE_MB }}MB each) that students can download.
                    Supported formats: PDF, Word, Excel, PowerPoint, images, and archives.
                </div>
            </div>

            @if ($assignment->attachments->count() > 0)
                <div class="mb-3">
                    <label class="form-label">Current Attachments</label>
                    @foreach ($assignment->attachments as $attachment)
                        <div class="existing-attachment">
                            <div class="file-icon">
                                <i class="{{ $attachment->icon_class }}"></i>
                            </div>
                            <div class="file-info">
                                <div class="file-name">{{ $attachment->display_name }}</div>
                                <div class="file-meta">
                                    {{ $attachment->original_name }} &bull; {{ $attachment->file_size_formatted }}
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="{{ route('lms.assignments.attachment.download', $attachment) }}"
                                    class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <label class="form-check mb-0" title="Check to delete">
                                    <input type="checkbox" name="delete_attachments[]" value="{{ $attachment->id }}"
                                        class="form-check-input">
                                    <span class="text-danger" style="font-size: 12px;">Delete</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                $remainingSlots =
                    \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS - $assignment->attachments->count();
            @endphp

            @if ($remainingSlots > 0)
                <div id="attachments-container">
                    {{-- New attachment rows will be added here dynamically --}}
                </div>

                <button type="button" class="btn-add-attachment" id="add-attachment-btn">
                    <i class="fas fa-plus"></i> Add Reference File
                </button>
                <span class="attachment-hint ms-2" id="attachment-count-hint">
                    {{ $assignment->attachments->count() }} of {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }}
                    files used ({{ $remainingSlots }} remaining)
                </span>
            @else
                <div class="alert alert-warning" style="font-size: 13px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Maximum attachments reached. Delete existing attachments to add new ones.
                </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('lms.assignments.show', $assignment) }}" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> Preview
                </a>
                @if ($assignment->status === 'draft')
                    <button type="button" class="btn btn-success"
                        onclick="document.getElementById('publishForm').submit();">
                        <i class="fas fa-paper-plane"></i> Publish
                    </button>
                @elseif ($assignment->status === 'published')
                    <button type="button" class="btn btn-danger"
                        onclick="document.getElementById('closeForm').submit();">
                        <i class="fas fa-ban"></i> Close Assignment
                    </button>
                @endif
                <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <form id="publishForm" action="{{ route('lms.assignments.publish', $assignment) }}" method="POST"
        style="display: none;">
        @csrf
    </form>

    <form id="closeForm" action="{{ route('lms.assignments.close', $assignment) }}" method="POST"
        style="display: none;">
        @csrf
    </form>
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
            @if ($remainingSlots > 0)
                const maxAttachments = {{ \App\Models\Lms\AssignmentAttachment::MAX_ATTACHMENTS }};
                const existingCount = {{ $assignment->attachments->count() }};
                const maxFileSizeMB = {{ \App\Models\Lms\AssignmentAttachment::MAX_FILE_SIZE_MB }};
                const allowedExtensions = @json(\App\Models\Lms\AssignmentAttachment::ALLOWED_MIMES);
                const attachmentsContainer = document.getElementById('attachments-container');
                const addAttachmentBtn = document.getElementById('add-attachment-btn');
                const countHint = document.getElementById('attachment-count-hint');
                let attachmentIndex = 0;

                function updateAttachmentCount() {
                    const newCount = attachmentsContainer.querySelectorAll('.attachment-row').length;
                    const deleteChecked = document.querySelectorAll('input[name="delete_attachments[]"]:checked')
                        .length;
                    const totalAfterChanges = existingCount - deleteChecked + newCount;
                    const remaining = maxAttachments - totalAfterChanges;

                    countHint.textContent =
                        `${existingCount} of ${maxAttachments} files used (${remaining} remaining)`;
                    addAttachmentBtn.disabled = remaining <= 0;
                }

                // Update count when delete checkboxes change
                document.querySelectorAll('input[name="delete_attachments[]"]').forEach(checkbox => {
                    checkbox.addEventListener('change', updateAttachmentCount);
                });

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
                                alert('File type not allowed. Allowed types: ' + allowedExtensions.join(
                                    ', '));
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
                    const deleteChecked = document.querySelectorAll(
                        'input[name="delete_attachments[]"]:checked').length;
                    const newCount = attachmentsContainer.querySelectorAll('.attachment-row').length;
                    const totalAfterChanges = existingCount - deleteChecked + newCount;

                    if (totalAfterChanges < maxAttachments) {
                        attachmentsContainer.appendChild(createAttachmentRow());
                        updateAttachmentCount();
                    }
                });

                updateAttachmentCount();
            @endif

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

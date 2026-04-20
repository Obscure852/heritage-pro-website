@extends('layouts.master')

@section('title')
    {{ $assignment->title }}
@endsection

@section('css')
    <style>
        .assignment-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 32px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .assignment-meta {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .assignment-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            opacity: 0.9;
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
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
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

        .instructions-content {
            line-height: 1.8;
            color: #374151;
        }

        .instructions-content p {
            margin-bottom: 16px;
        }

        .deadline-card {
            border-left: 4px solid #f59e0b;
        }

        .deadline-card.overdue {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        .deadline-card.submitted {
            border-left-color: #10b981;
            background: #ecfdf5;
        }

        .deadline-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .deadline-item:last-child {
            border-bottom: none;
        }

        .deadline-label {
            color: #6b7280;
            font-size: 13px;
        }

        .deadline-value {
            font-weight: 600;
            color: #1f2937;
        }

        .submission-card {
            background: #f0fdf4;
            border: 1px solid #86efac;
        }

        .submission-card .card-header {
            background: #dcfce7;
            border-bottom: 1px solid #86efac;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .file-list li:last-child {
            border-bottom: none;
        }

        .file-icon {
            width: 36px;
            height: 36px;
            background: #f3f4f6;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
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

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        .rubric-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rubric-table th,
        .rubric-table td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        .rubric-table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 13px;
        }

        .rubric-level {
            font-size: 12px;
            color: #6b7280;
        }

        .rubric-points {
            font-weight: 600;
            color: #f59e0b;
        }

        .grade-display {
            text-align: center;
            padding: 24px;
        }

        .grade-score {
            font-size: 48px;
            font-weight: 700;
            color: #1f2937;
        }

        .grade-max {
            font-size: 24px;
            color: #6b7280;
        }

        .grade-percentage {
            font-size: 14px;
            color: #6b7280;
            margin-top: 8px;
        }

        /* Reference Materials Styles */
        .reference-materials-card {
            border-left: 4px solid #3b82f6;
        }

        .reference-materials-card .card-header {
            background: #eff6ff;
            border-bottom: 1px solid #bfdbfe;
        }

        .attachment-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .attachment-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .attachment-list li:last-child {
            border-bottom: none;
        }

        .attachment-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .attachment-info {
            flex: 1;
        }

        .attachment-name {
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }

        .attachment-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .btn-download {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-download:hover {
            background: #dbeafe;
            color: #2563eb;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            {{ $assignment->title }}
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="assignment-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 style="margin:0;">{{ $assignment->title }}</h2>
                @if ($assignment->description)
                    <p style="margin:8px 0 0 0; opacity:0.9;">{{ $assignment->description }}</p>
                @endif
            </div>
            <span class="status-badge status-{{ $assignment->status }}">{{ $assignment->status }}</span>
        </div>

        <div class="assignment-meta">
            <div class="assignment-meta-item">
                <i class="fas fa-star"></i>
                {{ $assignment->max_points }} points
            </div>
            @if ($assignment->due_date)
                <div class="assignment-meta-item">
                    <i class="fas fa-calendar"></i>
                    Due: {{ $assignment->due_date->format('M j, Y g:i A') }}
                </div>
            @endif
            <div class="assignment-meta-item">
                <i class="fas fa-upload"></i>
                {{ ucfirst($assignment->submission_type) }} submission
            </div>
            @if ($assignment->rubric)
                <div class="assignment-meta-item">
                    <i class="fas fa-th-list"></i>
                    Rubric grading
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if ($assignment->instructions)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="instructions-content">
                            {!! nl2br(e($assignment->instructions)) !!}
                        </div>
                    </div>
                </div>
            @endif

            @if ($assignment->attachments->count() > 0)
                <div class="card reference-materials-card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-paperclip me-2"></i>Reference Materials</h5>
                    </div>
                    <div class="card-body">
                        <ul class="attachment-list">
                            @foreach ($assignment->attachments as $attachment)
                                <li>
                                    <div class="attachment-icon">
                                        <i class="{{ $attachment->icon_class }}"></i>
                                    </div>
                                    <div class="attachment-info">
                                        <div class="attachment-name">{{ $attachment->display_name }}</div>
                                        <div class="attachment-meta">{{ $attachment->file_size_formatted }}</div>
                                    </div>
                                    <a href="{{ route('lms.assignments.attachment.download', $attachment) }}"
                                        class="btn-download">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if ($assignment->rubric)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Grading Rubric: {{ $assignment->rubric->title }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="rubric-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Criterion</th>
                                    @foreach ($assignment->rubric->criteria->first()->levels ?? [] as $level)
                                        <th>{{ $level->title }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignment->rubric->criteria as $criterion)
                                    <tr>
                                        <td>
                                            <strong>{{ $criterion->title }}</strong>
                                            @if ($criterion->description)
                                                <br><small class="text-muted">{{ $criterion->description }}</small>
                                            @endif
                                        </td>
                                        @foreach ($criterion->levels as $level)
                                            <td>
                                                <div class="rubric-points">{{ $level->points }} pts</div>
                                                <div class="rubric-level">{{ $level->description }}</div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($submission && $submission->status === 'graded')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Feedback</h5>
                    </div>
                    <div class="card-body">
                        @if ($submission->feedback)
                            <div class="mb-3">
                                {!! nl2br(e($submission->feedback)) !!}
                            </div>
                        @else
                            <p class="text-muted mb-0">No written feedback provided.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            @can('manage-lms-content')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-cog me-1"></i> Teacher Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('lms.assignments.submissions', $assignment) }}" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> View Submissions
                                <span class="badge bg-light text-dark ms-1">{{ $assignment->submissions()->count() }}</span>
                            </a>
                            <a href="{{ route('lms.assignments.enrollments', $assignment) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-users me-1"></i> View Enrollments
                                <span class="badge bg-secondary ms-1">{{ $enrolledCount }}</span>
                            </a>
                            <a href="{{ route('lms.assignments.edit', $assignment) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> Edit Assignment
                            </a>
                            @if ($assignment->status === 'draft')
                                <form action="{{ route('lms.assignments.publish', $assignment) }}" method="POST" class="assignment-action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 btn-loading">
                                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Publish</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Publishing...
                                        </span>
                                    </button>
                                </form>
                            @elseif ($assignment->status === 'published')
                                <form action="{{ route('lms.assignments.close', $assignment) }}" method="POST" class="assignment-action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100 btn-loading">
                                        <span class="btn-text"><i class="fas fa-ban me-1"></i> Close Assignment</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Closing...
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title"><i class="fas fa-user-graduate me-1"></i> Enrolled Students</h5>
                        <span class="badge bg-secondary">{{ $enrollments->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($enrollments->count() > 0)
                            <div style="max-height: 280px; overflow-y: auto;">
                                @foreach ($enrollments as $enrollment)
                                    @if ($enrollment->student)
                                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
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
            @endcan

            <div class="card deadline-card {{ $submission ? 'submitted' : ($assignment->is_overdue ? 'overdue' : '') }}">
                <div class="card-header">
                    <h5 class="card-title">
                        @if ($submission)
                            <i class="fas fa-check-circle text-success me-1"></i> Submitted
                        @elseif ($assignment->is_overdue)
                            <i class="fas fa-exclamation-circle text-danger me-1"></i> Overdue
                        @else
                            <i class="fas fa-clock me-1"></i> Deadlines
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($assignment->available_from)
                        <div class="deadline-item">
                            <span class="deadline-label">Available from</span>
                            <span class="deadline-value">{{ $assignment->available_from->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($assignment->due_date)
                        <div class="deadline-item">
                            <span class="deadline-label">Due date</span>
                            <span class="deadline-value {{ $assignment->is_overdue ? 'text-danger' : '' }}">
                                {{ $assignment->due_date->format('M j, Y g:i A') }}
                            </span>
                        </div>
                    @endif
                    @if ($assignment->cutoff_date)
                        <div class="deadline-item">
                            <span class="deadline-label">Cutoff date</span>
                            <span class="deadline-value">{{ $assignment->cutoff_date->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($assignment->allow_late_submissions && $assignment->late_penalty_percent)
                        <div class="deadline-item">
                            <span class="deadline-label">Late penalty</span>
                            <span class="deadline-value text-warning">{{ $assignment->late_penalty_percent }}%</span>
                        </div>
                    @endif
                </div>
            </div>

            @if ($submission)
                <div class="card submission-card">
                    <div class="card-header">
                        <h5 class="card-title">Your Submission</h5>
                    </div>
                    <div class="card-body">
                        @if ($submission->status === 'graded')
                            <div class="grade-display">
                                <div>
                                    <span class="grade-score">{{ number_format($submission->final_score, 1) }}</span>
                                    <span class="grade-max">/ {{ $assignment->max_points }}</span>
                                </div>
                                <div class="grade-percentage">
                                    {{ number_format(($submission->final_score / $assignment->max_points) * 100, 1) }}%
                                </div>
                                @if ($submission->late_penalty_applied > 0)
                                    <div class="text-warning mt-2" style="font-size: 12px;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Late penalty applied: -{{ $submission->late_penalty_applied }}
                                    </div>
                                @endif
                            </div>
                            <hr>
                        @endif

                        <div class="mb-3">
                            <strong>Submitted:</strong> {{ $submission->submitted_at->format('M j, Y g:i A') }}
                        </div>

                        @if ($submission->attachedFiles->count())
                            <div class="mb-3">
                                <strong>Files:</strong>
                                <ul class="file-list mt-2">
                                    @foreach ($submission->attachedFiles as $file)
                                        <li>
                                            <div class="file-icon">
                                                <i class="{{ $file->icon_class }}"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('lms.submissions.download', $file) }}">
                                                    {{ $file->original_name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $file->formatted_size }}</small>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($submission->submission_text)
                            <div>
                                <strong>Text submission:</strong>
                                <div class="mt-2 p-3 bg-white rounded border">
                                    {!! nl2br(e(Str::limit($submission->submission_text, 300))) !!}
                                </div>
                            </div>
                        @endif

                        @if ($canSubmit && $assignment->allow_resubmission)
                            <hr>
                            <a href="{{ route('lms.assignments.submit.form', $assignment) }}"
                                class="btn btn-primary w-100">
                                <i class="fas fa-redo me-1"></i> Resubmit
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Submit Assignment</h5>
                    </div>
                    <div class="card-body">
                        @if ($canSubmit)
                            <p class="text-muted mb-3">
                                @if ($assignment->submission_type === 'file')
                                    Upload your files to submit this assignment.
                                @elseif ($assignment->submission_type === 'text')
                                    Enter your response to submit this assignment.
                                @else
                                    Upload files and/or enter text to submit.
                                @endif
                            </p>

                            @if (in_array($assignment->submission_type, ['file', 'both']))
                                <div class="mb-3" style="font-size: 13px;">
                                    <strong>Allowed files:</strong>
                                    {{ implode(', ', $assignment->allowed_file_types ?? []) }}<br>
                                    <strong>Max size:</strong> {{ $assignment->max_file_size_mb }}MB per file<br>
                                    <strong>Max files:</strong> {{ $assignment->max_files }}
                                </div>
                            @endif

                            <a href="{{ route('lms.assignments.submit.form', $assignment) }}"
                                class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-1"></i> Start Submission
                            </a>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-lock fa-2x mb-3"></i>
                                <p>
                                    @if ($assignment->status === 'closed')
                                        This assignment is closed and no longer accepting submissions.
                                    @elseif ($assignment->status === 'draft')
                                        This assignment is not yet available.
                                    @elseif ($assignment->cutoff_date && now()->gt($assignment->cutoff_date))
                                        The submission deadline has passed.
                                    @else
                                        You cannot submit to this assignment.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('lms.courses.show', $assignment->contentItem->module->course) }}"
                class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-1"></i> Back to Content
            </a>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.querySelectorAll('.assignment-action-form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
@endsection

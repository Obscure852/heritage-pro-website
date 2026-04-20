@extends('layouts.master')

@section('title')
    Grade Submission
@endsection

@section('css')
    <style>
        .grade-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 24px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .student-avatar-lg {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 20px;
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

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
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

        .submission-text {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
            max-height: 400px;
            overflow-y: auto;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }

        .file-name {
            font-weight: 500;
            color: #1f2937;
        }

        .file-size {
            font-size: 12px;
            color: #6b7280;
        }

        .rubric-grading {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .rubric-criterion {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px;
        }

        .rubric-criterion:last-child {
            border-bottom: none;
        }

        .criterion-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .criterion-title {
            font-weight: 600;
            color: #1f2937;
        }

        .criterion-description {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .criterion-points-input {
            width: 80px;
            text-align: center;
        }

        .level-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            margin-bottom: 12px;
        }

        .level-option {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .level-option:hover {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .level-option.selected {
            border-color: #f59e0b;
            background: #fef3c7;
        }

        .level-option input {
            display: none;
        }

        .level-points {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
        }

        .level-title {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .score-input-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .score-input {
            width: 100px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            padding: 12px;
        }

        .score-max {
            font-size: 20px;
            color: #6b7280;
        }

        .late-warning {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .late-warning i {
            color: #dc2626;
        }

        .late-warning .text {
            font-size: 13px;
            color: #991b1b;
        }

        .meta-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            font-size: 13px;
        }

        .meta-item {
            padding: 8px 0;
        }

        .meta-label {
            color: #6b7280;
        }

        .meta-value {
            font-weight: 500;
            color: #1f2937;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Grade Submission
        @endslot
    @endcomponent

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

    <div class="grade-header">
        <div class="student-header">
            <div class="student-avatar-lg">
                {{ strtoupper(substr($submission->student->firstname, 0, 1)) }}{{ strtoupper(substr($submission->student->lastname, 0, 1)) }}
            </div>
            <div>
                <h4 style="margin:0;">{{ $submission->student->firstname }} {{ $submission->student->lastname }}</h4>
                <p style="margin:4px 0 0 0; opacity:0.9;">
                    {{ $submission->assignment->title }} - Attempt {{ $submission->attempt_number }}
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('lms.submissions.grade.save', $submission) }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                @if ($submission->is_late)
                    <div class="late-warning">
                        <i class="fas fa-clock"></i>
                        <div class="text">
                            <strong>Late Submission</strong><br>
                            Submitted {{ $submission->submitted_at->diffForHumans() }} after deadline.
                            @if ($submission->assignment->late_penalty_percent)
                                A {{ $submission->assignment->late_penalty_percent }}% late penalty will be applied
                                automatically.
                            @endif
                        </div>
                    </div>
                @endif

                @if ($submission->submission_text)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Text Submission</h5>
                        </div>
                        <div class="card-body">
                            <div class="submission-text">{{ $submission->submission_text }}</div>
                        </div>
                    </div>
                @endif

                @if ($submission->attachedFiles->count())
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Submitted Files ({{ $submission->attachedFiles->count() }})</h5>
                        </div>
                        <div class="card-body">
                            <ul class="file-list">
                                @foreach ($submission->attachedFiles as $file)
                                    <li class="file-item">
                                        <div class="file-info">
                                            <div class="file-icon">
                                                <i class="{{ $file->icon_class }}"></i>
                                            </div>
                                            <div>
                                                <div class="file-name">{{ $file->original_name }}</div>
                                                <div class="file-size">{{ $file->formatted_size }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('lms.submissions.download', $file) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if ($submission->assignment->rubric)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Rubric Grading</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="rubric-grading">
                                @foreach ($submission->assignment->rubric->criteria as $criterion)
                                    <div class="rubric-criterion">
                                        <div class="criterion-header">
                                            <div>
                                                <div class="criterion-title">{{ $criterion->title }}</div>
                                                @if ($criterion->description)
                                                    <div class="criterion-description">{{ $criterion->description }}</div>
                                                @endif
                                            </div>
                                            <input type="number" name="rubric_scores[{{ $criterion->id }}][points]"
                                                class="form-control criterion-points-input"
                                                value="{{ old("rubric_scores.{$criterion->id}.points", $submission->rubric_scores[$criterion->id]['points'] ?? '') }}"
                                                min="0" max="{{ $criterion->levels->max('points') }}"
                                                placeholder="pts">
                                        </div>

                                        <div class="level-options">
                                            @foreach ($criterion->levels as $level)
                                                <label
                                                    class="level-option {{ old("rubric_scores.{$criterion->id}.level_id", $submission->rubric_scores[$criterion->id]['level_id'] ?? '') == $level->id ? 'selected' : '' }}">
                                                    <input type="radio"
                                                        name="rubric_scores[{{ $criterion->id }}][level_id]"
                                                        value="{{ $level->id }}" data-points="{{ $level->points }}"
                                                        {{ old("rubric_scores.{$criterion->id}.level_id", $submission->rubric_scores[$criterion->id]['level_id'] ?? '') == $level->id ? 'checked' : '' }}>
                                                    <div class="level-points">{{ $level->points }}</div>
                                                    <div class="level-title">{{ $level->title }}</div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <input type="text" name="rubric_scores[{{ $criterion->id }}][comment]"
                                            class="form-control" placeholder="Comment for this criterion..."
                                            value="{{ old("rubric_scores.{$criterion->id}.comment", $submission->rubric_scores[$criterion->id]['comment'] ?? '') }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Feedback</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="feedback" class="form-control" rows="6" placeholder="Provide feedback to the student...">{{ old('feedback', $submission->feedback) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Score</h5>
                    </div>
                    <div class="card-body">
                        <div class="score-input-group">
                            <input type="number" name="score"
                                class="form-control score-input @error('score') is-invalid @enderror"
                                value="{{ old('score', $submission->score) }}" min="0"
                                max="{{ $submission->assignment->max_points }}" step="0.1" required>
                            <span class="score-max">/ {{ $submission->assignment->max_points }}</span>
                        </div>
                        @error('score')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror

                        @if ($submission->is_late && $submission->assignment->late_penalty_percent)
                            <div class="alert alert-warning mt-3 mb-0" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i>
                                A {{ $submission->assignment->late_penalty_percent }}% late penalty will be automatically
                                applied to the final score.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Submission Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="meta-info">
                            <div class="meta-item">
                                <div class="meta-label">Submitted</div>
                                <div class="meta-value">{{ $submission->submitted_at->format('M j, Y g:i A') }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Attempt</div>
                                <div class="meta-value">{{ $submission->attempt_number }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Status</div>
                                <div class="meta-value">{{ ucfirst($submission->status) }}</div>
                            </div>
                            @if ($submission->graded_at)
                                <div class="meta-item">
                                    <div class="meta-label">Last Graded</div>
                                    <div class="meta-value">{{ $submission->graded_at->format('M j, Y') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-loading" id="gradeBtn">
                        <span class="btn-text"><i class="fas fa-check me-1"></i> Save Grade</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                    <a href="{{ route('lms.assignments.submissions', $submission->assignment) }}"
                        class="btn btn-secondary">
                        Back to Submissions
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        // Loading animation on form submit
        document.querySelector('form').addEventListener('submit', function() {
            var submitBtn = document.getElementById('gradeBtn');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });

        // When clicking a rubric level, update the points input and calculate total
        document.querySelectorAll('.level-option input').forEach(input => {
            input.addEventListener('change', function() {
                const criterion = this.closest('.rubric-criterion');
                const pointsInput = criterion.querySelector('.criterion-points-input');
                pointsInput.value = this.dataset.points;

                // Update selected state
                criterion.querySelectorAll('.level-option').forEach(opt => opt.classList.remove(
                'selected'));
                this.closest('.level-option').classList.add('selected');

                calculateTotalScore();
            });
        });

        // Watch points inputs for changes
        document.querySelectorAll('.criterion-points-input').forEach(input => {
            input.addEventListener('input', calculateTotalScore);
        });

        function calculateTotalScore() {
            let total = 0;
            document.querySelectorAll('.criterion-points-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Update main score input with rubric total (optional, teacher can override)
            // document.querySelector('.score-input').value = total;
        }
    </script>
@endsection

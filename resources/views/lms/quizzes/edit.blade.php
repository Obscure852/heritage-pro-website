@extends('layouts.master')

@section('title')
    Edit Quiz - {{ $quiz->title }}
@endsection

@section('css')
    <style>
        .page-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 3px 3px 0 0;
        }

        .page-header h4 {
            margin: 0 0 4px 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .page-body {
            padding: 24px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .form-label .required {
            color: #dc2626;
        }

        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            padding: 10px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-check-input:checked {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        @media (max-width: 992px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        .settings-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .settings-card h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .settings-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .settings-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .settings-item-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .settings-item-info p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .stat-card {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #f59e0b;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
        }

        .quick-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .quick-links li {
            border-bottom: 1px solid #e5e7eb;
        }

        .quick-links li:last-child {
            border-bottom: none;
        }

        .quick-links a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .quick-links a:hover {
            color: #f59e0b;
        }

        .quick-links a i {
            width: 20px;
            text-align: center;
            color: #9ca3af;
        }

        .quick-links a:hover i {
            color: #f59e0b;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
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
            background: #6c757d;
            border: none;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid #f59e0b;
            color: #f59e0b;
            background: white;
        }

        .btn-outline-primary:hover {
            background: #f59e0b;
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
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            @if ($quiz->contentItem && $quiz->contentItem->module)
                <a href="{{ route('lms.courses.edit', $quiz->contentItem->module->course) }}">
                    {{ $quiz->contentItem->module->course->title }}
                </a>
            @else
                Quiz
            @endif
        @endslot
        @slot('title')
            Edit Quiz
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('lms.quizzes.update', $quiz) }}" method="POST" class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        <div class="page-container">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>Edit Quiz Settings</h4>
                        <p>{{ $quiz->contentItem?->module?->title ?? 'Quiz' }} &bull; {{ $quiz->contentItem?->module?->course?->title ?? '' }}</p>
                    </div>
                    <a href="{{ route('lms.quizzes.questions', $quiz) }}" class="btn btn-outline-primary">
                        <i class="fas fa-list-ol"></i> Manage Questions
                    </a>
                </div>
            </div>

            <div class="page-body">
                <div class="details-grid">
                    <div>
                        <h6 class="section-title">Basic Information</h6>

                        <div class="mb-3">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control"
                                value="{{ old('title', $quiz->title) }}" placeholder="Enter quiz title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Brief description of this quiz">{{ old('description', $quiz->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Instructions</label>
                            <textarea name="instructions" class="form-control" rows="4"
                                placeholder="Instructions for students taking this quiz (rules, materials allowed, etc.)">{{ old('instructions', $quiz->instructions) }}</textarea>
                        </div>

                        <h6 class="section-title">Quiz Behavior</h6>

                        <div class="settings-card">
                            <h6><i class="fas fa-clock me-2"></i>Timing & Attempts</h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Time Limit</label>
                                    <div class="input-group">
                                        <input type="number" name="time_limit_minutes" class="form-control"
                                            min="1" max="480" placeholder="No limit"
                                            value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}">
                                        <span class="input-group-text">minutes</span>
                                    </div>
                                    <div class="form-text">Leave empty for no time limit</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Passing Score <span class="required">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="passing_score" class="form-control"
                                            min="0" max="100" required
                                            value="{{ old('passing_score', $quiz->passing_score ?? 70) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Attempts</label>
                                    <input type="number" name="max_attempts" class="form-control"
                                        min="1" placeholder="Unlimited"
                                        value="{{ old('max_attempts', $quiz->max_attempts) }}">
                                    <div class="form-text">Leave empty for unlimited</div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-card">
                            <h6><i class="fas fa-random me-2"></i>Display Settings</h6>

                            <div class="settings-item">
                                <div class="settings-item-info">
                                    <h6>Shuffle Questions</h6>
                                    <p>Randomize question order for each attempt</p>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input" name="shuffle_questions"
                                        id="shuffleQuestions" value="1"
                                        {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}
                                        style="width: 40px; height: 20px;">
                                </div>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-info">
                                    <h6>Shuffle Answers</h6>
                                    <p>Randomize answer choices for multiple choice questions</p>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input" name="shuffle_answers"
                                        id="shuffleAnswers" value="1"
                                        {{ old('shuffle_answers', $quiz->shuffle_answers) ? 'checked' : '' }}
                                        style="width: 40px; height: 20px;">
                                </div>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-info">
                                    <h6>Show Correct Answers</h6>
                                    <p>Display correct answers after quiz submission</p>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input" name="show_correct_answers"
                                        id="showCorrectAnswers" value="1"
                                        {{ old('show_correct_answers', $quiz->show_correct_answers) ? 'checked' : '' }}
                                        style="width: 40px; height: 20px;">
                                </div>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-info">
                                    <h6>Show Feedback</h6>
                                    <p>Display question feedback after submission</p>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input" name="show_feedback"
                                        id="showFeedback" value="1"
                                        {{ old('show_feedback', $quiz->show_feedback ?? true) ? 'checked' : '' }}
                                        style="width: 40px; height: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="section-title">Quiz Statistics</h6>

                        <div class="row">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ $quiz->questions()->count() }}</div>
                                    <div class="stat-label">Questions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ $quiz->questions()->sum('points') }}</div>
                                    <div class="stat-label">Total Points</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-value">{{ $quiz->attempts()->count() }}</div>
                                    <div class="stat-label">Attempts</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    @php
                                        $avgScore = $quiz->attempts()->whereNotNull('score')->avg('score');
                                    @endphp
                                    <div class="stat-value">{{ $avgScore ? number_format($avgScore, 1) . '%' : '-' }}</div>
                                    <div class="stat-label">Avg Score</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="stat-card">
                                    <div class="stat-value">{{ $enrolledCount }}</div>
                                    <div class="stat-label">Enrolled Students</div>
                                </div>
                            </div>
                        </div>

                        @if ($enrollments->count() > 0)
                            <div class="settings-card" style="padding: 0;">
                                <div style="padding: 12px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                    <h6 style="margin: 0; font-size: 14px;"><i class="fas fa-user-graduate me-1"></i> Student List</h6>
                                    <span class="badge bg-secondary">{{ $enrollments->count() }}</span>
                                </div>
                                <div style="max-height: 280px; overflow-y: auto;">
                                    @foreach ($enrollments as $enrollment)
                                        @if ($enrollment->student)
                                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 20px; border-bottom: 1px solid #e5e7eb; font-size: 13px;">
                                                <span style="color: #6b7280; min-width: 40px;">{{ $enrollment->student->id }}</span>
                                                <span style="color: #1f2937;">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <h6 class="section-title mt-4">Quick Links</h6>

                        <ul class="quick-links">
                            <li>
                                <a href="{{ route('lms.quizzes.questions', $quiz) }}">
                                    <i class="fas fa-list-ol"></i> Manage Questions
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.quizzes.attempts', $quiz) }}">
                                    <i class="fas fa-clipboard-check"></i> View Attempts
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.quizzes.enrollments', $quiz) }}">
                                    <i class="fas fa-users"></i> View Enrollments
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.quizzes.show', $quiz) }}">
                                    <i class="fas fa-eye"></i> Preview Quiz
                                </a>
                            </li>
                            @if ($quiz->contentItem && $quiz->contentItem->module)
                                <li>
                                    <a href="{{ route('lms.modules.edit', $quiz->contentItem->module) }}">
                                        <i class="fas fa-folder"></i> Back to Module
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('lms.courses.edit', $quiz->contentItem->module->course) }}">
                                        <i class="fas fa-book"></i> Back to Course
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="form-actions">
                    @if ($quiz->contentItem && $quiz->contentItem->module)
                        <a href="{{ route('lms.modules.edit', $quiz->contentItem->module) }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
        });

        function initializeFormValidation() {
            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
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
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        }
    </script>
@endsection

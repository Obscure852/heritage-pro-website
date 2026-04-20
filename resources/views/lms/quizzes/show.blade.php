@extends('layouts.master')

@section('title')
    {{ $quiz->title }}
@endsection

@section('css')
    <style>
        .quiz-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 32px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .quiz-meta {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .quiz-meta-item {
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

        .status-draft { background: rgba(255,255,255,0.2); color: white; }
        .status-published { background: #d1fae5; color: #065f46; }

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

        .settings-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .settings-item:last-child {
            border-bottom: none;
        }

        .settings-label {
            color: #6b7280;
            font-size: 13px;
        }

        .settings-value {
            font-weight: 600;
            color: #1f2937;
        }

        .attempts-card {
            border-left: 4px solid #f59e0b;
        }

        .attempts-card.passed {
            border-left-color: #10b981;
            background: #ecfdf5;
        }

        .attempt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attempt-table th, .attempt-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .attempt-table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 13px;
            color: #6b7280;
        }

        .attempt-table tr:last-child td {
            border-bottom: none;
        }

        .score-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 13px;
        }

        .score-badge.passed {
            background: #d1fae5;
            color: #065f46;
        }

        .score-badge.failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .remaining-attempts {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .remaining-number {
            font-size: 36px;
            font-weight: 700;
            color: #f59e0b;
        }

        .remaining-label {
            font-size: 13px;
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

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        .access-code-form {
            background: #fef3c7;
            padding: 16px;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .best-score-display {
            text-align: center;
            padding: 24px;
        }

        .best-score {
            font-size: 48px;
            font-weight: 700;
            color: #1f2937;
        }

        .best-max {
            font-size: 24px;
            color: #6b7280;
        }

        .best-percentage {
            font-size: 14px;
            color: #6b7280;
            margin-top: 8px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            @if ($quiz->contentItem && $quiz->contentItem->module)
                <a href="{{ route('lms.courses.show', $quiz->contentItem->module->course) }}">
                    {{ $quiz->contentItem->module->course->title }}
                </a>
            @else
                Quiz
            @endif
        @endslot
        @slot('title')
            {{ $quiz->title }}
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

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert label-icon"></i><strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="quiz-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 style="margin:0;">{{ $quiz->title }}</h2>
                @if ($quiz->contentItem && $quiz->contentItem->description)
                    <p style="margin:8px 0 0 0; opacity:0.9;">{{ $quiz->contentItem->description }}</p>
                @endif
            </div>
        </div>

        <div class="quiz-meta">
            <div class="quiz-meta-item">
                <i class="fas fa-question-circle"></i>
                {{ $quiz->questions->count() }} question{{ $quiz->questions->count() != 1 ? 's' : '' }}
            </div>
            <div class="quiz-meta-item">
                <i class="fas fa-star"></i>
                {{ $quiz->total_points }} points
            </div>
            @if ($quiz->hasTimeLimit())
                <div class="quiz-meta-item">
                    <i class="fas fa-clock"></i>
                    {{ $quiz->time_limit_minutes }} minutes
                </div>
            @endif
            <div class="quiz-meta-item">
                <i class="fas fa-trophy"></i>
                Pass: {{ $quiz->passing_score }}%
            </div>
            @if ($quiz->hasAttemptLimit())
                <div class="quiz-meta-item">
                    <i class="fas fa-redo"></i>
                    {{ $quiz->max_attempts }} attempt{{ $quiz->max_attempts != 1 ? 's' : '' }} max
                </div>
            @else
                <div class="quiz-meta-item">
                    <i class="fas fa-infinity"></i>
                    Unlimited attempts
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if ($quiz->instructions)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="instructions-content">
                            {!! clean($quiz->instructions) !!}
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-cog me-2"></i>Quiz Settings</h5>
                </div>
                <div class="card-body">
                    <div class="settings-item">
                        <span class="settings-label">Time Limit</span>
                        <span class="settings-value">
                            @if ($quiz->hasTimeLimit())
                                {{ $quiz->time_limit_minutes }} minutes
                            @else
                                No time limit
                            @endif
                        </span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Passing Score</span>
                        <span class="settings-value">{{ $quiz->passing_score }}%</span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Maximum Attempts</span>
                        <span class="settings-value">
                            @if ($quiz->hasAttemptLimit())
                                {{ $quiz->max_attempts }}
                            @else
                                Unlimited
                            @endif
                        </span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Question Order</span>
                        <span class="settings-value">
                            {{ $quiz->shuffle_questions ? 'Randomized' : 'Sequential' }}
                        </span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Answer Order</span>
                        <span class="settings-value">
                            {{ $quiz->shuffle_answers ? 'Randomized' : 'Fixed' }}
                        </span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Show Correct Answers</span>
                        <span class="settings-value">
                            @if ($quiz->show_correct_answers)
                                <span class="text-success"><i class="fas fa-check me-1"></i>Yes</span>
                            @else
                                <span class="text-muted"><i class="fas fa-times me-1"></i>No</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            @if ($attempts && $attempts->count() > 0)
                <div class="card attempts-card {{ $attempts->where('passed', true)->count() > 0 ? 'passed' : '' }}">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-history me-2"></i>Your Attempt History</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="attempt-table">
                            <thead>
                                <tr>
                                    <th>Attempt</th>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Time Spent</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attempts as $attempt)
                                    <tr>
                                        <td>#{{ $attempt->attempt_number }}</td>
                                        <td>{{ $attempt->started_at->format('M j, Y g:i A') }}</td>
                                        <td>
                                            @if ($attempt->is_submitted)
                                                {{ number_format($attempt->score, 1) }}%
                                            @else
                                                <span class="text-muted">In progress</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!$attempt->is_submitted)
                                                <span class="score-badge" style="background: #fef3c7; color: #92400e;">In Progress</span>
                                            @elseif ($attempt->passed)
                                                <span class="score-badge passed">Passed</span>
                                            @else
                                                <span class="score-badge failed">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($attempt->time_spent_seconds)
                                                {{ floor($attempt->time_spent_seconds / 60) }}m {{ $attempt->time_spent_seconds % 60 }}s
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($attempt->is_submitted)
                                                <a href="{{ route('lms.quizzes.results', [$quiz, $attempt]) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            @else
                                                <a href="{{ route('lms.quizzes.attempt', [$quiz, $attempt]) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-play"></i> Continue
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                            <a href="{{ route('lms.quizzes.attempts', $quiz) }}" class="btn btn-primary">
                                <i class="fas fa-clipboard-check me-1"></i> View Attempts
                            </a>
                            <a href="{{ route('lms.quizzes.enrollments', $quiz) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-users me-1"></i> View Enrollments
                                <span class="badge bg-secondary ms-1">{{ $enrolledCount }}</span>
                            </a>
                            <a href="{{ route('lms.quizzes.questions', $quiz) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-1"></i> Manage Questions
                            </a>
                            <a href="{{ route('lms.quizzes.edit', $quiz) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> Edit Quiz
                            </a>
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

            @if ($attempts && $attempts->where('is_submitted', true)->count() > 0)
                @php
                    $bestAttempt = $attempts->where('is_submitted', true)->sortByDesc('score')->first();
                @endphp
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-trophy me-2"></i>Best Score</h5>
                    </div>
                    <div class="card-body">
                        <div class="best-score-display">
                            <div>
                                <span class="best-score">{{ number_format($bestAttempt->score, 1) }}</span>
                                <span class="best-max">%</span>
                            </div>
                            <div class="best-percentage">
                                @if ($bestAttempt->passed)
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Passed</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Not Passed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        @if ($canAttempt)
                            <i class="fas fa-play-circle me-2"></i>Start Quiz
                        @else
                            <i class="fas fa-lock me-2"></i>Quiz Status
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($quiz->hasAttemptLimit() && auth('student')->check())
                        <div class="remaining-attempts">
                            <div class="remaining-number">{{ $quiz->getRemainingAttempts(auth('student')->id()) }}</div>
                            <div class="remaining-label">attempt{{ $quiz->getRemainingAttempts(auth('student')->id()) != 1 ? 's' : '' }} remaining</div>
                        </div>
                    @endif

                    @if ($quiz->require_access_code)
                        <form action="{{ route('lms.quizzes.start', $quiz) }}" method="POST" class="access-code-form">
                            @csrf
                            <label class="form-label"><i class="fas fa-key me-1"></i>Access Code Required</label>
                            <input type="text" name="access_code" class="form-control mb-3" placeholder="Enter access code" required>
                            @if ($canAttempt)
                                <button type="submit" class="btn btn-primary btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-play me-1"></i> Start Quiz</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Starting...
                                    </span>
                                </button>
                            @endif
                        </form>
                    @else
                        @if ($canAttempt)
                            <form action="{{ route('lms.quizzes.start', $quiz) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-loading w-100 mb-3">
                                    <span class="btn-text"><i class="fas fa-play me-1"></i> Start Quiz</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Starting...
                                    </span>
                                </button>
                            </form>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-lock fa-2x mb-3"></i>
                                <p class="mb-0">
                                    @if (auth('student')->check() && (!$quiz->hasAttemptLimit() || $quiz->getRemainingAttempts(auth('student')->id()) <= 0))
                                        You have used all your attempts for this quiz.
                                    @else
                                        This quiz is not available.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif

                    @if ($quiz->hasTimeLimit())
                        <div class="alert alert-warning mb-0" style="font-size: 13px;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Once started, you will have <strong>{{ $quiz->time_limit_minutes }} minutes</strong> to complete this quiz. The timer will not pause.
                        </div>
                    @endif
                </div>
            </div>

            @if ($quiz->contentItem && $quiz->contentItem->module)
                <a href="{{ route('lms.courses.show', $quiz->contentItem->module->course) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-1"></i> Back to Course
                </a>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection

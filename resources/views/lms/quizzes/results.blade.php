@extends('layouts.master')

@section('title')
    Quiz Results - {{ $quiz->title }}
@endsection

@section('css')
    <style>
        .results-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .results-header {
            background: linear-gradient(135deg, {{ $attempt->passed ? '#10b981' : '#ef4444' }} 0%, {{ $attempt->passed ? '#059669' : '#dc2626' }} 100%);
            color: white;
            padding: 32px;
            border-radius: 3px;
            margin-bottom: 24px;
            text-align: center;
        }

        .results-header h2 {
            margin: 0 0 8px 0;
        }

        .score-display {
            margin: 24px 0;
        }

        .score-big {
            font-size: 72px;
            font-weight: 700;
            line-height: 1;
        }

        .score-max {
            font-size: 24px;
            opacity: 0.8;
        }

        .score-label {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }

        .pass-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            background: rgba(255,255,255,0.2);
            margin-top: 16px;
        }

        .results-meta {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .results-meta-item {
            text-align: center;
        }

        .results-meta-item strong {
            display: block;
            font-size: 18px;
        }

        .results-meta-item span {
            font-size: 13px;
            opacity: 0.9;
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

        .summary-stat {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-stat:last-child {
            border-bottom: none;
        }

        .summary-stat-label {
            color: #6b7280;
        }

        .summary-stat-value {
            font-weight: 600;
            color: #1f2937;
        }

        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-bar-fill.passed {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        .progress-bar-fill.failed {
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
        }

        .passing-line {
            position: relative;
            margin-top: -30px;
            margin-bottom: 10px;
        }

        .passing-marker {
            position: absolute;
            height: 30px;
            width: 2px;
            background: #1f2937;
            top: 0;
        }

        .passing-marker::after {
            content: '{{ $quiz->passing_score }}%';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #6b7280;
            white-space: nowrap;
        }

        .question-review {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .question-review-header {
            padding: 14px 20px;
            background: #f9fafb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .question-review-number {
            font-weight: 600;
            color: #1f2937;
        }

        .question-review-score {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .question-review-score.correct {
            background: #d1fae5;
            color: #065f46;
        }

        .question-review-score.incorrect {
            background: #fee2e2;
            color: #991b1b;
        }

        .question-review-score.partial {
            background: #fef3c7;
            color: #92400e;
        }

        .question-review-body {
            padding: 20px;
        }

        .question-review-text {
            color: #1f2937;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .answer-review {
            margin-bottom: 12px;
        }

        .answer-review-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .answer-review-content {
            padding: 10px 14px;
            border-radius: 3px;
            font-size: 14px;
        }

        .answer-review-content.your-answer {
            background: #f3f4f6;
            border-left: 3px solid #6b7280;
        }

        .answer-review-content.your-answer.correct {
            background: #d1fae5;
            border-left-color: #10b981;
        }

        .answer-review-content.your-answer.incorrect {
            background: #fee2e2;
            border-left-color: #ef4444;
        }

        .answer-review-content.correct-answer {
            background: #d1fae5;
            border-left: 3px solid #10b981;
        }

        .feedback-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 12px 16px;
            margin-top: 12px;
            font-size: 14px;
            color: #1e40af;
        }

        .feedback-box i {
            margin-right: 8px;
        }

        .actions-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
        }

        .btn {
            padding: 12px 24px;
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

        .answers-hidden-notice {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .answers-hidden-notice i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #d1d5db;
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
            Quiz Results
        @endslot
    @endcomponent

    <div class="results-container">
        <div class="results-header">
            <h2>{{ $quiz->title }}</h2>
            <p>Attempt #{{ $attempt->attempt_number }}</p>

            <div class="score-display">
                <span class="score-big">{{ number_format($attempt->percentage, 1) }}</span>
                <span class="score-max">%</span>
                <div class="score-label">{{ $attempt->score }} / {{ $attempt->max_score }} points</div>
            </div>

            <span class="pass-badge">
                @if ($attempt->passed)
                    <i class="fas fa-check-circle me-1"></i> Passed
                @else
                    <i class="fas fa-times-circle me-1"></i> Not Passed
                @endif
            </span>

            <div class="results-meta">
                <div class="results-meta-item">
                    <strong>{{ $attempt->time_spent_formatted }}</strong>
                    <span>Time Spent</span>
                </div>
                <div class="results-meta-item">
                    <strong>{{ $attempt->submitted_at->format('M j, Y') }}</strong>
                    <span>Submitted</span>
                </div>
                <div class="results-meta-item">
                    <strong>{{ $quiz->passing_score }}%</strong>
                    <span>Passing Score</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @if ($showAnswers)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-list-check me-2"></i>Question Review</h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach ($quiz->questions as $index => $question)
                                @php
                                    $answers = $attempt->answers ?? [];
                                    $answerData = $answers[$question->id] ?? [];
                                    $studentResponse = $answerData['response'] ?? null;
                                    $isCorrect = $answerData['is_correct'] ?? false;
                                    $earnedPoints = $answerData['score'] ?? 0;
                                @endphp

                                <div class="question-review">
                                    <div class="question-review-header">
                                        <span class="question-review-number">Question {{ $index + 1 }}</span>
                                        <span class="question-review-score {{ $isCorrect ? 'correct' : ($earnedPoints > 0 ? 'partial' : 'incorrect') }}">
                                            @if ($isCorrect)
                                                <i class="fas fa-check me-1"></i>
                                            @elseif ($earnedPoints > 0)
                                                <i class="fas fa-minus me-1"></i>
                                            @else
                                                <i class="fas fa-times me-1"></i>
                                            @endif
                                            {{ $earnedPoints }} / {{ $question->points }} pts
                                        </span>
                                    </div>
                                    <div class="question-review-body">
                                        <div class="question-review-text">
                                            {!! clean($question->question_text) !!}
                                        </div>

                                        <div class="answer-review">
                                            <div class="answer-review-label">Your Answer</div>
                                            <div class="answer-review-content your-answer {{ $isCorrect ? 'correct' : 'incorrect' }}">
                                                @if ($studentResponse === null || $studentResponse === '')
                                                    <em class="text-muted">No answer provided</em>
                                                @elseif (in_array($question->type, ['multiple_choice', 'true_false']))
                                                    @php
                                                        $options = $question->options ?? ['True', 'False'];
                                                        $answerIndex = is_array($studentResponse) ? ($studentResponse[0] ?? 0) : $studentResponse;
                                                    @endphp
                                                    {{ $options[$answerIndex] ?? 'Unknown' }}
                                                @elseif ($question->type === 'multiple_answer')
                                                    @php
                                                        $options = $question->options ?? [];
                                                        $selectedIndices = is_array($studentResponse) ? $studentResponse : [$studentResponse];
                                                    @endphp
                                                    @foreach ($selectedIndices as $idx)
                                                        <span class="badge bg-secondary me-1">{{ $options[$idx] ?? 'Unknown' }}</span>
                                                    @endforeach
                                                @else
                                                    {{ $studentResponse }}
                                                @endif
                                            </div>
                                        </div>

                                        @if (!$isCorrect)
                                            <div class="answer-review">
                                                <div class="answer-review-label">Correct Answer</div>
                                                <div class="answer-review-content correct-answer">
                                                    @if (in_array($question->type, ['multiple_choice', 'true_false']))
                                                        @php
                                                            $options = $question->options ?? ['True', 'False'];
                                                            $correctIndices = $question->correct_answer ?? [];
                                                            $correctIndex = is_array($correctIndices) ? ($correctIndices[0] ?? 0) : $correctIndices;
                                                        @endphp
                                                        {{ $options[$correctIndex] ?? 'Unknown' }}
                                                    @elseif ($question->type === 'multiple_answer')
                                                        @php
                                                            $options = $question->options ?? [];
                                                            $correctIndices = $question->correct_answer ?? [];
                                                        @endphp
                                                        @foreach ($correctIndices as $idx)
                                                            <span class="badge bg-success me-1">{{ $options[$idx] ?? 'Unknown' }}</span>
                                                        @endforeach
                                                    @elseif ($question->type === 'fill_blank')
                                                        @php
                                                            $acceptableAnswers = $question->correct_answer ?? [];
                                                        @endphp
                                                        {{ is_array($acceptableAnswers) ? implode(' or ', $acceptableAnswers) : $acceptableAnswers }}
                                                    @else
                                                        <em class="text-muted">See feedback below</em>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if ($showFeedback)
                                            @if ($isCorrect && $question->feedback_correct)
                                                <div class="feedback-box">
                                                    <i class="fas fa-lightbulb"></i>{{ $question->feedback_correct }}
                                                </div>
                                            @elseif (!$isCorrect && $question->feedback_incorrect)
                                                <div class="feedback-box">
                                                    <i class="fas fa-lightbulb"></i>{{ $question->feedback_incorrect }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body answers-hidden-notice">
                            <i class="fas fa-eye-slash d-block"></i>
                            <h5>Answers Hidden</h5>
                            <p class="mb-0">
                                @if ($quiz->show_correct_answers_after)
                                    Correct answers will be available after {{ $quiz->show_correct_answers_after->format('M j, Y g:i A') }}.
                                @else
                                    The instructor has chosen not to display correct answers for this quiz.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif

                @if ($attempt->feedback)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-comment me-2"></i>Instructor Feedback</h5>
                        </div>
                        <div class="card-body">
                            {!! clean($attempt->feedback) !!}
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Score Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill {{ $attempt->passed ? 'passed' : 'failed' }}"
                                 style="width: {{ min($attempt->percentage, 100) }}%"></div>
                        </div>
                        <div class="passing-line">
                            <div class="passing-marker" style="left: {{ $quiz->passing_score }}%"></div>
                        </div>

                        <div class="summary-stat">
                            <span class="summary-stat-label">Your Score</span>
                            <span class="summary-stat-value">{{ number_format($attempt->percentage, 1) }}%</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-label">Points Earned</span>
                            <span class="summary-stat-value">{{ $attempt->score }} / {{ $attempt->max_score }}</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-label">Passing Score</span>
                            <span class="summary-stat-value">{{ $quiz->passing_score }}%</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-label">Time Spent</span>
                            <span class="summary-stat-value">{{ $attempt->time_spent_formatted }}</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-label">Submitted</span>
                            <span class="summary-stat-value">{{ $attempt->submitted_at->format('M j, g:i A') }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $canRetry = $quiz->canStudentAttempt(auth('student')->id());
                    $remainingAttempts = $quiz->getRemainingAttempts(auth('student')->id());
                @endphp

                @if ($canRetry)
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-3">
                                @if ($remainingAttempts !== null)
                                    You have <strong>{{ $remainingAttempts }}</strong> attempt{{ $remainingAttempts != 1 ? 's' : '' }} remaining.
                                @else
                                    You can attempt this quiz again.
                                @endif
                            </p>
                            <form action="{{ route('lms.quizzes.start', $quiz) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-loading w-100">
                                    <span class="btn-text"><i class="fas fa-redo me-1"></i> Try Again</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Starting...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="actions-row flex-column">
                    <a href="{{ route('lms.quizzes.show', $quiz) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-eye me-1"></i> View Quiz Details
                    </a>
                    @if ($quiz->contentItem && $quiz->contentItem->module)
                        <a href="{{ route('lms.courses.show', $quiz->contentItem->module->course) }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-1"></i> Back to Course
                        </a>
                    @endif
                </div>
            </div>
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

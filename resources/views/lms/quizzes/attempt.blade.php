@extends('layouts.master')

@section('title')
    {{ $quiz->title }} - Attempt
@endsection

@section('css')
    <style>
        .quiz-attempt-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .timer-bar {
            position: sticky;
            top: 70px;
            z-index: 100;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timer-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .quiz-title {
            font-weight: 600;
            color: #1f2937;
        }

        .timer-display {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #374151;
        }

        .timer-display.warning {
            color: #f59e0b;
        }

        .timer-display.danger {
            color: #dc2626;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .progress-info {
            font-size: 14px;
            color: #6b7280;
        }

        .question-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .question-header {
            background: #f9fafb;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-number {
            font-weight: 600;
            color: #1f2937;
        }

        .question-points {
            font-size: 13px;
            color: #6b7280;
            background: #e5e7eb;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .question-body {
            padding: 20px;
        }

        .question-text {
            font-size: 16px;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .answer-options {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .answer-option {
            margin-bottom: 12px;
        }

        .answer-option label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .answer-option label:hover {
            background: #f9fafb;
            border-color: #f59e0b;
        }

        .answer-option input:checked + label,
        .answer-option input[type="checkbox"]:checked ~ label {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .answer-option input[type="radio"],
        .answer-option input[type="checkbox"] {
            display: none;
        }

        .answer-option .option-indicator {
            width: 22px;
            height: 22px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .answer-option input[type="checkbox"] ~ label .option-indicator {
            border-radius: 3px;
        }

        .answer-option input:checked + label .option-indicator,
        .answer-option input[type="checkbox"]:checked ~ label .option-indicator {
            background: #f59e0b;
            border-color: #f59e0b;
        }

        .answer-option input:checked + label .option-indicator::after,
        .answer-option input[type="checkbox"]:checked ~ label .option-indicator::after {
            content: '\2714';
            color: white;
            font-size: 12px;
        }

        .answer-option .option-text {
            flex: 1;
            color: #374151;
        }

        .text-answer textarea,
        .text-answer input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 15px;
        }

        .text-answer textarea:focus,
        .text-answer input:focus {
            border-color: #f59e0b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .question-footer {
            padding: 12px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .flag-question {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        .flag-question input:checked + span {
            color: #f59e0b;
        }

        .auto-save-indicator {
            font-size: 12px;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .auto-save-indicator.saving {
            color: #6b7280;
        }

        .question-nav {
            position: fixed;
            right: 24px;
            top: 150px;
            width: 200px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 16px;
        }

        @media (max-width: 1200px) {
            .question-nav {
                display: none;
            }
        }

        .question-nav h6 {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .question-nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
        }

        .question-nav-item {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .question-nav-item:hover {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .question-nav-item.answered {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }

        .question-nav-item.flagged {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        .question-nav-item.current {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }

        .question-nav-legend {
            margin-top: 12px;
            font-size: 11px;
            color: #6b7280;
        }

        .question-nav-legend div {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .question-nav-legend span {
            width: 14px;
            height: 14px;
            border-radius: 2px;
        }

        .submit-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 24px;
            text-align: center;
            margin-top: 24px;
        }

        .submit-section h5 {
            margin-bottom: 12px;
            color: #1f2937;
        }

        .submit-section p {
            color: #6b7280;
            margin-bottom: 20px;
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

        .btn-outline-danger {
            border: 1px solid #dc2626;
            color: #dc2626;
            background: white;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
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
    </style>
@endsection

@section('content')
    <div class="quiz-attempt-container">
        <div class="timer-bar">
            <div class="timer-left">
                <span class="quiz-title">{{ $quiz->title }}</span>
                @if ($quiz->hasTimeLimit())
                    <div class="timer-display" id="timer">
                        <i class="fas fa-clock"></i>
                        <span id="timer-value">--:--</span>
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="progress-info">
                    <span id="answered-count">0</span> / {{ $quiz->questions->count() }} answered
                </span>
                <form action="{{ route('lms.quizzes.submit', [$quiz, $attempt]) }}" method="POST" id="submitForm">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-loading" onclick="return confirmSubmit(this)">
                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Submit Quiz</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Submitting...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <div id="questions-container">
            @foreach ($quiz->questions as $index => $question)
                <div class="question-card" id="question-{{ $question->id }}" data-question-id="{{ $question->id }}">
                    <div class="question-header">
                        <span class="question-number">Question {{ $index + 1 }}</span>
                        <span class="question-points">{{ $question->points }} point{{ $question->points != 1 ? 's' : '' }}</span>
                    </div>
                    <div class="question-body">
                        <div class="question-text">
                            {!! clean($question->question_text) !!}
                        </div>

                        @php
                            $savedAnswer = $attempt->getAnswerForQuestion($question->id);
                            $options = $question->options ?? [];
                            if ($quiz->shuffle_answers && in_array($question->type, ['multiple_choice', 'multiple_answer'])) {
                                $keys = array_keys($options);
                                shuffle($keys);
                                $shuffledOptions = [];
                                foreach ($keys as $key) {
                                    $shuffledOptions[$key] = $options[$key];
                                }
                                $options = $shuffledOptions;
                            }
                        @endphp

                        @switch($question->type)
                            @case('multiple_choice')
                                <ul class="answer-options">
                                    @foreach ($options as $optIndex => $option)
                                        <li class="answer-option">
                                            <input type="radio"
                                                name="question_{{ $question->id }}"
                                                id="q{{ $question->id }}_opt{{ $optIndex }}"
                                                value="{{ $optIndex }}"
                                                class="question-answer"
                                                data-question-id="{{ $question->id }}"
                                                {{ $savedAnswer == $optIndex ? 'checked' : '' }}>
                                            <label for="q{{ $question->id }}_opt{{ $optIndex }}">
                                                <span class="option-indicator"></span>
                                                <span class="option-text">{{ $option }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                @break

                            @case('multiple_answer')
                                <ul class="answer-options">
                                    @php
                                        $savedAnswers = is_array($savedAnswer) ? $savedAnswer : [];
                                    @endphp
                                    @foreach ($options as $optIndex => $option)
                                        <li class="answer-option">
                                            <input type="checkbox"
                                                name="question_{{ $question->id }}[]"
                                                id="q{{ $question->id }}_opt{{ $optIndex }}"
                                                value="{{ $optIndex }}"
                                                class="question-answer question-answer-multi"
                                                data-question-id="{{ $question->id }}"
                                                {{ in_array($optIndex, $savedAnswers) ? 'checked' : '' }}>
                                            <label for="q{{ $question->id }}_opt{{ $optIndex }}">
                                                <span class="option-indicator"></span>
                                                <span class="option-text">{{ $option }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                @break

                            @case('true_false')
                                <ul class="answer-options">
                                    <li class="answer-option">
                                        <input type="radio" name="question_{{ $question->id }}"
                                            id="q{{ $question->id }}_true" value="0"
                                            class="question-answer" data-question-id="{{ $question->id }}"
                                            {{ $savedAnswer === 0 || $savedAnswer === '0' ? 'checked' : '' }}>
                                        <label for="q{{ $question->id }}_true">
                                            <span class="option-indicator"></span>
                                            <span class="option-text">True</span>
                                        </label>
                                    </li>
                                    <li class="answer-option">
                                        <input type="radio" name="question_{{ $question->id }}"
                                            id="q{{ $question->id }}_false" value="1"
                                            class="question-answer" data-question-id="{{ $question->id }}"
                                            {{ $savedAnswer === 1 || $savedAnswer === '1' ? 'checked' : '' }}>
                                        <label for="q{{ $question->id }}_false">
                                            <span class="option-indicator"></span>
                                            <span class="option-text">False</span>
                                        </label>
                                    </li>
                                </ul>
                                @break

                            @case('fill_blank')
                                <div class="text-answer">
                                    <input type="text"
                                        name="question_{{ $question->id }}"
                                        class="question-answer"
                                        data-question-id="{{ $question->id }}"
                                        placeholder="Type your answer here..."
                                        value="{{ $savedAnswer ?? '' }}">
                                </div>
                                @break

                            @case('short_answer')
                                <div class="text-answer">
                                    <textarea
                                        name="question_{{ $question->id }}"
                                        class="question-answer"
                                        data-question-id="{{ $question->id }}"
                                        rows="3"
                                        placeholder="Type your answer here...">{{ $savedAnswer ?? '' }}</textarea>
                                </div>
                                @break

                            @case('essay')
                                <div class="text-answer">
                                    <textarea
                                        name="question_{{ $question->id }}"
                                        class="question-answer"
                                        data-question-id="{{ $question->id }}"
                                        rows="8"
                                        placeholder="Write your essay response here...">{{ $savedAnswer ?? '' }}</textarea>
                                </div>
                                @break

                            @default
                                <div class="text-answer">
                                    <textarea
                                        name="question_{{ $question->id }}"
                                        class="question-answer"
                                        data-question-id="{{ $question->id }}"
                                        rows="4"
                                        placeholder="Type your answer here...">{{ $savedAnswer ?? '' }}</textarea>
                                </div>
                        @endswitch
                    </div>
                    <div class="question-footer">
                        <label class="flag-question">
                            <input type="checkbox" id="flag_{{ $question->id }}" onchange="toggleFlag({{ $question->id }})">
                            <span><i class="fas fa-flag me-1"></i> Flag for review</span>
                        </label>
                        <span class="auto-save-indicator" id="save-indicator-{{ $question->id }}">
                            <i class="fas fa-check-circle"></i> Saved
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="submit-section">
            <h5>Ready to submit?</h5>
            <p>Make sure you have answered all questions before submitting. You cannot change your answers after submission.</p>
            <form action="{{ route('lms.quizzes.submit', [$quiz, $attempt]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg btn-loading" onclick="return confirmSubmit(this)">
                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i> Submit Quiz</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Submitting...
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- Question Navigation Sidebar --}}
    <div class="question-nav" id="questionNav">
        <h6>Questions</h6>
        <div class="question-nav-grid">
            @foreach ($quiz->questions as $index => $question)
                <a href="#question-{{ $question->id }}"
                   class="question-nav-item"
                   id="nav-{{ $question->id }}"
                   data-question-id="{{ $question->id }}">
                    {{ $index + 1 }}
                </a>
            @endforeach
        </div>
        <div class="question-nav-legend">
            <div><span style="background: #d1fae5; border: 1px solid #10b981;"></span> Answered</div>
            <div><span style="background: #fef3c7; border: 1px solid #f59e0b;"></span> Flagged</div>
            <div><span style="background: #e5e7eb; border: 1px solid #d1d5db;"></span> Unanswered</div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Configuration
        const quizId = {{ $quiz->id }};
        const attemptId = {{ $attempt->id }};
        const hasTimeLimit = {{ $quiz->hasTimeLimit() ? 'true' : 'false' }};
        const totalQuestions = {{ $quiz->questions->count() }};
        let remainingSeconds = {{ $attempt->remaining_time ?? 0 }};
        let timerInterval;
        let saveTimeout;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            if (hasTimeLimit && remainingSeconds > 0) {
                startTimer();
            }

            initializeAnswerTracking();
            updateAnsweredCount();
            preventNavigation();
        });

        // Timer functions
        function startTimer() {
            updateTimerDisplay();
            timerInterval = setInterval(() => {
                remainingSeconds--;
                updateTimerDisplay();

                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    autoSubmit();
                }

                // Warnings
                if (remainingSeconds === 300) {
                    showTimeWarning('5 minutes remaining!');
                }
                if (remainingSeconds === 60) {
                    showTimeWarning('1 minute remaining!', true);
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const timerEl = document.getElementById('timer');
            const timerValue = document.getElementById('timer-value');
            if (!timerEl || !timerValue) return;

            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            timerValue.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;

            timerEl.classList.remove('warning', 'danger');
            if (remainingSeconds <= 60) {
                timerEl.classList.add('danger');
            } else if (remainingSeconds <= 300) {
                timerEl.classList.add('warning');
            }
        }

        function showTimeWarning(message, isDanger = false) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Time Warning',
                    text: message,
                    icon: isDanger ? 'error' : 'warning',
                    toast: true,
                    position: 'top-end',
                    timer: 5000,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        }

        function autoSubmit() {
            alert('Time is up! Your quiz will be submitted automatically.');
            document.getElementById('submitForm').submit();
        }

        // Answer tracking
        function initializeAnswerTracking() {
            document.querySelectorAll('.question-answer').forEach(input => {
                input.addEventListener('change', function() {
                    const questionId = this.dataset.questionId;
                    let response;

                    if (this.classList.contains('question-answer-multi')) {
                        // Multiple answer - collect all checked values
                        const checkboxes = document.querySelectorAll(`input[name="question_${questionId}[]"]:checked`);
                        response = Array.from(checkboxes).map(cb => parseInt(cb.value));
                    } else if (this.type === 'radio') {
                        response = parseInt(this.value);
                    } else {
                        response = this.value;
                    }

                    saveAnswer(questionId, response);
                    updateNavItem(questionId, true);
                    updateAnsweredCount();
                });

                // For text inputs, save on blur or after typing stops
                if (input.tagName === 'TEXTAREA' || input.type === 'text') {
                    input.addEventListener('input', function() {
                        clearTimeout(saveTimeout);
                        const questionId = this.dataset.questionId;
                        showSaving(questionId);

                        saveTimeout = setTimeout(() => {
                            saveAnswer(questionId, this.value);
                            updateNavItem(questionId, this.value.trim().length > 0);
                            updateAnsweredCount();
                        }, 1000);
                    });
                }
            });
        }

        function saveAnswer(questionId, response) {
            showSaving(questionId);

            fetch(`{{ url('lms/quizzes') }}/${quizId}/attempts/${attemptId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    response: response
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSaved(questionId);
                } else {
                    showSaveError(questionId);
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                showSaveError(questionId);
            });
        }

        function showSaving(questionId) {
            const indicator = document.getElementById(`save-indicator-${questionId}`);
            if (indicator) {
                indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                indicator.classList.add('saving');
            }
        }

        function showSaved(questionId) {
            const indicator = document.getElementById(`save-indicator-${questionId}`);
            if (indicator) {
                indicator.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
                indicator.classList.remove('saving');
            }
        }

        function showSaveError(questionId) {
            const indicator = document.getElementById(`save-indicator-${questionId}`);
            if (indicator) {
                indicator.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Save failed';
                indicator.classList.remove('saving');
            }
        }

        function updateNavItem(questionId, isAnswered) {
            const navItem = document.getElementById(`nav-${questionId}`);
            if (navItem) {
                if (isAnswered) {
                    navItem.classList.add('answered');
                } else {
                    navItem.classList.remove('answered');
                }
            }
        }

        function toggleFlag(questionId) {
            const navItem = document.getElementById(`nav-${questionId}`);
            const checkbox = document.getElementById(`flag_${questionId}`);
            if (navItem && checkbox) {
                if (checkbox.checked) {
                    navItem.classList.add('flagged');
                } else {
                    navItem.classList.remove('flagged');
                }
            }
        }

        function updateAnsweredCount() {
            let count = 0;

            document.querySelectorAll('.question-card').forEach(card => {
                const questionId = card.dataset.questionId;
                const radios = card.querySelectorAll('input[type="radio"]:checked');
                const checkboxes = card.querySelectorAll('input[type="checkbox"].question-answer:checked');
                const textInputs = card.querySelectorAll('input[type="text"].question-answer, textarea.question-answer');

                let isAnswered = false;

                if (radios.length > 0) {
                    isAnswered = true;
                } else if (checkboxes.length > 0) {
                    isAnswered = true;
                } else if (textInputs.length > 0) {
                    textInputs.forEach(input => {
                        if (input.value.trim().length > 0) {
                            isAnswered = true;
                        }
                    });
                }

                if (isAnswered) {
                    count++;
                    updateNavItem(questionId, true);
                }
            });

            document.getElementById('answered-count').textContent = count;
        }

        // Prevent accidental navigation
        function preventNavigation() {
            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();
                e.returnValue = 'Your quiz progress may be lost if you leave this page.';
            });
        }

        // Confirm submit
        function confirmSubmit(btn) {
            const answeredCount = parseInt(document.getElementById('answered-count').textContent);
            const unanswered = totalQuestions - answeredCount;
            let confirmed;

            if (unanswered > 0) {
                confirmed = confirm(`You have ${unanswered} unanswered question(s). Are you sure you want to submit?`);
            } else {
                confirmed = confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.');
            }

            if (confirmed && btn) {
                btn.classList.add('loading');
                btn.disabled = true;
                // Also disable the other submit button
                document.querySelectorAll('button[type="submit"].btn-loading').forEach(function(b) {
                    b.classList.add('loading');
                    b.disabled = true;
                });
            }

            return confirmed;
        }
    </script>
@endsection

@extends('layouts.master')

@section('title')
    Quiz Questions - {{ $quiz->title }}
@endsection

@section('css')
    <style>
        .page-container { background: white; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,.1); margin-bottom: 24px; }
        .page-header { background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; padding: 20px 24px; border-radius: 3px 3px 0 0; }
        .page-body { padding: 24px; }
        .stat-item { padding: 6px 16px; text-align: center; }
        .stat-item h4 { font-size: 1.4rem; font-weight: 700; margin: 0; color: #fff; }
        .stat-item small { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
        .help-text { background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #4e73df; border-radius: 0 3px 3px 0; margin-bottom: 24px; }
        .help-text .help-title { font-weight: 600; color: #374151; margin-bottom: 4px; }
        .help-text .help-content { color: #6b7280; font-size: 13px; line-height: 1.5; margin: 0; }
        .section-title { font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
        .question-card { border: 1px solid #e5e7eb; border-radius: 3px; margin-bottom: 16px; background: #fff; }
        .question-header { display: flex; align-items: center; padding: 12px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .question-number { width: 32px; height: 32px; background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; margin-right: 12px; }
        .question-meta { flex: 1; }
        .question-type { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .question-points { font-weight: 600; color: #059669; }
        .question-body { padding: 16px; }
        .question-text { font-size: 15px; color: #1f2937; margin-bottom: 12px; }
        .question-options { padding-left: 20px; }
        .question-options li { margin-bottom: 6px; color: #4b5563; }
        .question-options li.correct { color: #059669; font-weight: 500; }
        .empty-state { text-align: center; padding: 48px 24px; color: #6b7280; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        .form-label { font-weight: 500; color: #374151; margin-bottom: 6px; font-size: 14px; }
        .form-control, .form-select { border: 1px solid #d1d5db; border-radius: 3px !important; font-size: 14px; padding: 10px 12px; }
        .form-control:focus, .form-select:focus { border-color: #4e73df; box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1); }
        .btn { padding: 10px 16px; border-radius: 3px !important; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; }
        .btn-primary { background: #4e73df; border: none; color: white; }
        .btn-primary:hover { background: #3d5fc7; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3); color: white; }
        .btn-loading.loading .btn-text { display: none; }
        .btn-loading.loading .btn-spinner { display: inline-flex !important; align-items: center; }
        .btn-loading:disabled { opacity: 0.7; cursor: not-allowed; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; border-top: 1px solid #e5e7eb; margin-top: 24px; }
        .options-builder .option-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .options-builder .option-row input[type="text"] { flex: 1; }
        .add-option-btn { font-size: 13px; color: #4e73df; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
        .type-fields { display: none; }
        .type-fields.active { display: block; }
        .quiz-info { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 3px; padding: 16px; margin-bottom: 24px; }
        .quiz-info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        @media (max-width: 768px) { .quiz-info-grid { grid-template-columns: repeat(2, 1fr); } }
        .quiz-info-item label { font-size: 12px; color: #6b7280; display: block; margin-bottom: 2px; }
        .quiz-info-item span { font-weight: 600; color: #1f2937; }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.edit', $quiz->contentItem->module->course) }}">{{ $quiz->contentItem->module->course->title }}</a>
        @endslot
        @slot('li_3')
            <a href="{{ route('lms.modules.edit', $quiz->contentItem->module) }}">{{ $quiz->contentItem->module->title }}</a>
        @endslot
        @slot('title')
            Quiz Questions
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="page-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 style="margin: 0 0 4px 0; font-weight: 600;">{{ $quiz->title }}</h4>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">Manage quiz questions</p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex justify-content-lg-end align-items-center mt-3 mt-lg-0">
                        <div class="stat-item">
                            <h4>{{ $quiz->questions->count() }}</h4>
                            <small>Questions</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $quiz->questions->sum('points') }}</h4>
                            <small>Total Points</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="quiz-info">
                <div class="quiz-info-grid">
                    <div class="quiz-info-item">
                        <label>Time Limit</label>
                        <span>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No limit' }}</span>
                    </div>
                    <div class="quiz-info-item">
                        <label>Passing Score</label>
                        <span>{{ $quiz->passing_score ?? 70 }}%</span>
                    </div>
                    <div class="quiz-info-item">
                        <label>Max Attempts</label>
                        <span>{{ $quiz->max_attempts ?? 'Unlimited' }}</span>
                    </div>
                    <div class="quiz-info-item">
                        <label>Shuffle</label>
                        <span>{{ $quiz->shuffle_questions ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
                @if($quiz->instructions)
                    <div class="mt-3 pt-3 border-top">
                        <label class="d-block mb-1" style="font-size: 12px; color: #6b7280;">Instructions</label>
                        <p class="mb-0">{{ $quiz->instructions }}</p>
                    </div>
                @endif
            </div>

            <div class="help-text">
                <div class="help-title">Manage Questions</div>
                <p class="help-content">Add, edit, or remove questions. Questions appear in order shown unless shuffle is enabled.</p>
            </div>

            <h6 class="section-title">Questions ({{ $quiz->questions->count() }})</h6>

            @forelse($quiz->questions as $index => $question)
                <div class="question-card">
                    <div class="question-header">
                        <div class="question-number">{{ $index + 1 }}</div>
                        <div class="question-meta">
                            <span class="question-type">{{ $question->type_name }}</span>
                        </div>
                        <span class="question-points">{{ $question->points }} pts</span>
                        <div class="ms-3">
                            <form action="{{ route('lms.quizzes.questions.destroy', $question) }}" method="POST" class="d-inline delete-question-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-loading">
                                    <span class="btn-text"><i class="bx bx-trash"></i></span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="question-body">
                        <div class="question-text">{{ $question->question_text }}</div>
                        @if($question->options)
                            <ul class="question-options">
                                @foreach($question->options as $optIndex => $option)
                                    <li class="{{ is_array($question->correct_answer) && in_array($optIndex, $question->correct_answer) ? 'correct' : '' }}">
                                        {{ $option }}
                                        @if(is_array($question->correct_answer) && in_array($optIndex, $question->correct_answer))
                                            <i class="fas fa-check ms-1"></i>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-question-circle d-block"></i>
                    <h5>No Questions Yet</h5>
                    <p>Add your first question below.</p>
                </div>
            @endforelse

            <h6 class="section-title mt-4">Add New Question</h6>

            <form action="{{ route('lms.quizzes.questions.store', $quiz) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Question Type <span class="text-danger">*</span></label>
                        <select name="type" id="questionType" class="form-select" required>
                            @foreach($questionTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Points <span class="text-danger">*</span></label>
                        <input type="number" name="points" class="form-control" value="1" min="0.5" max="100" step="0.5" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Question Text <span class="text-danger">*</span></label>
                    <textarea name="question_text" class="form-control" rows="3" required placeholder="Enter your question here..."></textarea>
                </div>

                <div class="type-fields active" id="fields-mc">
                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div class="options-builder" id="optionsBuilder">
                            <div class="option-row"><input type="text" name="options[]" class="form-control" placeholder="Option A"></div>
                            <div class="option-row"><input type="text" name="options[]" class="form-control" placeholder="Option B"></div>
                            <div class="option-row"><input type="text" name="options[]" class="form-control" placeholder="Option C"></div>
                            <div class="option-row"><input type="text" name="options[]" class="form-control" placeholder="Option D"></div>
                        </div>
                        <span class="add-option-btn mt-2" onclick="addOption()"><i class="fas fa-plus"></i> Add Option</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer (index: 0,1,2...) <span class="text-danger">*</span></label>
                        <input type="text" name="correct_answer" class="form-control" placeholder="0 for first option, or 0,2 for multiple">
                    </div>
                </div>

                <div class="type-fields" id="fields-tf">
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <select name="correct_answer_tf" class="form-select">
                            <option value="true">True</option>
                            <option value="false">False</option>
                        </select>
                    </div>
                </div>

                <div class="type-fields" id="fields-fill">
                    <div class="mb-3">
                        <label class="form-label">Accepted Answers (separate with |)</label>
                        <input type="text" name="correct_answer_fill" class="form-control" placeholder="answer1 | answer2">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Feedback (Correct)</label>
                        <textarea name="feedback_correct" class="form-control" rows="2" placeholder="Feedback shown when answer is correct..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Feedback (Incorrect)</label>
                        <textarea name="feedback_incorrect" class="form-control" rows="2" placeholder="Feedback shown when answer is incorrect..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.modules.edit', $quiz->contentItem->module) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Module</a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-plus"></i> Add Question</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Adding...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Question type toggle
            document.getElementById('questionType').addEventListener('change', function() {
                document.querySelectorAll('.type-fields').forEach(f => f.classList.remove('active'));
                const type = this.value;
                if (type === 'true_false') {
                    document.getElementById('fields-tf').classList.add('active');
                } else if (type === 'fill_blank' || type === 'short_answer' || type === 'essay') {
                    document.getElementById('fields-fill').classList.add('active');
                } else {
                    document.getElementById('fields-mc').classList.add('active');
                }
            });

            // Add Question form submit with loading animation
            const addForm = document.querySelector('form[action*="questions"][method="POST"]:not(.delete-question-form)');
            if (addForm) {
                addForm.addEventListener('submit', function() {
                    const submitBtn = addForm.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }

            // Delete question forms with confirm + loading animation
            document.querySelectorAll('.delete-question-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Delete this question?')) {
                        e.preventDefault();
                        return;
                    }
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    } else {
                        alert.classList.remove('show');
                        alert.classList.add('fade');
                    }
                }, 5000);
            });
        });

        function addOption() {
            const builder = document.getElementById('optionsBuilder');
            const count = builder.querySelectorAll('.option-row').length;
            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const letter = letters[count] || (count + 1);
            const row = document.createElement('div');
            row.className = 'option-row';
            row.innerHTML = '<input type="text" name="options[]" class="form-control" placeholder="Option ' + letter + '">';
            builder.appendChild(row);
        }
    </script>
@endsection

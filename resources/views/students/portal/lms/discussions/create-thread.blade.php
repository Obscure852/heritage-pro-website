@extends('layouts.master-student-portal')

@section('title')
    New Discussion - {{ $course->title }}
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 24px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .page-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .back-link {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 12px;
            opacity: 0.9;
        }

        .back-link:hover {
            color: white;
            opacity: 1;
        }

        .form-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            min-height: 200px;
            resize: vertical;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
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

        .type-selector {
            display: flex;
            gap: 12px;
        }

        .type-option {
            flex: 1;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .type-option:hover {
            border-color: #3b82f6;
        }

        .type-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .type-option input {
            display: none;
        }

        .type-option i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #6b7280;
        }

        .type-option.selected i {
            color: #3b82f6;
        }

        .type-option h6 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .type-option small {
            font-size: 12px;
            color: #9ca3af;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #f3f4f6;
            color: #6b7280;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-submit {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }

        .anonymous-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            margin-top: 16px;
        }

        .anonymous-option label {
            font-size: 13px;
            color: #374151;
            margin: 0;
            cursor: pointer;
        }

        .anonymous-option small {
            color: #9ca3af;
            margin-left: 4px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            LMS
        @endslot
        @slot('li_2')
            <a href="{{ route('student.lms.discussions.forum', $course) }}">Discussions</a>
        @endslot
        @slot('title')
            New Discussion
        @endslot
    @endcomponent

    <div class="page-header">
        <a href="{{ route('student.lms.discussions.forum', $course) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Discussions
        </a>
        <h4><i class="fas fa-plus-circle me-2"></i>Start New Discussion</h4>
        <p>{{ $course->title }}</p>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="form-container">
        <div class="help-text">
            <div class="help-title"><i class="fas fa-lightbulb me-2"></i>Tips for a Great Discussion</div>
            <div class="help-content">
                Use a clear, descriptive title. Provide enough context in your post so others can understand and help.
                For questions, be specific about what you've tried and where you're stuck.
            </div>
        </div>

        <form action="{{ route('student.lms.discussions.store', $course) }}" method="POST" id="createThreadForm">
            @csrf

            <div class="form-group">
                <label class="form-label">Type <span class="required">*</span></label>
                <div class="type-selector">
                    <label class="type-option selected">
                        <input type="radio" name="type" value="discussion" checked>
                        <i class="fas fa-comments"></i>
                        <h6>Discussion</h6>
                        <small>Share thoughts or start a conversation</small>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="question">
                        <i class="fas fa-question-circle"></i>
                        <h6>Question</h6>
                        <small>Ask something and get answers</small>
                    </label>
                </div>
                @error('type')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Title <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-control"
                       placeholder="e.g., How do I solve problem 3 in the assignment?"
                       maxlength="255" required value="{{ old('title') }}">
                @error('title')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            @if($forum->categories->isNotEmpty())
                <div class="form-group">
                    <label class="form-label" for="category_id">Category (Optional)</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">-- Select a category --</option>
                        @foreach($forum->categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label" for="body">Content <span class="required">*</span></label>
                <textarea name="body" id="body" class="form-control form-textarea"
                          placeholder="Describe your discussion topic or question in detail..."
                          required minlength="10" maxlength="50000">{{ old('body') }}</textarea>
                @error('body')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            @if($forum->allow_anonymous)
                <div class="anonymous-option">
                    <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                    <label for="is_anonymous">
                        Post anonymously <small>(Your name will not be visible to other students)</small>
                    </label>
                </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('student.lms.discussions.forum', $course) }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Create Discussion
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        // Type selector
        document.querySelectorAll('.type-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });

        // Form submit loading state
        document.getElementById('createThreadForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        });
    </script>
@endsection

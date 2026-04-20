@extends('layouts.master')

@section('title', 'New Discussion Thread')

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
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
            line-height: 1.4;
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
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {

            .form-grid,
            .form-grid-2 {
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

        textarea.form-control {
            min-height: 180px;
            resize: vertical;
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-label {
            font-weight: 500;
            color: #374151;
            cursor: pointer;
        }

        .form-check .form-text {
            margin-top: 0;
            margin-left: 26px;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #3b82f6;
            font-weight: 500;
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
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
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

        /* Guidelines Card */
        .guidelines-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 3px;
            padding: 16px;
            margin-top: 24px;
        }

        .guidelines-card h6 {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guidelines-card ul {
            margin: 0;
            padding-left: 20px;
        }

        .guidelines-card li {
            color: #78350f;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .guidelines-card li:last-child {
            margin-bottom: 0;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.edit', $forum->course) }}">Learning Space</a>
        @endslot
        @slot('title')
            New Thread
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Create New Thread</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Start a Discussion</div>
            <div class="help-content">
                Create a new discussion thread in {{ $forum->course->title }}. Fields marked with <span
                    class="text-danger">*</span> are required.
            </div>
        </div>

        <form action="{{ route('lms.discussions.store-thread', $forum) }}" method="POST" enctype="multipart/form-data"
            id="threadForm">
            @csrf

            <h3 class="section-title">Thread Details</h3>

            <div class="form-grid" style="margin-bottom: 16px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="title" class="form-label">Thread Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title"
                        class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                        placeholder="Enter a descriptive title for your thread" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="type" class="form-label">Thread Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="discussion" {{ old('type') === 'discussion' ? 'selected' : '' }}>Discussion</option>
                        <option value="question" {{ old('type') === 'question' ? 'selected' : '' }}>Question</option>
                        @if ($isInstructor ?? false)
                            <option value="announcement" {{ old('type') === 'announcement' ? 'selected' : '' }}>Announcement
                            </option>
                        @endif
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Choose "Question" if you need an answer</div>
                </div>
            </div>

            @if ($forum->categories->count())
                <div class="form-grid" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id"
                            class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">-- Select Category (Optional) --</option>
                            @foreach ($forum->categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <h3 class="section-title">Content</h3>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="body" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror"
                    placeholder="Write your discussion content here. Be clear and detailed to get better responses..." required>{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Minimum 10 characters required</div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Attachments (Optional)</label>
                <div class="custom-file-input">
                    <input type="file" name="attachments[]" id="attachments" multiple
                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip">
                    <label for="attachments" class="file-input-label">
                        <div class="file-input-icon">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <div class="file-input-text">
                            <span class="file-label">Choose Files</span>
                            <span class="file-hint" id="fileHint">PDF, DOC, TXT, JPG, PNG, ZIP (max 10MB each)</span>
                            <span class="file-selected d-none" id="fileSelected"></span>
                        </div>
                    </label>
                </div>
                @error('attachments.*')
                    <div class="text-danger mt-1" style="font-size: 12px;">{{ $message }}</div>
                @enderror
            </div>

            <h3 class="section-title">Options</h3>

            <div class="form-grid-2">
                @if ($isInstructor ?? false)
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" id="is_pinned" class="form-check-input" value="1"
                            {{ old('is_pinned') ? 'checked' : '' }}>
                        <div>
                            <label for="is_pinned" class="form-check-label">Pin this thread</label>
                            <div class="form-text">Pinned threads appear at the top of the forum</div>
                        </div>
                    </div>
                @else
                    @if ($forum->allow_anonymous)
                        <div class="form-check">
                            <input type="checkbox" name="is_anonymous" id="is_anonymous" class="form-check-input"
                                value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                            <div>
                                <label for="is_anonymous" class="form-check-label">Post anonymously</label>
                                <div class="form-text">Your identity will be hidden from other students but visible to
                                    instructors</div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="guidelines-card">
                <h6><i class="fas fa-lightbulb"></i> Posting Guidelines</h6>
                <ul>
                    <li>Be respectful and constructive in your discussions.</li>
                    <li>Use a clear, descriptive title that summarizes your topic.</li>
                    <li>Search existing threads before creating a new one.</li>
                    <li>For questions, choose the "Question" type to get marked answers.</li>
                    <li>Avoid posting personal or sensitive information.</li>
                </ul>
            </div>

            <div class="form-actions">
                <a href="{{ route('lms.discussions.forum', $forum->course) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-paper-plane"></i> Create Thread</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        // File input display
        const attachmentsInput = document.getElementById('attachments');
        const fileHint = document.getElementById('fileHint');
        const fileSelected = document.getElementById('fileSelected');

        if (attachmentsInput) {
            attachmentsInput.addEventListener('change', function(e) {
                if (this.files && this.files.length > 0) {
                    const fileNames = Array.from(this.files).map(f => f.name).join(', ');
                    fileHint.classList.add('d-none');
                    fileSelected.classList.remove('d-none');
                    fileSelected.textContent = this.files.length === 1 ?
                        this.files[0].name :
                        `${this.files.length} files selected`;
                } else {
                    fileHint.classList.remove('d-none');
                    fileSelected.classList.add('d-none');
                    fileSelected.textContent = '';
                }
            });
        }

        // Form submission loading state
        const form = document.getElementById('threadForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }
    </script>
@endsection

@extends('layouts.master')

@section('title')
    Edit Learning Path
@endsection

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
            min-height: 100px;
            resize: vertical;
        }

        .required {
            color: #dc2626;
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 0;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
        }

        /* Current Thumbnail */
        .current-thumbnail {
            margin-bottom: 12px;
        }

        .current-thumbnail img {
            max-width: 200px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        /* Subject Selection */
        .subject-selection {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            background: #f9fafb;
        }

        .subject-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: white;
        }

        .subject-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }

        .subject-item:last-child {
            border-bottom: none;
        }

        .subject-item:hover {
            background: #f9fafb;
        }

        .subject-item.selected {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
        }

        .subject-item input[type="checkbox"] {
            margin-right: 12px;
        }

        .subject-item .subject-title {
            font-size: 14px;
            color: #1f2937;
        }

        .subject-item .subject-code {
            font-size: 12px;
            color: #6b7280;
            margin-left: auto;
        }

        /* Category Selection */
        .category-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .category-checkbox {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Objectives */
        .objectives-container {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px;
        }

        .objective-input {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .objective-input input {
            flex-grow: 1;
        }

        .objective-input .btn-remove {
            padding: 10px 12px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-add-objective {
            padding: 8px 16px;
            background: #f3f4f6;
            color: #374151;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            width: 100%;
        }

        .btn-add-objective:hover {
            background: #e5e7eb;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
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
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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

        /* Status Badge */
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.draft {
            background: #fef3c7;
            color: #92400e;
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
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
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
            color: #4e73df;
            font-weight: 500;
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
            <a class="text-muted font-size-14" href="{{ route('lms.learning-paths.index') }}">Learning Paths</a>
        @endslot
        @slot('title')
            Edit Learning Path
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

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Learning Path</h1>
                <span class="status-badge {{ $learningPath->is_published ? 'published' : 'draft' }}">
                    {{ $learningPath->is_published ? 'Published' : 'Draft' }}
                </span>
            </div>
            <div>
                <form action="{{ route('lms.learning-paths.toggle-publish', $learningPath) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $learningPath->is_published ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                        <i class="fas fa-{{ $learningPath->is_published ? 'eye-slash' : 'eye' }} me-1"></i>
                        {{ $learningPath->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Edit Learning Path</div>
            <div class="help-content">
                Update the learning path details and subject sequence. Changes will be reflected for all enrolled students.
                Fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="POST" action="{{ route('lms.learning-paths.update', $learningPath) }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <h3 class="section-title">Basic Information</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / 3;">
                    <label class="form-label" for="title">Title <span class="required">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                        name="title" id="title" value="{{ old('title', $learningPath->title) }}"
                        placeholder="e.g., Web Development Fundamentals" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="grade_id">Grade <span class="required">*</span></label>
                    <select class="form-select @error('grade_id') is-invalid @enderror" name="grade_id" id="grade_id" required>
                        <option value="">Select Grade</option>
                        @foreach(\App\Models\Grade::where('active', true)->orderBy('sequence')->get() as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id', $learningPath->grade_id) == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                        name="description" id="description" rows="3"
                        placeholder="Describe what students will learn...">{{ old('description', $learningPath->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="thumbnail">Thumbnail Image</label>
                    @if($learningPath->thumbnail)
                        <div class="current-thumbnail">
                            <img src="{{ Storage::url($learningPath->thumbnail) }}" alt="Current thumbnail">
                        </div>
                    @endif
                    <div class="custom-file-input">
                        <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                        <label for="thumbnail" class="file-input-label">
                            <div class="file-input-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="file-input-text">
                                <span class="file-label">Choose New Image</span>
                                <span class="file-hint" id="thumbnailHint">PNG, JPG or GIF (max 2MB)</span>
                                <span class="file-selected d-none" id="thumbnailName"></span>
                            </div>
                        </label>
                    </div>
                    <div class="form-text">Leave empty to keep current. Recommended: 800x400px</div>
                    @error('thumbnail')
                        <div class="text-danger form-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Categories</h3>
            @php
                $selectedCategories = old('categories', $learningPath->categories->pluck('id')->toArray());
            @endphp
            <div class="category-checkboxes">
                @foreach($categories as $category)
                    <label class="category-checkbox">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                            {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        <span>{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>

            <h3 class="section-title">Learning Objectives</h3>
            @php
                $objectives = old('objectives', $learningPath->objectives ?? []);
                if (empty($objectives)) $objectives = [''];
            @endphp
            <div class="objectives-container" id="objectivesContainer">
                @foreach($objectives as $objective)
                    <div class="objective-input">
                        <input type="text" class="form-control" name="objectives[]" value="{{ $objective }}" placeholder="What will students learn?">
                        <button type="button" class="btn-remove" onclick="removeObjective(this)"><i class="fas fa-times"></i></button>
                    </div>
                @endforeach
                <button type="button" class="btn-add-objective" onclick="addObjective()">
                    <i class="fas fa-plus me-2"></i>Add Another Objective
                </button>
            </div>

            <h3 class="section-title">Subjects <span class="required">*</span></h3>
            @php
                $selectedCourses = old('courses', $learningPath->pathCourses->pluck('course_id')->toArray());
            @endphp
            <div class="subject-selection">
                <p class="form-text mb-3">Select subjects in the order they should be completed. Click to select/deselect.</p>
                <div class="subject-list">
                    @foreach($courses as $course)
                        <label class="subject-item {{ in_array($course->id, $selectedCourses) ? 'selected' : '' }}">
                            <input type="checkbox" name="courses[]" value="{{ $course->id }}"
                                {{ in_array($course->id, $selectedCourses) ? 'checked' : '' }}>
                            <span class="subject-title">{{ $course->title }}</span>
                            <span class="subject-code">{{ $course->code }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <h3 class="section-title">Settings</h3>
            <div class="form-grid-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="enforce_sequence" id="enforce_sequence" value="1"
                        {{ old('enforce_sequence', $learningPath->enforce_sequence) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enforce_sequence">
                        Enforce subject sequence (students must complete subjects in order)
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="allow_skip" id="allow_skip" value="1"
                        {{ old('allow_skip', $learningPath->allow_skip) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_skip">
                        Allow skipping optional subjects
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('lms.learning-paths.show', $learningPath) }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="mt-5 pt-4 border-top">
            <h3 class="section-title text-danger">Danger Zone</h3>
            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                <div>
                    <strong>Delete Learning Path</strong>
                    <p class="text-muted small mb-0">Once deleted, this cannot be undone. All enrollments will be removed.</p>
                </div>
                <form action="{{ route('lms.learning-paths.destroy', $learningPath) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this learning path? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function addObjective() {
            const container = document.getElementById('objectivesContainer');
            const button = container.querySelector('.btn-add-objective');
            const newInput = document.createElement('div');
            newInput.className = 'objective-input';
            newInput.innerHTML = `
                <input type="text" class="form-control" name="objectives[]" placeholder="What will students learn?">
                <button type="button" class="btn-remove" onclick="removeObjective(this)"><i class="fas fa-times"></i></button>
            `;
            container.insertBefore(newInput, button);
        }

        function removeObjective(btn) {
            const container = document.getElementById('objectivesContainer');
            const inputs = container.querySelectorAll('.objective-input');
            if (inputs.length > 1) {
                btn.parentElement.remove();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('.needs-validation');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                }
                form.classList.add('was-validated');
            });

            // Subject selection styling
            const subjectItems = document.querySelectorAll('.subject-item');
            subjectItems.forEach(function(item) {
                const checkbox = item.querySelector('input[type="checkbox"]');
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        item.classList.add('selected');
                    } else {
                        item.classList.remove('selected');
                    }
                });
            });

            // File input display
            const thumbnailInput = document.getElementById('thumbnail');
            if (thumbnailInput) {
                const thumbnailHint = document.getElementById('thumbnailHint');
                const thumbnailName = document.getElementById('thumbnailName');

                thumbnailInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        thumbnailHint.classList.add('d-none');
                        thumbnailName.classList.remove('d-none');
                        thumbnailName.textContent = file.name;
                    } else {
                        thumbnailHint.classList.remove('d-none');
                        thumbnailName.classList.add('d-none');
                        thumbnailName.textContent = '';
                    }
                });
            }
        });
    </script>
@endsection

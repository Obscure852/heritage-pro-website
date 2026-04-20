@extends('layouts.master')

@section('title')
    Create Content
@endsection

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .form-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .form-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .form-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
        }

        .form-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #6366f1;
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
            line-height: 1.5;
            margin: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:not(:first-of-type) {
            margin-top: 32px;
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

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            padding: 10px 12px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
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
            padding: 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #6366f1;
            background: #faf5ff;
        }

        .file-input-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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
            font-weight: 600;
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
            color: #6366f1;
            font-weight: 500;
        }

        /* Buttons */
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
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
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

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 32px;
        }

        /* Button Loading Animation */
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
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            Create Content
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

    <form action="{{ route('lms.courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-container">
            <div class="form-header">
                <h3>Create New Content</h3>
                <p>Add new learning content to the Learning Space</p>
            </div>

            <div class="form-body">
                <div class="help-text">
                    <div class="help-title">Getting Started</div>
                    <p class="help-content">Fill in the content details below. After creating the content, you can add
                        modules and content. Fields marked with <span class="text-danger">*</span> are required.</p>
                </div>

                <!-- Course Details Section -->
                <h6 class="section-title">Content Details</h6>

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Code <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" placeholder="e.g., MATH101" required>
                        <div class="form-text">Unique identifier</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="Enter content title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Grade <span class="required">*</span></label>
                        <select name="grade_id" id="gradeSelect" class="form-select @error('grade_id') is-invalid @enderror"
                            required>
                            <option value="">Select Grade</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('grade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Subject <span class="required">*</span></label>
                        <select name="grade_subject_id" id="subjectSelect"
                            class="form-select @error('grade_subject_id') is-invalid @enderror" required>
                            <option value="">Select Grade First</option>
                        </select>
                        <div class="form-text">For Term {{ $currentTerm->term }}, {{ $currentTerm->year }}</div>
                        @error('grade_subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teacher <span class="required">*</span></label>
                        <select name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror"
                            required>
                            <option value="">Select Teacher</option>
                            @foreach ($instructors as $instructor)
                                <option value="{{ $instructor->id }}"
                                    {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                    {{ $instructor->firstname }} {{ $instructor->lastname }}
                                </option>
                            @endforeach
                        </select>
                        @error('instructor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Term <span class="required">*</span></label>
                        <select name="term_id" class="form-select @error('term_id') is-invalid @enderror" required>
                            <option value="">Select Term</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ old('term_id', $currentTerm->id) == $term->id ? 'selected' : '' }}>
                                    Term {{ $term->term }}, {{ $term->year }}
                                </option>
                            @endforeach
                        </select>
                        @error('term_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5"
                        placeholder="Describe what students will learn in this content">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Learning Objectives</label>
                    <textarea name="learning_objectives" class="form-control" rows="5"
                        placeholder="Enter each objective on a new line&#10;e.g.&#10;- Understand basic algebra concepts&#10;- Solve linear equations&#10;- Apply mathematical reasoning">{{ old('learning_objectives') }}</textarea>
                    <div class="form-text">List the key learning outcomes (one per line)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Prerequisites</label>
                    <textarea name="prerequisites_text" class="form-control" rows="2"
                        placeholder="e.g. Basic understanding of arithmetic, completion of Grade 7 Mathematics">{{ old('prerequisites_text') }}</textarea>
                </div>

                <!-- Schedule & Enrollment Section -->
                <h6 class="section-title">Schedule & Enrollment</h6>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Maximum Students</label>
                        <input type="number" name="max_students" class="form-control"
                            value="{{ old('max_students') }}" min="1" placeholder="No limit">
                        <div class="form-text">Leave empty for unlimited</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Passing Grade (%)</label>
                        <input type="number" name="passing_grade" class="form-control"
                            value="{{ old('passing_grade', 60) }}" min="0" max="100">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="self_enrollment" id="selfEnrollment"
                                value="1" {{ old('self_enrollment') ? 'checked' : '' }}>
                            <label class="form-check-label" for="selfEnrollment">
                                Allow Self-Enrollment
                            </label>
                        </div>
                        <div class="form-text">Students can enroll themselves</div>
                    </div>
                    <div class="col-md-4 mb-3" id="enrollmentKeyField" style="display: none;">
                        <label class="form-label">Enrollment Key</label>
                        <input type="text" name="enrollment_key" class="form-control"
                            value="{{ old('enrollment_key') }}" placeholder="Optional password">
                        <div class="form-text">Required to self-enroll</div>
                    </div>
                </div>

                <!-- Course Thumbnail Section -->
                <h6 class="section-title">Content Thumbnail</h6>

                <div class="row">
                    <div class="col-md-6 mb-0">
                        <div class="custom-file-input">
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                            <label for="thumbnail" class="file-input-label">
                                <div class="file-input-icon">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div class="file-input-text">
                                    <span class="file-label">Choose Image File</span>
                                    <span class="file-hint" id="thumbnailHint">PNG, JPG or GIF (max 2MB) - Recommended:
                                        400x300px</span>
                                    <span class="file-selected d-none" id="thumbnailName"></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.courses.index') }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Content</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
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
            const selfEnrollment = document.getElementById('selfEnrollment');
            const enrollmentKeyField = document.getElementById('enrollmentKeyField');

            selfEnrollment.addEventListener('change', function() {
                enrollmentKeyField.style.display = this.checked ? 'block' : 'none';
            });

            // Show on page load if checked
            if (selfEnrollment.checked) {
                enrollmentKeyField.style.display = 'block';
            }

            // Grade and Subject dynamic loading
            const gradeSelect = document.getElementById('gradeSelect');
            const subjectSelect = document.getElementById('subjectSelect');
            const oldSubjectId = '{{ old('grade_subject_id') }}';

            gradeSelect.addEventListener('change', function() {
                const gradeId = this.value;
                subjectSelect.innerHTML = '<option value="">Loading...</option>';

                if (!gradeId) {
                    subjectSelect.innerHTML = '<option value="">Select Grade First</option>';
                    return;
                }

                fetch(`{{ route('lms.courses.subjects-by-grade') }}?grade_id=${gradeId}`)
                    .then(response => response.json())
                    .then(subjects => {
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                        subjects.forEach(subject => {
                            const option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.name;
                            if (oldSubjectId && subject.id == oldSubjectId) {
                                option.selected = true;
                            }
                            subjectSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading subjects:', error);
                        subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                    });
            });

            // Trigger on page load if grade is pre-selected
            if (gradeSelect.value) {
                gradeSelect.dispatchEvent(new Event('change'));
            }

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

            // Form submission loading state
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection

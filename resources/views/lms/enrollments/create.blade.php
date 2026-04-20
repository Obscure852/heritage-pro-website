@extends('layouts.master')

@section('title')
    Enroll Students - {{ $course->title }}
@endsection

@section('css')
    <style>
        .enrollment-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .enrollment-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 3px 3px 0 0;
        }

        .enrollment-body {
            padding: 24px;
        }

        /* Helper Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
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

        /* Section Title */
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

        /* Enrollment Type Cards */
        .enrollment-type-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .enrollment-type-cards {
                grid-template-columns: 1fr;
            }
        }

        .enrollment-type-card {
            border: 2px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }

        .enrollment-type-card:hover {
            border-color: #4e73df;
            background: #f8faff;
        }

        .enrollment-type-card.selected {
            border-color: #4e73df;
            background: #eef2ff;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .enrollment-type-card input[type="radio"] {
            display: none;
        }

        .enrollment-type-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .enrollment-type-card.class-type .enrollment-type-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .enrollment-type-card.individual-type .enrollment-type-icon {
            background: #d1fae5;
            color: #059669;
        }

        .enrollment-type-card h5 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .enrollment-type-card p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        /* Form Controls */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            padding: 10px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        /* Class Select */
        .class-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .class-option .student-count {
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            color: #6b7280;
        }

        /* Student List */
        .student-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .student-list-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }

        .student-list-item:hover {
            background: #f9fafb;
        }

        .student-list-item:last-child {
            border-bottom: none;
        }

        .student-list-item.selected {
            background: #eef2ff;
        }

        .student-list-item input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            accent-color: #4e73df;
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df, #36b9cc);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            margin-right: 12px;
        }

        .student-info {
            flex: 1;
        }

        .student-info strong {
            display: block;
            color: #1f2937;
            font-size: 14px;
        }

        .student-info span {
            font-size: 12px;
            color: #6b7280;
        }

        /* Enrollment Fields */
        .enrollment-fields {
            display: none;
        }

        .enrollment-fields.active {
            display: block;
        }

        /* Search Box */
        .search-box {
            margin-bottom: 16px;
        }

        .search-box .input-group-text {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 3px 0 0 3px !important;
        }

        .search-box .form-control {
            border-left: none;
            border-radius: 0 3px 3px 0 !important;
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
            background: #4e73df;
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #3d5fc7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
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

        /* Selection Info */
        .selection-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .selection-info span {
            color: #0369a1;
            font-weight: 500;
        }

        .selection-info button {
            background: none;
            border: none;
            color: #0369a1;
            cursor: pointer;
            font-size: 13px;
        }

        .selection-info button:hover {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.edit', $course) }}">{{ $course->title }}</a>
        @endslot
        @slot('li_3')
            <a href="{{ route('lms.enrollments.index', $course) }}">Enrollments</a>
        @endslot
        @slot('title')
            Enroll Students
        @endslot
    @endcomponent

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

    <form action="{{ route('lms.enrollments.store', $course) }}" method="POST" id="enrollmentForm">
        @csrf

        <div class="enrollment-container">
            <div class="enrollment-header">
                <h4 style="margin: 0 0 4px 0; font-weight: 600;">Enroll Students</h4>
                <p style="margin: 0; opacity: 0.9; font-size: 14px;">{{ $course->title }}</p>
            </div>

            <div class="enrollment-body">
                <div class="help-text">
                    <div class="help-title">Add Students to Course</div>
                    <p class="help-content">Choose to enroll an entire class or select individual students. Students who are already enrolled will be skipped automatically.</p>
                </div>

                <!-- Enrollment Type Selection -->
                <h6 class="section-title">Enrollment Method</h6>

                <div class="enrollment-type-cards">
                    <label class="enrollment-type-card class-type" data-type="class">
                        <input type="radio" name="enrollment_type" value="class" checked>
                        <div class="enrollment-type-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Enroll by Class</h5>
                        <p>Enroll all students from a selected class at once</p>
                    </label>

                    <label class="enrollment-type-card individual-type" data-type="individual">
                        <input type="radio" name="enrollment_type" value="individual">
                        <div class="enrollment-type-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5>Select Individual Students</h5>
                        <p>Choose specific students to enroll in the content</p>
                    </label>
                </div>

                <!-- Class Enrollment Fields -->
                <div class="enrollment-fields active" id="class-fields">
                    <h6 class="section-title">Select Class</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="klass_id" class="form-select" id="klassSelect">
                                <option value="">Select a class...</option>
                                @foreach ($classes as $klass)
                                    <option value="{{ $klass->id }}">
                                        {{ $klass->name }} ({{ $klass->students_count }} students)
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">All students in the selected class will be enrolled</div>
                        </div>
                    </div>
                </div>

                <!-- Individual Student Selection Fields -->
                <div class="enrollment-fields" id="individual-fields">
                    <h6 class="section-title">Select Students</h6>

                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="studentSearch" placeholder="Search students by name...">
                        </div>
                    </div>

                    <div class="selection-info" id="selectionInfo" style="display: none;">
                        <span><span id="selectedCount">0</span> student(s) selected</span>
                        <button type="button" id="clearSelection">Clear selection</button>
                    </div>

                    <div class="student-list" id="studentList">
                        @forelse ($availableStudents as $student)
                            <label class="student-list-item" data-name="{{ strtolower($student->full_name) }}">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                </div>
                                <div class="student-info">
                                    <strong>{{ $student->full_name }}</strong>
                                    <span>{{ $student->current_class->name ?? 'No class' }}</span>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-user-check mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                <p class="mb-0">All students are already enrolled in this content</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.enrollments.index', $course) }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-user-plus"></i> Enroll Students</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Enrolling...
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
            // Enrollment type selection
            const typeCards = document.querySelectorAll('.enrollment-type-card');
            const classFields = document.getElementById('class-fields');
            const individualFields = document.getElementById('individual-fields');

            typeCards.forEach(card => {
                card.addEventListener('click', function() {
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    this.querySelector('input[type="radio"]').checked = true;

                    const type = this.dataset.type;
                    if (type === 'class') {
                        classFields.classList.add('active');
                        individualFields.classList.remove('active');
                    } else {
                        classFields.classList.remove('active');
                        individualFields.classList.add('active');
                    }
                });
            });

            // Select first type by default
            document.querySelector('.enrollment-type-card').click();

            // Student search
            const searchInput = document.getElementById('studentSearch');
            const studentItems = document.querySelectorAll('.student-list-item');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    studentItems.forEach(item => {
                        const name = item.dataset.name;
                        item.style.display = name.includes(query) ? 'flex' : 'none';
                    });
                });
            }

            // Selection counter
            const checkboxes = document.querySelectorAll('.student-list-item input[type="checkbox"]');
            const selectionInfo = document.getElementById('selectionInfo');
            const selectedCount = document.getElementById('selectedCount');
            const clearSelection = document.getElementById('clearSelection');

            function updateSelectionCount() {
                const count = document.querySelectorAll('.student-list-item input[type="checkbox"]:checked').length;
                if (selectedCount) selectedCount.textContent = count;
                if (selectionInfo) selectionInfo.style.display = count > 0 ? 'flex' : 'none';

                // Update item styling
                checkboxes.forEach(cb => {
                    cb.closest('.student-list-item').classList.toggle('selected', cb.checked);
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectionCount);
            });

            if (clearSelection) {
                clearSelection.addEventListener('click', function() {
                    checkboxes.forEach(cb => cb.checked = false);
                    updateSelectionCount();
                });
            }

            // Form submission loading state
            const form = document.getElementById('enrollmentForm');
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

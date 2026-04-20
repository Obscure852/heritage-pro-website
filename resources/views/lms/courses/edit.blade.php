@extends('layouts.master')

@section('title')
    Edit Content - {{ $course->title }}
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

        .form-body {
            padding: 24px;
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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc2626;
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

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .module-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .module-card:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
        }

        .module-header {
            background: #f9fafb;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .module-title {
            font-weight: 600;
            color: #1f2937;
        }

        .module-body {
            padding: 12px 16px;
        }

        .content-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .content-item:last-child {
            border-bottom: none;
        }

        .content-icon {
            width: 32px;
            height: 32px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }

        .content-icon.video {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-icon.video_youtube {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-icon.video_upload {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-icon.document {
            background: #dbeafe;
            color: #2563eb;
        }

        .content-icon.quiz {
            background: #fef3c7;
            color: #d97706;
        }

        .content-icon.text {
            background: #d1fae5;
            color: #059669;
        }

        .content-icon.scorm {
            background: #f3e8ff;
            color: #8b5cf6;
        }

        .content-icon.external_link {
            background: #cffafe;
            color: #0891b2;
        }

        .content-icon.audio {
            background: #fce7f3;
            color: #db2777;
        }

        .content-icon.image {
            background: #dcfce7;
            color: #16a34a;
        }

        .content-icon.assignment {
            background: #d1fae5;
            color: #059669;
        }

        /* Custom badge colors */
        .bg-soft-purple {
            background: #f3e8ff !important;
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .bg-soft-cyan {
            background: #cffafe !important;
        }

        .text-cyan {
            color: #0891b2 !important;
        }

        .bg-soft-pink {
            background: #fce7f3 !important;
        }

        .text-pink {
            color: #db2777 !important;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-archived {
            background: #e5e7eb;
            color: #374151;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #6366f1;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
            background: transparent;
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
            border-color: #6366f1;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
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
            color: #6366f1;
            font-weight: 500;
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

        /* Helper Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #6366f1;
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
            line-height: 1.5;
            margin: 0;
        }

        /* Module Action Buttons */
        .module-actions {
            display: flex;
            gap: 4px;
        }

        .module-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .module-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .module-actions .btn i {
            font-size: 14px;
        }

        .content-actions .btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .content-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content-actions .btn i {
            font-size: 12px;
        }

        /* Settings Tab Styling */
        .settings-section {
            margin-bottom: 24px;
        }

        .settings-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .quick-link-card {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            margin-bottom: 12px;
        }

        .quick-link-card:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
            color: inherit;
        }

        .quick-link-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 20px;
        }

        .quick-link-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .quick-link-icon.purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .quick-link-icon.green {
            background: #d1fae5;
            color: #059669;
        }

        .quick-link-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .quick-link-content h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .quick-link-content p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .action-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .action-card:last-child {
            margin-bottom: 0;
        }

        .action-info {
            display: flex;
            align-items: center;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
        }

        .action-icon.gray {
            background: #f3f4f6;
            color: #6b7280;
        }

        .action-icon.yellow {
            background: #fef3c7;
            color: #d97706;
        }

        .action-text h6 {
            margin: 0 0 2px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .action-text p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .danger-zone {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 20px;
        }

        .danger-zone-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .danger-zone-header i {
            color: #dc2626;
            font-size: 20px;
            margin-right: 10px;
        }

        .danger-zone-header h6 {
            margin: 0;
            color: #991b1b;
            font-weight: 600;
        }

        .danger-zone-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: #fff;
            border: 1px solid #fecaca;
            border-radius: 3px;
        }

        .danger-zone-text h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .danger-zone-text p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .course-stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            text-align: center;
        }

        .course-stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .course-stat-card .stat-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .course-stat-card.purple {
            border-top: 3px solid #7c3aed;
        }

        .course-stat-card.blue {
            border-top: 3px solid #2563eb;
        }

        .course-stat-card.green {
            border-top: 3px solid #059669;
        }

        .course-stat-card.orange {
            border-top: 3px solid #ea580c;
        }

        /* Sidebar Styles */
        .sidebar-container {
            position: sticky;
            top: 100px;
        }

        .sidebar-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-section-title:not(:first-of-type) {
            margin-top: 20px;
        }

        /* Quick Links (matching show page) */
        .quick-link {
            display: flex;
            align-items: center;
            padding: 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            margin-bottom: 10px;
        }

        .quick-link:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
            color: inherit;
        }

        .quick-link-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
        }

        .quick-link-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .quick-link-icon.green {
            background: #d1fae5;
            color: #059669;
        }

        .quick-link-icon.purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .quick-link-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .quick-link-content h6 {
            margin: 0 0 2px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
        }

        .quick-link-content p {
            margin: 0;
            font-size: 11px;
            color: #6b7280;
        }

        /* Stats Card */
        .stats-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-row-label {
            font-size: 12px;
            color: #6b7280;
        }

        .stat-row-value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
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
            Edit Content
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
        <div class="form-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">{{ $course->title }}</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">{{ $course->code }} | {{ $course->grade->name ?? '' }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="status-badge status-{{ $course->status }}">
                        {{ ucfirst($course->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="form-body">
            <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#details" role="tab">
                        <i class="fas fa-info-circle me-2 text-muted"></i>Content Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#modules" role="tab">
                        <i class="fas fa-layer-group me-2 text-muted"></i>Modules & Content
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                        <i class="fas fa-cog me-2 text-muted"></i>Settings
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="details">
                    <div class="help-text">
                        <div class="help-title">Content Details</div>
                        <p class="help-content">Update the content information, schedule, and enrollment settings. Fields
                            marked with <span class="text-danger">*</span> are required.</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-9">
                            <form action="{{ route('lms.courses.update', $course) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Course Information Section -->
                                <h6 class="section-title">Content Information</h6>

                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Code <span class="required">*</span></label>
                                        <input type="text" name="code" class="form-control"
                                            value="{{ old('code', $course->code) }}" required>
                                        <div class="form-text">Unique identifier</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Title <span class="required">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $course->title) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Grade <span class="required">*</span></label>
                                        <select name="grade_id" id="gradeSelect" class="form-select" required>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade->id }}"
                                                    {{ old('grade_id', $course->grade_id) == $grade->id ? 'selected' : '' }}>
                                                    {{ $grade->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Subject <span class="required">*</span></label>
                                        <select name="grade_subject_id" id="subjectSelect" class="form-select" required>
                                            <option value="">Select Subject</option>
                                            @foreach ($subjects as $gradeSubject)
                                                <option value="{{ $gradeSubject->id }}"
                                                    {{ old('grade_subject_id', $course->grade_subject_id) == $gradeSubject->id ? 'selected' : '' }}>
                                                    {{ $gradeSubject->subject->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">For Term {{ $currentTerm->term }},
                                            {{ $currentTerm->year }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teacher <span class="required">*</span></label>
                                        <select name="instructor_id" class="form-select" required>
                                            @foreach ($instructors as $instructor)
                                                <option value="{{ $instructor->id }}"
                                                    {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                                    {{ $instructor->firstname }} {{ $instructor->lastname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Term <span class="required">*</span></label>
                                        <select name="term_id" class="form-select" required>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}"
                                                    {{ old('term_id', $course->term_id) == $term->id ? 'selected' : '' }}>
                                                    Term {{ $term->term }}, {{ $term->year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="8"
                                        placeholder="Describe what students will learn in this content">{{ old('description', $course->description) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Learning Objectives</label>
                                    <textarea name="learning_objectives" class="form-control" rows="8"
                                        placeholder="Enter each objective on a new line&#10;e.g.&#10;- Understand basic algebra concepts&#10;- Solve linear equations&#10;- Apply mathematical reasoning">{{ old('learning_objectives', is_array($course->learning_objectives) ? implode("\n", $course->learning_objectives) : '') }}</textarea>
                                    <div class="form-text">List the key learning outcomes (one per line)</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Prerequisites</label>
                                    <textarea name="prerequisites_text" class="form-control" rows="5"
                                        placeholder="e.g. Basic understanding of arithmetic, completion of Grade 7 Mathematics">{{ old('prerequisites_text', $course->prerequisites_text) }}</textarea>
                                </div>

                                <!-- Schedule & Enrollment Section -->
                                <h6 class="section-title">Schedule & Enrollment</h6>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ old('start_date', $course->start_date?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ old('end_date', $course->end_date?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Maximum Students</label>
                                        <input type="number" name="max_students" class="form-control"
                                            value="{{ old('max_students', $course->max_students) }}" min="1"
                                            placeholder="No limit">
                                        <div class="form-text">Leave empty for unlimited</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Passing Grade (%)</label>
                                        <input type="number" name="passing_grade" class="form-control"
                                            value="{{ old('passing_grade', $course->passing_grade ?? 60) }}"
                                            min="0" max="100">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="self_enrollment"
                                                id="selfEnrollment" value="1"
                                                {{ old('self_enrollment', $course->self_enrollment) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="selfEnrollment">Allow
                                                Self-Enrollment</label>
                                        </div>
                                        <div class="form-text">Students can enroll themselves</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Enrollment Key</label>
                                        <input type="text" name="enrollment_key" class="form-control"
                                            value="{{ old('enrollment_key', $course->enrollment_key) }}"
                                            placeholder="Optional password">
                                        <div class="form-text">Required to self-enroll</div>
                                    </div>
                                </div>

                                <!-- Course Thumbnail Section -->
                                <h6 class="section-title">Content Thumbnail</h6>
                                <div class="row">
                                    <div class="col-12 mb-0">
                                        @if ($course->thumbnail_path)
                                            <div class="mb-3">
                                                <img src="{{ Storage::url($course->thumbnail_path) }}" alt=""
                                                    style="max-width: 200px; border-radius: 3px;">
                                            </div>
                                        @endif
                                        <div class="custom-file-input">
                                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                                            <label for="thumbnail" class="file-input-label">
                                                <div class="file-input-icon">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                                <div class="file-input-text">
                                                    <span
                                                        class="file-label">{{ $course->thumbnail_path ? 'Change Image' : 'Choose Image File' }}</span>
                                                    <span class="file-hint" id="thumbnailHint">PNG, JPG or GIF (max 2MB) -
                                                        Recommended: 400x300px</span>
                                                    <span class="file-selected d-none" id="thumbnailName"></span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('lms.courses.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                    @if ($course->status === 'draft')
                                        <button type="button" class="btn btn-success"
                                            onclick="document.getElementById('publishForm').submit();">
                                            <i class="fas fa-globe me-1"></i> Publish Content
                                        </button>
                                    @elseif ($course->status === 'published')
                                        <button type="button" class="btn btn-warning"
                                            onclick="document.getElementById('unpublishForm').submit();">
                                            <i class="fas fa-eye-slash me-1"></i> Unpublish
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-3">
                            <div class="sidebar-container">
                                <h6 class="sidebar-section-title">Quick Actions</h6>

                                <a href="{{ route('lms.enrollments.index', $course) }}" class="quick-link">
                                    <div class="quick-link-icon blue">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="quick-link-content">
                                        <h6>Manage Enrollments</h6>
                                        <p>View and manage students</p>
                                    </div>
                                </a>

                                <a href="{{ route('lms.courses.show', $course) }}" class="quick-link">
                                    <div class="quick-link-icon purple">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div class="quick-link-content">
                                        <h6>Preview Content</h6>
                                        <p>See student view</p>
                                    </div>
                                </a>

                                <a href="{{ route('lms.modules.create', $course) }}" class="quick-link">
                                    <div class="quick-link-icon green">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="quick-link-content">
                                        <h6>Add Module</h6>
                                        <p>Create new module</p>
                                    </div>
                                </a>

                                <a href="{{ route('lms.discussions.forum', $course) }}" class="quick-link">
                                    <div class="quick-link-icon orange">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <div class="quick-link-content">
                                        <h6>Discussions</h6>
                                        <p>Course forum</p>
                                    </div>
                                </a>

                                <h6 class="sidebar-section-title">Content Stats</h6>

                                <div class="stats-card">
                                    <div class="stat-row">
                                        <span class="stat-row-label">Modules</span>
                                        <span class="stat-row-value">{{ $course->modules->count() }}</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-row-label">Content Items</span>
                                        <span
                                            class="stat-row-value">{{ $course->modules->sum(fn($m) => $m->contentItems->count()) }}</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-row-label">Enrolled</span>
                                        <span class="stat-row-value">{{ $course->enrollments->count() ?? 0 }}</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-row-label">Pass Grade</span>
                                        <span class="stat-row-value">{{ $course->passing_grade ?? 60 }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="modules">
                    <div class="help-text">
                        <div class="help-title">Modules & Content</div>
                        <p class="help-content">Organize your content into modules and add learning content. Each module
                            can
                            contain videos, documents, quizzes, and other materials.</p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Content Modules</h5>
                        <a href="{{ route('lms.modules.create', $course) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Module
                        </a>
                    </div>

                    @forelse ($course->modules as $module)
                        <div class="module-card">
                            <div class="module-header">
                                <div>
                                    <span class="module-title">{{ $loop->iteration }}. {{ $module->title }}</span>
                                    <span class="text-muted ms-2">({{ $module->contentItems->count() }} items)</span>
                                </div>
                                <div class="module-actions">
                                    <a href="{{ route('lms.modules.edit', $module) }}"
                                        class="btn btn-sm btn-outline-info" title="Edit Module">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('lms.content.create', $module) }}"
                                        class="btn btn-sm btn-outline-success" title="Add Content">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            @if ($module->contentItems->count() > 0)
                                <div class="module-body">
                                    @foreach ($module->contentItems as $content)
                                        <div class="content-item">
                                            <div class="content-icon {{ $content->type }}">
                                                @switch($content->type)
                                                    @case('video_youtube')
                                                        <i class="fab fa-youtube"></i>
                                                    @break

                                                    @case('video_upload')
                                                        <i class="fas fa-video"></i>
                                                    @break

                                                    @case('document')
                                                        <i class="fas fa-file-alt"></i>
                                                    @break

                                                    @case('quiz')
                                                        <i class="fas fa-question-circle"></i>
                                                    @break

                                                    @case('scorm')
                                                        <i class="fas fa-cube"></i>
                                                    @break

                                                    @case('external_link')
                                                        <i class="fas fa-external-link-alt"></i>
                                                    @break

                                                    @case('audio')
                                                        <i class="fas fa-headphones"></i>
                                                    @break

                                                    @case('image')
                                                        <i class="fas fa-image"></i>
                                                    @break

                                                    @case('text')
                                                        <i class="fas fa-align-left"></i>
                                                    @break

                                                    @case('assignment')
                                                        <i class="fas fa-tasks"></i>
                                                    @break

                                                    @default
                                                        <i class="fas fa-file"></i>
                                                @endswitch
                                            </div>
                                            <div class="flex-grow-1">
                                                <span>{{ $content->title }}</span>
                                                @if ($content->estimated_duration)
                                                    <span class="text-muted ms-2">({{ $content->estimated_duration }}
                                                        min)</span>
                                                @endif
                                                {{-- Content Status --}}
                                                @if ($content->type === 'quiz' && $content->contentable && $content->contentable->questions->count() > 0)
                                                    <span class="badge bg-soft-primary text-primary ms-2">
                                                        <i
                                                            class="fas fa-question-circle me-1"></i>{{ $content->contentable->questions->count() }}
                                                        question{{ $content->contentable->questions->count() != 1 ? 's' : '' }}
                                                    </span>
                                                @elseif (
                                                    $content->type === 'document' &&
                                                        ($content->library_item_id ||
                                                            ($content->contentable && $content->contentable->file_path) ||
                                                            $content->file_path))
                                                    <span class="badge bg-soft-success text-success ms-2">
                                                        <i
                                                            class="fas fa-file me-1"></i>{{ $content->library_item_id ? 'From Library' : 'File uploaded' }}
                                                    </span>
                                                @elseif ($content->type === 'video_youtube' && $content->contentable && $content->contentable->source_id)
                                                    <span class="badge bg-soft-danger text-danger ms-2">
                                                        <i class="fab fa-youtube me-1"></i>YouTube linked
                                                    </span>
                                                @elseif (
                                                    $content->type === 'video_upload' &&
                                                        ($content->library_item_id ||
                                                            ($content->contentable && $content->contentable->file_path) ||
                                                            $content->file_path))
                                                    <span class="badge bg-soft-danger text-danger ms-2">
                                                        <i
                                                            class="fas fa-video me-1"></i>{{ $content->library_item_id ? 'From Library' : 'Video uploaded' }}
                                                    </span>
                                                @elseif ($content->type === 'scorm' && $content->contentable)
                                                    <span class="badge bg-soft-purple text-purple ms-2">
                                                        <i class="fas fa-cube me-1"></i>SCORM package
                                                    </span>
                                                @elseif ($content->type === 'text' && $content->content)
                                                    <span class="badge bg-soft-info text-info ms-2">
                                                        <i class="fas fa-align-left me-1"></i>Content added
                                                    </span>
                                                @elseif ($content->type === 'external_link' && $content->external_url)
                                                    <span class="badge bg-soft-cyan text-cyan ms-2">
                                                        <i class="fas fa-external-link-alt me-1"></i>Link added
                                                    </span>
                                                @elseif ($content->type === 'audio' && ($content->library_item_id || $content->file_path))
                                                    <span class="badge bg-soft-pink text-pink ms-2">
                                                        <i
                                                            class="fas fa-headphones me-1"></i>{{ $content->library_item_id ? 'From Library' : 'Audio uploaded' }}
                                                    </span>
                                                @elseif ($content->type === 'image' && ($content->library_item_id || $content->file_path))
                                                    <span class="badge bg-soft-success text-success ms-2">
                                                        <i
                                                            class="fas fa-image me-1"></i>{{ $content->library_item_id ? 'From Library' : 'Image uploaded' }}
                                                    </span>
                                                @elseif ($content->type === 'assignment' && $content->contentable)
                                                    <span class="badge bg-soft-success text-success ms-2">
                                                        <i class="fas fa-tasks me-1"></i>Assignment
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-warning text-warning ms-2">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>No content
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="content-actions">
                                                <a href="{{ route('lms.content.edit', $content) }}"
                                                    class="btn btn-sm btn-outline-info" title="Edit Content">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('lms.content.destroy', $content) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this content?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Delete Content">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>No modules yet. Add your first module to get started.</p>
                                <a href="{{ route('lms.modules.create', $course) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add First Module
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="settings">
                        <div class="help-text">
                            <div class="help-title">Content Settings</div>
                            <p class="help-content">Manage content actions and settings. Quick links and statistics are
                                available in the Content Details tab sidebar.</p>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <!-- Course Actions -->
                                <div class="settings-section">
                                    <h6 class="settings-section-title">Content Actions</h6>

                                    <div class="action-card">
                                        <div class="action-info">
                                            <div class="action-icon gray">
                                                <i class="fas fa-copy"></i>
                                            </div>
                                            <div class="action-text">
                                                <h6>Duplicate Content</h6>
                                                <p>Create a copy of this content with all modules</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('lms.courses.duplicate', $course) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Duplicate
                                            </button>
                                        </form>
                                    </div>

                                    @if ($course->status !== 'archived')
                                        <div class="action-card">
                                            <div class="action-info">
                                                <div class="action-icon yellow">
                                                    <i class="fas fa-archive"></i>
                                                </div>
                                                <div class="action-text">
                                                    <h6>Archive Content</h6>
                                                    <p>Hide content from active listings</p>
                                                </div>
                                            </div>
                                            <form action="{{ route('lms.courses.archive', $course) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    Archive
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <!-- Danger Zone -->
                                <div class="settings-section">
                                    <div class="danger-zone">
                                        <div class="danger-zone-header">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <h6>Danger Zone</h6>
                                        </div>
                                        <div class="danger-zone-content">
                                            <div class="danger-zone-text">
                                                <h6>Delete this content</h6>
                                                <p>Once deleted, this course and all its data cannot be recovered.</p>
                                            </div>
                                            <form action="{{ route('lms.courses.destroy', $course) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this content? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Delete Content
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="publishForm" action="{{ route('lms.courses.publish', $course) }}" method="POST" style="display:none;">
            @csrf</form>
        <form id="unpublishForm" action="{{ route('lms.courses.unpublish', $course) }}" method="POST"
            style="display:none;">@csrf</form>
    @endsection

    @section('script')
        <script>
            // Grade and Subject dynamic loading
            const gradeSelect = document.getElementById('gradeSelect');
            const subjectSelect = document.getElementById('subjectSelect');
            const currentSubjectId = '{{ $course->grade_subject_id }}';

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
                            if (currentSubjectId && subject.id == currentSubjectId) {
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
            const form = document.querySelector('#details form');
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

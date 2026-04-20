@extends('layouts.master')

@section('title')
    Learning Content
@endsection

@section('css')
    <style>
        .courses-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .courses-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .courses-body {
            padding: 24px;
        }

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

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 3px 0 0 3px !important;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 3px 3px 0 !important;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }

        .course-thumbnail {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 3px;
            background: #f3f4f6;
        }

        .course-title {
            font-weight: 600;
            color: #1f2937;
        }

        .course-title:hover {
            color: #6366f1;
        }

        .course-code {
            font-size: 12px;
            color: #6b7280;
        }

        .status-draft {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-published {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-archived {
            background-color: #e5e7eb;
            color: #374151;
        }

        /* Course Card Styles */
        .course-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 3px;
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .course-card .card-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 12px 16px;
        }

        .course-card .card-header .card-title a {
            color: white !important;
        }

        .course-card .card-header .card-title a:hover {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .course-card .card-header small {
            color: rgba(255, 255, 255, 0.8);
        }

        .course-card .card-header .student-count-badge {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white;
        }

        .course-card-thumbnail {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 3px;
        }

        .course-card .card-body {
            padding: 20px 16px;
        }

        .course-card .card-body h6 {
            font-size: 12px;
            margin-bottom: 8px !important;
        }

        .course-card .card-body .list-group-item {
            padding: 6px 0 !important;
        }

        .course-card .card-body > div {
            margin-bottom: 14px !important;
        }

        .course-card .card-body > div:last-child {
            margin-bottom: 0 !important;
        }

        .course-card .card-footer {
            padding: 12px 16px;
        }

        .course-card .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* View Toggle Buttons */
        .view-toggle-group {
            display: inline-flex;
        }

        .view-toggle-group .btn {
            padding: 10px 14px;
        }

        .view-toggle-group .btn.active {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-color: #4f46e5;
        }

        .view-toggle-group .btn:not(.active) {
            background: #f8f9fa;
            color: #6c757d;
        }

        .student-count-badge {
            font-size: 12px;
        }

        #coursesCardView {
            display: none;
        }

        /* Loading placeholder for cards */
        .card-placeholder {
            animation: placeholder-glow 2s ease-in-out infinite;
        }

        @keyframes placeholder-glow {
            50% {
                opacity: 0.5;
            }
        }

        .placeholder-item {
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#">Dashboard</a>
        @endslot
        @slot('title')
            Learning Content
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-alert-circle label-icon"></i>
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="courses-container">
        <div class="courses-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Learning Space</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage content, modules, and learning materials</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $courses->total() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $courses->where('status', 'published')->count() }}
                                </h4>
                                <small class="opacity-75">Published</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $courses->where('status', 'draft')->count() }}</h4>
                                <small class="opacity-75">Drafts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="courses-body">
            <div class="help-text">
                <div class="help-title">Learning Space Content</div>
                <div class="help-content">
                    Create and manage learning content for your students. Add modules, materials, quizzes, and track student progress.
                    Published content will be available for student enrollment.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-9 col-md-8">
                    <form method="GET" action="{{ route('lms.courses.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search content..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>
                                        Draft</option>
                                    <option value="published"
                                        {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published
                                    </option>
                                    <option value="archived"
                                        {{ ($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <select name="grade_id" class="form-select">
                                    <option value="">All Grades</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2">
                                <select name="term_id" class="form-select">
                                    <option value="">All Terms</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}"
                                            {{ ($filters['term_id'] ?? '') == $term->id ? 'selected' : '' }}>
                                            {{ $term->year }} Term {{ $term->term }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-1">
                                <a href="{{ route('lms.courses.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 col-md-4 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <div class="btn-group view-toggle-group" role="group" aria-label="View toggle">
                            <button type="button" class="btn btn-light view-toggle active" data-view="list" title="List View">
                                <i class="fas fa-list"></i>
                            </button>
                            <button type="button" class="btn btn-light view-toggle" data-view="cards" title="Card View">
                                <i class="fas fa-th-large"></i>
                            </button>
                        </div>
                        @can('manage-lms-courses')
                            <a href="{{ route('lms.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Content
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div id="coursesListView">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;"></th>
                                <th>Content</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Teacher</th>
                                <th class="text-center">Modules</th>
                                <th class="text-center">Enrolled</th>
                                <th>Status</th>
                                <th style="width: 120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                <tr>
                                    <td>
                                        @if ($course->thumbnail_path)
                                            <img src="{{ Storage::url($course->thumbnail_path) }}" alt=""
                                                class="course-thumbnail">
                                        @else
                                            <div class="course-thumbnail d-flex align-items-center justify-content-center">
                                                <i class="fas fa-book text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('lms.courses.show', $course) }}"
                                            class="course-title text-decoration-none">
                                            {{ $course->title }}
                                        </a>
                                        <div class="course-code">{{ $course->code }}</div>
                                    </td>
                                    <td>{{ $course->gradeSubject->subject->name ?? '-' }}</td>
                                    <td>{{ $course->grade->name ?? '-' }}</td>
                                    <td>{{ $course->instructor->firstname ?? '' }} {{ $course->instructor->lastname ?? '' }}
                                    </td>
                                    <td class="text-center">{{ $course->modules_count }}</td>
                                    <td class="text-center">{{ $course->enrollments_count }}</td>
                                    <td>
                                        <span class="badge status-{{ $course->status }}">
                                            {{ ucfirst($course->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('lms.courses.show', $course) }}" class="btn btn-light"
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('manage-lms-courses')
                                                <a href="{{ route('lms.courses.edit', $course) }}" class="btn btn-light"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('lms.enrollments.index', $course) }}" class="btn btn-light"
                                                    title="Enrollments">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-book-open fa-2x mb-2"></i>
                                            <p class="mb-0">No content found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($courses->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $courses->withQueryString()->links() }}
                    </div>
                @endif
            </div>

            <!-- Card View -->
            <div id="coursesCardView">
                <div id="coursesCardsContainer"></div>

                <!-- Loading placeholder -->
                <div id="cardsLoadingPlaceholder" class="d-none">
                    <div class="row">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card card-placeholder">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center">
                                                <span class="placeholder-item me-2" style="width: 40px; height: 40px;"></span>
                                                <div>
                                                    <span class="placeholder-item" style="width: 120px; height: 16px;"></span>
                                                    <br><span class="placeholder-item mt-1" style="width: 60px; height: 12px;"></span>
                                                </div>
                                            </div>
                                            <span class="placeholder-item" style="width: 40px; height: 24px; border-radius: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <span class="placeholder-item" style="width: 60px; height: 20px;"></span>
                                            <span class="placeholder-item ms-1" style="width: 50px; height: 20px;"></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="placeholder-item" style="width: 80px; height: 14px;"></span>
                                            <br><span class="placeholder-item mt-1" style="width: 100px; height: 14px;"></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="placeholder-item" style="width: 70px; height: 14px;"></span>
                                            <br><span class="placeholder-item mt-1" style="width: 90px; height: 14px;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const STORAGE_KEY = 'courses_view_preference';
        let cardsLoaded = false;

        // Get current filter params from URL
        function getFilterParams() {
            const urlParams = new URLSearchParams(window.location.search);
            return {
                search: urlParams.get('search') || '',
                status: urlParams.get('status') || '',
                grade_id: urlParams.get('grade_id') || '',
                term_id: urlParams.get('term_id') || ''
            };
        }

        // Load cards via AJAX
        function loadCards() {
            const params = getFilterParams();

            $('#cardsLoadingPlaceholder').removeClass('d-none');
            $('#coursesCardsContainer').empty();

            $.ajax({
                url: "{{ route('lms.courses.partial') }}",
                method: 'GET',
                data: params,
                success: function(response) {
                    $('#cardsLoadingPlaceholder').addClass('d-none');
                    $('#coursesCardsContainer').html(response).fadeIn(200, function() {
                        // Initialize tooltips for new content
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    });
                    cardsLoaded = true;
                },
                error: function(xhr, status, error) {
                    $('#cardsLoadingPlaceholder').addClass('d-none');
                    $('#coursesCardsContainer').html(`
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2 fs-4"></i>
                            <div>
                                <strong>Oops! Something went wrong.</strong><br>
                                We couldn't load the courses. Please try again.
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Switch view
        function switchView(view) {
            $('.view-toggle').removeClass('active');
            $(`.view-toggle[data-view="${view}"]`).addClass('active');

            if (view === 'list') {
                $('#coursesCardView').hide();
                $('#coursesListView').show();
            } else {
                $('#coursesListView').hide();
                $('#coursesCardView').show();

                // Load cards if not already loaded or if filters may have changed
                if (!cardsLoaded) {
                    loadCards();
                }
            }

            // Save preference
            localStorage.setItem(STORAGE_KEY, view);
        }

        // View toggle click handler
        $('.view-toggle').on('click', function() {
            const view = $(this).data('view');
            switchView(view);
        });

        // Restore saved preference on page load
        const savedView = localStorage.getItem(STORAGE_KEY);
        if (savedView === 'cards') {
            switchView('cards');
        }

        // Reset cards loaded flag when form is submitted (filters change)
        $('form').on('submit', function() {
            cardsLoaded = false;
        });
    });
</script>
@endsection

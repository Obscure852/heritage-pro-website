@extends('layouts.master')

@section('title')
    My Courses
@endsection

@section('css')
    <style>
        .courses-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .courses-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .courses-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #6b7280;
        }

        .course-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .course-card.completed {
            border-color: #10b981;
        }

        .course-thumbnail {
            height: 140px;
            object-fit: cover;
            width: 100%;
        }

        .course-thumbnail-placeholder {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .course-thumbnail-placeholder.active {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .course-thumbnail-placeholder.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .course-thumbnail-placeholder i {
            font-size: 48px;
            color: white;
            opacity: 0.5;
        }

        .course-card-body {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .course-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .course-instructor {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .progress-wrapper {
            margin-bottom: 12px;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .progress-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .progress-info .percentage {
            font-weight: 600;
            color: #059669;
        }

        .progress {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .grade-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .completed-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: auto;
        }

        .final-grade {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .final-grade .grade-value {
            font-weight: 600;
            color: #059669;
        }

        .course-card-footer {
            padding: 12px 16px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid #3b82f6;
            color: #3b82f6;
            background: transparent;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-outline-success {
            border: 1px solid #10b981;
            color: #10b981;
            background: transparent;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .btn-outline-success:hover {
            background: #10b981;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .courses-header {
                padding: 20px;
            }

            .courses-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
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

    @if (session('info'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-information label-icon"></i><strong>{{ session('info') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="courses-container">
        <div class="courses-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-book-reader me-2"></i>My Courses</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track your enrolled courses and continue learning</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $enrollments->count();
                        $activeCount = $activeCourses->count();
                        $completedCount = $completedCourses->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activeCount }}</h4>
                                <small class="opacity-75">In Progress</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $completedCount }}</h4>
                                <small class="opacity-75">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="courses-body">
            <div class="help-text">
                <div class="help-title">Learning Dashboard</div>
                <div class="help-content">
                    View all your enrolled courses and track your learning progress. Click on a course to continue learning
                    or view course details. Completed courses will show your final grade and certificate options.
                </div>
            </div>

            @if($activeCourses->count())
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-play-circle"></i>
                        In Progress ({{ $activeCourses->count() }})
                    </div>
                    <div class="row g-4">
                        @foreach($activeCourses as $enrollment)
                            <div class="col-md-6 col-lg-4">
                                <div class="course-card">
                                    @if($enrollment->course->thumbnail_path)
                                        <img src="{{ Storage::url($enrollment->course->thumbnail_path) }}"
                                             class="course-thumbnail"
                                             alt="{{ $enrollment->course->title }}">
                                    @else
                                        <div class="course-thumbnail-placeholder active">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    @endif
                                    <div class="course-card-body">
                                        <h6 class="course-title">{{ $enrollment->course->title }}</h6>
                                        <div class="course-instructor">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $enrollment->course->instructor?->name ?? 'No Teacher' }}
                                        </div>

                                        <div class="progress-wrapper">
                                            <div class="progress-info">
                                                <small>Progress</small>
                                                <span class="percentage">{{ number_format($enrollment->progress_percentage, 0) }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                            </div>
                                        </div>

                                        @if($enrollment->course->grade)
                                            <span class="grade-badge">{{ $enrollment->course->grade->name }}</span>
                                        @endif
                                    </div>
                                    <div class="course-card-footer">
                                        <a href="{{ route('lms.courses.learn', $enrollment->course) }}" class="btn btn-primary w-100">
                                            <i class="fas fa-play me-2"></i>Continue Learning
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($completedCourses->count())
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-check-circle text-success"></i>
                        Completed ({{ $completedCourses->count() }})
                    </div>
                    <div class="row g-4">
                        @foreach($completedCourses as $enrollment)
                            <div class="col-md-6 col-lg-4">
                                <div class="course-card completed">
                                    @if($enrollment->course->thumbnail_path)
                                        <img src="{{ Storage::url($enrollment->course->thumbnail_path) }}"
                                             class="course-thumbnail"
                                             alt="{{ $enrollment->course->title }}">
                                    @else
                                        <div class="course-thumbnail-placeholder completed">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                    @endif
                                    <div class="course-card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="course-title mb-0">{{ $enrollment->course->title }}</h6>
                                            <span class="status-badge status-completed"><i class="fas fa-check"></i></span>
                                        </div>
                                        <div class="course-instructor">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $enrollment->course->instructor?->name ?? 'No Teacher' }}
                                        </div>

                                        @if($enrollment->final_grade)
                                            <div class="final-grade">
                                                <small class="text-muted">Final Grade:</small>
                                                <span class="grade-value">{{ number_format($enrollment->final_grade, 1) }}%</span>
                                            </div>
                                        @endif

                                        <div class="completed-info">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Completed {{ $enrollment->completed_at?->format('M j, Y') ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="course-card-footer">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('lms.courses.show', $enrollment->course) }}" class="btn btn-outline-primary flex-grow-1">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            @if($enrollment->course->certificate_enabled)
                                                <a href="{{ route('lms.certificates.my') }}" class="btn btn-outline-success flex-grow-1">
                                                    <i class="fas fa-certificate me-1"></i>Certificate
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($enrollments->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h5>No Courses Yet</h5>
                    <p>You haven't enrolled in any courses yet. Browse available courses to get started!</p>
                    <a href="{{ route('lms.courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Browse Courses
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

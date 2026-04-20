@extends('layouts.master')

@section('title')
    {{ $learningPath->title }}
@endsection

@section('css')
    <style>
        .path-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .path-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .path-body {
            padding: 24px;
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

        /* Form Controls */
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

        /* Buttons */
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 15px;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid #3b82f6;
            color: #3b82f6;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #6b7280;
            color: #6b7280;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: #6b7280;
            color: white;
        }

        /* Save Button Loading Animation */
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

        /* Cards */
        .info-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .info-card-header {
            background: #f9fafb;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .info-card-body {
            padding: 20px;
        }

        /* Path Thumbnail */
        .path-thumbnail-large {
            height: 250px;
            object-fit: cover;
            width: 100%;
            border-radius: 3px 3px 0 0;
        }

        /* Stats */
        .stat-row {
            display: flex;
            justify-content: space-around;
            text-align: center;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .stat-item {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
        }

        .stat-value.info {
            color: #0ea5e9;
        }

        .stat-value.success {
            color: #10b981;
        }

        .stat-value.warning {
            color: #f59e0b;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }

        /* Badge */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-beginner {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-intermediate {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-advanced {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #cffafe;
            color: #0e7490;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .category-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-right: 4px;
        }

        /* Curriculum */
        .curriculum-item {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .curriculum-item:last-child {
            border-bottom: none;
        }

        .curriculum-item.completed {
            background: #f9fafb;
        }

        .subject-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .subject-number.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .subject-number.in-progress {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .subject-number.locked {
            background: #9ca3af;
        }

        /* Objectives */
        .objective-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .objective-item i {
            color: #10b981;
            margin-right: 10px;
            margin-top: 4px;
        }

        /* Milestones */
        .milestone-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .milestone-item:last-child {
            margin-bottom: 0;
        }

        .milestone-icon {
            width: 50px;
            text-align: center;
            margin-right: 16px;
        }

        .milestone-icon i {
            font-size: 28px;
            color: #f59e0b;
        }

        /* Sidebar */
        .sidebar-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
        }

        .sidebar-card.sticky {
            position: sticky;
            top: 20px;
        }

        .price-tag {
            font-size: 1.75rem;
            font-weight: 700;
            color: #3b82f6;
        }

        .free-tag {
            background: #d1fae5;
            color: #065f46;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Progress */
        .progress {
            height: 10px;
            background: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Info List */
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .info-list li i {
            color: #3b82f6;
            width: 24px;
            margin-right: 8px;
        }

        /* Related Paths */
        .related-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            height: 100%;
        }

        .related-card:hover {
            border-color: #3b82f6;
        }

        @media (max-width: 768px) {
            .path-header {
                padding: 20px;
            }

            .path-body {
                padding: 16px;
            }

            .stat-row {
                flex-wrap: wrap;
            }

            .stat-item {
                flex: 0 0 50%;
                margin-bottom: 12px;
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

    <div class="path-container">
        <div class="path-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="mb-2">
                        <span class="badge badge-{{ $learningPath->level }}" style="background: rgba(255,255,255,0.2); color: white;">{{ $learningPath->level_label }}</span>
                        @foreach($learningPath->categories as $cat)
                            <span class="category-badge" style="background: rgba(255,255,255,0.2); color: white;">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                    <h3 style="margin:0;">{{ $learningPath->title }}</h3>
                    <p style="margin:12px 0 0 0; opacity:.9;">{{ $learningPath->description }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    @can('manage-lms-content')
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-secondary dropdown-toggle" style="border-color: white; color: white;" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="{{ route('lms.learning-paths.edit', $learningPath) }}" class="dropdown-item"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li>
                                    <form action="{{ route('lms.learning-paths.toggle-publish', $learningPath) }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item">
                                            <i class="fas fa-{{ $learningPath->is_published ? 'eye-slash' : 'eye' }} me-2"></i>
                                            {{ $learningPath->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="path-body">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Stats -->
                    <div class="info-card">
                        @if($learningPath->thumbnail_url)
                            <img src="{{ $learningPath->thumbnail_url }}" class="path-thumbnail-large" alt="{{ $learningPath->title }}">
                        @endif
                        <div class="info-card-body">
                            <div class="stat-row" style="border-top: none; padding-top: 0; margin-top: 0;">
                                <div class="stat-item">
                                    <div class="stat-value">{{ $learningPath->courses_count }}</div>
                                    <div class="stat-label">Subjects</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value info">{{ $learningPath->estimated_duration }}</div>
                                    <div class="stat-label">Duration</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value success">{{ $learningPath->enrollments_count }}</div>
                                    <div class="stat-label">Enrolled</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value warning">{{ $learningPath->milestones->count() }}</div>
                                    <div class="stat-label">Milestones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Learning Objectives -->
                    @if(!empty($learningPath->objectives))
                        <div class="info-card">
                            <div class="info-card-header">
                                <h6><i class="fas fa-bullseye me-2"></i>What You'll Learn</h6>
                            </div>
                            <div class="info-card-body">
                                <div class="row">
                                    @foreach($learningPath->objectives as $objective)
                                        <div class="col-md-6">
                                            <div class="objective-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span>{{ $objective }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Subject Curriculum -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <h6><i class="fas fa-list-ol me-2"></i>Subject Curriculum</h6>
                        </div>
                        <div class="info-card-body p-0">
                            @foreach($learningPath->pathCourses as $index => $pathCourse)
                                @php
                                    $courseProgress = $enrollment ? $progress->where('path_course_id', $pathCourse->id)->first() : null;
                                    $isCompleted = $courseProgress?->status === 'completed';
                                    $isInProgress = $courseProgress?->status === 'in_progress';
                                    $isLocked = $courseProgress?->status === 'locked';
                                @endphp
                                <div class="curriculum-item {{ $isCompleted ? 'completed' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="subject-number me-3 {{ $isCompleted ? 'completed' : ($isInProgress ? 'in-progress' : ($isLocked ? 'locked' : '')) }}">
                                            @if($isCompleted)
                                                <i class="fas fa-check"></i>
                                            @elseif($isInProgress)
                                                <i class="fas fa-spinner fa-spin"></i>
                                            @elseif($isLocked)
                                                <i class="fas fa-lock"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $pathCourse->course->title }}</h6>
                                                    <p class="text-muted small mb-2">{{ Str::limit($pathCourse->course->description, 100) }}</p>
                                                    <div>
                                                        @if($pathCourse->is_required)
                                                            <span class="badge badge-primary">Required</span>
                                                        @else
                                                            <span class="badge badge-secondary">Optional</span>
                                                        @endif
                                                        @if($pathCourse->is_milestone)
                                                            <span class="badge badge-warning">Milestone: {{ $pathCourse->milestone_title }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    @if($pathCourse->course->duration_hours)
                                                        <small class="text-muted">{{ $pathCourse->course->duration_hours }}h</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Milestones -->
                    @if($learningPath->milestones->count())
                        <div class="info-card">
                            <div class="info-card-header">
                                <h6><i class="fas fa-flag-checkered me-2"></i>Milestones & Rewards</h6>
                            </div>
                            <div class="info-card-body">
                                @foreach($learningPath->milestones as $milestone)
                                    <div class="milestone-item">
                                        <div class="milestone-icon">
                                            <i class="{{ $milestone->icon }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $milestone->title }}</h6>
                                            <small class="text-muted">Complete {{ $milestone->courses_required }} subjects</small>
                                        </div>
                                        <div class="text-end">
                                            @if($milestone->badge)
                                                <span class="badge badge-info">Badge: {{ $milestone->badge->name }}</span>
                                            @endif
                                            @if($milestone->points_awarded > 0)
                                                <span class="badge badge-success">+{{ $milestone->points_awarded }} XP</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar-card sticky">
                        @if($enrollment)
                            <!-- Progress -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Your Progress</span>
                                    <span class="text-primary fw-bold">{{ $enrollment->progress_percentage }}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ $enrollment->courses_completed }}/{{ $enrollment->total_courses }} subjects completed</small>
                            </div>

                            <a href="{{ route('lms.learning-paths.learn', $learningPath) }}" class="btn btn-success btn-lg w-100 mb-3">
                                <i class="fas fa-play me-2"></i>Continue Learning
                            </a>

                            <div class="text-center text-muted small">
                                Enrolled {{ $enrollment->enrolled_at->diffForHumans() }}
                            </div>
                        @else
                            @if($learningPath->price > 0)
                                <div class="text-center mb-3">
                                    <span class="price-tag">BWP {{ number_format($learningPath->price, 2) }}</span>
                                </div>
                            @else
                                <div class="text-center mb-3">
                                    <span class="free-tag">Free</span>
                                </div>
                            @endif

                            <form action="{{ route('lms.learning-paths.enroll', $learningPath) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100 btn-loading">
                                    <span class="btn-text"><i class="fas fa-user-plus me-2"></i>Enroll Now</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Enrolling...
                                    </span>
                                </button>
                            </form>
                        @endif

                        <hr>

                        <!-- Path Info -->
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-layer-group"></i>
                                <strong>Level:</strong>&nbsp;{{ $learningPath->level_label }}
                            </li>
                            <li>
                                <i class="fas fa-book"></i>
                                <strong>Subjects:</strong>&nbsp;{{ $learningPath->courses_count }}
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <strong>Duration:</strong>&nbsp;{{ $learningPath->estimated_duration }}
                            </li>
                            @if($learningPath->enforce_sequence)
                                <li>
                                    <i class="fas fa-list-ol"></i>
                                    <strong>Sequential:</strong>&nbsp;Yes
                                </li>
                            @endif
                            @if($learningPath->certificateTemplate)
                                <li>
                                    <i class="fas fa-certificate" style="color: #f59e0b;"></i>
                                    <strong>Certificate:</strong>&nbsp;Included
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Related Paths -->
            @if($relatedPaths->count())
                <div class="mt-5">
                    <h5 class="mb-3"><i class="fas fa-compass me-2"></i>Related Learning Paths</h5>
                    <div class="row g-4">
                        @foreach($relatedPaths as $related)
                            <div class="col-md-3">
                                <div class="related-card">
                                    <span class="badge badge-{{ $related->level }} mb-2">{{ $related->level_label }}</span>
                                    <h6>{{ $related->title }}</h6>
                                    <small class="text-muted">{{ $related->courses_count }} subjects</small>
                                    <div class="mt-3">
                                        <a href="{{ route('lms.learning-paths.show', $related) }}" class="btn btn-sm btn-outline-primary w-100">View</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle enroll button loading state
            const enrollForm = document.querySelector('form[action*="enroll"]');
            if (enrollForm) {
                enrollForm.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection

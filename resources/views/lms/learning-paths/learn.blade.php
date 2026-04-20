@extends('layouts.master')

@section('title')
    {{ $learningPath->title }} - Learning
@endsection

@section('css')
    <style>
        .learn-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .learn-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .learn-body {
            padding: 24px;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .back-link:hover {
            color: white;
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

        /* Sidebar Card */
        .sidebar-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .sidebar-card-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 16px;
        }

        .sidebar-card-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar-card-body {
            padding: 12px;
        }

        /* Progress */
        .progress-wrapper {
            padding: 12px;
            margin-bottom: 8px;
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
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Subject List */
        .subject-list-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s ease;
        }

        .subject-list-item:last-child {
            border-bottom: none;
        }

        .subject-list-item:hover {
            background: #f9fafb;
        }

        .subject-list-item.current {
            background: #f0f9ff;
            border-left: 3px solid #3b82f6;
        }

        .subject-list-item.locked {
            opacity: 0.7;
        }

        .subject-status {
            margin-right: 12px;
        }

        .status-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.in-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.locked {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-badge.pending {
            background: #f3f4f6;
            color: #374151;
        }

        .subject-info {
            flex-grow: 1;
        }

        .subject-info .title {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .subject-info .progress-small {
            height: 4px;
            margin-top: 6px;
        }

        .subject-action .btn {
            padding: 4px 10px;
            font-size: 12px;
        }

        .milestone-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Milestones Sidebar */
        .milestone-list-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .milestone-list-item:last-child {
            border-bottom: none;
        }

        .milestone-list-item.unlocked {
            background: #f0fdf4;
        }

        .milestone-icon {
            font-size: 20px;
            margin-right: 12px;
        }

        .milestone-icon.unlocked {
            color: #10b981;
        }

        .milestone-icon.locked {
            color: #9ca3af;
        }

        .milestone-info {
            flex-grow: 1;
        }

        .milestone-info .title {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
        }

        .milestone-info .subtitle {
            font-size: 11px;
            color: #6b7280;
        }

        /* Main Content */
        .content-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .content-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-card-body {
            padding: 20px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Current Subject */
        .current-subject {
            background: #f0f9ff;
            border-color: #3b82f6;
        }

        .current-subject h4 {
            color: #1f2937;
            margin-bottom: 8px;
        }

        .current-subject p {
            color: #6b7280;
            margin-bottom: 16px;
        }

        .current-subject .progress {
            height: 10px;
            margin-bottom: 16px;
        }

        /* Completion State */
        .completion-card {
            text-align: center;
            padding: 40px 20px;
        }

        .completion-card i {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .completion-card.success i {
            color: #10b981;
        }

        .completion-card.ready i {
            color: #3b82f6;
        }

        .completion-card h4 {
            color: #1f2937;
            margin-bottom: 8px;
        }

        .completion-card p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        /* Stats Cards */
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            text-align: center;
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-card .value.primary {
            color: #3b82f6;
        }

        .stat-card .value.info {
            color: #0ea5e9;
        }

        .stat-card .value.success {
            color: #10b981;
        }

        .stat-card .value.warning {
            color: #f59e0b;
        }

        .stat-card .label {
            font-size: 12px;
            color: #6b7280;
        }

        @media (max-width: 991px) {
            .sidebar-card {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 768px) {
            .learn-header {
                padding: 20px;
            }

            .learn-body {
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

    <div class="learn-container">
        <div class="learn-header">
            <a href="{{ route('lms.learning-paths.my-paths') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>Back to My Paths
            </a>
            <h3 style="margin:0;"><i class="fas fa-route me-2"></i>{{ $learningPath->title }}</h3>
            <div class="mt-3">
                <div class="d-flex align-items-center gap-3">
                    <span>Overall Progress</span>
                    <div class="flex-grow-1" style="max-width: 200px;">
                        <div class="progress" style="background: rgba(255,255,255,0.3);">
                            <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%; background: white;"></div>
                        </div>
                    </div>
                    <span class="fw-bold">{{ $enrollment->progress_percentage }}%</span>
                </div>
            </div>
        </div>

        <div class="learn-body">
            <div class="row">
                <!-- Sidebar - Subject List -->
                <div class="col-lg-4 order-lg-1 order-2">
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <h6><i class="fas fa-list me-2"></i>Subject Progress</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($learningPath->pathCourses as $index => $pathCourse)
                                @php
                                    $courseProgress = $progress->get($pathCourse->id);
                                    $isLocked = $courseProgress?->status === 'locked';
                                    $isCompleted = $courseProgress?->status === 'completed';
                                    $isInProgress = $courseProgress?->status === 'in_progress';
                                    $isCurrent = $currentCourse && $currentCourse->path_course_id === $pathCourse->id;
                                @endphp
                                <div class="subject-list-item {{ $isCurrent ? 'current' : '' }} {{ $isLocked ? 'locked' : '' }}">
                                    <div class="subject-status">
                                        @if($isCompleted)
                                            <span class="status-badge completed"><i class="fas fa-check"></i></span>
                                        @elseif($isInProgress)
                                            <span class="status-badge in-progress"><i class="fas fa-spinner fa-spin"></i></span>
                                        @elseif($isLocked)
                                            <span class="status-badge locked"><i class="fas fa-lock"></i></span>
                                        @else
                                            <span class="status-badge pending">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="subject-info">
                                        <div class="title">{{ $pathCourse->course->title }}</div>
                                        @if($courseProgress && $courseProgress->progress_percentage > 0 && !$isCompleted)
                                            <div class="progress progress-small">
                                                <div class="progress-bar" style="width: {{ $courseProgress->progress_percentage }}%"></div>
                                            </div>
                                        @endif
                                        @if($pathCourse->is_milestone)
                                            <span class="milestone-badge">
                                                <i class="fas fa-flag"></i>{{ $pathCourse->milestone_title }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="subject-action">
                                        @if($isCompleted)
                                            <span class="badge badge-success">Done</span>
                                        @elseif(!$isLocked)
                                            <form action="{{ route('lms.learning-paths.start-course', [$learningPath, $pathCourse]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary btn-loading">
                                                    <span class="btn-text">{{ $isInProgress ? 'Continue' : 'Start' }}</span>
                                                    <span class="btn-spinner d-none">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Milestones -->
                    @if($learningPath->milestones->count())
                        <div class="sidebar-card">
                            <div class="sidebar-card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <h6><i class="fas fa-trophy me-2"></i>Milestones</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($learningPath->milestones as $milestone)
                                    @php $isUnlocked = $milestoneCompletions->contains($milestone->id); @endphp
                                    <div class="milestone-list-item {{ $isUnlocked ? 'unlocked' : '' }}">
                                        <i class="{{ $milestone->icon }} milestone-icon {{ $isUnlocked ? 'unlocked' : 'locked' }}"></i>
                                        <div class="milestone-info">
                                            <div class="title">{{ $milestone->title }}</div>
                                            <div class="subtitle">{{ $milestone->courses_required }} subjects</div>
                                        </div>
                                        @if($isUnlocked)
                                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Main Content -->
                <div class="col-lg-8 order-lg-2 order-1">
                    @if($currentCourse)
                        @php $currentPathCourse = $currentCourse->pathCourse; @endphp
                        <div class="content-card current-subject">
                            <div class="content-card-header">
                                <h5>
                                    <i class="fas fa-play-circle text-primary"></i>
                                    Currently Learning
                                </h5>
                                <span class="badge badge-primary">{{ $currentCourse->progress_percentage }}% complete</span>
                            </div>
                            <div class="content-card-body">
                                <h4>{{ $currentPathCourse->course->title }}</h4>
                                <p>{{ $currentPathCourse->course->description }}</p>

                                <div class="progress mb-4">
                                    <div class="progress-bar" style="width: {{ $currentCourse->progress_percentage }}%"></div>
                                </div>

                                <a href="{{ route('lms.courses.learn', $currentPathCourse->course) }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play me-2"></i>Continue Subject
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="content-card">
                            <div class="content-card-body">
                                @if($enrollment->status === 'completed')
                                    <div class="completion-card success">
                                        <i class="fas fa-trophy"></i>
                                        <h4>Congratulations!</h4>
                                        <p>You have completed this learning path!</p>
                                        @if($learningPath->certificateTemplate)
                                            <a href="{{ route('lms.certificates.my-certificates') }}" class="btn btn-success btn-lg">
                                                <i class="fas fa-certificate me-2"></i>View Certificate
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <div class="completion-card ready">
                                        <i class="fas fa-play-circle"></i>
                                        <h4>Ready to Start?</h4>
                                        <p>Select a subject from the list to begin learning.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Path Stats -->
                    <div class="row g-4 mt-2">
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="value primary">{{ $enrollment->courses_completed }}</div>
                                <div class="label">Completed</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="value info">{{ $enrollment->total_courses - $enrollment->courses_completed }}</div>
                                <div class="label">Remaining</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="value success">{{ $milestoneCompletions->count() }}</div>
                                <div class="label">Milestones</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="value warning">{{ $enrollment->enrolled_at->diffInDays() }}</div>
                                <div class="label">Days Enrolled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle start/continue button loading states
            const forms = document.querySelectorAll('form[action*="start-course"]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection

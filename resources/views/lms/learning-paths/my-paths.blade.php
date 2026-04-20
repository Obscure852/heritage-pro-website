@extends('layouts.master')

@section('title')
    My Learning Paths
@endsection

@section('css')
    <style>
        .my-paths-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .my-paths-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .my-paths-body {
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
            color: #3b82f6;
        }

        .section-title i.text-success {
            color: #10b981 !important;
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
            padding: 8px 16px;
            font-size: 13px;
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

        .btn-outline-success {
            border: 1px solid #10b981;
            color: #10b981;
            background: transparent;
        }

        .btn-outline-success:hover {
            background: #10b981;
            color: white;
        }

        /* Path Cards */
        .path-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .path-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .path-card.completed {
            border-color: #10b981;
        }

        .path-thumbnail {
            height: 120px;
            object-fit: cover;
            width: 100%;
        }

        .path-thumbnail-placeholder {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .path-thumbnail-placeholder.active {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .path-thumbnail-placeholder.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .path-thumbnail-placeholder i {
            font-size: 40px;
            color: white;
            opacity: 0.5;
        }

        .path-card-body {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .path-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

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

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        /* Progress */
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
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .path-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .path-card-footer {
            padding: 12px 16px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
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
            .my-paths-header {
                padding: 20px;
            }

            .my-paths-body {
                padding: 16px;
            }

            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
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

    <div class="my-paths-container">
        <div class="my-paths-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-route me-2"></i>My Learning Paths</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track your progress across learning journeys</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $enrollments->count();
                        $activeCount = $activeEnrollments->count();
                        $completedCount = $completedEnrollments->count();
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

        <div class="my-paths-body">
            <div class="help-text">
                <div class="help-title">Your Learning Journey</div>
                <div class="help-content">
                    Track your progress across all enrolled learning paths. Continue where you left off or explore
                    new paths to expand your skills. Complete all subjects in a path to earn certificates and badges.
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <a href="{{ route('lms.learning-paths.index') }}" class="btn btn-primary">
                    <i class="fas fa-compass me-1"></i>Browse Paths
                </a>
            </div>

            <!-- In Progress -->
            @if($activeEnrollments->count())
                <div class="mb-5">
                    <div class="section-title">
                        <i class="fas fa-spinner fa-spin"></i>
                        In Progress ({{ $activeEnrollments->count() }})
                    </div>
                    <div class="row g-4">
                        @foreach($activeEnrollments as $enrollment)
                            <div class="col-lg-4 col-md-6">
                                <div class="path-card">
                                    @if($enrollment->learningPath->thumbnail_url)
                                        <img src="{{ $enrollment->learningPath->thumbnail_url }}" class="path-thumbnail" alt="{{ $enrollment->learningPath->title }}">
                                    @else
                                        <div class="path-thumbnail-placeholder active">
                                            <i class="fas fa-route"></i>
                                        </div>
                                    @endif
                                    <div class="path-card-body">
                                        <div class="mb-2">
                                            <span class="badge badge-{{ $enrollment->learningPath->level }}">{{ $enrollment->learningPath->level_label }}</span>
                                        </div>
                                        <h6 class="path-title">{{ $enrollment->learningPath->title }}</h6>

                                        <div class="progress-wrapper">
                                            <div class="progress-info">
                                                <small>Progress</small>
                                                <span class="percentage">{{ $enrollment->progress_percentage }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                            </div>
                                        </div>

                                        <div class="path-meta">
                                            <span><i class="fas fa-check-circle me-1"></i>{{ $enrollment->courses_completed }}/{{ $enrollment->total_courses }} subjects</span>
                                            <span><i class="fas fa-calendar me-1"></i>{{ $enrollment->enrolled_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="path-card-footer">
                                        <a href="{{ route('lms.learning-paths.learn', $enrollment->learningPath) }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-play me-1"></i>Continue Learning
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Completed -->
            @if($completedEnrollments->count())
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-check-circle text-success"></i>
                        Completed ({{ $completedEnrollments->count() }})
                    </div>
                    <div class="row g-4">
                        @foreach($completedEnrollments as $enrollment)
                            <div class="col-lg-4 col-md-6">
                                <div class="path-card completed">
                                    @if($enrollment->learningPath->thumbnail_url)
                                        <img src="{{ $enrollment->learningPath->thumbnail_url }}" class="path-thumbnail" alt="{{ $enrollment->learningPath->title }}">
                                    @else
                                        <div class="path-thumbnail-placeholder completed">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                    @endif
                                    <div class="path-card-body">
                                        <div class="mb-2">
                                            <span class="badge badge-completed"><i class="fas fa-check me-1"></i>Completed</span>
                                        </div>
                                        <h6 class="path-title">{{ $enrollment->learningPath->title }}</h6>

                                        <div class="path-meta">
                                            <span><i class="fas fa-book me-1"></i>{{ $enrollment->total_courses }} subjects</span>
                                            <span><i class="fas fa-calendar-check me-1"></i>{{ $enrollment->completed_at?->format('M j, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="path-card-footer">
                                        <a href="{{ route('lms.learning-paths.learn', $enrollment->learningPath) }}" class="btn btn-outline-success btn-sm w-100">
                                            <i class="fas fa-eye me-1"></i>View Path
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($enrollments->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-route"></i>
                    <h5>No Learning Paths Yet</h5>
                    <p>Start your learning journey by enrolling in a learning path.</p>
                    <a href="{{ route('lms.learning-paths.index') }}" class="btn btn-primary">
                        <i class="fas fa-compass me-1"></i>Browse Learning Paths
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('layouts.master-student-portal')

@section('title')
    My Learning Paths
@endsection

@section('css')
    <style>
        .portal-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .portal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .portal-header h3 {
            margin: 0 0 6px 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .portal-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .header-stats {
            display: flex;
            gap: 32px;
        }

        .header-stat {
            text-align: center;
        }

        .header-stat .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        .header-stat .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.85;
            margin-top: 4px;
        }

        .portal-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 14px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
        }

        .help-text .help-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .btn-action {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .section-card {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3b82f6;
        }

        .paths-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .path-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .path-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .path-thumbnail {
            height: 100px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            position: relative;
        }

        .path-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .level-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .level-beginner {
            background: #dcfce7;
            color: #166534;
        }

        .level-intermediate {
            background: #fef3c7;
            color: #92400e;
        }

        .level-advanced {
            background: #fee2e2;
            color: #991b1b;
        }

        .level-expert {
            background: #ede9fe;
            color: #5b21b6;
        }

        .path-body {
            padding: 16px;
        }

        .path-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .progress-section {
            margin-bottom: 12px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .progress-bar-container {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
            border-radius: 3px;
        }

        .path-meta {
            display: flex;
            gap: 12px;
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .path-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-continue {
            width: 100%;
            padding: 8px 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-review {
            width: 100%;
            padding: 8px 14px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-review:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 12px;
        }

        .empty-state h5 {
            color: #374151;
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .portal-header {
                padding: 20px;
            }

            .header-stats {
                gap: 20px;
                flex-wrap: wrap;
            }

            .header-stat .stat-value {
                font-size: 1.5rem;
            }

            .paths-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            LMS
        @endslot
        @slot('title')
            My Learning Paths
        @endslot
    @endcomponent

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3><i class="fas fa-bookmark me-2"></i>My Learning Paths</h3>
                    <p>Track your progress through structured learning journeys</p>
                </div>
                <div class="col-lg-6 mt-lg-0 mt-3">
                    <div class="header-stats justify-content-lg-end">
                        <div class="header-stat">
                            <div class="stat-value">{{ $enrollments->count() }}</div>
                            <div class="stat-label">Total</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value">{{ $inProgress->count() }}</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value">{{ $completed->count() }}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portal-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- My Learning Paths Section -->
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div class="help-text flex-grow-1">
                    <div class="help-title">My Learning Paths</div>
                    <div class="help-content">
                        View and continue your enrolled learning paths. Track your progress through each structured journey.
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <a href="{{ route('student.lms.learning-paths') }}" class="btn-action flex-shrink-0">
                        <i class="fas fa-search"></i> Browse Paths
                    </a>
                </div>
            </div>

            @if ($enrollments->isEmpty())
                <div class="section-card">
                    <div class="empty-state">
                        <i class="fas fa-route"></i>
                        <h5>No Learning Paths Yet</h5>
                        <p>You haven't enrolled in any learning paths. Browse available paths to get started.</p>
                    </div>
                </div>
            @else
                <!-- In Progress Section -->
                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-play-circle"></i> In Progress
                        </h5>
                        <span class="badge bg-primary">{{ $inProgress->count() }}</span>
                    </div>

                    @if ($inProgress->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-hourglass-start"></i>
                            <h5>No Paths In Progress</h5>
                            <p>Start a learning path to see it here.</p>
                        </div>
                    @else
                        <div class="paths-grid">
                            @foreach ($inProgress as $enrollment)
                                @php
                                    $path = $enrollment->learningPath;
                                    $totalCourses = $path->pathCourses->count();
                                    $completedCourses = $enrollment->courses_completed ?? 0;
                                    $progress = $enrollment->progress_percentage ?? 0;
                                @endphp
                                <div class="path-card">
                                    <div class="path-thumbnail">
                                        @if ($path->thumbnail)
                                            <img src="{{ Storage::url($path->thumbnail) }}" alt="{{ $path->title }}">
                                        @else
                                            <i class="fas fa-route"></i>
                                        @endif
                                        @if ($path->level)
                                            <span class="level-badge level-{{ $path->level }}">{{ $path->level }}</span>
                                        @endif
                                    </div>
                                    <div class="path-body">
                                        <h5 class="path-title">{{ $path->title }}</h5>

                                        <div class="progress-section">
                                            <div class="progress-label">
                                                <span>Progress</span>
                                                <span>{{ $completedCourses }}/{{ $totalCourses }} courses</span>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>

                                        <div class="path-meta">
                                            <span><i class="fas fa-book"></i> {{ $totalCourses }} Courses</span>
                                            <span><i class="fas fa-calendar"></i>
                                                {{ $enrollment->enrolled_at->diffForHumans() }}</span>
                                        </div>

                                        <a href="{{ route('student.lms.learning-path.learn', $path) }}"
                                            class="btn-continue">
                                            <i class="fas fa-play"></i> Continue
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Completed Section -->
                <div class="section-card mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-trophy"></i> Completed
                        </h5>
                        <span class="badge bg-success">{{ $completed->count() }}</span>
                    </div>

                    @if ($completed->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-trophy"></i>
                            <h5>No Completed Paths</h5>
                            <p>Complete a learning path to see it here.</p>
                        </div>
                    @else
                        <div class="paths-grid">
                            @foreach ($completed as $enrollment)
                                @php
                                    $path = $enrollment->learningPath;
                                @endphp
                                <div class="path-card">
                                    <div class="path-thumbnail">
                                        @if ($path->thumbnail)
                                            <img src="{{ Storage::url($path->thumbnail) }}" alt="{{ $path->title }}">
                                        @else
                                            <i class="fas fa-route"></i>
                                        @endif
                                        @if ($path->level)
                                            <span class="level-badge level-{{ $path->level }}">{{ $path->level }}</span>
                                        @endif
                                    </div>
                                    <div class="path-body">
                                        <h5 class="path-title">{{ $path->title }}</h5>

                                        <div class="path-meta">
                                            <span><i class="fas fa-check-circle text-success"></i> Completed</span>
                                            @if ($enrollment->completed_at)
                                                <span><i class="fas fa-calendar"></i>
                                                    {{ $enrollment->completed_at->format('M d, Y') }}</span>
                                            @endif
                                        </div>

                                        <a href="{{ route('student.lms.learning-path.learn', $path) }}" class="btn-review">
                                            <i class="fas fa-redo"></i> Review
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

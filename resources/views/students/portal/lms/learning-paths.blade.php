@extends('layouts.master-student-portal')

@section('title')
    Browse Learning Paths
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
            height: 120px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            position: relative;
        }

        .path-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .enrolled-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #10b981;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .level-badge {
            position: absolute;
            bottom: 8px;
            left: 8px;
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
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .path-description {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }

        .path-meta {
            display: flex;
            gap: 12px;
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .path-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .path-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 12px;
        }

        .category-tag {
            padding: 2px 8px;
            background: #f3f4f6;
            color: #4b5563;
            border-radius: 3px;
            font-size: 0.7rem;
        }

        .path-actions {
            display: flex;
            gap: 8px;
        }

        .btn-view {
            flex: 1;
            padding: 8px 12px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-view:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-enroll {
            flex: 1;
            padding: 8px 12px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-enroll:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        .btn-continue {
            flex: 1;
            padding: 8px 12px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            background: #f9fafb;
            border-radius: 3px;
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
            Browse Learning Paths
        @endslot
    @endcomponent

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3><i class="fas fa-route me-2"></i>Browse Learning Paths</h3>
                    <p>Follow structured learning journeys to master new skills</p>
                </div>
                <div class="col-lg-6 mt-lg-0 mt-3">
                    <div class="header-stats justify-content-lg-end">
                        <div class="header-stat">
                            <div class="stat-value">{{ $learningPaths->total() }}</div>
                            <div class="stat-label">Available</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value">{{ count($enrolledPathIds) }}</div>
                            <div class="stat-label">Enrolled</div>
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

            <!-- Browse Paths Section -->
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div class="help-text flex-grow-1">
                    <div class="help-title">Browse Learning Paths</div>
                    <div class="help-content">
                        Explore available learning paths and enroll to start your structured learning journey.
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <a href="{{ route('student.lms.my-learning-paths') }}" class="btn-action flex-shrink-0">
                        <i class="fas fa-bookmark"></i> My Paths
                    </a>
                </div>
            </div>

            @if ($learningPaths->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-route"></i>
                    <h5>No Learning Paths Available</h5>
                    <p>Check back later for new learning paths.</p>
                </div>
            @else
                <div class="paths-grid">
                    @foreach ($learningPaths as $path)
                        @php
                            $isEnrolled = in_array($path->id, $enrolledPathIds);
                            $coursesCount = $path->pathCourses->count();
                        @endphp
                        <div class="path-card">
                            <div class="path-thumbnail">
                                @if ($path->thumbnail)
                                    <img src="{{ Storage::url($path->thumbnail) }}" alt="{{ $path->title }}">
                                @else
                                    <i class="fas fa-route"></i>
                                @endif
                                @if ($isEnrolled)
                                    <span class="enrolled-badge"><i class="fas fa-check me-1"></i>Enrolled</span>
                                @endif
                                @if ($path->level)
                                    <span class="level-badge level-{{ $path->level }}">{{ $path->level }}</span>
                                @endif
                            </div>
                            <div class="path-body">
                                <h5 class="path-title">{{ $path->title }}</h5>
                                <p class="path-description">{{ $path->description }}</p>

                                <div class="path-meta">
                                    <span><i class="fas fa-book"></i> {{ $coursesCount }} Courses</span>
                                    @if ($path->estimated_duration)
                                        <span><i class="fas fa-clock"></i> {{ $path->estimated_duration }}</span>
                                    @endif
                                </div>

                                @if ($path->categories->isNotEmpty())
                                    <div class="path-categories">
                                        @foreach ($path->categories->take(3) as $category)
                                            <span class="category-tag">{{ $category->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="path-actions">
                                    <a href="{{ route('student.lms.learning-path', $path) }}" class="btn-view">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    @if ($isEnrolled)
                                        <a href="{{ route('student.lms.learning-path.learn', $path) }}"
                                            class="btn-continue">
                                            <i class="fas fa-play me-1"></i>Continue
                                        </a>
                                    @else
                                        <form action="{{ route('student.lms.learning-path.enroll', $path) }}"
                                            method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn-enroll" style="width: 100%;">
                                                <i class="fas fa-plus me-1"></i>Enroll
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $learningPaths->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

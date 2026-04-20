@extends('layouts.master-student-portal')

@section('title')
    My Courses
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 24px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .page-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .course-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .course-thumbnail {
            height: 160px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }

        .course-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-body {
            padding: 20px;
        }

        .course-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .course-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .progress-section {
            margin-bottom: 16px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .progress-bar-container {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
            border-radius: 4px;
            transition: width 0.3s;
        }

        .course-actions {
            display: flex;
            gap: 10px;
        }

        .btn-continue {
            flex: 1;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #f59e0b;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f9fafb;
            border-radius: 3px;
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

        .btn-browse {
            padding: 10px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-browse:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        .tabs-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: none;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #374151;
        }

        .tab-btn.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .courses-grid {
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
            My Courses
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4><i class="fas fa-graduation-cap me-2"></i>My Courses</h4>
                <p>Track your enrolled courses and continue learning</p>
            </div>
            <div class="col-md-6">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-item">
                            <h4 class="text-white">{{ $enrollments->count() }}</h4>
                            <small>Total</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h4 class="text-white">{{ $activeCourses->count() }}</h4>
                            <small>In Progress</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h4 class="text-white">{{ $completedCourses->count() }}</h4>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($enrollments->isEmpty())
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h5>No Courses Yet</h5>
            <p>You haven't enrolled in any courses yet. Browse available courses to get started.</p>
            <a href="{{ route('student.lms.courses') }}" class="btn-browse">
                <i class="fas fa-search"></i> Browse Courses
            </a>
        </div>
    @else
        <div class="tabs-nav">
            <button class="tab-btn active" data-tab="active">
                <i class="fas fa-play-circle me-2"></i>In Progress ({{ $activeCourses->count() }})
            </button>
            <button class="tab-btn" data-tab="completed">
                <i class="fas fa-check-circle me-2"></i>Completed ({{ $completedCourses->count() }})
            </button>
        </div>

        <div class="tab-content active" id="tab-active">
            @if($activeCourses->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-hourglass-start"></i>
                    <h5>No Active Courses</h5>
                    <p>You don't have any courses in progress.</p>
                </div>
            @else
                <div class="courses-grid">
                    @foreach($activeCourses as $enrollment)
                        @php
                            $course = $enrollment->course;
                            $progress = $enrollment->progress_percentage ?? 0;
                        @endphp
                        <div class="course-card">
                            <div class="course-thumbnail">
                                @if($course->thumbnail)
                                    <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}">
                                @else
                                    <i class="fas fa-book"></i>
                                @endif
                            </div>
                            <div class="course-body">
                                <h5 class="course-title">{{ $course->title }}</h5>
                                <div class="course-meta">
                                    @if($course->modules_count ?? $course->modules->count())
                                        <span><i class="fas fa-layer-group"></i> {{ $course->modules_count ?? $course->modules->count() }} Modules</span>
                                    @endif
                                    @if($course->instructor)
                                        <span><i class="fas fa-user"></i> {{ $course->instructor->name }}</span>
                                    @endif
                                </div>
                                <div class="progress-section">
                                    <div class="progress-label">
                                        <span>Progress</span>
                                        <span>{{ number_format($progress, 0) }}%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                <div class="course-actions">
                                    <a href="{{ route('student.lms.learn', $course) }}" class="btn-continue">
                                        <i class="fas fa-play me-2"></i>Continue Learning
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="tab-content" id="tab-completed">
            @if($completedCourses->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <h5>No Completed Courses</h5>
                    <p>Complete your enrolled courses to see them here.</p>
                </div>
            @else
                <div class="courses-grid">
                    @foreach($completedCourses as $enrollment)
                        @php
                            $course = $enrollment->course;
                        @endphp
                        <div class="course-card">
                            <div class="course-thumbnail">
                                @if($course->thumbnail)
                                    <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}">
                                @else
                                    <i class="fas fa-book"></i>
                                @endif
                            </div>
                            <div class="course-body">
                                <h5 class="course-title">{{ $course->title }}</h5>
                                <div class="course-meta">
                                    <span><i class="fas fa-check-circle text-success"></i> Completed</span>
                                    @if($enrollment->completed_at)
                                        <span><i class="fas fa-calendar"></i> {{ $enrollment->completed_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                                <div class="course-actions">
                                    <a href="{{ route('student.lms.learn', $course) }}" class="btn-continue">
                                        <i class="fas fa-redo me-2"></i>Review Course
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endsection

@section('script')
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active from all tabs
                document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // Add active to clicked tab
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).classList.add('active');
            });
        });
    </script>
@endsection

@extends('layouts.master-student-portal')

@section('title')
    {{ $learningPath->title }}
@endsection

@section('css')
    <style>
        .portal-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .portal-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
            max-width: 600px;
        }

        .header-meta {
            display: flex;
            gap: 20px;
            margin-top: 16px;
            font-size: 0.9rem;
        }

        .header-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0.9;
        }

        .portal-body {
            padding: 24px;
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

        .course-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .course-list-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
            gap: 12px;
        }

        .course-list-item:last-child {
            margin-bottom: 0;
        }

        .course-number {
            width: 28px;
            height: 28px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .course-info {
            flex: 1;
        }

        .course-info h6 {
            margin: 0 0 4px 0;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .course-info .course-meta {
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            gap: 12px;
        }

        .course-status {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 3px;
        }

        .course-status.not-started {
            background: #f3f4f6;
            color: #6b7280;
        }

        .course-status.in-progress {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .course-status.completed {
            background: #d1fae5;
            color: #047857;
        }

        .objectives-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .objectives-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .objectives-list li:last-child {
            border-bottom: none;
        }

        .objectives-list li i {
            color: #10b981;
            margin-top: 3px;
        }

        .sidebar-card {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .sidebar-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-title i {
            color: #3b82f6;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list .label {
            color: #6b7280;
        }

        .info-list .value {
            color: #1f2937;
            font-weight: 500;
        }

        .btn-enroll {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 3px;
            font-size: 0.95rem;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-enroll:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-continue {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 3px;
            font-size: 0.95rem;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .categories-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .category-tag {
            background: #e0e7ff;
            color: #4338ca;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 3px;
        }

        .level-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: white;
            color: #1f2937;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 3px;
            margin-top: 12px;
        }

        .level-badge i {
            color: #f59e0b;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('student.lms.learning-paths') }}">Learning Paths</a>
        @endslot
        @slot('title')
            {{ $learningPath->title }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="portal-container">
                <div class="portal-header">
                    <h3>{{ $learningPath->title }}</h3>
                    <p>{{ $learningPath->description }}</p>
                    <div class="header-meta">
                        <span><i class="mdi mdi-book-multiple"></i> {{ $learningPath->pathCourses->count() }} Courses</span>
                        @if($learningPath->estimated_duration_hours)
                            <span><i class="mdi mdi-clock-outline"></i> {{ $learningPath->estimated_duration_hours }} Hours</span>
                        @endif
                        @if($learningPath->creator)
                            <span><i class="mdi mdi-account"></i> {{ $learningPath->creator->name }}</span>
                        @endif
                    </div>
                    @if($learningPath->level)
                        <div class="level-badge">
                            <i class="mdi mdi-signal"></i> {{ ucfirst($learningPath->level) }} Level
                        </div>
                    @endif
                </div>

                <div class="portal-body">
                    @if($learningPath->objectives && count($learningPath->objectives) > 0)
                        <!-- Learning Objectives -->
                        <div class="section-card">
                            <h5 class="section-title">
                                <i class="mdi mdi-target"></i> What You'll Learn
                            </h5>
                            <ul class="objectives-list">
                                @foreach($learningPath->objectives as $objective)
                                    <li>
                                        <i class="mdi mdi-check-circle"></i>
                                        {{ $objective }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Courses in this Path -->
                    <div class="section-card">
                        <h5 class="section-title">
                            <i class="mdi mdi-book-open-page-variant"></i> Courses in this Path
                        </h5>
                        <ul class="course-list">
                            @foreach($learningPath->pathCourses->sortBy('position') as $pathCourse)
                                @php
                                    $course = $pathCourse->course;
                                    $status = $courseProgress[$course->id] ?? 'not_started';
                                @endphp
                                <li class="course-list-item">
                                    <span class="course-number">{{ $pathCourse->position }}</span>
                                    <div class="course-info">
                                        <h6>{{ $course->title }}</h6>
                                        <div class="course-meta">
                                            <span><i class="mdi mdi-book-outline"></i> {{ $course->modules->count() }} Modules</span>
                                            @if($pathCourse->is_required)
                                                <span><i class="mdi mdi-asterisk"></i> Required</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="course-status {{ $status }}">
                                        @if($status === 'completed')
                                            <i class="mdi mdi-check"></i> Completed
                                        @elseif($status === 'active')
                                            In Progress
                                        @else
                                            Not Started
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Enrollment Card -->
            <div class="sidebar-card">
                @if($enrollment)
                    <h5 class="sidebar-title">
                        <i class="mdi mdi-check-circle text-success"></i> You're Enrolled
                    </h5>
                    <p class="text-muted small mb-3">Continue your learning journey</p>
                    <a href="{{ route('student.lms.learning-path.learn', $learningPath) }}" class="btn-continue">
                        <i class="mdi mdi-play"></i> Continue Learning
                    </a>
                @else
                    <h5 class="sidebar-title">
                        <i class="mdi mdi-school"></i> Start Learning
                    </h5>
                    <p class="text-muted small mb-3">Enroll to begin this learning path</p>
                    <form action="{{ route('student.lms.learning-path.enroll', $learningPath) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-enroll">
                            <i class="mdi mdi-plus"></i> Enroll Now
                        </button>
                    </form>
                @endif
            </div>

            <!-- Path Info -->
            <div class="sidebar-card">
                <h5 class="sidebar-title">
                    <i class="mdi mdi-information"></i> Path Details
                </h5>
                <ul class="info-list">
                    <li>
                        <span class="label">Courses</span>
                        <span class="value">{{ $learningPath->pathCourses->count() }}</span>
                    </li>
                    <li>
                        <span class="label">Level</span>
                        <span class="value">{{ ucfirst($learningPath->level ?? 'All Levels') }}</span>
                    </li>
                    @if($learningPath->estimated_duration_hours)
                        <li>
                            <span class="label">Duration</span>
                            <span class="value">{{ $learningPath->estimated_duration_hours }} hours</span>
                        </li>
                    @endif
                    <li>
                        <span class="label">Sequence</span>
                        <span class="value">{{ $learningPath->enforce_sequence ? 'Required' : 'Flexible' }}</span>
                    </li>
                    @if($learningPath->published_at)
                        <li>
                            <span class="label">Published</span>
                            <span class="value">{{ $learningPath->published_at->format('M d, Y') }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            @if($learningPath->categories && $learningPath->categories->count() > 0)
                <!-- Categories -->
                <div class="sidebar-card">
                    <h5 class="sidebar-title">
                        <i class="mdi mdi-tag-multiple"></i> Categories
                    </h5>
                    <div class="categories-list">
                        @foreach($learningPath->categories as $category)
                            <span class="category-tag">{{ $category->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Back Button -->
            <div class="sidebar-card">
                <a href="{{ route('student.lms.learning-paths') }}" class="btn btn-outline-secondary w-100">
                    <i class="mdi mdi-arrow-left"></i> Browse All Paths
                </a>
            </div>
        </div>
    </div>
@endsection

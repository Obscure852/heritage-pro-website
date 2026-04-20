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
        }

        .header-stats {
            display: flex;
            gap: 32px;
            margin-top: 16px;
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

        .progress-overview {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 3px;
            height: 12px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .courses-timeline {
            position: relative;
            padding-left: 40px;
        }

        .courses-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .course-item {
            position: relative;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .course-item:hover:not(.locked) {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .course-item.locked {
            opacity: 0.7;
        }

        .course-item.locked .course-content {
            background: #f9fafb;
        }

        .course-item.completed .course-status-marker {
            background: #10b981;
        }

        .course-item.in-progress .course-status-marker {
            background: #3b82f6;
        }

        .course-item.available .course-status-marker {
            background: #f59e0b;
        }

        .course-item.locked .course-status-marker {
            background: #9ca3af;
        }

        .course-status-marker {
            position: absolute;
            left: -33px;
            top: 20px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            z-index: 1;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .course-content {
            padding: 16px 20px;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .course-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .course-position {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 3px;
        }

        .course-meta {
            display: flex;
            gap: 16px;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .course-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .course-progress-bar {
            background: #e5e7eb;
            border-radius: 3px;
            height: 6px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .course-progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .course-progress-fill.completed {
            background: #10b981;
        }

        .course-progress-fill.in-progress {
            background: #3b82f6;
        }

        .course-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .course-status-badge.completed {
            background: #d1fae5;
            color: #047857;
        }

        .course-status-badge.in-progress {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .course-status-badge.available {
            background: #fef3c7;
            color: #b45309;
        }

        .course-status-badge.locked {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-course {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-course.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .btn-course.primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-course.secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-course.secondary:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-course.disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
            pointer-events: none;
        }

        .locked-message {
            font-size: 0.8rem;
            color: #9ca3af;
            font-style: italic;
        }

        .milestone-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 3px;
            margin-left: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #9ca3af;
            font-size: 0.9rem;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('student.lms.my-learning-paths') }}">My Learning Paths</a>
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
                    <div class="header-stats">
                        <div class="header-stat">
                            <div class="stat-value">{{ $completedCourses }}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value">{{ $totalCourses }}</div>
                            <div class="stat-label">Total Courses</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value">{{ round($enrollment->progress_percentage ?? 0) }}%</div>
                            <div class="stat-label">Progress</div>
                        </div>
                    </div>
                </div>

                <div class="portal-body">
                    <!-- Progress Overview -->
                    <div class="progress-overview">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                        </div>
                        <div class="progress-text">
                            <span>{{ $completedCourses }} of {{ $totalCourses }} courses completed</span>
                            <span>{{ round($enrollment->progress_percentage ?? 0) }}% Complete</span>
                        </div>
                    </div>

                    <!-- Courses Timeline -->
                    <div class="courses-timeline">
                        @forelse($learningPath->pathCourses->sortBy('position') as $pathCourse)
                            @php
                                $course = $pathCourse->course;
                                $progress = $pathProgress[$course->id] ?? null;
                                $courseEnrollment = $courseEnrollments[$course->id] ?? null;

                                $status = $progress ? $progress->status : 'available';
                                $isLocked = $status === 'locked';
                                $isCompleted = $status === 'completed';
                                $isInProgress = $status === 'in_progress';
                                $isAvailable = $status === 'available';

                                $progressPercent = $progress->progress_percentage ?? ($courseEnrollment->progress_percentage ?? 0);
                            @endphp

                            <div class="course-item {{ $status }}">
                                <div class="course-status-marker">
                                    @if($isCompleted)
                                        <i class="mdi mdi-check"></i>
                                    @elseif($isInProgress)
                                        <i class="mdi mdi-play"></i>
                                    @elseif($isAvailable)
                                        <i class="mdi mdi-lock-open"></i>
                                    @else
                                        <i class="mdi mdi-lock"></i>
                                    @endif
                                </div>

                                <div class="course-content">
                                    <div class="course-header">
                                        <h4 class="course-title">
                                            {{ $course->title }}
                                            @if($pathCourse->is_milestone)
                                                <span class="milestone-badge">
                                                    <i class="mdi mdi-flag"></i> {{ $pathCourse->milestone_title ?? 'Milestone' }}
                                                </span>
                                            @endif
                                        </h4>
                                        <span class="course-position">Course {{ $pathCourse->position }}</span>
                                    </div>

                                    <div class="course-meta">
                                        <span><i class="mdi mdi-book-outline"></i> {{ $course->modules->count() }} Modules</span>
                                        <span><i class="mdi mdi-file-document-outline"></i> {{ $course->modules->sum(fn($m) => $m->contentItems->count()) }} Items</span>
                                        @if($course->estimated_duration_hours)
                                            <span><i class="mdi mdi-clock-outline"></i> {{ $course->estimated_duration_hours }}h</span>
                                        @endif
                                    </div>

                                    @if(!$isLocked)
                                        <div class="course-progress-bar">
                                            <div class="course-progress-fill {{ $isCompleted ? 'completed' : 'in-progress' }}"
                                                 style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                    @endif

                                    <div class="course-actions">
                                        <span class="course-status-badge {{ $status }}">
                                            @if($isCompleted)
                                                <i class="mdi mdi-check-circle"></i> Completed
                                            @elseif($isInProgress)
                                                <i class="mdi mdi-play-circle"></i> In Progress ({{ round($progressPercent) }}%)
                                            @elseif($isAvailable)
                                                <i class="mdi mdi-lock-open"></i> Ready to Start
                                            @else
                                                <i class="mdi mdi-lock"></i> Locked
                                            @endif
                                        </span>

                                        @if($isLocked)
                                            <span class="locked-message">
                                                <i class="mdi mdi-information"></i> Complete previous courses to unlock
                                            </span>
                                        @elseif($isCompleted)
                                            <a href="{{ route('student.lms.learn', $course) }}" class="btn-course secondary">
                                                <i class="mdi mdi-eye"></i> Review
                                            </a>
                                        @elseif($isInProgress)
                                            <a href="{{ route('student.lms.learn', $course) }}" class="btn-course primary">
                                                <i class="mdi mdi-play"></i> Continue
                                            </a>
                                        @else
                                            @if($courseEnrollment)
                                                <a href="{{ route('student.lms.learn', $course) }}" class="btn-course primary">
                                                    <i class="mdi mdi-play"></i> Start Course
                                                </a>
                                            @else
                                                <form action="{{ route('student.lms.enroll', $course) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-course primary">
                                                        <i class="mdi mdi-plus"></i> Enroll & Start
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="mdi mdi-book-open-page-variant"></i>
                                <h5>No Courses</h5>
                                <p>This learning path doesn't have any courses yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Path Info -->
            <div class="sidebar-card">
                <h5 class="sidebar-title">
                    <i class="mdi mdi-information"></i> Path Information
                </h5>
                <ul class="info-list">
                    <li>
                        <span class="label">Level</span>
                        <span class="value">{{ ucfirst($learningPath->level ?? 'All Levels') }}</span>
                    </li>
                    <li>
                        <span class="label">Enrolled</span>
                        <span class="value">{{ $enrollment->enrolled_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </li>
                    @if($enrollment->started_at)
                        <li>
                            <span class="label">Started</span>
                            <span class="value">{{ $enrollment->started_at->format('M d, Y') }}</span>
                        </li>
                    @endif
                    @if($learningPath->estimated_duration_hours)
                        <li>
                            <span class="label">Est. Duration</span>
                            <span class="value">{{ $learningPath->estimated_duration_hours }} hours</span>
                        </li>
                    @endif
                    <li>
                        <span class="label">Sequence</span>
                        <span class="value">{{ $enforceSequence ? 'Required' : 'Flexible' }}</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="sidebar-card">
                <h5 class="sidebar-title">
                    <i class="mdi mdi-lightning-bolt"></i> Quick Actions
                </h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('student.lms.my-learning-paths') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back to My Paths
                    </a>
                    <a href="{{ route('student.lms.calendar') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-calendar"></i> View Calendar
                    </a>
                </div>
            </div>

            @if($learningPath->objectives && count($learningPath->objectives) > 0)
                <!-- Learning Objectives -->
                <div class="sidebar-card">
                    <h5 class="sidebar-title">
                        <i class="mdi mdi-target"></i> Learning Objectives
                    </h5>
                    <ul class="ps-3 mb-0" style="font-size: 0.9rem; color: #6b7280;">
                        @foreach($learningPath->objectives as $objective)
                            <li class="mb-2">{{ $objective }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

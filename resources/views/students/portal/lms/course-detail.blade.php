@extends('layouts.master-student-portal')

@section('title')
    {{ $course->title }}
@endsection

@section('css')
    <style>
        .course-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .course-header h3 {
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .course-header p {
            margin: 0;
            opacity: 0.9;
        }

        .course-meta-header {
            display: flex;
            gap: 20px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .course-meta-header span {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            opacity: 0.9;
        }

        .course-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
        }

        .course-card h5 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .course-card h5 i {
            color: #3b82f6;
        }

        .course-description {
            color: #4b5563;
            line-height: 1.7;
        }

        .module-list {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .module-item {
            border-bottom: 1px solid #e5e7eb;
        }

        .module-item:last-child {
            border-bottom: none;
        }

        .module-header {
            background: #f9fafb;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .module-header:hover {
            background: #f3f4f6;
        }

        .module-title {
            font-weight: 600;
            color: #374151;
        }

        .module-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .content-list {
            padding: 0;
            display: none;
        }

        .content-list.show {
            display: block;
        }

        .content-item {
            padding: 12px 16px 12px 40px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4b5563;
            font-size: 14px;
        }

        .content-item i {
            color: #9ca3af;
            width: 16px;
        }

        .content-duration {
            margin-left: auto;
            font-size: 12px;
            color: #9ca3af;
        }

        .btn-enroll {
            padding: 12px 24px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-enroll:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .btn-continue {
            padding: 12px 24px;
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

        .btn-continue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .enrolled-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .stat-box {
            text-align: center;
            padding: 16px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .stat-box .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #374151;
        }

        .stat-box .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .instructor-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
        }

        .instructor-name {
            font-weight: 600;
            color: #374151;
        }

        .instructor-role {
            font-size: 13px;
            color: #6b7280;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            LMS
        @endslot
        @slot('li_2')
            <a href="{{ route('student.lms.courses') }}">Browse Courses</a>
        @endslot
        @slot('title')
            {{ $course->title }}
        @endslot
    @endcomponent

    <div class="course-header">
        <h3>{{ $course->title }}</h3>
        <p>{{ Str::limit($course->description, 150) }}</p>
        <div class="course-meta-header">
            @if($course->grade)
                <span><i class="fas fa-layer-group"></i> {{ $course->grade->name }}</span>
            @endif
            @if($course->instructor)
                <span><i class="fas fa-user"></i> {{ $course->instructor->name }}</span>
            @endif
            <span><i class="fas fa-book"></i> {{ $course->modules->count() }} Modules</span>
            <span><i class="fas fa-file-alt"></i> {{ $course->modules->sum(fn($m) => $m->contentItems->count()) }} Lessons</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Description -->
            <div class="course-card">
                <h5><i class="fas fa-info-circle"></i> About this Course</h5>
                <div class="course-description">
                    {!! nl2br(e($course->description)) !!}
                </div>
                @if($course->learning_objectives && count($course->learning_objectives) > 0)
                    <h6 class="mt-4 mb-3">What you'll learn:</h6>
                    <ul>
                        @foreach($course->learning_objectives as $objective)
                            <li>{{ $objective }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Course Content -->
            <div class="course-card">
                <h5><i class="fas fa-list"></i> Course Content</h5>
                <div class="module-list">
                    @forelse($course->modules as $module)
                        <div class="module-item">
                            <div class="module-header" onclick="this.nextElementSibling.classList.toggle('show')">
                                <div>
                                    <div class="module-title">{{ $module->title }}</div>
                                    <div class="module-meta">{{ $module->contentItems->count() }} lessons</div>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="content-list">
                                @foreach($module->contentItems as $content)
                                    <div class="content-item">
                                        @if($content->type === 'video_youtube' || $content->type === 'video_upload')
                                            <i class="fas fa-play-circle"></i>
                                        @elseif($content->type === 'document')
                                            <i class="fas fa-file-alt"></i>
                                        @elseif($content->type === 'quiz')
                                            <i class="fas fa-question-circle"></i>
                                        @elseif($content->type === 'scorm')
                                            <i class="fas fa-cube"></i>
                                        @else
                                            <i class="fas fa-book"></i>
                                        @endif
                                        <span>{{ $content->title }}</span>
                                        @if($content->duration_minutes)
                                            <span class="content-duration">{{ $content->duration_minutes }} min</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            No content available yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Enrollment Card -->
            <div class="course-card">
                @if($enrollment)
                    <div class="text-center mb-3">
                        <span class="enrolled-badge">
                            <i class="fas fa-check-circle"></i> Enrolled
                        </span>
                    </div>
                    <a href="{{ route('student.lms.learn', $course) }}" class="btn-continue w-100 justify-content-center">
                        <i class="fas fa-play"></i>
                        {{ $enrollment->status === 'completed' ? 'Review Course' : 'Continue Learning' }}
                    </a>
                    @if($enrollment->progress_percentage > 0)
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progress</small>
                                <small class="text-muted">{{ $enrollment->progress_percentage }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $enrollment->progress_percentage }}%"></div>
                            </div>
                        </div>
                    @endif
                @elseif($course->self_enrollment)
                    <form action="{{ route('student.lms.enroll', $course) }}" method="POST">
                        @csrf
                        @if($course->enrollment_key)
                            <div class="mb-3">
                                <label class="form-label">Enrollment Key</label>
                                <input type="text" name="enrollment_key" class="form-control" placeholder="Enter enrollment key" required>
                            </div>
                        @endif
                        <button type="submit" class="btn-enroll w-100">
                            <i class="fas fa-plus me-2"></i>Enroll in Course
                        </button>
                    </form>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-lock mb-2" style="font-size: 24px;"></i>
                        <p class="mb-0">Contact your instructor to enroll in this course.</p>
                    </div>
                @endif
            </div>

            <!-- Stats -->
            <div class="course-card">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-value">{{ $course->modules->count() }}</div>
                            <div class="stat-label">Modules</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-value">{{ $course->modules->sum(fn($m) => $m->contentItems->count()) }}</div>
                            <div class="stat-label">Lessons</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor -->
            @if($course->instructor)
                <div class="course-card">
                    <h5><i class="fas fa-chalkboard-teacher"></i> Instructor</h5>
                    <div class="instructor-info">
                        <div class="instructor-avatar">
                            {{ strtoupper(substr($course->instructor->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="instructor-name">{{ $course->instructor->name }}</div>
                            <div class="instructor-role">Course Instructor</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

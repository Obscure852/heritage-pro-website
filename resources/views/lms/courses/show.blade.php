@extends('layouts.master')

@section('title')
    {{ $course->title }}
@endsection

@section('css')
    <style>
        .course-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .course-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 3px 3px 0 0;
        }

        .course-body {
            padding: 24px;
        }

        /* Header Stats */
        .stat-item {
            padding: 6px 16px;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .stat-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        /* Helper Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #6366f1;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:not(:first-of-type) {
            margin-top: 32px;
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-draft { background: rgba(254, 243, 199, 0.9); color: #92400e; }
        .status-published { background: rgba(209, 250, 229, 0.9); color: #065f46; }
        .status-archived { background: rgba(229, 231, 235, 0.9); color: #374151; }

        /* Module Accordion */
        .module-accordion .accordion-item {
            border: 1px solid #e5e7eb;
            margin-bottom: 8px;
            border-radius: 3px !important;
            overflow: hidden;
        }

        .module-accordion .accordion-button {
            padding: 16px 20px;
            font-weight: 600;
            color: #1f2937;
            background: #f9fafb;
            border-radius: 0 !important;
        }

        .module-accordion .accordion-button:not(.collapsed) {
            background: #eef2ff;
            color: #4f46e5;
            box-shadow: none;
        }

        .module-accordion .accordion-button:focus {
            box-shadow: none;
        }

        .module-accordion .accordion-body {
            padding: 0;
        }

        /* Content List */
        .content-list-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .content-list-item:hover {
            background: #f9fafb;
        }

        .content-list-item:last-child {
            border-bottom: none;
        }

        .content-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 16px;
        }

        .content-icon.video { background: #fee2e2; color: #dc2626; }
        .content-icon.document { background: #dbeafe; color: #2563eb; }
        .content-icon.quiz { background: #fef3c7; color: #d97706; }
        .content-icon.text { background: #d1fae5; color: #059669; }
        .content-icon.audio { background: #fce7f3; color: #db2777; }
        .content-icon.image { background: #e0e7ff; color: #4f46e5; }
        .content-icon.link { background: #cffafe; color: #0891b2; }

        .content-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .content-info span {
            font-size: 13px;
            color: #6b7280;
        }

        /* Course Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .info-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
            background: #fff;
            color: #6366f1;
            border: 1px solid #e5e7eb;
        }

        .info-item-content small {
            color: #6b7280;
            font-size: 12px;
            display: block;
        }

        .info-item-content strong {
            color: #1f2937;
            font-size: 14px;
        }

        /* Objective List */
        .objective-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .objective-list li {
            padding: 10px 0;
            padding-left: 28px;
            position: relative;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }

        .objective-list li:before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #10b981;
        }

        .objective-list li:last-child {
            border-bottom: none;
        }

        /* Enrollment List */
        .enrollment-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .enrollment-item:last-child {
            border-bottom: none;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            font-size: 14px;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
        }

        .progress-bar {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        /* Quick Links */
        .quick-link {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            margin-bottom: 12px;
        }

        .quick-link:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
            color: inherit;
        }

        .quick-link-icon {
            width: 44px;
            height: 44px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 18px;
        }

        .quick-link-icon.blue { background: #dbeafe; color: #2563eb; }
        .quick-link-icon.green { background: #d1fae5; color: #059669; }
        .quick-link-icon.purple { background: #ede9fe; color: #7c3aed; }
        .quick-link-icon.orange { background: #ffedd5; color: #ea580c; }

        .quick-link-content h6 {
            margin: 0 0 2px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .quick-link-content p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        /* Two Column Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            {{ $course->title }}
        @endslot
    @endcomponent

    <div class="course-container">
        <div class="course-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="status-badge status-{{ $course->status }}">{{ ucfirst($course->status) }}</span>
                    <h4 style="margin: 6px 0; font-weight: 600;">{{ $course->title }}</h4>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">
                        <strong>{{ $course->code }}</strong> | {{ $course->grade->name ?? '' }}
                        @if ($course->instructor)
                            | {{ $course->instructor->firstname }} {{ $course->instructor->lastname }}
                        @endif
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex justify-content-lg-end align-items-center mt-3 mt-lg-0">
                        <div class="stat-item">
                            <h4>{{ $enrollmentStats['total'] }}</h4>
                            <small>Enrolled</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $enrollmentStats['completed'] }}</h4>
                            <small>Completed</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $enrollmentStats['active'] }}</h4>
                            <small>Active</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $enrollmentStats['average_progress'] }}%</h4>
                            <small>Avg Progress</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="course-body">
            <div class="help-text">
                <div class="help-title">Content Preview</div>
                <p class="help-content">This is how the content appears to students. Review the structure and ensure all materials are properly organized before publishing.</p>
            </div>

            <div class="content-grid">
                <div>
                    <!-- About Section -->
                    @if ($course->description)
                        <h6 class="section-title">About This Course</h6>
                        <p style="line-height: 1.7; color: #374151; margin-bottom: 24px;">{{ $course->description }}</p>
                    @endif

                    <!-- Learning Objectives -->
                    @if ($course->learning_objectives && count($course->learning_objectives) > 0)
                        <h6 class="section-title">Learning Objectives</h6>
                        <ul class="objective-list mb-4">
                            @foreach ($course->learning_objectives as $objective)
                                <li>{{ $objective }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <!-- Content Modules -->
                    <h6 class="section-title">
                        Content Modules
                        <span class="float-end fw-normal text-muted" style="font-size: 13px; text-transform: none; letter-spacing: 0;">
                            {{ $course->modules->count() }} modules | {{ $course->modules->sum(fn($m) => $m->contentItems->count()) }} items
                        </span>
                    </h6>

                    @if ($course->modules->count() > 0)
                        <div class="accordion module-accordion" id="modulesAccordion">
                            @foreach ($course->modules as $module)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#module{{ $module->id }}">
                                            <span class="me-2">{{ $loop->iteration }}.</span>
                                            {{ $module->title }}
                                            <span class="text-muted" style="font-weight: normal; font-size: 13px; position: absolute; right: 50px;">
                                                {{ $module->contentItems->count() }} items
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="module{{ $module->id }}"
                                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                        data-bs-parent="#modulesAccordion">
                                        <div class="accordion-body">
                                            @forelse ($module->contentItems as $content)
                                                <div class="content-list-item">
                                                    <div class="content-icon {{ str_contains($content->type, 'video') ? 'video' : $content->type }}">
                                                        @switch($content->type)
                                                            @case('video_youtube')
                                                            @case('video_upload')
                                                                <i class="fas fa-play"></i>
                                                                @break
                                                            @case('document')
                                                                <i class="fas fa-file-alt"></i>
                                                                @break
                                                            @case('quiz')
                                                                <i class="fas fa-question-circle"></i>
                                                                @break
                                                            @case('audio')
                                                                <i class="fas fa-headphones"></i>
                                                                @break
                                                            @case('image')
                                                                <i class="fas fa-image"></i>
                                                                @break
                                                            @case('external_link')
                                                                <i class="fas fa-external-link-alt"></i>
                                                                @break
                                                            @default
                                                                <i class="fas fa-align-left"></i>
                                                        @endswitch
                                                    </div>
                                                    <div class="content-info flex-grow-1">
                                                        <h6>{{ $content->title }}</h6>
                                                        <span>
                                                            {{ ucfirst(str_replace('_', ' ', $content->type)) }}
                                                            @if ($content->estimated_duration)
                                                                | {{ $content->estimated_duration }} min
                                                            @endif
                                                        </span>
                                                    </div>
                                                    @if ($content->is_required)
                                                        <span class="badge bg-info">Required</span>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="text-center py-4 text-muted">
                                                    <p class="mb-0">No content in this module yet</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <p>No modules have been added to this course yet.</p>
                        </div>
                    @endif
                </div>

                <div>
                    <!-- Quick Actions -->
                    @can('manage-lms-courses')
                        <h6 class="section-title">Quick Actions</h6>

                        <a href="{{ route('lms.courses.edit', $course) }}" class="quick-link">
                            <div class="quick-link-icon purple">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="quick-link-content">
                                <h6>Edit Content</h6>
                                <p>Modify content details and settings</p>
                            </div>
                        </a>

                        <a href="{{ route('lms.enrollments.index', $course) }}" class="quick-link">
                            <div class="quick-link-icon blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="quick-link-content">
                                <h6>Manage Enrollments</h6>
                                <p>View and manage student enrollments</p>
                            </div>
                        </a>

                        <a href="{{ route('lms.modules.create', $course) }}" class="quick-link">
                            <div class="quick-link-icon green">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="quick-link-content">
                                <h6>Add Module</h6>
                                <p>Create a new content module</p>
                            </div>
                        </a>

                        @php
                            $discussionForum = \App\Models\Lms\DiscussionForum::where('course_id', $course->id)->first();
                            $threadCount = $discussionForum ? $discussionForum->threads()->count() : 0;
                        @endphp
                        <a href="{{ route('lms.discussions.forum', $course) }}" class="quick-link">
                            <div class="quick-link-icon orange">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="quick-link-content">
                                <h6>Discussions @if($threadCount > 0)<span class="badge bg-primary ms-1">{{ $threadCount }}</span>@endif</h6>
                                <p>Course forum</p>
                            </div>
                        </a>
                    @endcan

                    <!-- Content Details -->
                    <h6 class="section-title">Content Details</h6>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-item-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="info-item-content">
                                <small>Subject</small>
                                <strong>{{ $course->gradeSubject->subject->name ?? '-' }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-item-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="info-item-content">
                                <small>Grade</small>
                                <strong>{{ $course->grade->name ?? '-' }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-item-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="info-item-content">
                                <small>Term</small>
                                <strong>{{ $course->term?->year }} Term {{ $course->term?->term }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-item-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-item-content">
                                <small>Teacher</small>
                                <strong>{{ $course->instructor?->firstname }} {{ $course->instructor?->lastname }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-item-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="info-item-content">
                                <small>Passing Grade</small>
                                <strong>{{ $course->passing_grade ?? 60 }}%</strong>
                            </div>
                        </div>

                        @if ($course->start_date)
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <div class="info-item-content">
                                    <small>Start Date</small>
                                    <strong>{{ $course->start_date->format('M d, Y') }}</strong>
                                </div>
                            </div>
                        @endif

                        @if ($course->end_date)
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-stop-circle"></i>
                                </div>
                                <div class="info-item-content">
                                    <small>End Date</small>
                                    <strong>{{ $course->end_date->format('M d, Y') }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Enrollments -->
                    @can('manage-lms-enrollments')
                        <h6 class="section-title">Recent Enrollments</h6>

                        @forelse ($course->enrollments->take(5) as $enrollment)
                            <div class="enrollment-item">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($enrollment->student->first_name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size: 14px;">{{ $enrollment->student->full_name }}</div>
                                    <div class="progress mt-1" style="width: 100px;">
                                        <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                    </div>
                                </div>
                                <span class="text-muted" style="font-size: 13px;">{{ $enrollment->progress_percentage }}%</span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-users" style="font-size: 24px; opacity: 0.5; margin-bottom: 8px; display: block;"></i>
                                <p class="mb-0" style="font-size: 14px;">No enrollments yet</p>
                            </div>
                        @endforelse

                        @if ($course->enrollments->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('lms.enrollments.index', $course) }}" class="text-primary" style="font-size: 13px;">
                                    View all {{ $course->enrollments->count() }} enrollments <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

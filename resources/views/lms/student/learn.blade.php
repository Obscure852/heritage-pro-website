@extends('layouts.master')

@section('title')
    {{ $course->title }} - Learn
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

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 12px;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: white;
        }

        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.9);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.6);
        }

        .progress-stats {
            text-align: right;
        }

        .progress-percentage {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .progress-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .progress-bar-header {
            height: 8px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            overflow: hidden;
            width: 150px;
            margin-left: auto;
        }

        .progress-bar-header .progress-fill {
            height: 100%;
            background: white;
            border-radius: 4px;
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

        /* Sidebar Module Accordion */
        .module-sidebar {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .module-sidebar-header {
            background: #f9fafb;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .module-sidebar-header h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .accordion-button {
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            background: white;
            color: #374151;
        }

        .accordion-button:not(.collapsed) {
            background: #f3f4f6;
            color: #1f2937;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }

        .content-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s ease;
        }

        .content-item:last-child {
            border-bottom: none;
        }

        .content-item:hover {
            background: #f9fafb;
        }

        .content-status {
            margin-right: 10px;
            font-size: 14px;
        }

        .content-status .fa-check-circle {
            color: #10b981;
        }

        .content-status .fa-circle {
            color: #f59e0b;
        }

        .content-status .far.fa-circle {
            color: #d1d5db;
        }

        .content-link {
            flex-grow: 1;
            text-decoration: none;
            color: #374151;
            font-size: 13px;
        }

        .content-link:hover {
            color: #3b82f6;
        }

        .content-link.completed {
            color: #9ca3af;
        }

        .content-type-icon {
            color: #9ca3af;
            font-size: 12px;
            margin-left: 8px;
        }

        /* Continue Section */
        .continue-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            background: #f9fafb;
            margin-bottom: 20px;
        }

        .continue-card h5 {
            color: #374151;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .continue-content {
            display: flex;
            align-items: center;
            padding: 16px;
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        .continue-icon {
            margin-right: 16px;
            font-size: 32px;
        }

        .continue-icon.video {
            color: #ef4444;
        }

        .continue-icon.quiz {
            color: #f59e0b;
        }

        .continue-icon.assignment {
            color: #3b82f6;
        }

        .continue-icon.document {
            color: #6b7280;
        }

        .continue-info {
            flex-grow: 1;
        }

        .continue-info h6 {
            margin: 0 0 4px 0;
            color: #1f2937;
        }

        .continue-info small {
            color: #6b7280;
        }

        /* Completion Card */
        .completion-card {
            border: 2px solid #10b981;
            border-radius: 3px;
            padding: 32px;
            text-align: center;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            margin-bottom: 20px;
        }

        .completion-card i {
            font-size: 48px;
            color: #10b981;
            margin-bottom: 16px;
        }

        .completion-card h5 {
            color: #065f46;
            margin-bottom: 8px;
        }

        .completion-card p {
            color: #047857;
            margin-bottom: 20px;
        }

        /* Overview Card */
        .overview-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .overview-header {
            background: #f9fafb;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .overview-header h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .overview-body {
            padding: 20px;
        }

        .overview-stat {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .overview-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            background: white;
        }

        .overview-stat-icon.modules {
            color: #3b82f6;
        }

        .overview-stat-icon.content {
            color: #10b981;
        }

        .overview-stat-icon.completed {
            color: #059669;
        }

        .overview-stat-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .overview-stat-info strong {
            color: #1f2937;
            font-size: 16px;
        }

        .objectives-list {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .objectives-list li {
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        @media (max-width: 991px) {
            .module-sidebar {
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

            .progress-stats {
                text-align: left;
                margin-top: 16px;
            }

            .progress-bar-header {
                margin-left: 0;
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
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('lms.my-courses') }}">My Courses</a></li>
                            <li class="breadcrumb-item active">{{ $course->title }}</li>
                        </ol>
                    </nav>
                    <h3 style="margin:0;">{{ $course->title }}</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        <i class="fas fa-user me-1"></i>
                        Teacher: {{ $course->instructor?->name ?? 'N/A' }}
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="progress-stats">
                        <div class="progress-percentage">{{ number_format($enrollment->progress_percentage, 0) }}%</div>
                        <div class="progress-label">Complete</div>
                        <div class="progress-bar-header mt-2">
                            <div class="progress-fill" style="width: {{ $enrollment->progress_percentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="learn-body">
            <div class="row">
                <!-- Course Content Sidebar -->
                <div class="col-lg-4 col-xl-3 order-lg-1 order-2">
                    <div class="module-sidebar sticky-top" style="top: 20px;">
                        <div class="module-sidebar-header">
                            <h6><i class="fas fa-list me-2"></i>Course Content</h6>
                        </div>
                        <div class="accordion accordion-flush" id="moduleAccordion">
                            @foreach($course->modules as $index => $module)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#module{{ $module->id }}">
                                            <span class="me-2">{{ $index + 1 }}.</span>
                                            {{ $module->title }}
                                        </button>
                                    </h2>
                                    <div id="module{{ $module->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                        data-bs-parent="#moduleAccordion">
                                        <div class="accordion-body p-0">
                                            @foreach($module->contentItems as $content)
                                                @php
                                                    $isCompleted = in_array($content->id, $completedContent);
                                                    $progress = $contentProgress[$content->id] ?? 0;
                                                @endphp
                                                <div class="content-item">
                                                    <div class="content-status">
                                                        @if($isCompleted)
                                                            <i class="fas fa-check-circle"></i>
                                                        @elseif($progress > 0)
                                                            <i class="fas fa-circle"></i>
                                                        @else
                                                            <i class="far fa-circle"></i>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('lms.content.player', $content) }}"
                                                        class="content-link {{ $isCompleted ? 'completed' : '' }}">
                                                        {{ $content->title }}
                                                    </a>
                                                    <span class="content-type-icon">
                                                        @if($content->type === 'video')
                                                            <i class="fas fa-video"></i>
                                                        @elseif($content->type === 'quiz')
                                                            <i class="fas fa-question-circle"></i>
                                                        @elseif($content->type === 'assignment')
                                                            <i class="fas fa-tasks"></i>
                                                        @else
                                                            <i class="fas fa-file-alt"></i>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-lg-8 col-xl-9 order-lg-2 order-1">
                    @if($nextContent)
                        <div class="continue-card">
                            <h5><i class="fas fa-play-circle text-primary me-2"></i>Continue Where You Left Off</h5>
                            <div class="continue-content">
                                <div class="continue-icon {{ $nextContent->type }}">
                                    @if($nextContent->type === 'video')
                                        <i class="fas fa-video"></i>
                                    @elseif($nextContent->type === 'quiz')
                                        <i class="fas fa-question-circle"></i>
                                    @elseif($nextContent->type === 'assignment')
                                        <i class="fas fa-tasks"></i>
                                    @else
                                        <i class="fas fa-file-alt"></i>
                                    @endif
                                </div>
                                <div class="continue-info">
                                    <h6>{{ $nextContent->title }}</h6>
                                    <small>{{ ucfirst($nextContent->type) }}</small>
                                </div>
                                <a href="{{ route('lms.content.player', $nextContent) }}" class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Start
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="completion-card">
                            <i class="fas fa-trophy"></i>
                            <h5>Course Completed!</h5>
                            <p>Congratulations! You've completed all content in this course.</p>
                            @if($course->certificate_enabled)
                                <a href="{{ route('lms.certificates.request', $course) }}" class="btn btn-success">
                                    <i class="fas fa-certificate me-2"></i>Get Certificate
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Course Overview -->
                    <div class="overview-card">
                        <div class="overview-header">
                            <h6><i class="fas fa-info-circle me-2"></i>Course Overview</h6>
                        </div>
                        <div class="overview-body">
                            @if($course->description)
                                <p class="mb-4">{{ $course->description }}</p>
                            @endif

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="overview-stat">
                                        <div class="overview-stat-icon modules">
                                            <i class="fas fa-cubes"></i>
                                        </div>
                                        <div class="overview-stat-info">
                                            <small class="d-block">Modules</small>
                                            <strong>{{ $course->modules->count() }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="overview-stat">
                                        <div class="overview-stat-icon content">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="overview-stat-info">
                                            <small class="d-block">Content Items</small>
                                            <strong>{{ $course->modules->sum(fn($m) => $m->contentItems->count()) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="overview-stat">
                                        <div class="overview-stat-icon completed">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="overview-stat-info">
                                            <small class="d-block">Completed</small>
                                            <strong>{{ count($completedContent) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($course->learning_objectives && is_array($course->learning_objectives))
                                <hr>
                                <h6 class="mb-3"><i class="fas fa-bullseye me-2"></i>Learning Objectives</h6>
                                <ul class="objectives-list">
                                    @foreach($course->learning_objectives as $objective)
                                        <li>{{ $objective }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

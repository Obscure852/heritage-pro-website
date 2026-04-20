@extends('layouts.master')

@section('title')
    Modules - {{ $course->title }}
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 24px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
        }

        .stats-row {
            display: flex;
            gap: 24px;
            margin-top: 16px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .module-accordion {
            margin-bottom: 16px;
        }

        .module-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 12px;
            transition: box-shadow 0.2s;
        }

        .module-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #f9fafb;
            cursor: pointer;
            user-select: none;
        }

        .module-header:hover {
            background: #f3f4f6;
        }

        .module-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .module-number {
            width: 36px;
            height: 36px;
            background: #3b82f6;
            color: white;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .module-info h5 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .module-info p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .module-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-count-badge {
            background: #e5e7eb;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #4b5563;
        }

        .module-toggle {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: transform 0.2s;
        }

        .module-card.expanded .module-toggle {
            transform: rotate(180deg);
        }

        .module-body {
            display: none;
            padding: 0;
            border-top: 1px solid #e5e7eb;
        }

        .module-card.expanded .module-body {
            display: block;
        }

        .module-description {
            padding: 16px 20px;
            background: #f9fafb;
            font-size: 14px;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }

        .content-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .content-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .content-item:last-child {
            border-bottom: none;
        }

        .content-item:hover {
            background: #f9fafb;
        }

        .content-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .content-type-icon.video { background: #fee2e2; color: #dc2626; }
        .content-type-icon.document { background: #dbeafe; color: #2563eb; }
        .content-type-icon.quiz { background: #fef3c7; color: #d97706; }
        .content-type-icon.text { background: #d1fae5; color: #059669; }
        .content-type-icon.audio { background: #ede9fe; color: #7c3aed; }
        .content-type-icon.image { background: #fce7f3; color: #db2777; }
        .content-type-icon.link { background: #e0e7ff; color: #4f46e5; }

        .content-info {
            flex: 1;
        }

        .content-info h6 {
            margin: 0 0 4px 0;
            font-weight: 500;
            color: #1f2937;
        }

        .content-info p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .content-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .content-duration {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .required-indicator {
            width: 8px;
            height: 8px;
            background: #f59e0b;
            border-radius: 50%;
        }

        .empty-module {
            padding: 32px 20px;
            text-align: center;
            color: #6b7280;
        }

        .empty-module i {
            font-size: 32px;
            margin-bottom: 12px;
            color: #d1d5db;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid #3b82f6;
            color: #3b82f6;
            background: white;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        .admin-actions {
            display: flex;
            gap: 8px;
        }

        .admin-actions .btn {
            padding: 6px 12px;
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        .empty-state i {
            font-size: 48px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.show', $course) }}">{{ $course->title }}</a>
        @endslot
        @slot('title')
            Modules
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4>{{ $course->title }}</h4>
                <p>Course Modules</p>
                <div class="stats-row">
                    <div class="stat-item">
                        <i class="fas fa-folder"></i>
                        {{ $modules->count() }} module{{ $modules->count() != 1 ? 's' : '' }}
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-file-alt"></i>
                        {{ $modules->sum('content_items_count') }} content items
                    </div>
                </div>
            </div>
            @can('manage-lms-content')
                <a href="{{ route('lms.modules.create', $course) }}" class="btn btn-outline-primary" style="border-color: white; color: white;">
                    <i class="fas fa-plus me-1"></i> Add Module
                </a>
            @endcan
        </div>
    </div>

    @if ($modules->count() > 0)
        <div class="module-accordion">
            @foreach ($modules as $index => $module)
                <div class="module-card {{ $index === 0 ? 'expanded' : '' }}" id="module-{{ $module->id }}">
                    <div class="module-header" onclick="toggleModule({{ $module->id }})">
                        <div class="module-header-left">
                            <div class="module-number">{{ $index + 1 }}</div>
                            <div class="module-info">
                                <h5>{{ $module->title }}</h5>
                                <p>{{ $module->content_items_count }} content item{{ $module->content_items_count != 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <div class="module-header-right">
                            @can('manage-lms-content')
                                <div class="admin-actions" onclick="event.stopPropagation()">
                                    <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('lms.content.create', $module) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add Content
                                    </a>
                                </div>
                            @endcan
                            <span class="content-count-badge">
                                {{ $module->content_items_count }} items
                            </span>
                            <div class="module-toggle">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                    <div class="module-body">
                        @if ($module->description)
                            <div class="module-description">
                                {{ $module->description }}
                            </div>
                        @endif

                        @if ($module->contentItems->count() > 0)
                            <ul class="content-list">
                                @foreach ($module->contentItems as $content)
                                    <li class="content-item">
                                        @php
                                            $typeClass = match($content->type) {
                                                'video_youtube', 'video_upload' => 'video',
                                                'document' => 'document',
                                                'quiz' => 'quiz',
                                                'text' => 'text',
                                                'audio' => 'audio',
                                                'image' => 'image',
                                                'external_link' => 'link',
                                                default => 'document'
                                            };
                                            $typeIcon = match($content->type) {
                                                'video_youtube', 'video_upload' => 'fa-play-circle',
                                                'document' => 'fa-file-alt',
                                                'quiz' => 'fa-question-circle',
                                                'text' => 'fa-align-left',
                                                'audio' => 'fa-headphones',
                                                'image' => 'fa-image',
                                                'external_link' => 'fa-external-link-alt',
                                                default => 'fa-file'
                                            };
                                        @endphp
                                        <div class="content-type-icon {{ $typeClass }}">
                                            <i class="fas {{ $typeIcon }}"></i>
                                        </div>
                                        <div class="content-info">
                                            <h6>
                                                <a href="{{ route('lms.content.show', $content) }}" class="text-decoration-none text-dark">
                                                    {{ $content->title }}
                                                </a>
                                            </h6>
                                            <p>{{ ucfirst(str_replace('_', ' ', $content->type)) }}</p>
                                        </div>
                                        <div class="content-meta">
                                            @if ($content->estimated_duration)
                                                <span class="content-duration">
                                                    <i class="fas fa-clock"></i> {{ $content->estimated_duration }} min
                                                </span>
                                            @endif
                                            @if ($content->is_required)
                                                <span class="required-indicator" title="Required"></span>
                                            @endif
                                            @can('manage-lms-content')
                                                <a href="{{ route('lms.content.edit', $content) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-module">
                                <i class="fas fa-folder-open d-block"></i>
                                <p class="mb-0">No content in this module</p>
                                @can('manage-lms-content')
                                    <a href="{{ route('lms.content.create', $module) }}" class="btn btn-sm btn-outline-primary mt-3">
                                        <i class="fas fa-plus me-1"></i> Add Content
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-folder-open d-block"></i>
            <h5>No modules yet</h5>
            <p>This course doesn't have any modules. Add a module to start organizing content.</p>
            @can('manage-lms-content')
                <a href="{{ route('lms.modules.create', $course) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Module
                </a>
            @endcan
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('lms.courses.show', $course) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Course
        </a>
    </div>
@endsection

@section('script')
    <script>
        function toggleModule(moduleId) {
            const card = document.getElementById(`module-${moduleId}`);
            if (card) {
                card.classList.toggle('expanded');
            }
        }
    </script>
@endsection

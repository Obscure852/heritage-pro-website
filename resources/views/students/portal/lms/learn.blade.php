@extends('layouts.master-student-portal')

@section('title')
    {{ $course->title }} - Learn
@endsection

@section('css')
    <style>
        .learn-row {
            min-height: calc(100vh - 200px);
        }

        .learn-row .col-9 {
            display: flex;
        }

        .course-sidebar {
            background: white;
            border-radius: 3px 0 0 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-height: calc(100vh - 150px);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 20px;
        }

        .sidebar-header h5 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 6px;
            opacity: 0.9;
        }

        .progress-bar-bg {
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #10b981;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .modules-list {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }

        .module-item {
            margin-bottom: 8px;
        }

        .module-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .module-header:hover {
            background: #f3f4f6;
        }

        .module-header.expanded {
            background: #eff6ff;
            border-radius: 6px 6px 0 0;
        }

        .module-icon {
            width: 28px;
            height: 28px;
            background: #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #6b7280;
        }

        .module-title {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .module-toggle {
            color: #9ca3af;
            transition: transform 0.2s;
        }

        .module-header.expanded .module-toggle {
            transform: rotate(180deg);
        }

        .content-list {
            display: none;
            background: #f9fafb;
            border-radius: 0 0 6px 6px;
            padding: 8px;
        }

        .content-list.show {
            display: block;
        }

        .content-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .content-item:hover {
            background: #e5e7eb;
        }

        .content-item.active {
            background: #dbeafe;
        }

        .content-item.completed {
            opacity: 0.7;
        }

        .content-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #6b7280;
        }

        .content-icon.completed {
            color: #10b981;
        }

        .content-title {
            flex: 1;
            font-size: 13px;
            color: #4b5563;
        }

        .content-duration {
            font-size: 11px;
            color: #9ca3af;
        }

        .learn-content {
            background: white;
            width: 100%;
            border-radius: 0 3px 3px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
            height: 100%;
        }

        .content-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .content-header h4 {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .content-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: #6b7280;
        }

        .content-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .content-body {
            line-height: 1.7;
            color: #374151;
        }

        .content-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-nav {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-nav i {
            position: relative;
            top: 2px;
        }

        .btn-nav-prev {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-nav-prev:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-nav-next {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-nav-next:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-complete:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .welcome-content {
            text-align: center;
            padding: 60px 40px;
        }

        .welcome-content i {
            font-size: 64px;
            color: #3b82f6;
            margin-bottom: 20px;
        }

        .welcome-content h4 {
            margin-bottom: 12px;
        }

        .welcome-content p {
            color: #6b7280;
            margin-bottom: 24px;
        }

        .sidebar-actions {
            display: flex;
            gap: 8px;
            padding: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 12px;
        }

        .sidebar-action-btn {
            flex: 1;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 12px;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .sidebar-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-discuss-content {
            padding: 4px 8px;
            background: #f3f4f6;
            border: none;
            border-radius: 3px;
            color: #6b7280;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-discuss-content:hover {
            background: #dbeafe;
            color: #3b82f6;
        }

        .content-actions-bar {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-discuss {
            padding: 8px 16px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #374151;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-discuss:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        @media (max-width: 992px) {
            .learn-container {
                grid-template-columns: 1fr;
            }

            .course-sidebar {
                position: static;
                max-height: none;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="#">My Courses</a>
        @endslot
        @slot('title')
            {{ $course->title }}
        @endslot
    @endcomponent

    <div class="row g-0 learn-row">
        <div class="col-3">
            <div class="course-sidebar">
                <div class="sidebar-header">
                    <h5>{{ $course->title }}</h5>
                    @php
                        $totalContents = $course->modules->sum(fn($m) => $m->contentItems->count());
                        $completedContents = $progress->where('status', 'completed')->count();
                        $progressPercent = $totalContents > 0 ? ($completedContents / $totalContents) * 100 : 0;
                    @endphp
                    <div class="progress-info">
                        <span>Progress</span>
                        <span>{{ $completedContents }}/{{ $totalContents }} completed</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <div class="sidebar-actions">
                        <a href="{{ route('student.lms.discussions.forum', $course) }}" class="sidebar-action-btn">
                            <i class="fas fa-comments"></i> Discussions
                        </a>
                        <a href="{{ route('student.lms.messages.compose', ['course_id' => $course->id]) }}" class="sidebar-action-btn">
                            <i class="fas fa-envelope"></i> Message
                        </a>
                    </div>
                </div>

                <div class="modules-list">
                    @foreach ($course->modules as $module)
                        <div class="module-item">
                            <div class="module-header" onclick="toggleModule(this)">
                                <div class="module-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <span class="module-title">{{ $module->title }}</span>
                                <i class="fas fa-chevron-down module-toggle"></i>
                            </div>
                            <div class="content-list">
                                @foreach ($module->contentItems as $content)
                                    @php
                                        $contentProgress = $progress->get($content->id);
                                        $isCompleted = $contentProgress?->status === 'completed';
                                    @endphp
                                    @if ($content->type === 'scorm')
                                        <a href="{{ route('student.lms.scorm.play', [$course, $content]) }}"
                                            class="content-item {{ $isCompleted ? 'completed' : '' }}">
                                            <div class="content-icon {{ $isCompleted ? 'completed' : '' }}">
                                                @if ($isCompleted)
                                                    <i class="fas fa-check-circle"></i>
                                                @else
                                                    <i class="fas fa-cube"></i>
                                                @endif
                                            </div>
                                            <span class="content-title">{{ $content->title }}</span>
                                            <span class="content-duration"
                                                style="font-size: 10px; background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 3px;">SCORM</span>
                                        </a>
                                    @else
                                        <a href="#" class="content-item {{ $isCompleted ? 'completed' : '' }}"
                                            data-content-id="{{ $content->id }}">
                                            <div class="content-icon {{ $isCompleted ? 'completed' : '' }}">
                                                @if ($isCompleted)
                                                    <i class="fas fa-check-circle"></i>
                                                @elseif($content->type === 'video')
                                                    <i class="fas fa-play-circle"></i>
                                                @elseif($content->type === 'document')
                                                    <i class="fas fa-file-alt"></i>
                                                @elseif($content->type === 'quiz')
                                                    <i class="fas fa-question-circle"></i>
                                                @else
                                                    <i class="fas fa-book"></i>
                                                @endif
                                            </div>
                                            <span class="content-title">{{ $content->title }}</span>
                                            @if ($content->duration_minutes)
                                                <span class="content-duration">{{ $content->duration_minutes }}m</span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-1"></div>

        <div class="col-8">
            <div class="learn-content">
                @php
                    // Find the next uncompleted content item or first item
                    $allContentItems = $course->modules->flatMap(fn($m) => $m->contentItems);
                    $completedIds = $progress->where('status', 'completed')->pluck('content_item_id')->toArray();
                    $hasStarted = count($completedIds) > 0;

                    // Find first uncompleted item
                    $nextItem = $allContentItems->first(fn($item) => !in_array($item->id, $completedIds));

                    // If all completed, use the last item
                    if (!$nextItem && $allContentItems->count() > 0) {
                        $nextItem = $allContentItems->last();
                    }

                    // Fallback to first item
                    if (!$nextItem) {
                        $nextItem = $course->modules->first()?->contentItems->first();
                    }
                @endphp
                <div class="welcome-content">
                    <i class="fas fa-graduation-cap"></i>
                    <h4>{{ $hasStarted ? 'Continue' : 'Welcome to' }} {{ $course->title }}</h4>
                    <p>
                        @if($hasStarted)
                            You've completed {{ count($completedIds) }} of {{ $allContentItems->count() }} lessons. Keep going!
                        @else
                            Select a lesson from the sidebar to start learning. Track your progress as you complete each section.
                        @endif
                    </p>
                    @if ($nextItem)
                        <a href="#" class="btn-nav btn-nav-next"
                            data-content-id="{{ $nextItem->id }}" id="resume-btn">
                            {{ $hasStarted ? 'Continue Learning' : 'Start Learning' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    use Illuminate\Support\Facades\Storage;
    $contentDataArray = [];
    foreach ($course->modules as $module) {
        foreach ($module->contentItems as $item) {
            $videoData = null;
            if (in_array($item->type, ['video_youtube', 'video_upload']) && $item->video) {
                $videoData = [
                    'source_type' => $item->video->source_type,
                    'source_id' => $item->video->source_id,
                    'video_url' => $item->video->video_url,
                    'thumbnail' => $item->video->thumbnail_path ? Storage::disk('public')->url($item->video->thumbnail_path) : null,
                ];
            }
            $contentDataArray[$item->id] = [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'type' => $item->type,
                'content' => $item->content,
                'file_url' => $item->file_url,
                'external_url' => $item->external_url,
                'duration_minutes' => $item->duration_minutes,
                'module_title' => $module->title,
                'video' => $videoData,
            ];
        }
    }
@endphp

@section('script')
    <script>
        // Content data for JavaScript
        const contentData = @json($contentDataArray);
        const contentIds = Object.keys(contentData).map(Number);

        function toggleModule(header) {
            header.classList.toggle('expanded');
            const contentList = header.nextElementSibling;
            contentList.classList.toggle('show');
        }

        function loadContent(contentId) {
            const content = contentData[contentId];
            if (!content) return;

            // Update active state in sidebar
            document.querySelectorAll('.content-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.contentId == contentId) {
                    item.classList.add('active');
                }
            });

            // Build content HTML
            let contentHtml = `
                <div class="content-header">
                    <h4>${content.title}</h4>
                    <div class="content-meta">
                        <span><i class="fas fa-folder me-1"></i>${content.module_title}</span>
                        ${content.duration_minutes ? `<span><i class="fas fa-clock me-1"></i>${content.duration_minutes} min</span>` : ''}
                    </div>
                </div>
                <div class="content-body">
            `;

            // Render content based on type
            if (content.type === 'video_youtube' || content.type === 'video_upload') {
                if (content.video) {
                    if (content.video.source_type === 'youtube' && content.video.source_id) {
                        contentHtml += `<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/${content.video.source_id}" allowfullscreen></iframe></div>`;
                    } else if (content.video.video_url) {
                        contentHtml += `<video controls class="w-100 mb-3"><source src="${content.video.video_url}" type="video/mp4"></video>`;
                    }
                } else if (content.external_url && content.external_url.includes('youtube')) {
                    const videoId = extractYouTubeId(content.external_url);
                    contentHtml += `<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/${videoId}" allowfullscreen></iframe></div>`;
                } else if (content.file_url) {
                    contentHtml += `<video controls class="w-100 mb-3"><source src="${content.file_url}" type="video/mp4"></video>`;
                }
            } else if (content.type === 'document') {
                if (content.file_url) {
                    contentHtml += `<div class="mb-3"><a href="${content.file_url}" target="_blank" class="btn btn-primary"><i class="fas fa-download me-2"></i>Download Document</a></div>`;
                    if (content.file_url.endsWith('.pdf')) {
                        contentHtml += `<iframe src="${content.file_url}" class="w-100" style="height: 600px; border: 1px solid #e5e7eb; border-radius: 3px;"></iframe>`;
                    }
                }
            } else if (content.type === 'text' || content.type === 'html') {
                contentHtml += `<div class="content-text">${content.content || ''}</div>`;
            } else if (content.external_url) {
                contentHtml += `<a href="${content.external_url}" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt me-2"></i>Open External Link</a>`;
            }

            if (content.description) {
                contentHtml += `<div class="mt-3"><p>${content.description}</p></div>`;
            }

            contentHtml += '</div>';

            // Add Discuss button for quiz and assignment types
            const discussableTypes = ['quiz', 'assignment', 'assessment'];
            if (discussableTypes.includes(content.type)) {
                contentHtml += `
                    <div class="content-actions-bar">
                        <a href="/student/lms/content/${contentId}/discussions" class="btn-discuss">
                            <i class="fas fa-comments"></i> Discuss this ${content.type}
                        </a>
                    </div>
                `;
            }

            // Add navigation and complete button
            const currentIndex = contentIds.indexOf(contentId);
            const prevId = currentIndex > 0 ? contentIds[currentIndex - 1] : null;
            const nextId = currentIndex < contentIds.length - 1 ? contentIds[currentIndex + 1] : null;

            contentHtml += `
                <div class="content-navigation">
                    <div>
                        ${prevId ? `<button onclick="loadContent(${prevId})" class="btn-nav btn-nav-prev"><i class="fas fa-arrow-left me-2"></i>Previous</button>` : ''}
                    </div>
                    <div class="d-flex gap-2">
                        <button onclick="markComplete(${contentId})" class="btn-nav btn-complete" id="btn-complete-${contentId}">
                            <i class="fas fa-check me-2"></i>Mark Complete
                        </button>
                        ${nextId ? `<button onclick="loadContent(${nextId})" class="btn-nav btn-nav-next">Next<i class="fas fa-arrow-right ms-2"></i></button>` : ''}
                    </div>
                </div>
            `;

            // Update main content area
            document.querySelector('.learn-content').innerHTML = contentHtml;
        }

        function markComplete(contentId) {
            const btn = document.getElementById(`btn-complete-${contentId}`);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            fetch(`/student/lms/courses/{{ $course->id }}/content/${contentId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Completed';
                    btn.classList.remove('btn-complete');
                    btn.classList.add('btn-nav-prev');
                    btn.disabled = true;

                    // Update sidebar item
                    const sidebarItem = document.querySelector(`.content-item[data-content-id="${contentId}"]`);
                    if (sidebarItem) {
                        sidebarItem.classList.add('completed');
                        const icon = sidebarItem.querySelector('.content-icon');
                        if (icon) {
                            icon.classList.add('completed');
                            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                        }
                    }

                    // Update progress bar
                    document.querySelector('.progress-bar-fill').style.width = `${data.progress_percent}%`;
                    document.querySelector('.progress-info span:last-child').textContent = `${data.completed_count}/${data.total_count} completed`;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Mark Complete';
                    alert('Failed to save progress');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Mark Complete';
                console.error('Error:', error);
            });
        }

        function extractYouTubeId(url) {
            const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            return match ? match[1] : '';
        }

        // Expand module containing the target content
        function expandModuleForContent(contentId) {
            const contentItem = document.querySelector(`.content-item[data-content-id="${contentId}"]`);
            if (contentItem) {
                const contentList = contentItem.closest('.content-list');
                const moduleHeader = contentList?.previousElementSibling;
                if (moduleHeader && contentList) {
                    moduleHeader.classList.add('expanded');
                    contentList.classList.add('show');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle content item clicks
            document.querySelectorAll('.content-item[data-content-id]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadContent(this.dataset.contentId);
                });
            });

            // Handle Start/Continue Learning button
            const resumeBtn = document.getElementById('resume-btn');
            if (resumeBtn) {
                resumeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadContent(this.dataset.contentId);
                });
            }

            // Auto-load content if user has started the course
            @if($hasStarted && $nextItem)
                const nextContentId = {{ $nextItem->id }};
                expandModuleForContent(nextContentId);
                loadContent(nextContentId);
            @else
                // Expand first module by default
                const firstModule = document.querySelector('.module-header');
                if (firstModule) {
                    firstModule.classList.add('expanded');
                    firstModule.nextElementSibling.classList.add('show');
                }
            @endif
        });
    </script>
@endsection

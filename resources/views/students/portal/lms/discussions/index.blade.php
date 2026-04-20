@extends('layouts.master-student-portal')

@section('title')
    Discussions - {{ $course->title }}
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

        .back-link {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 12px;
            opacity: 0.9;
        }

        .back-link:hover {
            color: white;
            opacity: 1;
        }

        .discussions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .discussions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .discussions-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .btn-new-thread {
            padding: 10px 20px;
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
            transition: all 0.2s;
        }

        .btn-new-thread:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .categories-bar {
            padding: 12px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .category-chip {
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            background: white;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .category-chip:hover,
        .category-chip.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .thread-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .thread-item {
            display: flex;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .thread-item:hover {
            background: #f9fafb;
        }

        .thread-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .thread-content {
            flex: 1;
            min-width: 0;
        }

        .thread-header {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 4px;
        }

        .thread-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            text-decoration: none;
            flex: 1;
        }

        .thread-title:hover {
            color: #3b82f6;
        }

        .thread-badges {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pinned {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-question {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-resolved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-announcement {
            background: #fee2e2;
            color: #991b1b;
        }

        .thread-preview {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .thread-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: #9ca3af;
        }

        .thread-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .thread-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding-left: 16px;
            margin-left: 16px;
            border-left: 1px solid #e5e7eb;
            min-width: 60px;
        }

        .stat-box {
            text-align: center;
        }

        .stat-box .number {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .stat-box .label {
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
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

        .pagination-container {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('student.lms.learn', $course) }}">{{ Str::limit($course->title, 30) }}</a>
        @endslot
        @slot('title')
            Discussions
        @endslot
    @endcomponent

    <div class="page-header">
        <a href="{{ route('student.lms.learn', $course) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Course
        </a>
        <h4><i class="fas fa-comments me-2"></i>{{ $course->title }} - Discussions</h4>
        <p>Ask questions, share insights, and connect with classmates</p>
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

    <div class="discussions-container">
        <div class="discussions-header">
            <span class="discussions-title">
                <i class="fas fa-list me-2"></i>All Discussions ({{ $threads->total() }})
            </span>
            <a href="{{ route('student.lms.discussions.create', $course) }}" class="btn-new-thread">
                <i class="fas fa-plus"></i> New Discussion
            </a>
        </div>

        @if ($forum->categories->isNotEmpty())
            <div class="categories-bar">
                <a href="{{ route('student.lms.discussions.forum', $course) }}" class="category-chip active">
                    All Topics
                </a>
                @foreach ($forum->categories as $category)
                    <a href="{{ route('student.lms.discussions.forum', ['course' => $course, 'category' => $category->id]) }}"
                        class="category-chip" style="border-color: {{ $category->color }}">
                        <i class="{{ $category->icon }} me-1"></i>{{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($threads->isEmpty())
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h5>No Discussions Yet</h5>
                <p>Be the first to start a discussion in this course.</p>
                <a href="{{ route('student.lms.discussions.create', $course) }}" class="btn-new-thread">
                    <i class="fas fa-plus"></i> Start a Discussion
                </a>
            </div>
        @else
            <ul class="thread-list">
                @foreach ($threads as $thread)
                    @php
                        $initials = $thread->is_anonymous
                            ? 'A'
                            : strtoupper(substr($thread->author->first_name ?? 'U', 0, 1));
                    @endphp
                    <li class="thread-item">
                        <div class="thread-avatar">{{ $initials }}</div>
                        <div class="thread-content">
                            <div class="thread-header">
                                <a href="{{ route('student.lms.discussions.thread', $thread) }}" class="thread-title">
                                    {{ $thread->title }}
                                </a>
                                <div class="thread-badges">
                                    @if ($thread->is_pinned)
                                        <span class="badge badge-pinned"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                                    @endif
                                    @if ($thread->type === 'question')
                                        <span class="badge badge-question"><i
                                                class="fas fa-question-circle me-1"></i>Question</span>
                                    @endif
                                    @if ($thread->status === 'resolved')
                                        <span class="badge badge-resolved"><i
                                                class="fas fa-check-circle me-1"></i>Resolved</span>
                                    @endif
                                    @if ($thread->type === 'announcement')
                                        <span class="badge badge-announcement"><i
                                                class="fas fa-bullhorn me-1"></i>Announcement</span>
                                    @endif
                                </div>
                            </div>
                            <div class="thread-preview">{{ Str::limit(strip_tags($thread->body), 150) }}</div>
                            <div class="thread-meta">
                                <span>
                                    <i class="fas fa-user"></i>
                                    {{ $thread->display_author }}
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $thread->last_activity_at?->diffForHumans() ?? $thread->created_at->diffForHumans() }}
                                </span>
                                @if ($thread->category)
                                    <span style="color: {{ $thread->category->color }}">
                                        <i class="{{ $thread->category->icon }}"></i>
                                        {{ $thread->category->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="thread-stats">
                            <div class="stat-box">
                                <div class="number">{{ $thread->replies_count }}</div>
                                <div class="label">Replies</div>
                            </div>
                            <div class="stat-box">
                                <div class="number">{{ $thread->views_count }}</div>
                                <div class="label">Views</div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($threads->hasPages())
                <div class="pagination-container">
                    {{ $threads->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

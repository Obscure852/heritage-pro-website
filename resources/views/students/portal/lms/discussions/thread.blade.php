@extends('layouts.master-student-portal')
@section('title')
    {{ $thread->title }} - Discussions
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
            font-size: 20px;
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

        .thread-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .thread-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .thread-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-question {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-resolved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-discussion {
            background: #f3f4f6;
            color: #4b5563;
        }

        .badge-announcement {
            background: #fee2e2;
            color: #991b1b;
        }

        .thread-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .thread-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: #6b7280;
        }

        .thread-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .thread-body {
            padding: 20px;
            font-size: 15px;
            line-height: 1.7;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        .thread-actions {
            padding: 12px 20px;
            display: flex;
            gap: 16px;
            background: #f9fafb;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .action-btn.active {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .posts-section {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .posts-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .post-item {
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .post-item:last-child {
            border-bottom: none;
        }

        .post-item.is-answer {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-avatar {
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
        }

        .author-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .author-info small {
            color: #9ca3af;
            font-size: 12px;
        }

        .answer-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #10b981;
            color: white;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .post-body {
            font-size: 14px;
            line-height: 1.7;
            color: #374151;
            margin-bottom: 12px;
        }

        .post-attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .attachment-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 12px;
            color: #374151;
            text-decoration: none;
        }

        .attachment-link:hover {
            background: #e5e7eb;
        }

        .post-actions {
            display: flex;
            gap: 12px;
        }

        .post-action {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: none;
            border: none;
            font-size: 12px;
            color: #6b7280;
            cursor: pointer;
            border-radius: 3px;
        }

        .post-action:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .nested-replies {
            margin-left: 52px;
            padding-left: 20px;
            border-left: 2px solid #e5e7eb;
        }

        .nested-reply {
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .nested-reply:last-child {
            border-bottom: none;
        }

        .reply-form {
            padding: 20px;
            background: #f9fafb;
        }

        .reply-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 14px;
            resize: none;
            min-height: 120px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .reply-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .reply-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        .btn-submit-reply {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-submit-reply:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
        }

        .btn-submit-reply:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .content-context {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-context i {
            font-size: 20px;
            color: #3b82f6;
        }

        .content-context-info h6 {
            margin: 0;
            font-size: 13px;
            color: #1e40af;
        }

        .content-context-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .empty-posts {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
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
            <a href="{{ route('student.lms.discussions.forum', $course) }}">Discussions</a>
        @endslot
        @slot('title')
            Thread
        @endslot
    @endcomponent

    <div class="page-header">
        <h4>{{ Str::limit($thread->title, 60) }}</h4>
        <p>{{ $course->title }}</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="thread-container">
        <div class="thread-header">
            <div class="thread-badges">
                @if ($thread->type === 'question')
                    <span class="badge badge-question"><i class="fas fa-question-circle me-1"></i>Question</span>
                @elseif($thread->type === 'announcement')
                    <span class="badge badge-announcement"><i class="fas fa-bullhorn me-1"></i>Announcement</span>
                @else
                    <span class="badge badge-discussion"><i class="fas fa-comments me-1"></i>Discussion</span>
                @endif
                @if ($thread->status === 'resolved')
                    <span class="badge badge-resolved"><i class="fas fa-check-circle me-1"></i>Resolved</span>
                @endif
            </div>
            <h1 class="thread-title">{{ $thread->title }}</h1>
            <div class="thread-meta">
                <span><i class="fas fa-user"></i> {{ $thread->display_author }}</span>
                <span><i class="fas fa-clock"></i> {{ $thread->created_at->format('M d, Y \a\t g:i A') }}</span>
                <span><i class="fas fa-eye"></i> {{ $thread->views_count }} views</span>
            </div>
        </div>

        @if ($thread->contentItem)
            <div class="content-context" style="margin: 0 20px 0 20px; margin-top: 16px;">
                <i class="fas fa-link"></i>
                <div class="content-context-info">
                    <h6>Related to: {{ $thread->contentItem->title }}</h6>
                    <small>{{ ucfirst($thread->contentItem->type) }} -
                        {{ $thread->contentItem->module->title ?? '' }}</small>
                </div>
            </div>
        @endif

        <div class="thread-body">
            {!! nl2br(e($thread->body)) !!}
        </div>
    </div>

    <div class="posts-section">
        <div class="posts-header">
            <i class="fas fa-reply me-2"></i>Replies ({{ $thread->replies_count }})
        </div>

        @if ($posts->isEmpty())
            <div class="empty-posts">
                <i class="fas fa-comments fa-3x mb-3" style="color: #d1d5db;"></i>
                <p>No replies yet. Be the first to respond!</p>
            </div>
        @else
            @foreach ($posts as $post)
                @php
                    $postInitials = $post->is_anonymous
                        ? 'A'
                        : strtoupper(substr($post->author->first_name ?? 'U', 0, 1));
                    $isAnswer = $thread->accepted_answer_id === $post->id;
                @endphp
                <div class="post-item {{ $isAnswer ? 'is-answer' : '' }}">
                    <div class="post-header">
                        <div class="post-author">
                            <div class="author-avatar">{{ $postInitials }}</div>
                            <div class="author-info">
                                <h6>{{ $post->is_anonymous ? 'Anonymous' : $post->author->full_name ?? 'Unknown' }}</h6>
                                <small>{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if ($isAnswer)
                            <span class="answer-badge"><i class="fas fa-check"></i> Accepted Answer</span>
                        @endif
                    </div>

                    <div class="post-body">
                        {!! nl2br(e($post->body)) !!}
                    </div>

                    @if ($post->attachments->isNotEmpty())
                        <div class="post-attachments">
                            @foreach ($post->attachments as $attachment)
                                <a href="{{ $attachment->url }}" target="_blank" class="attachment-link">
                                    <i class="{{ $attachment->icon_class }}"></i>
                                    {{ Str::limit($attachment->original_filename, 25) }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($thread->type === 'question' && $thread->author_id === $student->id && !$thread->accepted_answer_id)
                        <div class="post-actions">
                            <button class="post-action mark-answer-btn" data-id="{{ $post->id }}">
                                <i class="fas fa-check"></i> Mark as Answer
                            </button>
                        </div>
                    @endif

                    @if ($post->replies->isNotEmpty())
                        <div class="nested-replies">
                            @foreach ($post->replies as $reply)
                                @php
                                    $replyInitials = $reply->is_anonymous
                                        ? 'A'
                                        : strtoupper(substr($reply->author->first_name ?? 'U', 0, 1));
                                @endphp
                                <div class="nested-reply">
                                    <div class="post-author" style="margin-bottom: 8px;">
                                        <div class="author-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                                            {{ $replyInitials }}</div>
                                        <div class="author-info">
                                            <h6 style="font-size: 13px;">
                                                {{ $reply->is_anonymous ? 'Anonymous' : $reply->author->full_name ?? 'Unknown' }}
                                            </h6>
                                            <small>{{ $reply->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="post-body" style="font-size: 13px;">
                                        {!! nl2br(e($reply->body)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($posts->hasPages())
                <div class="pagination-container">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif

        @if (!$thread->is_locked)
            <div class="reply-form">
                <form action="{{ route('student.lms.discussions.post', $thread) }}" method="POST" id="replyForm">
                    @csrf
                    <textarea name="body" class="reply-textarea" placeholder="Write your reply..." required minlength="2"
                        maxlength="10000"></textarea>
                    <div class="reply-actions">
                        @if ($forum->allow_anonymous)
                            <label class="d-flex align-items-center gap-2" style="font-size: 13px; color: #6b7280;">
                                <input type="checkbox" name="is_anonymous" value="1"> Post anonymously
                            </label>
                        @else
                            <div></div>
                        @endif
                        <button type="submit" class="btn-submit-reply">
                            <i class="fas fa-paper-plane"></i> Post Reply
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="reply-form" style="text-align: center; color: #6b7280;">
                <i class="fas fa-lock me-2"></i>This thread is locked and cannot accept new replies.
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        // Mark as answer
        document.querySelectorAll('.mark-answer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Mark this post as the accepted answer?')) {
                    fetch(`/student/lms/discussions/posts/${this.dataset.id}/answer`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            }
                        });
                }
            });
        });

        // Reply form
        document.getElementById('replyForm')?.addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit-reply');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
        });
    </script>
@endsection

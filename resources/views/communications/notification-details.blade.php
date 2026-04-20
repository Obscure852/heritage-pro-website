@extends('layouts.master')
@section('title')
    Notification Details
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .admissions-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .admissions-body {
            padding: 24px;
        }

        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
            padding: 0 24px;
            background: #f8f9fa;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            padding: 14px 16px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            border-bottom-color: #d1d5db;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .notification-content {
            font-size: 1rem;
            line-height: 1.6;
        }

        .notification-content p {
            margin-bottom: 1rem;
        }

        .notification-content img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .avatar-sm {
            height: 3rem;
            width: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-title {
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transform: translateY(-1px);
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 3px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .chat-conversation {
            max-height: 400px;
            overflow-y: auto;
        }

        .ctext-wrap-content {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .conversation-name {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .conversation-name .time {
            font-size: 12px;
            color: #6b7280;
            font-weight: 400;
            margin-left: 8px;
        }

        .right .ctext-wrap-content {
            background: #e3f2fd;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('dashboard') }}">Dashboard</a>
        @endslot
        @slot('title')
            Notification Details
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="admissions-container">
                <div class="admissions-header">
                    <h4>
                        @if ($notification->is_pinned)
                            <i class="fas fa-thumbtack me-2" title="Pinned"></i>
                        @endif
                        {{ $notification->title ?? 'Notification' }}
                    </h4>
                    <p>
                        Created {{ $notification->created_at->diffForHumans() }}
                        @if ($notification->department)
                            | {{ $notification->department->name }}
                        @endif
                        @if ($notification->is_pinned)
                            | <span class="badge bg-warning text-dark"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                        @endif
                    </p>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-overview" data-bs-toggle="tab" href="#overview" role="tab">
                            <i class="bx bx-info-circle me-1"></i> Notification
                        </a>
                    </li>
                    @if ($notification->allow_comments)
                        <li class="nav-item">
                            <a class="nav-link" id="tab-about" data-bs-toggle="tab" href="#about" role="tab">
                                <i class="bx bx-message-dots me-1"></i> Comments
                                @if ($notification->notificationComments->count() > 0)
                                    <span class="badge bg-primary ms-1">{{ $notification->notificationComments->count() }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="admissions-body">
                    <div class="tab-content">
                        <!-- Notification Tab -->
                        <div class="tab-pane active" id="overview" role="tabpanel">
                            <!-- Date Range Section -->
                            @if ($notification->start_date || $notification->end_date)
                                <div class="info-card mb-4">
                                    <div class="d-flex gap-4">
                                        @if ($notification->start_date)
                                            <div>
                                                <small class="text-muted d-block">Start Date</small>
                                                <strong>{{ \Carbon\Carbon::parse($notification->start_date)->format('M d, Y') }}</strong>
                                            </div>
                                        @endif
                                        @if ($notification->end_date)
                                            <div>
                                                <small class="text-muted d-block">End Date</small>
                                                <strong>{{ \Carbon\Carbon::parse($notification->end_date)->format('M d, Y') }}</strong>
                                            </div>
                                        @endif
                                        @if ($notification->end_date && $notification->end_date > now())
                                            <div class="ms-auto">
                                                <span class="badge bg-warning text-dark">
                                                    Expires {{ \Carbon\Carbon::parse($notification->end_date)->diffForHumans() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Content Section -->
                            <div class="notification-content mb-4">
                                <div class="bg-light p-4 rounded" style="border-radius: 3px;">
                                    {!! $notification->body ?? '' !!}
                                </div>
                            </div>

                            <!-- Attachments Section -->
                            @if ($notification->attachments->count() > 0)
                                <div class="mt-4">
                                    <h6 class="mb-3">
                                        <i class="bx bx-paperclip me-2"></i>
                                        Attachments ({{ $notification->attachments->count() }})
                                    </h6>
                                    <div class="row g-3">
                                        @foreach ($notification->attachments as $attachment)
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 border rounded bg-light hover-shadow" style="border-radius: 3px;">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm">
                                                            <span class="avatar-title rounded {{ $notification->getFileColorClass($attachment->file_type) }}">
                                                                {!! $notification->getFileIcon($attachment->file_type) !!}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1 text-truncate" style="max-width: 200px;">
                                                            {{ substr($attachment->file_path, 12) }}
                                                        </h6>
                                                        <p class="text-muted mb-0 small">
                                                            {{ Str::upper(pathinfo($attachment->file_path, PATHINFO_EXTENSION)) }}
                                                            @if (isset($attachment->file_size))
                                                                &middot; {{ $notification->formatFileSize($attachment->file_size) }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="ms-4">
                                                        <a href="{{ route('notification.download-attachment', $attachment->id) }}" class="btn btn-sm btn-primary" title="Download">
                                                            <i class="bx bx-download me-1"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane" id="about" role="tabpanel">
                            <div class="chat-conversation p-3" data-simplebar>
                                <ul class="list-unstyled mb-0">
                                    @forelse ($notification->notificationComments as $comment)
                                        <li class="{{ $comment->user->id == auth()->id() ? 'right' : '' }} mb-3">
                                            <div class="conversation-list">
                                                <div class="ctext-wrap">
                                                    <div class="ctext-wrap-content">
                                                        <h5 class="conversation-name">
                                                            <a href="#" class="user-name fw-medium">{{ $comment->user->fullName ?? '' }}</a>
                                                            <span class="time">{{ $comment->created_at->diffForHumans() }}</span>
                                                        </h5>
                                                        <p class="mb-0">{{ $comment->body }}</p>
                                                    </div>
                                                    @if ($comment->user->id == auth()->id())
                                                        <div class="dropdown align-self-start">
                                                            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item text-danger" href="{{ route('notification.delete-comment', $comment->id) }}" onclick="return confirm('Are you sure you want to delete this comment?')">
                                                                    <i class="bx bx-trash me-2"></i> Delete
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-center py-4 text-muted">
                                            <i class="bx bx-message-dots fs-2 mb-2"></i>
                                            <p class="mb-0">No comments yet. Be the first to comment!</p>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                            <!-- Comment Form -->
                            <div class="p-3 border-top mt-3">
                                <form method="POST" action="{{ route('notifications.notification-comment') }}" id="commentForm">
                                    @csrf
                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                    <div class="row g-2">
                                        <div class="col">
                                            <div class="position-relative">
                                                <input type="text" name="body" class="form-control" placeholder="Enter your comment..." required>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary btn-loading" id="sendCommentBtn">
                                                <span class="btn-text"><i class="mdi mdi-send me-1"></i> Send</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                                    Sending...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tab persistence
            function saveActiveTab() {
                let activeTab = document.querySelector('.nav-link.active').id;
                localStorage.setItem('notificationActiveTab', activeTab);
            }

            function restoreActiveTab() {
                let activeTab = localStorage.getItem('notificationActiveTab');
                if (activeTab) {
                    let tabElement = document.getElementById(activeTab);
                    if (tabElement) {
                        new bootstrap.Tab(tabElement).show();
                    }
                }
            }

            let tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', saveActiveTab);
            });

            restoreActiveTab();

            // Comment form loading animation
            const commentForm = document.getElementById('commentForm');
            const sendCommentBtn = document.getElementById('sendCommentBtn');

            if (commentForm && sendCommentBtn) {
                commentForm.addEventListener('submit', function() {
                    sendCommentBtn.classList.add('loading');
                    sendCommentBtn.disabled = true;
                });
            }
        });
    </script>
@endsection

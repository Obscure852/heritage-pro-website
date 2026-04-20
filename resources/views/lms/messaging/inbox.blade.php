@extends('layouts.master')

@section('title')
    Messages
@endsection

@section('css')
    <style>
        .messaging-wrapper {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .messaging-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            padding: 24px 28px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .messaging-header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .messaging-title h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .messaging-title p {
            margin: 6px 0 0;
            opacity: 0.85;
            font-size: 13px;
        }

        .messaging-header-right {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .messaging-stats {
            display: flex;
            gap: 24px;
        }

        .stat-box {
            text-align: center;
        }

        .stat-box .stat-number {
            font-size: 22px;
            font-weight: 700;
            display: block;
        }

        .stat-box .stat-label {
            font-size: 11px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .messaging-tabs {
            display: flex;
            gap: 4px;
            padding: 0 28px;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-btn {
            padding: 14px 20px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: #374151;
        }

        .tab-btn.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
        }

        .tab-btn .badge {
            background: #ef4444;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .conversation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 16px 28px;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
        }

        .conversation-item:hover {
            background: #f9fafb;
        }

        .conversation-item.unread {
            background: #f0f7ff;
            border-left: 3px solid #4e73df;
        }

        .conversation-item.unread:hover {
            background: #e8f2ff;
        }

        .conversation-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .conversation-body {
            flex: 1;
            min-width: 0;
            margin-left: 14px;
        }

        .conversation-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }

        .conversation-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
        }

        .unread .conversation-name {
            color: #1e40af;
        }

        .conversation-time {
            font-size: 12px;
            color: #9ca3af;
            flex-shrink: 0;
        }

        .conversation-subject {
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 2px;
            font-weight: 500;
        }

        .conversation-preview {
            font-size: 13px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }

        .course-tag {
            font-size: 11px;
            padding: 3px 8px;
            background: #e5e7eb;
            color: #4b5563;
            border-radius: 3px;
        }

        .unread-indicator {
            width: 8px;
            height: 8px;
            background: #4e73df;
            border-radius: 50%;
            margin-left: auto;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-state-icon i {
            font-size: 32px;
            color: #9ca3af;
        }

        .empty-state h5 {
            color: #374151;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .pagination-footer {
            padding: 16px 28px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            LMS
        @endslot
        @slot('title')
            Messages
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="messaging-wrapper">
        <div class="messaging-header">
            <div class="messaging-header-left">
                <div class="messaging-title">
                    <h4><i class="fas fa-envelope me-2"></i>Student Messages</h4>
                    <p>Messages from students in your courses</p>
                </div>
            </div>
            <div class="messaging-header-right">
                <div class="messaging-stats">
                    <div class="stat-box">
                        <span class="stat-number">{{ $conversations->total() }}</span>
                        <span class="stat-label">Total</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number">{{ $totalUnread }}</span>
                        <span class="stat-label">Unread</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="messaging-tabs">
            <a href="{{ route('lms.messaging.inbox') }}" class="tab-btn {{ !$showArchived ? 'active' : '' }}">
                <i class="fas fa-inbox"></i> Inbox
                @if($totalUnread > 0 && !$showArchived)
                    <span class="badge">{{ $totalUnread }}</span>
                @endif
            </a>
            <a href="{{ route('lms.messaging.inbox', ['archived' => true]) }}" class="tab-btn {{ $showArchived ? 'active' : '' }}">
                <i class="fas fa-archive"></i> Archived
            </a>
        </div>

        @if($conversations->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h5>{{ $showArchived ? 'No Archived Messages' : 'No Messages Yet' }}</h5>
                <p>{{ $showArchived ? 'Archived conversations will appear here.' : 'Messages from students in your courses will appear here.' }}</p>
            </div>
        @else
            <ul class="conversation-list">
                @foreach($conversations as $conversation)
                    @php
                        $lastMessage = $conversation->messages->first();
                        $initials = strtoupper(substr($conversation->student->first_name ?? 'S', 0, 1));
                    @endphp
                    <li>
                        <a href="{{ route('lms.messaging.conversation', $conversation) }}"
                           class="conversation-item {{ $conversation->has_unread ? 'unread' : '' }}">
                            <div class="conversation-avatar">{{ $initials }}</div>
                            <div class="conversation-body">
                                <div class="conversation-top">
                                    <span class="conversation-name">{{ $conversation->student->full_name ?? 'Unknown Student' }}</span>
                                    <span class="conversation-time">
                                        {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                @if($conversation->subject)
                                    <div class="conversation-subject">{{ $conversation->subject }}</div>
                                @endif
                                @if($lastMessage)
                                    <div class="conversation-preview">
                                        @if($lastMessage->isSentByInstructor())
                                            <span style="color: #9ca3af;">You:</span>
                                        @endif
                                        {{ Str::limit($lastMessage->body, 70) }}
                                    </div>
                                @endif
                                <div class="conversation-meta">
                                    @if($conversation->course)
                                        <span class="course-tag">{{ Str::limit($conversation->course->title, 25) }}</span>
                                    @endif
                                    @if($conversation->has_unread)
                                        <span class="unread-indicator"></span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if($conversations->hasPages())
                <div class="pagination-footer">
                    {{ $conversations->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

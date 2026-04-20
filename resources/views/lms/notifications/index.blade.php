@extends('layouts.master')

@section('title', 'Notifications')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            Notifications
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Notification Center</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            View all your LMS notifications including course updates, assignment deadlines, grade releases, and announcements. Manage your notification preferences to control what you receive.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h4>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group me-2">
                <a href="{{ route('lms.notifications.index', ['filter' => 'all']) }}" 
                   class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                <a href="{{ route('lms.notifications.index', ['filter' => 'unread']) }}" 
                   class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Unread ({{ $unreadCount }})
                </a>
            </div>
            @if($unreadCount > 0)
                <form action="{{ route('lms.notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-check-double me-1"></i>Mark All Read
                    </button>
                </form>
            @endif
            <a href="{{ route('lms.notifications.preferences') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i>Preferences
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <div class="list-group-item {{ $notification->is_read ? '' : 'bg-light' }}">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon me-3" style="color: {{ $notification->color }};">
                            <i class="{{ $notification->icon }} fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 {{ $notification->is_read ? 'text-muted' : '' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($notification->action_url)
                                        <a href="{{ route('lms.notifications.read', $notification) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            {{ $notification->action_text ?? 'View' }}
                                        </a>
                                    @endif
                                    @if(!$notification->is_read)
                                        <form action="{{ route('lms.notifications.read', $notification) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('lms.notifications.destroy', $notification) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5>No notifications</h5>
                    <p class="text-muted mb-0">You're all caught up!</p>
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

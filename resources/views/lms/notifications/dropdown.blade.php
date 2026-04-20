<div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" style="width: 350px; max-height: 450px;">
    <div class="dropdown-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
        <strong><i class="fas fa-bell me-2"></i>Notifications</strong>
        @if($unreadCount > 0)
            <span class="badge bg-danger">{{ $unreadCount }} new</span>
        @endif
    </div>

    <div class="notification-list" style="max-height: 320px; overflow-y: auto;">
        @forelse($notifications as $notification)
            <a href="{{ $notification->action_url ? route('lms.notifications.read', $notification) : '#' }}"
               class="dropdown-item d-flex align-items-start py-2 px-3 {{ $notification->is_read ? '' : 'bg-light' }}">
                <div class="notification-icon me-3 mt-1" style="color: {{ $notification->color }};">
                    <i class="{{ $notification->icon }}"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between">
                        <strong class="text-truncate {{ $notification->is_read ? 'text-muted' : '' }}">
                            {{ Str::limit($notification->title, 35) }}
                        </strong>
                        @if(!$notification->is_read)
                            <span class="badge bg-primary ms-2">New</span>
                        @endif
                    </div>
                    <p class="mb-0 small text-muted text-truncate">{{ Str::limit($notification->message, 50) }}</p>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
            </a>
        @empty
            <div class="text-center py-4">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No notifications</p>
            </div>
        @endforelse
    </div>

    <div class="dropdown-footer border-top py-2 px-3 bg-light">
        <div class="d-flex justify-content-between">
            <a href="{{ route('lms.notifications.index') }}" class="text-decoration-none small">
                View All Notifications
            </a>
            @if($unreadCount > 0)
                <form action="{{ route('lms.notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none small">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<style>
    .notifications-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
    }

    .notifications-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
        font-size: 14px;
    }

    .notifications-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .action-buttons .btn i {
        font-size: 16px;
    }

    .pinned-row {
        background: #fffbeb !important;
        border-left: 3px solid #f59e0b;
    }

    .pinned-row:hover {
        background: #fef3c7 !important;
    }

    .pin-badge {
        background: #fef3c7;
        color: #b45309;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 500;
    }

    .recipient-badge {
        background: #e0e7ff;
        color: #4338ca;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 3px;
        font-weight: 500;
    }

    .comment-badge {
        background: #fef3c7;
        color: #b45309;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 500;
    }
</style>

@if (!empty($notifications) && $notifications->count() > 0)
    <div class="table-responsive">
        <table id="notifications-table" class="table table-striped notifications-table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>General</th>
                    <th>Comments</th>
                    <th>Attachments</th>
                    @if (!session('is_past_term'))
                        <th class="text-end">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($notifications as $index => $notification)
                    <tr class="notification-row {{ $notification->is_pinned ? 'pinned-row' : '' }}"
                        data-title="{{ strtolower($notification->title ?? '') }}"
                        data-general="{{ $notification->is_general ? 'yes' : 'no' }}"
                        data-attachments="{{ $notification->attachments->isNotEmpty() ? 'yes' : 'no' }}"
                        data-comments="{{ $notification->notificationComments->count() > 0 ? 'yes' : 'no' }}"
                        data-pinned="{{ $notification->is_pinned ? 'yes' : 'no' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <a href="{{ route('notification.details', $notification->id) }}" class="fw-medium text-dark text-decoration-none">
                                        @if ($notification->is_pinned)
                                            <i class="fas fa-thumbtack text-warning me-1" title="Pinned"></i>
                                        @endif
                                        {{ $notification->title ?? '' }}
                                    </a>
                                    <div class="text-muted" style="font-size: 12px;">
                                        @if ($notification->sponsorRecipients->count() > 0)
                                            {{ $notification->sponsorRecipients->count() }} recipients
                                        @else
                                            {{ $notification->recipients->count() }} recipients
                                        @endif
                                        @if ($notification->is_pinned)
                                            <span class="pin-badge ms-1"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($notification->is_general)
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Yes</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            {{ $notification->allow_comments ? 'Yes' : 'No' }}
                            @if ($notification->notificationComments->count())
                                <span class="comment-badge ms-1">
                                    {{ $notification->notificationComments->count() }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($notification->attachments->isNotEmpty())
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Yes</span>
                                <span class="text-muted small">({{ $notification->attachments->count() }})</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </td>
                        @if (!session('is_past_term'))
                            <td class="text-end">
                                <div class="action-buttons">
                                    <form action="{{ route('notification.toggle-pin', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $notification->is_pinned ? 'btn-warning' : 'btn-outline-secondary' }}"
                                            title="{{ $notification->is_pinned ? 'Unpin' : 'Pin' }} Notification">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('notification.details', $notification->id) }}"
                                        class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if ($notification->sponsorRecipients->count() > 0)
                                        <a href="{{ route('notifications.edit-sponsor-notification', $notification->id) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit Sponsor Notification">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('notifications.edit-notification', $notification->id) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit Notification">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('notification.delete-notification', $notification->id) }}"
                                        class="btn btn-sm btn-outline-danger" onclick="return confirmDeleteNotification()"
                                        title="Delete Notification">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="text-muted" id="results-info">
            Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">{{ count($notifications) }}</span> Notifications
        </div>
        <nav id="pagination-nav">
            <!-- Pagination will be inserted here by JavaScript -->
        </nav>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="bx bx-bell-off" style="font-size: 48px; opacity: 0.5;"></i>
        <p class="mt-3">No notifications found for this term.</p>
    </div>
@endif

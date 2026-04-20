@extends('layouts.master')

@section('title')
    Direct Messages
@endsection

@section('css')
    <style>
        .messaging-shell {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 24px;
        }

        .messaging-card {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .messaging-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: #fff;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .messaging-header h4,
        .online-panel h5 {
            margin: 0;
            font-weight: 600;
        }

        .messaging-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 13px;
        }

        .messaging-stats {
            display: flex;
            gap: 16px;
        }

        .messaging-stat {
            min-width: 88px;
            text-align: center;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 3px;
            padding: 10px 12px;
        }

        .messaging-stat strong {
            display: block;
            font-size: 20px;
        }

        .messaging-stat span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.88;
        }

        .online-panel {
            padding: 20px;
        }

        .helper-section {
            margin-bottom: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 16px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .helper-action {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: 100%;
        }

        .helper-action .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
        }

        .compose-modal-body {
            padding: 20px 24px;
        }

        .new-message-feedback[hidden] {
            display: none !important;
        }

        .new-message-feedback {
            margin-bottom: 16px;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .compose-helper {
            color: #6b7280;
            font-size: 12px;
            margin-top: 8px;
        }

        .recipient-search-results {
            margin-top: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #fff;
            max-height: 260px;
            overflow-y: auto;
        }

        .recipient-search-results[hidden] {
            display: none !important;
        }

        .recipient-search-state {
            padding: 14px 12px;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }

        .recipient-search-item {
            width: 100%;
            border: 0;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s ease;
        }

        .recipient-search-item:last-child {
            border-bottom: 0;
        }

        .recipient-search-item:hover {
            background: #f8fafc;
        }

        .recipient-search-avatar,
        .recipient-summary-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        }

        .recipient-search-avatar img,
        .recipient-summary-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .recipient-search-meta,
        .recipient-summary-meta {
            min-width: 0;
            flex: 1;
        }

        .recipient-search-meta strong,
        .recipient-summary-meta strong {
            display: block;
            color: #111827;
            font-size: 14px;
        }

        .recipient-search-meta span,
        .recipient-summary-meta span {
            display: block;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.45;
        }

        .recipient-search-badges,
        .recipient-summary-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .recipient-status-pill,
        .recipient-thread-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
        }

        .recipient-status-pill.online {
            background: #dcfce7;
            color: #166534;
        }

        .recipient-status-pill.offline {
            background: #f3f4f6;
            color: #4b5563;
        }

        .recipient-thread-pill {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .recipient-summary {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid #dbeafe;
            border-radius: 3px;
            background: #f8fbff;
            padding: 12px;
            margin-top: 14px;
        }

        .recipient-summary[hidden] {
            display: none !important;
        }

        .recipient-summary-action {
            flex-shrink: 0;
        }

        .compose-message-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
            color: #6b7280;
            font-size: 12px;
        }

        .compose-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .compose-actions .btn {
            min-width: 145px;
            justify-content: center;
        }

        .compose-modal-footer {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 16px 24px 20px;
            border-top: 1px solid #eef2f7;
        }

        .compose-modal-footer-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .compose-modal-footer-actions .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
        }

        .online-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .online-panel-header p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .online-note {
            background: #eef6ff;
            border: 1px solid #dbeafe;
            border-radius: 3px;
            color: #1d4ed8;
            font-size: 12px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .online-users-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .online-user-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .online-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
        }

        .online-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .online-user-meta {
            min-width: 0;
            flex: 1;
        }

        .online-user-meta strong {
            display: block;
            color: #111827;
            font-size: 14px;
        }

        .online-user-meta span {
            display: block;
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .online-user-action .btn {
            white-space: nowrap;
        }

        .conversation-tabs {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 28px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .conversation-tab {
            color: #6b7280;
            text-decoration: none;
            padding: 14px 0;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            font-size: 13px;
            margin-right: 20px;
        }

        .conversation-tab.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .conversation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .conversation-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 28px;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
        }

        .conversation-row:hover {
            background: #f9fafb;
        }

        .conversation-row.unread {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
        }

        .conversation-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .conversation-body {
            min-width: 0;
            flex: 1;
        }

        .conversation-line {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 4px;
        }

        .conversation-name {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        .conversation-time {
            color: #9ca3af;
            font-size: 12px;
            white-space: nowrap;
        }

        .conversation-preview {
            color: #6b7280;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 11px;
            color: #6b7280;
        }

        .conversation-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 0 6px;
        }

        .empty-state {
            padding: 48px 28px;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 42px;
            color: #cbd5e1;
            display: block;
            margin-bottom: 16px;
        }

        .pagination-wrap {
            padding: 16px 28px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 991px) {
            .messaging-shell {
                grid-template-columns: 1fr;
            }

            .messaging-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .compose-modal-footer-actions {
                width: 100%;
            }

            .compose-modal-footer-actions .btn {
                flex: 1 1 auto;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Staff
        @endslot
        @slot('title')
            Direct Messages
        @endslot
    @endcomponent

    @include('staff.messaging.partials.flash-alerts')

    <div class="modal fade" id="staffNewMessageModal" tabindex="-1" aria-labelledby="staffNewMessageModalLabel"
        data-bs-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffNewMessageModalLabel">New Message</h5>
                    <button type="button" class="btn-close" id="staff-close-new-message-modal" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="compose-modal-body">
                    <div class="alert alert-danger new-message-feedback" id="new-message-feedback" role="alert" hidden></div>

                    <div class="mb-3">
                        <label class="form-label mb-2" for="staff-recipient-search">Find staff member</label>
                        <input type="search" class="form-control" id="staff-recipient-search"
                            placeholder="Search by name, position, or department" autocomplete="off">
                        <div class="compose-helper" id="staff-recipient-search-help">
                            Type at least 2 characters to search by name, position, or department.
                        </div>
                        <div class="recipient-search-results" id="staff-recipient-results" hidden></div>
                    </div>

                    <div class="recipient-summary" id="staff-selected-recipient" hidden>
                        <div class="recipient-summary-avatar" id="staff-selected-avatar">SU</div>
                        <div class="recipient-summary-meta">
                            <strong id="staff-selected-name">Selected staff member</strong>
                            <span id="staff-selected-role">Staff User</span>
                            <div class="recipient-summary-badges">
                                <span class="recipient-status-pill offline" id="staff-selected-status">Offline</span>
                                <span class="recipient-thread-pill" id="staff-selected-thread" hidden>Existing conversation</span>
                            </div>
                        </div>
                        <div class="recipient-summary-action">
                            <button type="button" class="btn btn-sm btn-light" id="staff-clear-recipient">Change</button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label mb-2" for="staff-recipient-body">Message</label>
                        <textarea class="form-control" id="staff-recipient-body" rows="5" maxlength="5000"
                            placeholder="Write your message here. Leave this blank if you only want to open the conversation first."></textarea>
                        <div class="compose-message-meta">
                            <span>You can open the conversation first or send the first message from this modal.</span>
                            <span id="staff-recipient-body-count">0 / 5000 characters</span>
                        </div>
                    </div>
                </div>

                <div class="compose-modal-footer">
                    <div class="compose-modal-footer-actions">
                        <button type="button" class="btn btn-light" id="staff-cancel-new-message" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary" id="staff-open-conversation" disabled>
                            Open Conversation
                        </button>
                        <button type="button" class="btn btn-primary btn-loading" id="staff-send-message" disabled>
                            <span class="btn-text"><i class="bx bx-send me-1"></i> Send Message</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Sending...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="messaging-card mb-4">
        <div class="messaging-header">
            <div>
                <h4><i class="bx bx-chat me-2"></i>Staff Direct Messages</h4>
                <p>Internal conversations stay separate from LMS messaging and external communication channels.</p>
            </div>
            <div class="messaging-stats">
                <div class="messaging-stat">
                    <strong>{{ $unreadCount }}</strong>
                    <span>Unread</span>
                </div>
                <div class="messaging-stat">
                    <strong>{{ $conversations->total() }}</strong>
                    <span>{{ $showArchived ? 'Archived' : 'Threads' }}</span>
                </div>
                <div class="messaging-stat">
                    <strong>{{ $onlineUsersCount }}</strong>
                    <span>Online Now</span>
                </div>
            </div>
        </div>
    </div>

    <div class="helper-section">
        <div class="help-text">
            <div class="help-title">Create New Direct Message</div>
            <div class="help-content">
                Use <strong>New Message</strong> to search any active staff member, even when they are not online, and write the first message from the popup. The left panel remains focused on staff who are online now.
            </div>
        </div>
        <div class="helper-action">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#staffNewMessageModal">
                <i class="bx bx-message-square-add me-1"></i> New Message
            </button>
        </div>
    </div>

    <div class="messaging-shell">
        <div class="messaging-card">
            <div class="online-panel">
                <div class="online-panel-header">
                    <div>
                        <h5>Online Staff</h5>
                        <p>Visible while you work, quiet unless you choose to open a conversation.</p>
                    </div>
                </div>

                @if (!$launcherEnabled)
                    <div class="online-note">
                        The topbar launcher is disabled in Communications Setup. Search for any staff member above, or use the online list below when you need to start a direct message.
                    </div>
                @else
                    <div class="online-note">
                        Search any staff member above, or click an online colleague below when you need to send a message. This list still updates quietly in the background.
                    </div>
                @endif

                <div class="online-users-list">
                    @forelse ($onlineUsers as $onlineUser)
                        <div class="online-user-card">
                            <div class="online-user-avatar">
                                @if ($onlineUser->avatar)
                                    <img src="{{ str_starts_with($onlineUser->avatar, 'http') ? $onlineUser->avatar : asset('storage/' . ltrim($onlineUser->avatar, '/')) }}" alt="{{ $onlineUser->full_name }}">
                                @else
                                    {{ strtoupper(substr($onlineUser->full_name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="online-user-meta">
                                <strong>{{ $onlineUser->full_name }}</strong>
                                <span>{{ $onlineUser->position ?: 'Staff User' }}</span>
                                <span>{{ $onlineUser->department ?: 'Department not set' }}</span>
                            </div>
                            <div class="online-user-action">
                                <form action="{{ route('staff.messages.start') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="recipient_id" value="{{ $onlineUser->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bx bx-send"></i> Message
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bx bx-user-x"></i>
                            <div>No other staff users are online right now.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="messaging-card">
            <div class="conversation-tabs">
                <a href="{{ route('staff.messages.inbox') }}" class="conversation-tab {{ !$showArchived ? 'active' : '' }}">Active</a>
                <a href="{{ route('staff.messages.inbox', ['archived' => true]) }}" class="conversation-tab {{ $showArchived ? 'active' : '' }}">Archived</a>
            </div>

            @if ($conversations->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-message-square-dots"></i>
                    <div>{{ $showArchived ? 'No archived conversations yet.' : 'No conversations yet. Click New Message to search any staff member, or start from the online staff list.' }}</div>
                </div>
            @else
                <ul class="conversation-list">
                    @foreach ($conversations as $conversation)
                        @php
                            $participant = $conversation->otherParticipant;
                            $latestMessage = $conversation->latestMessage;
                        @endphp
                        <li>
                            <a href="{{ route('staff.messages.conversation', $conversation) }}" class="conversation-row {{ $conversation->has_unread ? 'unread' : '' }}">
                                <div class="conversation-avatar">
                                    @if ($participant->avatar)
                                        <img src="{{ str_starts_with($participant->avatar, 'http') ? $participant->avatar : asset('storage/' . ltrim($participant->avatar, '/')) }}" alt="{{ $participant->full_name }}">
                                    @else
                                        {{ strtoupper(substr($participant->full_name, 0, 2)) }}
                                    @endif
                                </div>
                                <div class="conversation-body">
                                    <div class="conversation-line">
                                        <span class="conversation-name">{{ $participant->full_name }}</span>
                                        <span class="conversation-time">{{ optional($conversation->last_message_at)->diffForHumans() ?: 'New' }}</span>
                                    </div>
                                    <div class="conversation-preview">
                                        {{ $latestMessage?->body ?: 'No messages yet. Open this thread to start the conversation.' }}
                                    </div>
                                    <div class="conversation-meta">
                                        <span>{{ $participant->position ?: 'Staff User' }}</span>
                                        <span>{{ $participant->department ?: 'Department not set' }}</span>
                                        @if ($conversation->has_unread)
                                            <span class="conversation-badge">{{ $conversation->unread_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="pagination-wrap">
                    {{ $conversations->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newMessageModal = document.getElementById('staffNewMessageModal');
            const searchInput = document.getElementById('staff-recipient-search');
            const searchResults = document.getElementById('staff-recipient-results');
            const searchHelp = document.getElementById('staff-recipient-search-help');
            const bodyInput = document.getElementById('staff-recipient-body');
            const bodyCount = document.getElementById('staff-recipient-body-count');
            const openButton = document.getElementById('staff-open-conversation');
            const sendButton = document.getElementById('staff-send-message');
            const feedback = document.getElementById('new-message-feedback');
            const selectedRecipientCard = document.getElementById('staff-selected-recipient');
            const selectedAvatar = document.getElementById('staff-selected-avatar');
            const selectedName = document.getElementById('staff-selected-name');
            const selectedRole = document.getElementById('staff-selected-role');
            const selectedStatus = document.getElementById('staff-selected-status');
            const selectedThread = document.getElementById('staff-selected-thread');
            const clearRecipientButton = document.getElementById('staff-clear-recipient');
            const closeModalButton = document.getElementById('staff-close-new-message-modal');
            const cancelModalButton = document.getElementById('staff-cancel-new-message');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const minimumQueryLength = 2;
            const routes = {
                recipients: @json(route('staff.messages.recipients', [], false)),
                startConversation: @json(route('staff.messages.start', [], false)),
            };

            if (!searchInput || !searchResults || !bodyInput || !openButton || !sendButton) {
                return;
            }

            let selectedRecipient = null;
            let searchTimer = null;
            let searchAbortController = null;
            let requestInFlight = false;
            const currentResults = new Map();

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function initialsFor(name) {
                return String(name || 'SU')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part.charAt(0).toUpperCase())
                    .join('') || 'SU';
            }

            function avatarMarkup(recipient) {
                if (recipient.avatar_url) {
                    return `<img src="${escapeHtml(recipient.avatar_url)}" alt="${escapeHtml(recipient.name)}">`;
                }

                return escapeHtml(initialsFor(recipient.name));
            }

            function roleText(recipient) {
                const parts = [recipient.position, recipient.department].filter(Boolean);

                return parts.length ? parts.join(' | ') : 'Staff User';
            }

            function statusText(recipient) {
                if (recipient.is_online) {
                    return 'Online now';
                }

                return recipient.last_seen_label && recipient.last_seen_label !== 'Offline' ?
                    `Last seen ${recipient.last_seen_label}` :
                    'Offline';
            }

            function showFeedback(message) {
                if (!feedback) {
                    return;
                }

                feedback.hidden = false;
                feedback.textContent = message;
            }

            function hideFeedback() {
                if (!feedback) {
                    return;
                }

                feedback.hidden = true;
                feedback.textContent = '';
            }

            function setLoadingState(button, activeText, isLoading) {
                if (!button) {
                    return;
                }

                const buttonText = button.querySelector('.btn-text');
                const buttonSpinner = button.querySelector('.btn-spinner');

                if (button.classList.contains('btn-loading') && buttonText && buttonSpinner) {
                    button.classList.toggle('loading', isLoading);
                    return;
                }

                if (!button.dataset.defaultText) {
                    button.dataset.defaultText = button.textContent.trim();
                }

                button.textContent = isLoading ? activeText : button.dataset.defaultText;
            }

            function updateDismissControls() {
                [closeModalButton, cancelModalButton].forEach((button) => {
                    if (!button) {
                        return;
                    }

                    button.disabled = requestInFlight;
                    button.setAttribute('aria-disabled', requestInFlight ? 'true' : 'false');
                });
            }

            function updateButtons() {
                const hasRecipient = Boolean(selectedRecipient);
                const hasBody = bodyInput.value.trim().length > 0;

                openButton.disabled = requestInFlight || !hasRecipient;
                sendButton.disabled = requestInFlight || !hasRecipient || !hasBody;
                updateDismissControls();
            }

            function updateBodyCount() {
                bodyCount.textContent = `${bodyInput.value.length} / 5000 characters`;
                updateButtons();
            }

            function clearSearchResults() {
                currentResults.clear();
                searchResults.hidden = true;
                searchResults.innerHTML = '';
            }

            function setSelectedRecipient(recipient) {
                const isDifferentRecipient = selectedRecipient && String(selectedRecipient.id) !== String(recipient.id);

                if (isDifferentRecipient) {
                    bodyInput.value = '';
                    updateBodyCount();
                }

                selectedRecipient = recipient;

                selectedAvatar.innerHTML = avatarMarkup(recipient);
                selectedName.textContent = recipient.name;
                selectedRole.textContent = roleText(recipient);
                selectedStatus.textContent = statusText(recipient);
                selectedStatus.className = `recipient-status-pill ${recipient.is_online ? 'online' : 'offline'}`;

                if (recipient.conversation_id) {
                    selectedThread.hidden = false;
                    selectedThread.textContent = 'Existing conversation';
                } else {
                    selectedThread.hidden = true;
                    selectedThread.textContent = '';
                }

                selectedRecipientCard.hidden = false;
                hideFeedback();
                updateButtons();
            }

            function parseErrorMessage(error) {
                const validationErrors = error?.data?.errors;
                if (validationErrors && typeof validationErrors === 'object') {
                    const firstField = Object.keys(validationErrors)[0];
                    const firstMessage = validationErrors[firstField]?.[0];

                    if (firstMessage) {
                        return firstMessage;
                    }
                }

                return error?.message || 'Unable to complete that action right now.';
            }

            function resetComposer() {
                selectedRecipient = null;
                requestInFlight = false;
                if (searchAbortController) {
                    searchAbortController.abort();
                    searchAbortController = null;
                }

                searchInput.value = '';
                bodyInput.value = '';
                selectedRecipientCard.hidden = true;
                hideFeedback();
                clearSearchResults();
                searchHelp.textContent = 'Type at least 2 characters to search by name, position, or department.';
                setLoadingState(openButton, '', false);
                setLoadingState(sendButton, '', false);
                updateBodyCount();
            }

            async function parseJsonResponse(response, url) {
                const contentType = String(response.headers.get('content-type') || '').toLowerCase();

                if (!contentType.includes('application/json')) {
                    const bodyPreview = (await response.text()).slice(0, 200);
                    const error = new Error(`Expected JSON response from ${url}.`);
                    error.status = response.status;
                    error.bodyPreview = bodyPreview;
                    throw error;
                }

                return response.json();
            }

            async function fetchJson(url, options = {}) {
                const {
                    headers: optionHeaders = {},
                    ...requestOptions
                } = options;

                const response = await fetch(url, {
                    credentials: 'same-origin',
                    ...requestOptions,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        ...optionHeaders,
                    },
                });

                const data = await parseJsonResponse(response, url);

                if (!response.ok) {
                    const error = new Error(data?.message || `Request failed with status ${response.status}.`);
                    error.status = response.status;
                    error.data = data;
                    throw error;
                }

                return data;
            }

            function renderSearchResults(users, query) {
                currentResults.clear();

                if (query.length < minimumQueryLength) {
                    clearSearchResults();
                    return;
                }

                if (!Array.isArray(users) || users.length === 0) {
                    searchResults.hidden = false;
                    searchResults.innerHTML = '<div class="recipient-search-state">No matching staff members found.</div>';
                    return;
                }

                searchResults.hidden = false;
                searchResults.innerHTML = users.map((recipient) => {
                    currentResults.set(String(recipient.id), recipient);

                    const badges = `
                        <div class="recipient-search-badges">
                            <span class="recipient-status-pill ${recipient.is_online ? 'online' : 'offline'}">${escapeHtml(statusText(recipient))}</span>
                            ${recipient.conversation_id ? '<span class="recipient-thread-pill">Existing conversation</span>' : ''}
                        </div>
                    `;

                    return `
                        <button type="button" class="recipient-search-item" data-recipient-id="${escapeHtml(recipient.id)}">
                            <div class="recipient-search-avatar">${avatarMarkup(recipient)}</div>
                            <div class="recipient-search-meta">
                                <strong>${escapeHtml(recipient.name)}</strong>
                                <span>${escapeHtml(roleText(recipient))}</span>
                                ${badges}
                            </div>
                        </button>
                    `;
                }).join('');
            }

            async function searchRecipients(query) {
                const trimmedQuery = query.trim();

                if (trimmedQuery.length < minimumQueryLength) {
                    clearSearchResults();
                    return;
                }

                if (searchAbortController) {
                    searchAbortController.abort();
                }

                searchAbortController = new AbortController();
                searchResults.hidden = false;
                searchResults.innerHTML = '<div class="recipient-search-state">Searching staff...</div>';

                try {
                    const url = new URL(routes.recipients, window.location.origin);
                    url.searchParams.set('query', trimmedQuery);

                    const data = await fetchJson(url.toString(), {
                        signal: searchAbortController.signal,
                    });

                    renderSearchResults(data.users || [], trimmedQuery);
                    hideFeedback();
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    searchResults.hidden = false;
                    searchResults.innerHTML = '<div class="recipient-search-state">Unable to search staff right now.</div>';
                    showFeedback(parseErrorMessage(error));
                }
            }

            async function submitConversation(sendFirstMessage) {
                if (!selectedRecipient || requestInFlight) {
                    return;
                }

                hideFeedback();
                requestInFlight = true;
                updateButtons();
                setLoadingState(openButton, 'Opening...', !sendFirstMessage);
                setLoadingState(sendButton, 'Sending...', sendFirstMessage);

                try {
                    const payload = {
                        recipient_id: selectedRecipient.id,
                    };

                    if (sendFirstMessage) {
                        payload.body = bodyInput.value;
                    }

                    const data = await fetchJson(routes.startConversation, {
                        method: 'POST',
                        body: JSON.stringify(payload),
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });

                    if (data?.redirect_url) {
                        window.location.assign(data.redirect_url);
                    }
                } catch (error) {
                    requestInFlight = false;
                    setLoadingState(openButton, '', false);
                    setLoadingState(sendButton, '', false);
                    updateButtons();
                    showFeedback(parseErrorMessage(error));
                }
            }

            searchInput.addEventListener('input', function() {
                window.clearTimeout(searchTimer);
                const query = this.value.trim();

                if (query.length < minimumQueryLength) {
                    clearSearchResults();
                    searchHelp.textContent = 'Type at least 2 characters to search by name, position, or department.';
                    return;
                }

                searchHelp.textContent = 'Select a staff member to open the conversation or send the first message.';
                searchTimer = window.setTimeout(() => {
                    searchRecipients(query);
                }, 250);
            });

            searchResults.addEventListener('click', function(event) {
                const button = event.target.closest('[data-recipient-id]');
                if (!button) {
                    return;
                }

                const recipient = currentResults.get(button.getAttribute('data-recipient-id'));
                if (!recipient) {
                    return;
                }

                setSelectedRecipient(recipient);
                clearSearchResults();
                searchInput.value = '';
                searchHelp.textContent = 'Recipient selected. Search again anytime to choose someone else.';
            });

            clearRecipientButton?.addEventListener('click', function() {
                selectedRecipient = null;
                bodyInput.value = '';
                selectedRecipientCard.hidden = true;
                searchInput.focus();
                searchHelp.textContent = 'Type at least 2 characters to search by name, position, or department.';
                hideFeedback();
                updateBodyCount();
                updateButtons();
            });

            bodyInput.addEventListener('input', updateBodyCount);
            openButton.addEventListener('click', function() {
                submitConversation(false);
            });
            sendButton.addEventListener('click', function() {
                submitConversation(true);
            });

            document.addEventListener('click', function(event) {
                if (!event.target.closest('#staffNewMessageModal')) {
                    clearSearchResults();
                }
            });

            if (newMessageModal) {
                newMessageModal.addEventListener('hide.bs.modal', function(event) {
                    if (!requestInFlight) {
                        return;
                    }

                    event.preventDefault();
                });

                newMessageModal.addEventListener('shown.bs.modal', function() {
                    searchInput.focus();
                });

                newMessageModal.addEventListener('hidden.bs.modal', function() {
                    resetComposer();
                });
            }

            updateBodyCount();
        });
    </script>
@endsection

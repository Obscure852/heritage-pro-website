@extends('layouts.master')

@section('title')
    {{ $otherParticipant->full_name }}
@endsection

@section('css')
    <style>
        .staff-chat-card {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }

        .staff-chat-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: #fff;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }

        .staff-chat-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .staff-chat-back {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .staff-chat-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-weight: 700;
        }

        .staff-chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .staff-chat-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .staff-chat-header small {
            display: block;
            margin-top: 4px;
            opacity: 0.88;
        }

        .staff-online-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .staff-online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #86efac;
        }

        .staff-online-indicator.offline .staff-online-dot {
            background: rgba(255, 255, 255, 0.45);
        }

        .staff-chat-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .staff-chat-actions .btn {
            border: none;
        }

        .staff-chat-body {
            flex: 1;
            padding: 24px;
            background: #f8fafc;
            overflow-y: auto;
            position: relative;
        }

        .staff-chat-history-note {
            background: #eff6ff;
            border-bottom: 1px solid #dbeafe;
            color: #1d4ed8;
            padding: 12px 24px;
            font-size: 13px;
        }

        .staff-chat-history-note a {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
        }

        .staff-message-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .staff-message-row.sent {
            flex-direction: row-reverse;
        }

        .staff-message-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #10b981;
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .staff-message-row.sent .staff-message-avatar {
            background: #2563eb;
        }

        .staff-message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .staff-message-content {
            max-width: 68%;
        }

        .staff-message-bubble {
            background: #fff;
            padding: 6px 11px;
            border-radius: 3px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            color: #111827;
            line-height: 1.35;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .staff-message-row.sent .staff-message-bubble {
            background: #2563eb;
            color: #fff;
        }

        .staff-message-meta {
            margin-top: 6px;
            font-size: 11px;
            color: #9ca3af;
            padding: 0 4px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .staff-message-row.sent .staff-message-meta {
            justify-content: flex-end;
            text-align: right;
        }

        .staff-message-status {
            font-weight: 600;
            color: #94a3b8;
        }

        .staff-message-status.seen {
            color: #2563eb;
        }

        .staff-chat-empty {
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #6b7280;
        }

        .staff-chat-live-indicator {
            position: sticky;
            bottom: 12px;
            display: flex;
            justify-content: center;
            padding-top: 12px;
        }

        .staff-chat-live-indicator[hidden] {
            display: none !important;
        }

        .staff-chat-live-indicator .btn {
            border-radius: 999px;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.18);
        }

        .staff-chat-composer {
            border-top: 1px solid #e5e7eb;
            background: #fff;
            padding: 18px 24px;
        }

        .staff-chat-composer textarea {
            min-height: 110px;
            resize: vertical;
        }

        .staff-chat-inline-feedback[hidden] {
            display: none !important;
        }

        .staff-chat-inline-feedback {
            margin-bottom: 16px;
        }

        .staff-chat-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .staff-chat-composer .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .staff-chat-composer .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: #fff;
        }

        .staff-chat-composer .btn-loading.loading .btn-text {
            display: none;
        }

        .staff-chat-composer .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .staff-chat-composer .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Back
        @endslot
        @slot('li_1_url')
            {{ route('staff.messages.inbox') }}
        @endslot
        @slot('title')
            Direct Message
        @endslot
    @endcomponent

    @include('staff.messaging.partials.flash-alerts')

    <div class="staff-chat-card">
        <div class="staff-chat-header">
            <div class="staff-chat-header-left">
                <a href="{{ route('staff.messages.inbox') }}" class="staff-chat-back">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div class="staff-chat-avatar">
                    @if ($otherParticipant->avatar)
                        <img src="{{ str_starts_with($otherParticipant->avatar, 'http') ? $otherParticipant->avatar : asset('storage/' . ltrim($otherParticipant->avatar, '/')) }}" alt="{{ $otherParticipant->full_name }}">
                    @else
                        {{ strtoupper(substr($otherParticipant->full_name, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <h5>{{ $otherParticipant->full_name }}</h5>
                    <small>
                        {{ $otherParticipant->position ?: 'Staff User' }}
                        <span class="staff-online-indicator ms-2 {{ $participantPresence['is_online'] ? 'online' : 'offline' }}"
                            id="staffParticipantStatus">
                            <span class="staff-online-dot"></span>
                            <span id="staffParticipantStatusText">{{ $participantPresence['last_seen_label'] }}</span>
                        </span>
                    </small>
                </div>
            </div>

            <div class="staff-chat-actions">
                @if ($conversation->isArchivedFor(auth()->user()))
                    <form action="{{ route('staff.messages.unarchive', $conversation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="bx bx-reset me-1"></i> Restore
                        </button>
                    </form>
                @else
                    <form action="{{ route('staff.messages.archive', $conversation) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="bx bx-archive me-1"></i> Archive
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (!$isLiveMode)
            <div class="staff-chat-history-note">
                Live updates appear on the latest conversation page only.
                <a href="{{ route('staff.messages.conversation', $conversation) }}">Back to latest</a>
            </div>
        @endif

        <div class="staff-chat-body" id="staffChatMessages">
            <div id="staffChatEmptyState" @if (!$messages->isEmpty()) hidden @endif>
                <div class="staff-chat-empty">
                    <div>
                        <i class="bx bx-message-square-dots d-block mb-3" style="font-size: 42px; color: #cbd5e1;"></i>
                        No messages yet. Send the first message when you are ready.
                    </div>
                </div>
            </div>

            <div id="staffChatMessageList" @if ($messages->isEmpty()) hidden @endif>
                @foreach ($messages as $message)
                    @php
                        $isSentByCurrentUser = $message->sender_id === auth()->id();
                        $isSeenByOtherParticipant = $isSentByCurrentUser && $message->id <= $otherParticipantLastReadMessageId;
                    @endphp
                    <div class="staff-message-row {{ $isSentByCurrentUser ? 'sent' : '' }}" data-message-id="{{ $message->id }}">
                        <div class="staff-message-avatar">
                            @if ($message->sender && $message->sender->avatar)
                                <img src="{{ str_starts_with($message->sender->avatar, 'http') ? $message->sender->avatar : asset('storage/' . ltrim($message->sender->avatar, '/')) }}" alt="{{ $message->sender->full_name }}">
                            @else
                                {{ strtoupper(substr($message->sender?->full_name ?? 'SU', 0, 2)) }}
                            @endif
                        </div>
                        <div class="staff-message-content">
                            <div class="staff-message-bubble">
                                {{ $message->body }}
                            </div>
                            <div class="staff-message-meta">
                                <span>{{ $message->created_at->format('M d, Y H:i') }}</span>
                                @if ($isSentByCurrentUser)
                                    <span class="staff-message-status {{ $isSeenByOtherParticipant ? 'seen' : '' }}"
                                        data-message-status
                                        data-receipt-status="{{ $isSeenByOtherParticipant ? 'seen' : 'sent' }}">
                                        {{ $isSeenByOtherParticipant ? 'Seen' : 'Sent' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="staff-chat-live-indicator" id="staffChatNewMessagesIndicator" hidden>
                <button type="button" class="btn btn-primary btn-sm" id="staffChatJumpToLatest">
                    <i class="bx bx-down-arrow-alt me-1"></i>
                    <span id="staffChatNewMessagesLabel">New messages</span>
                </button>
            </div>
        </div>

        @if ($messages->hasPages())
            <div class="staff-chat-pagination">
                {{ $messages->onEachSide(1)->links() }}
            </div>
        @endif

        <div class="staff-chat-composer">
            <form action="{{ route('staff.messages.reply', $conversation) }}" method="POST" id="staffMessageReplyForm">
                @csrf
                <div class="alert alert-danger staff-chat-inline-feedback" id="staffChatInlineFeedback" role="alert" hidden></div>
                <div class="mb-3">
                    <label for="messageBody" class="form-label">Message</label>
                    <textarea name="body" id="messageBody" class="form-control" maxlength="5000" placeholder="Write your message here..." required>{{ old('body') }}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="bx bx-send"></i> Send Message</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('staffChatMessages');
            const messageList = document.getElementById('staffChatMessageList');
            const emptyState = document.getElementById('staffChatEmptyState');
            const newMessagesIndicator = document.getElementById('staffChatNewMessagesIndicator');
            const newMessagesLabel = document.getElementById('staffChatNewMessagesLabel');
            const jumpToLatestButton = document.getElementById('staffChatJumpToLatest');
            const replyForm = document.getElementById('staffMessageReplyForm');
            const messageBody = document.getElementById('messageBody');
            const inlineFeedback = document.getElementById('staffChatInlineFeedback');
            const participantStatus = document.getElementById('staffParticipantStatus');
            const participantStatusText = document.getElementById('staffParticipantStatusText');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const liveChatConfig = {
                isLiveMode: @json($isLiveMode),
                conversationId: @json($conversation->id),
                lastRenderedMessageId: @json($lastRenderedMessageId),
                otherParticipantLastReadMessageId: @json($otherParticipantLastReadMessageId),
                updatesRoute: @json(route('staff.messages.updates', $conversation, false)),
                replyRoute: @json(route('staff.messages.reply', $conversation, false)),
                pollSeconds: @json($conversationPollSeconds),
            };

            let lastRenderedMessageId = Number(liveChatConfig.lastRenderedMessageId || 0);
            let otherParticipantLastReadMessageId = Number(liveChatConfig.otherParticipantLastReadMessageId || 0);
            let liveRefreshTimer = null;
            let unseenIncomingCount = 0;
            let isSendingReply = false;
            let refreshInFlight = false;
            let liveUpdatesStopped = !liveChatConfig.isLiveMode;
            const renderedMessageIds = new Set(
                Array.from(document.querySelectorAll('[data-message-id]')).map((element) => Number(element.dataset.messageId))
            );

            function dispatchMessagingRefresh() {
                window.dispatchEvent(new CustomEvent('staff-messaging:refresh'));
            }

            function showInlineFeedback(message) {
                if (!inlineFeedback) {
                    return;
                }

                inlineFeedback.hidden = false;
                inlineFeedback.textContent = message;
            }

            function hideInlineFeedback() {
                if (!inlineFeedback) {
                    return;
                }

                inlineFeedback.hidden = true;
                inlineFeedback.textContent = '';
            }

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

            function createRequestError(message, details = {}) {
                const error = new Error(message);
                Object.assign(error, details);

                return error;
            }

            async function parseJsonResponse(response, url) {
                const contentType = String(response.headers.get('content-type') || '').toLowerCase();

                if (!contentType.includes('application/json')) {
                    const bodyPreview = (await response.text()).slice(0, 200);

                    throw createRequestError(
                        `Expected JSON response from ${url} but received ${contentType || 'an unknown content type'}.`,
                        {
                            status: response.status,
                            redirected: response.redirected,
                            url: response.url || url,
                            bodyPreview,
                        }
                    );
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

                if (response.redirected) {
                    throw createRequestError(`Request to ${url} was redirected to ${response.url || url}.`, {
                        status: response.status,
                        redirected: true,
                        url: response.url || url,
                    });
                }

                const data = await parseJsonResponse(response, url);

                if (!response.ok) {
                    throw createRequestError(
                        data?.message || data?.error?.message || `Request to ${url} failed with status ${response.status}.`,
                        {
                            status: response.status,
                            redirected: false,
                            url: response.url || url,
                            data,
                        }
                    );
                }

                return data;
            }

            function logConversationError(context, error) {
                console.error(context, {
                    message: error?.message || 'Unknown error',
                    status: error?.status ?? null,
                    redirected: Boolean(error?.redirected),
                    url: error?.url || null,
                    response: error?.data ?? error?.bodyPreview ?? null,
                });
            }

            function setLoadingState(button, activeText, isLoading) {
                if (!button) {
                    return;
                }

                const buttonText = button.querySelector('.btn-text');
                const buttonSpinner = button.querySelector('.btn-spinner');

                if (button.classList.contains('btn-loading') && buttonText && buttonSpinner) {
                    button.classList.toggle('loading', isLoading);
                    button.disabled = isLoading;
                    return;
                }

                if (!button.dataset.defaultText) {
                    button.dataset.defaultText = button.textContent.trim();
                }

                button.textContent = isLoading ? activeText : button.dataset.defaultText;
                button.disabled = isLoading;
            }

            function scrollToLatest() {
                if (!chatMessages) {
                    return;
                }

                chatMessages.scrollTop = chatMessages.scrollHeight;
                unseenIncomingCount = 0;
                if (newMessagesIndicator) {
                    newMessagesIndicator.hidden = true;
                }
            }

            function isNearBottom() {
                if (!chatMessages) {
                    return true;
                }

                return (chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight) <= 120;
            }

            function updateNewMessagesIndicator() {
                if (!newMessagesIndicator || !newMessagesLabel) {
                    return;
                }

                if (unseenIncomingCount <= 0) {
                    newMessagesIndicator.hidden = true;
                    newMessagesLabel.textContent = 'New messages';
                    return;
                }

                newMessagesIndicator.hidden = false;
                newMessagesLabel.textContent = unseenIncomingCount === 1
                    ? '1 new message'
                    : `${unseenIncomingCount} new messages`;
            }

            function avatarMarkup(message) {
                if (message.sender_avatar_url) {
                    return `<img src="${escapeHtml(message.sender_avatar_url)}" alt="${escapeHtml(message.sender_name)}">`;
                }

                return escapeHtml(initialsFor(message.sender_name));
            }

            function isOutgoingMessageSeen(messageId) {
                return otherParticipantLastReadMessageId > 0 && Number(messageId) <= otherParticipantLastReadMessageId;
            }

            function receiptLabelForMessage(messageId) {
                return isOutgoingMessageSeen(messageId) ? 'Seen' : 'Sent';
            }

            function receiptMarkupForMessage(message) {
                if (!message.is_sent_by_current_user) {
                    return '';
                }

                const receiptLabel = receiptLabelForMessage(message.id);
                const receiptStatusClass = receiptLabel === 'Seen' ? 'seen' : '';
                const receiptStatusValue = receiptLabel === 'Seen' ? 'seen' : 'sent';

                return `<span class="staff-message-status ${receiptStatusClass}" data-message-status data-receipt-status="${receiptStatusValue}">${receiptLabel}</span>`;
            }

            function renderMessage(message) {
                const messageRow = document.createElement('div');
                messageRow.className = `staff-message-row ${message.is_sent_by_current_user ? 'sent' : ''}`;
                messageRow.dataset.messageId = String(message.id);
                messageRow.innerHTML = `
                    <div class="staff-message-avatar">
                        ${avatarMarkup(message)}
                    </div>
                    <div class="staff-message-content">
                        <div class="staff-message-bubble">${escapeHtml(message.body)}</div>
                        <div class="staff-message-meta">
                            <span>${escapeHtml(message.created_at_label || '')}</span>
                            ${receiptMarkupForMessage(message)}
                        </div>
                    </div>
                `;

                return messageRow;
            }

            function applySeenState(lastReadMessageId) {
                const resolvedLastReadMessageId = Math.max(Number(lastReadMessageId) || 0, 0);

                if (resolvedLastReadMessageId < otherParticipantLastReadMessageId) {
                    return;
                }

                otherParticipantLastReadMessageId = resolvedLastReadMessageId;

                document.querySelectorAll('.staff-message-row.sent[data-message-id]').forEach(function(messageRow) {
                    const messageId = Number(messageRow.dataset.messageId || 0);
                    const statusElement = messageRow.querySelector('[data-message-status]');

                    if (!messageId || !statusElement) {
                        return;
                    }

                    const receiptLabel = receiptLabelForMessage(messageId);
                    const isSeen = receiptLabel === 'Seen';

                    statusElement.textContent = receiptLabel;
                    statusElement.dataset.receiptStatus = isSeen ? 'seen' : 'sent';
                    statusElement.classList.toggle('seen', isSeen);
                });
            }

            function appendMessages(messages, options = {}) {
                if (!messageList || !Array.isArray(messages) || messages.length === 0) {
                    return 0;
                }

                const nearBottomBeforeAppend = options.forceScroll || isNearBottom();
                let appendedCount = 0;
                let incomingCount = 0;

                messages.forEach((message) => {
                    const messageId = Number(message.id);
                    if (!messageId || renderedMessageIds.has(messageId)) {
                        return;
                    }

                    renderedMessageIds.add(messageId);
                    lastRenderedMessageId = Math.max(lastRenderedMessageId, messageId);
                    messageList.appendChild(renderMessage(message));
                    appendedCount += 1;

                    if (!message.is_sent_by_current_user) {
                        incomingCount += 1;
                    }
                });

                if (appendedCount === 0) {
                    return 0;
                }

                if (emptyState) {
                    emptyState.hidden = true;
                }

                messageList.hidden = false;

                if (nearBottomBeforeAppend) {
                    scrollToLatest();
                } else if (incomingCount > 0) {
                    unseenIncomingCount += incomingCount;
                    updateNewMessagesIndicator();
                }

                return appendedCount;
            }

            function updateParticipantPresence(isOnline, label) {
                if (!participantStatus || !participantStatusText) {
                    return;
                }

                participantStatus.classList.toggle('online', Boolean(isOnline));
                participantStatus.classList.toggle('offline', !Boolean(isOnline));
                participantStatusText.textContent = label || (isOnline ? 'Active now' : 'Offline');
            }

            function stopLiveUpdates() {
                liveUpdatesStopped = true;
                if (liveRefreshTimer) {
                    window.clearInterval(liveRefreshTimer);
                    liveRefreshTimer = null;
                }
            }

            async function refreshConversationUpdates() {
                if (!liveChatConfig.isLiveMode || liveUpdatesStopped || document.hidden || refreshInFlight) {
                    return;
                }

                refreshInFlight = true;

                try {
                    const url = new URL(liveChatConfig.updatesRoute, window.location.origin);
                    if (lastRenderedMessageId > 0) {
                        url.searchParams.set('after_message_id', String(lastRenderedMessageId));
                    }

                    const data = await fetchJson(url.toString());
                    const appendedCount = appendMessages(data.messages || []);

                    if (data.latest_message_id) {
                        lastRenderedMessageId = Math.max(lastRenderedMessageId, Number(data.latest_message_id));
                    }

                    updateParticipantPresence(data.participant_online, data.participant_last_seen_label);
                    applySeenState(data.other_participant_last_read_message_id);
                    if (appendedCount > 0) {
                        dispatchMessagingRefresh();
                    }
                } catch (error) {
                    if (error.status === 403 || error.status === 404) {
                        stopLiveUpdates();
                        return;
                    }

                    logConversationError('Conversation live refresh failed.', error);
                } finally {
                    refreshInFlight = false;
                }
            }

            function scheduleLiveUpdates() {
                if (!liveChatConfig.isLiveMode || liveUpdatesStopped) {
                    return;
                }

                if (liveRefreshTimer) {
                    window.clearInterval(liveRefreshTimer);
                }

                liveRefreshTimer = window.setInterval(function() {
                    refreshConversationUpdates();
                }, Math.max(Number(liveChatConfig.pollSeconds) || 5, 3) * 1000);
            }

            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
                applySeenState(otherParticipantLastReadMessageId);

                chatMessages.addEventListener('scroll', function() {
                    if (isNearBottom()) {
                        unseenIncomingCount = 0;
                        updateNewMessagesIndicator();
                    }
                });
            }

            jumpToLatestButton?.addEventListener('click', function() {
                scrollToLatest();
            });

            if (liveChatConfig.isLiveMode) {
                scheduleLiveUpdates();

                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        refreshConversationUpdates();
                    }
                });

                window.addEventListener('focus', function() {
                    refreshConversationUpdates();
                });
            }

            if (replyForm) {
                replyForm.addEventListener('submit', async function(event) {
                    if (!replyForm.checkValidity()) {
                        return;
                    }

                    const submitBtn = replyForm.querySelector('button[type="submit"].btn-loading');
                    if (!liveChatConfig.isLiveMode) {
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                        return;
                    }

                    event.preventDefault();

                    if (isSendingReply) {
                        return;
                    }

                    hideInlineFeedback();
                    isSendingReply = true;
                    if (submitBtn) {
                        setLoadingState(submitBtn, 'Sending...', true);
                    }

                    try {
                        const data = await fetchJson(liveChatConfig.replyRoute, {
                            method: 'POST',
                            body: JSON.stringify({
                                body: messageBody.value,
                            }),
                            headers: {
                                'Content-Type': 'application/json',
                            },
                        });

                        if (data.message) {
                            appendMessages([data.message], {
                                forceScroll: true,
                            });
                            messageBody.value = '';
                            lastRenderedMessageId = Math.max(lastRenderedMessageId, Number(data.latest_message_id || data.message.id || 0));
                            applySeenState(data.other_participant_last_read_message_id);
                            dispatchMessagingRefresh();
                        }
                    } catch (error) {
                        showInlineFeedback(parseErrorMessage(error));
                    } finally {
                        isSendingReply = false;
                        if (submitBtn) {
                            setLoadingState(submitBtn, 'Sending...', false);
                        }
                    }
                });
            }
        });
    </script>
@endsection

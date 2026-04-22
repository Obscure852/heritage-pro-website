@extends('layouts.crm')

@php
    $currentUser = auth()->user();
    $selectedCounterpart = $selectedThread->isDirectMessage() ? $selectedThread->counterpartFor($currentUser) : null;
    $selectedParticipants = $selectedThread->isGroupChat() ? $selectedThread->otherParticipantsFor($currentUser) : collect();
    $selectedCampaign = $selectedThread->campaigns->sortByDesc('id')->first();
    $participantSummary = function ($thread) use ($currentUser) {
        $names = $thread->otherParticipantsFor($currentUser)->pluck('name')->filter()->values();

        if ($names->isEmpty()) {
            return 'No additional members yet.';
        }

        if ($names->count() <= 3) {
            return $names->join(', ');
        }

        return $names->take(3)->join(', ') . ' +' . ($names->count() - 3) . ' more';
    };
    $selectedLabel = $selectedThread->isCompanyChat()
        ? 'Company Chat'
        : ($selectedThread->isGroupChat()
            ? $selectedThread->subject
            : ($selectedCounterpart?->name ?: $selectedThread->subject));
@endphp

@section('title', $selectedLabel . ' - App Messaging')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Slack-like internal messaging with company chat, direct messages, reusable group chats, attachment sharing, and in-app previews.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $directThreads->count(), 'label' => 'DIRECT THREADS'])
    @include('crm.partials.header-stat', ['value' => $groupThreads->count(), 'label' => 'GROUP THREADS'])
    @include('crm.partials.header-stat', ['value' => $selectedThread->messages->count(), 'label' => 'OPEN THREAD MESSAGES'])
    @include('crm.partials.header-stat', ['value' => $recentFiles->count(), 'label' => 'RECENT FILES'])
@endsection

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.app.company-chat') }}" class="btn crm-app-btn crm-app-btn-company">
            <i class="bx bx-buildings"></i> Company chat
        </a>
        <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn crm-app-btn crm-app-btn-direct">
            <i class="bx bx-message-square-dots"></i> New DM
        </a>
        <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
            <i class="bx bx-group"></i> New group chat
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'app'])

    <div class="crm-app-shell">
        <aside class="crm-app-sidebar">
            <section class="crm-app-sidebar-section">
                <div>
                    <p class="crm-kicker">Compose</p>
                    <h3>Switch messaging mode</h3>
                </div>
                <div class="crm-app-thread-list">
                    <a href="{{ route('crm.discussions.app.direct.create') }}" class="crm-app-thread-link crm-app-thread-link-direct">
                        <span class="crm-app-thread-icon crm-app-thread-icon-direct"><i class="bx bx-message-square-dots"></i></span>
                        <div class="crm-app-thread-copy">
                            <strong>Direct message</strong>
                            <span>Start or resume a one-to-one app conversation.</span>
                        </div>
                    </a>
                    <a href="{{ route('crm.discussions.app.bulk.create') }}" class="crm-app-thread-link crm-app-thread-link-group">
                        <span class="crm-app-thread-icon crm-app-thread-icon-group"><i class="bx bx-group"></i></span>
                        <div class="crm-app-thread-copy">
                            <strong>Group chat</strong>
                            <span>Create a multi-person thread from custom contacts and departments.</span>
                        </div>
                    </a>
                </div>
            </section>

            <section class="crm-app-sidebar-section">
                <div>
                    <p class="crm-kicker">Workspace</p>
                    <h3>Internal channels</h3>
                </div>
                <div class="crm-app-thread-list">
                    @php
                        $companyParticipant = $companyChatThread->participants->firstWhere('user_id', $currentUser?->id);
                        $companyUnread = $companyChatThread->last_message_at
                            && optional($companyParticipant?->last_read_at)->lt($companyChatThread->last_message_at);
                    @endphp
                    <a href="{{ route('crm.discussions.app.company-chat') }}" class="crm-app-thread-link {{ $selectedThread->id === $companyChatThread->id ? 'active' : '' }} {{ $companyUnread ? 'unread' : '' }}">
                        <span class="crm-app-thread-icon crm-app-thread-icon-company"><i class="bx bx-buildings"></i></span>
                        <div class="crm-app-thread-copy">
                            <strong>Company Chat</strong>
                            <span>Persistent shared room for announcements and internal discussion.</span>
                            @if ($companyUnread)
                                <span class="crm-app-thread-unread">Unread activity</span>
                            @endif
                        </div>
                    </a>
                </div>
            </section>

            <section class="crm-app-sidebar-section">
                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                    <div>
                        <p class="crm-kicker">Group chats</p>
                        <h3>Teams</h3>
                    </div>
                    <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
                        <i class="bx bx-plus"></i> New
                    </a>
                </div>

                @if ($groupThreads->isEmpty())
                    <div class="crm-empty-inline">No group chats have been created yet.</div>
                @else
                    <div class="crm-app-thread-list">
                        @foreach ($groupThreads as $thread)
                            @php
                                $participant = $thread->participants->firstWhere('user_id', $currentUser?->id);
                                $isUnread = $thread->last_message_at && optional($participant?->last_read_at)->lt($thread->last_message_at);
                            @endphp
                            <a href="{{ route('crm.discussions.app.threads.show', $thread) }}" class="crm-app-thread-link {{ $selectedThread->id === $thread->id ? 'active' : '' }} {{ $isUnread ? 'unread' : '' }}">
                                <span class="crm-app-thread-icon crm-app-thread-icon-group"><i class="bx bx-group"></i></span>
                                <div class="crm-app-thread-copy">
                                    <strong>{{ $thread->subject }}</strong>
                                    <span>{{ $participantSummary($thread) }}</span>
                                    @if ($isUnread)
                                        <span class="crm-app-thread-unread">Unread activity</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="crm-app-sidebar-section">
                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                    <div>
                        <p class="crm-kicker">Direct messages</p>
                        <h3>People</h3>
                    </div>
                    <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn crm-app-btn crm-app-btn-direct">
                        <i class="bx bx-plus"></i> New
                    </a>
                </div>

                @if ($directThreads->isEmpty())
                    <div class="crm-empty-inline">No direct message threads exist yet.</div>
                @else
                    <div class="crm-app-thread-list">
                        @foreach ($directThreads as $thread)
                            @php
                                $counterpart = $thread->counterpartFor($currentUser);
                                $participant = $thread->participants->firstWhere('user_id', $currentUser?->id);
                                $isUnread = $thread->last_message_at && optional($participant?->last_read_at)->lt($thread->last_message_at);
                            @endphp
                            <a href="{{ route('crm.discussions.app.threads.show', $thread) }}" class="crm-app-thread-link {{ $selectedThread->id === $thread->id ? 'active' : '' }} {{ $isUnread ? 'unread' : '' }}">
                                <span class="crm-app-thread-icon crm-app-thread-icon-direct"><i class="bx bx-user"></i></span>
                                <div class="crm-app-thread-copy">
                                    <strong>{{ $counterpart?->name ?: $thread->subject }}</strong>
                                    <span>{{ \Illuminate\Support\Str::limit($thread->latestMessage?->body ?: ($thread->notes ?: 'No messages yet.'), 70) }}</span>
                                    @if ($isUnread)
                                        <span class="crm-app-thread-unread">Unread activity</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="crm-app-sidebar-section">
                <div>
                    <p class="crm-kicker">Recent files</p>
                    <h3>Shared in app chat</h3>
                </div>

                @if ($recentFiles->isEmpty())
                    <div class="crm-empty-inline">No app attachments have been shared yet.</div>
                @else
                    <div class="crm-app-file-grid">
                        @foreach ($recentFiles as $attachment)
                            <article class="crm-app-file-card">
                                <strong>{{ $attachment->original_name }}</strong>
                                <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                                <div class="crm-action-row">
                                    <a href="{{ route('crm.discussions.app.attachments.open', $attachment) }}" class="btn crm-app-btn crm-app-btn-open" target="_blank" rel="noopener">
                                        <i class="bx bx-link-external"></i> Open
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>

        <section class="crm-app-main">
            <div class="crm-app-main-header">
                <div>
                    <p class="crm-kicker">{{ $selectedThread->isCompanyChat() ? 'Shared room' : ($selectedThread->isGroupChat() ? 'Group chat' : 'Direct conversation') }}</p>
                    <h2>{{ $selectedLabel }}</h2>
                    <p>
                        @if ($selectedThread->isCompanyChat())
                            Company-wide conversation for announcements and shared files.
                        @elseif ($selectedThread->isGroupChat())
                            Group chat with {{ $selectedParticipants->count() }} other member(s). Use this thread for focused team coordination outside the company-wide room.
                        @else
                            Conversation with {{ $selectedCounterpart?->name ?: 'the selected user' }}.
                        @endif
                    </p>
                    @if ($selectedThread->participants->isNotEmpty())
                        <div class="crm-app-participant-list" style="margin-top: 14px;">
                            @foreach ($selectedThread->participants as $participant)
                                <span class="crm-pill muted">{{ $participant->user?->name ?: 'User #' . $participant->user_id }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="crm-action-row">
                    @if ($selectedThread->isDirectMessage())
                        <a href="{{ route('crm.discussions.app.direct.edit', $selectedThread) }}" class="btn crm-app-btn crm-app-btn-direct">
                            <i class="bx bx-edit"></i> Edit thread
                        </a>
                    @elseif ($selectedThread->isGroupChat() && $selectedCampaign)
                        <a href="{{ route('crm.discussions.app.bulk.edit', $selectedCampaign) }}" class="btn crm-app-btn crm-app-btn-group">
                            <i class="bx bx-edit"></i> Open setup
                        </a>
                    @endif
                    @if ($selectedThread->notes)
                        <span class="crm-pill primary">Notes saved</span>
                    @endif
                </div>
            </div>

            <div class="crm-app-message-panel" data-app-message-panel>
                <div id="crm-app-thread-messages" data-poll-url="{{ route('crm.discussions.app.threads.poll', $selectedThread) }}" data-last-message-at="{{ optional($selectedThread->last_message_at)->toIso8601String() }}">
                    @include('crm.discussions.app.partials.thread-messages', ['selectedThread' => $selectedThread])
                </div>
            </div>

            <div class="crm-app-composer">
                @if ($selectedThread->notes)
                    <div class="crm-discussion-form-note">{{ $selectedThread->notes }}</div>
                @endif

                <form
                    method="POST"
                    action="{{ $selectedThread->isCompanyChat() ? route('crm.discussions.app.company-chat.messages.store') : route('crm.discussions.app.direct.messages.store', $selectedThread) }}"
                    class="crm-form"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <div class="crm-field">
                        <label for="body">Message</label>
                        <textarea id="body" name="body" placeholder="Write a message for {{ strtolower($selectedLabel) }}">{{ old('body') }}</textarea>
                    </div>

                    @include('crm.discussions.partials.attachment-dropzone', [
                        'inputId' => 'workspace-attachments',
                        'title' => 'Attachments',
                        'hint' => 'Drop images, PDFs, or DOCX files here and keep the preview inside app messaging.',
                    ])

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="bx bx-send"></i> Send message</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div
        class="modal fade crm-app-preview-modal"
        id="crm-app-attachment-modal"
        tabindex="-1"
        aria-labelledby="crm-app-attachment-modal-title"
        aria-hidden="true"
        data-bs-backdrop="static"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="crm-kicker" style="margin-bottom: 6px;">Attachment preview</p>
                        <h5 class="modal-title" id="crm-app-attachment-modal-title">Preview</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close preview"></button>
                </div>
                <div class="modal-body">
                    <div class="crm-app-preview-modal-status" data-attachment-modal-status>Loading preview...</div>
                    <div class="crm-app-preview-modal-body" data-attachment-modal-body hidden></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.6/dist/docx-preview.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var messagesPanel = document.querySelector('[data-app-message-panel]');
            var messagesContainer = document.getElementById('crm-app-thread-messages');
            var modalElement = document.getElementById('crm-app-attachment-modal');
            var modalTitle = document.getElementById('crm-app-attachment-modal-title');
            var modalStatus = modalElement ? modalElement.querySelector('[data-attachment-modal-status]') : null;
            var modalBody = modalElement ? modalElement.querySelector('[data-attachment-modal-body]') : null;
            var previewModal = modalElement && window.bootstrap && window.bootstrap.Modal
                ? new window.bootstrap.Modal(modalElement, { backdrop: 'static' })
                : null;

            if (!messagesContainer) {
                return;
            }

            var pollUrl = messagesContainer.getAttribute('data-poll-url');
            var lastMessageAt = messagesContainer.getAttribute('data-last-message-at') || '';

            function scrollToBottom(force) {
                if (!messagesPanel) {
                    return;
                }

                var nearBottom = messagesPanel.scrollHeight - messagesPanel.scrollTop - messagesPanel.clientHeight < 180;

                if (force || nearBottom) {
                    messagesPanel.scrollTop = messagesPanel.scrollHeight;
                }
            }

            function resetModalPreview() {
                if (!modalBody || !modalStatus) {
                    return;
                }

                modalBody.hidden = true;
                modalBody.innerHTML = '';
                modalStatus.hidden = false;
                modalStatus.textContent = 'Loading preview...';
            }

            function showModalMessage(message) {
                if (!modalStatus || !modalBody) {
                    return;
                }

                modalBody.hidden = true;
                modalBody.innerHTML = '';
                modalStatus.hidden = false;
                modalStatus.textContent = message;
            }

            function showModalBody(contentNode) {
                if (!modalStatus || !modalBody) {
                    return;
                }

                modalStatus.hidden = true;
                modalBody.hidden = false;
                modalBody.innerHTML = '';
                modalBody.appendChild(contentNode);
            }

            function openAttachmentModal(button) {
                if (!button || !previewModal || !modalTitle) {
                    return;
                }

                var previewKind = button.getAttribute('data-preview-kind');
                var previewUrl = button.getAttribute('data-preview-url');
                var previewName = button.getAttribute('data-preview-name') || 'Attachment preview';

                modalTitle.textContent = previewName;
                resetModalPreview();
                previewModal.show();

                if (!previewUrl || !previewKind) {
                    showModalMessage('Preview is unavailable for this attachment.');
                    return;
                }

                if (previewKind === 'image') {
                    var image = document.createElement('img');
                    image.className = 'crm-app-preview-modal-image';
                    image.alt = previewName;
                    image.onload = function () {
                        showModalBody(image);
                    };
                    image.onerror = function () {
                        showModalMessage('Image preview is unavailable. Use Open or Download instead.');
                    };
                    image.src = previewUrl;

                    return;
                }

                if (previewKind === 'pdf') {
                    var frame = document.createElement('iframe');
                    frame.className = 'crm-app-preview-modal-frame';
                    frame.src = previewUrl;
                    frame.title = previewName;
                    showModalBody(frame);
                    return;
                }

                if (previewKind === 'docx') {
                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        showModalMessage('DOCX preview is unavailable. Use Open or Download instead.');
                        return;
                    }

                    var docxContainer = document.createElement('div');
                    docxContainer.className = 'crm-docx-preview crm-app-preview-modal-docx';
                    showModalBody(docxContainer);

                    fetch(previewUrl)
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Failed to load preview');
                            }

                            return response.blob();
                        })
                        .then(function (blob) {
                            docxContainer.innerHTML = '';

                            return window.docx.renderAsync(blob, docxContainer, null, {
                                className: 'crm-docx-preview-shell',
                            });
                        })
                        .catch(function () {
                            showModalMessage('DOCX preview is unavailable. Use Open or Download instead.');
                        });
                }
            }

            function pollThread() {
                if (!pollUrl || document.visibilityState !== 'visible') {
                    return;
                }

                fetch(pollUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Polling failed');
                        }

                        return response.json();
                    })
                    .then(function (payload) {
                        var hasNewMessages = (payload.last_message_at || '') !== lastMessageAt;

                        messagesContainer.innerHTML = payload.html;
                        lastMessageAt = payload.last_message_at || '';
                        messagesContainer.setAttribute('data-last-message-at', lastMessageAt);

                        scrollToBottom(hasNewMessages);
                    })
                    .catch(function () {
                        // Ignore polling failures and rely on the next interval.
                    });
            }

            messagesContainer.addEventListener('click', function (event) {
                var toggleButton = event.target.closest('[data-attachment-view]');

                if (toggleButton) {
                    event.preventDefault();
                    openAttachmentModal(toggleButton);
                }
            });

            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function () {
                    resetModalPreview();
                });
            }

            scrollToBottom(true);

            window.setInterval(pollThread, 10000);
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    pollThread();
                }
            });
        });
    </script>
@endpush

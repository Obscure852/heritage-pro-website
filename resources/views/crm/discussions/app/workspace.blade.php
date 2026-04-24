@extends('layouts.crm')

@php
    $currentUser = auth()->user();
    $selectedCounterpart = $selectedThread->isDirectMessage() ? $selectedThread->counterpartFor($currentUser) : null;
    $selectedParticipants = $selectedThread->isGroupChat() ? $selectedThread->otherParticipantsFor($currentUser) : collect();
    $selectedCampaign = $selectedThread->campaigns->sortByDesc('id')->first();
    $mentionsEnabled = $selectedThread->isCompanyChat() || $selectedThread->isGroupChat();
    $mentionableUsers = $crmUsers
        ->map(fn ($user) => [
            'id' => (int) $user->id,
            'name' => $user->name ?: ($user->email ?: ('User #' . $user->id)),
            'email' => $user->email,
        ])
        ->values();
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
    $threadSnippet = function ($thread, string $fallback = 'No messages yet.') {
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($thread->latestMessage?->body ?: $thread->notes ?: ''))));

        return $text !== '' ? \Illuminate\Support\Str::limit($text, 86) : $fallback;
    };
    $threadCharacterSnippet = function ($thread, int $characters = 20, string $fallback = 'No messages yet.') {
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($thread->latestMessage?->body ?: $thread->notes ?: ''))));

        return $text !== '' ? \Illuminate\Support\Str::limit($text, $characters, '...') : $fallback;
    };
    $threadTimestamp = function ($thread) {
        $timestamp = $thread->last_message_at;

        if (! $timestamp) {
            return 'No activity';
        }

        return $timestamp->isToday() ? $timestamp->format('H:i') : $timestamp->format('d M');
    };
    $companyParticipant = $companyChatThread->participants->firstWhere('user_id', $currentUser?->id);
    $companyUnread = $companyChatThread->last_message_at
        && optional($companyParticipant?->last_read_at)->lt($companyChatThread->last_message_at);
    $firstDirectThread = $directThreads->first();
    $firstGroupThread = $groupThreads->first();
    $defaultInboxFilter = $selectedThread->isCompanyChat()
        ? 'company'
        : ($selectedThread->isGroupChat() ? 'group' : 'direct');
    $showInboxPane = $defaultInboxFilter !== 'company'
        && (($defaultInboxFilter === 'direct' && $directThreads->isNotEmpty())
            || ($defaultInboxFilter === 'group' && $groupThreads->isNotEmpty()));
    $compactAttachmentName = function (?string $name): string {
        $name = trim((string) $name);

        if ($name === '') {
            return 'attachment';
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);

        if ($basename === '') {
            return $name;
        }

        $shortBase = \Illuminate\Support\Str::substr($basename, 0, 10);

        return $extension !== '' ? $shortBase . '.' . $extension : $shortBase;
    };
    $userInitials = function ($user): string {
        $label = trim((string) ($user?->name ?: $user?->email ?: 'User'));
        $parts = preg_split('/\s+/', $label) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
            ->implode('');

        if ($initials !== '') {
            return $initials;
        }

        return \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($label, 0, 2));
    };
@endphp

@section('title', $selectedLabel . ' - App Messaging')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Company chat, direct conversations, and group threads organized into one calmer internal messaging workspace.')
@section('crm_shell_attributes', 'data-crm-active-discussion-thread="' . $selectedThread->id . '"')

@section('content')
    @include('crm.discussions.partials.channel-styles')

    <div class="crm-app-workspace-head">
        <div class="crm-app-workspace-copy">
            <p class="crm-kicker">Internal messaging</p>
            <h2>App Messaging</h2>
            <p>One workspace for the company room, direct conversations, and smaller team threads with the same attachment and mention flow you already use.</p>
        </div>
        <div class="crm-app-workspace-stats" aria-label="App messaging overview">
            <article class="crm-app-workspace-stat">
                <span>Direct</span>
                <strong>{{ $directThreads->count() }}</strong>
            </article>
            <article class="crm-app-workspace-stat">
                <span>Groups</span>
                <strong>{{ $groupThreads->count() }}</strong>
            </article>
            <article class="crm-app-workspace-stat">
                <span>Open thread</span>
                <strong>{{ $selectedThread->messages->count() }}</strong>
            </article>
            <article class="crm-app-workspace-stat">
                <span>Files</span>
                <strong>{{ $recentFiles->count() }}</strong>
            </article>
        </div>
    </div>

    <div class="crm-app-shell {{ $showInboxPane ? '' : 'is-inbox-hidden' }}" data-crm-active-discussion-thread="{{ $selectedThread->id }}" data-app-shell>
        <aside class="crm-app-rail">
            <section class="crm-app-rail-section">
                <div>
                    <p class="crm-kicker">Compose</p>
                    <h3>Start a conversation</h3>
                </div>
                <div class="crm-app-compose-grid">
                    <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn crm-app-btn crm-app-btn-direct">
                        <i class="bx bx-message-square-dots"></i> New DM
                    </a>
                    <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
                        <i class="bx bx-group"></i> New group
                    </a>
                </div>
            </section>

            <section class="crm-app-rail-section">
                <div>
                    <p class="crm-kicker">Workspace</p>
                    <h3>Jump to a section</h3>
                </div>
                <nav class="crm-app-rail-nav" aria-label="App messaging sections">
                    <a href="{{ route('crm.discussions.app.company-chat') }}" class="crm-app-rail-nav-link {{ $defaultInboxFilter === 'company' ? 'active' : '' }}" data-inbox-filter-link data-inbox-filter="company">
                        <i class="bx bx-buildings"></i>
                        <span>Company Chat</span>
                        @if ($companyUnread)
                            <em>Unread</em>
                        @endif
                    </a>
                    <a href="{{ $firstDirectThread ? route('crm.discussions.app.threads.show', $firstDirectThread) : '#crm-app-inbox-direct' }}" class="crm-app-rail-nav-link {{ $defaultInboxFilter === 'direct' ? 'active' : '' }}" data-inbox-filter-link data-inbox-filter="direct">
                        <i class="bx bx-user"></i>
                        <span>Direct Messages</span>
                        <em>{{ $directThreads->count() }}</em>
                    </a>
                    <a href="{{ $firstGroupThread ? route('crm.discussions.app.threads.show', $firstGroupThread) : '#crm-app-inbox-groups' }}" class="crm-app-rail-nav-link {{ $defaultInboxFilter === 'group' ? 'active' : '' }}" data-inbox-filter-link data-inbox-filter="group">
                        <i class="bx bx-group"></i>
                        <span>Group Chats</span>
                        <em>{{ $groupThreads->count() }}</em>
                    </a>
                    <a href="#crm-app-rail-files" class="crm-app-rail-nav-link">
                        <i class="bx bx-paperclip"></i>
                        <span>Recent Files</span>
                        <em>{{ $recentFiles->count() }}</em>
                    </a>
                </nav>
            </section>

            <section class="crm-app-rail-section" id="crm-app-rail-files">
                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                    <div>
                        <p class="crm-kicker">Recent files</p>
                        <h3>Shared in app chat</h3>
                    </div>
                    <span class="crm-pill muted">{{ $recentFiles->count() }}</span>
                </div>

                @if ($recentFiles->isEmpty())
                    <div class="crm-empty-inline">No app attachments have been shared yet.</div>
                @else
                    <div class="crm-app-file-list">
                        @foreach ($recentFiles as $attachment)
                            @php
                                $fileTypeClass = $attachment->isPdf()
                                    ? 'is-pdf'
                                    : ($attachment->isImage()
                                        ? 'is-image'
                                        : ($attachment->isDocx() ? 'is-docx' : 'is-file'));
                            @endphp
                            <article class="crm-app-file-row">
                                <span class="crm-app-file-badge {{ $fileTypeClass }}" aria-hidden="true">
                                    <i class="{{ $attachment->iconClass() }}"></i>
                                </span>
                                <div class="crm-app-file-row-copy">
                                    <strong>{{ $compactAttachmentName($attachment->original_name) }}</strong>
                                    <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                                </div>
                                <a href="{{ route('crm.discussions.app.attachments.open', $attachment) }}" class="btn crm-icon-btn crm-icon-btn-open" target="_blank" rel="noopener" title="Open attachment" aria-label="Open attachment">
                                    <i class="bx bx-link-external"></i>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>

        <section class="crm-app-inbox crm-app-pane" data-app-inbox-pane @if (! $showInboxPane) hidden @endif>
            <div class="crm-app-pane-head">
                <div>
                    <p class="crm-kicker">Inbox</p>
                    <h3>Conversations</h3>
                </div>
                <span class="crm-pill muted" data-app-inbox-count>{{ $defaultInboxFilter === 'company' ? 1 : ($defaultInboxFilter === 'group' ? $groupThreads->count() : $directThreads->count()) }} {{ ($defaultInboxFilter === 'company' ? 1 : ($defaultInboxFilter === 'group' ? $groupThreads->count() : $directThreads->count())) === 1 ? 'thread' : 'threads' }}</span>
            </div>

            <div class="crm-app-inbox-body" data-app-inbox-body data-default-inbox-filter="{{ $defaultInboxFilter }}">
                <section class="crm-app-inbox-section" id="crm-app-inbox-company" data-inbox-section="company" @if ($defaultInboxFilter !== 'company') hidden @endif>
                    <div class="crm-app-inbox-section-head">
                        <span>Pinned room</span>
                    </div>

                    <a href="{{ route('crm.discussions.app.company-chat') }}" class="crm-app-inbox-row {{ $selectedThread->id === $companyChatThread->id ? 'active' : '' }} {{ $companyUnread ? 'unread' : '' }}">
                        <span class="crm-app-inbox-row-icon is-company"><i class="bx bx-buildings"></i></span>
                        <div class="crm-app-inbox-row-main">
                            <div class="crm-app-inbox-row-headline">
                                <div class="crm-app-inbox-row-identity">
                                    <strong>Company Chat</strong>
                                    <span class="crm-app-inbox-row-secondary">Company-wide shared room</span>
                                </div>
                                <span class="crm-app-inbox-row-time">{{ $threadTimestamp($companyChatThread) }}</span>
                            </div>
                            <div class="crm-app-inbox-row-summary">{{ $threadSnippet($companyChatThread, 'Persistent shared room for announcements and internal updates.') }}</div>
                            @if ($companyUnread)
                                <div class="crm-app-inbox-row-meta">
                                    <span class="crm-app-inbox-row-dot">Unread</span>
                                </div>
                            @endif
                        </div>
                    </a>
                </section>

                <section class="crm-app-inbox-section" id="crm-app-inbox-groups" data-inbox-section="group" @if ($defaultInboxFilter !== 'group') hidden @endif>
                    <div class="crm-app-inbox-section-head">
                        <span>Group chats</span>
                        <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
                            <i class="bx bx-plus"></i> New
                        </a>
                    </div>

                    @if ($groupThreads->isEmpty())
                        <div class="crm-empty-inline">No group chats have been created yet.</div>
                    @else
                        <div class="crm-app-inbox-list">
                            @foreach ($groupThreads as $thread)
                                @php
                                    $participant = $thread->participants->firstWhere('user_id', $currentUser?->id);
                                    $isUnread = $thread->last_message_at && optional($participant?->last_read_at)->lt($thread->last_message_at);
                                @endphp
                                <a href="{{ route('crm.discussions.app.threads.show', $thread) }}" class="crm-app-inbox-row {{ $selectedThread->id === $thread->id ? 'active' : '' }} {{ $isUnread ? 'unread' : '' }}">
                                    <span class="crm-app-inbox-row-icon is-group"><i class="bx bx-group"></i></span>
                                    <div class="crm-app-inbox-row-main">
                                        <div class="crm-app-inbox-row-headline">
                                            <div class="crm-app-inbox-row-identity">
                                                <strong>{{ $thread->subject }}</strong>
                                                <span class="crm-app-inbox-row-secondary">{{ $participantSummary($thread) }}</span>
                                            </div>
                                            <span class="crm-app-inbox-row-time">{{ $threadTimestamp($thread) }}</span>
                                        </div>
                                        <div class="crm-app-inbox-row-summary">{{ $threadSnippet($thread) }}</div>
                                        @if ($isUnread)
                                            <div class="crm-app-inbox-row-meta">
                                                <span class="crm-app-inbox-row-dot">Unread</span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="crm-app-inbox-section" id="crm-app-inbox-direct" data-inbox-section="direct" @if ($defaultInboxFilter !== 'direct') hidden @endif>
                    <div class="crm-app-inbox-section-head">
                        <span>Direct messages</span>
                        <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn crm-app-btn crm-app-btn-direct">
                            <i class="bx bx-plus"></i> New
                        </a>
                    </div>

                    @if ($directThreads->isEmpty())
                        <div class="crm-empty-inline">No direct message threads exist yet.</div>
                    @else
                        <div class="crm-app-inbox-list">
                            @foreach ($directThreads as $thread)
                                @php
                                    $counterpart = $thread->counterpartFor($currentUser);
                                    $participant = $thread->participants->firstWhere('user_id', $currentUser?->id);
                                    $isUnread = $thread->last_message_at && optional($participant?->last_read_at)->lt($thread->last_message_at);
                                @endphp
                                <a href="{{ route('crm.discussions.app.threads.show', $thread) }}" class="crm-app-inbox-row {{ $selectedThread->id === $thread->id ? 'active' : '' }} {{ $isUnread ? 'unread' : '' }}">
                                    <span class="crm-app-inbox-avatar" aria-hidden="true">
                                        @if ($counterpart?->avatar_url)
                                            <img src="{{ $counterpart->avatar_url }}" alt="{{ $counterpart?->name ?: 'CRM user' }}" class="crm-app-inbox-avatar-photo">
                                        @else
                                            <span class="crm-app-inbox-avatar-initials">{{ $userInitials($counterpart) }}</span>
                                        @endif
                                    </span>
                                    <div class="crm-app-inbox-row-main">
                                        <div class="crm-app-inbox-row-headline">
                                            <div class="crm-app-inbox-row-identity">
                                                <strong>{{ $counterpart?->name ?: $thread->subject }}</strong>
                                                <span class="crm-app-inbox-row-secondary">{{ $counterpart?->email ?: 'Direct message' }}</span>
                                            </div>
                                            <span class="crm-app-inbox-row-time">{{ $threadTimestamp($thread) }}</span>
                                        </div>
                                        <div class="crm-app-inbox-row-summary">{{ $threadCharacterSnippet($thread, 20) }}</div>
                                        @if ($isUnread)
                                            <div class="crm-app-inbox-row-meta">
                                                <span class="crm-app-inbox-row-dot">Unread</span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </section>

        <section class="crm-app-thread-panel crm-app-pane">
            <div class="crm-app-thread-header">
                <div class="crm-app-thread-header-main">
                    <div class="crm-app-thread-header-top">
                        <p class="crm-kicker">{{ $selectedThread->isCompanyChat() ? 'Shared room' : ($selectedThread->isGroupChat() ? 'Group chat' : 'Direct conversation') }}</p>
                        @if ($selectedThread->notes)
                            <span class="crm-pill primary">Notes saved</span>
                        @endif
                    </div>
                    <h3>{{ $selectedLabel }}</h3>
                    <p class="crm-app-thread-summary">
                        @if ($selectedThread->isCompanyChat())
                            Company-wide conversation for announcements, coordination, and shared files.
                        @elseif ($selectedThread->isGroupChat())
                            Focused team thread with {{ $selectedParticipants->count() }} other member(s).
                        @else
                            Conversation with {{ $selectedCounterpart?->name ?: 'the selected user' }}.
                        @endif
                    </p>
                    @if ($selectedThread->participants->isNotEmpty())
                        <div class="crm-app-participant-list">
                            @foreach ($selectedThread->participants as $participant)
                                <span class="crm-pill muted">{{ $participant->user?->name ?: 'User #' . $participant->user_id }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="crm-action-row crm-app-thread-actions">
                    @if ($selectedThread->isDirectMessage())
                        <a href="{{ route('crm.discussions.app.direct.edit', $selectedThread) }}" class="btn crm-app-btn crm-app-btn-direct">
                            <i class="bx bx-edit"></i> Edit thread
                        </a>
                    @elseif ($selectedThread->isGroupChat() && $selectedCampaign)
                        <a href="{{ route('crm.discussions.app.bulk.edit', $selectedCampaign) }}" class="btn crm-app-btn crm-app-btn-group">
                            <i class="bx bx-edit"></i> Open setup
                        </a>
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
                    data-live-composer-form
                    data-mention-enabled="{{ $mentionsEnabled ? 'true' : 'false' }}"
                    data-mentionable-users='{{ json_encode($mentionableUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                >
                    @csrf

                    <div class="crm-field crm-app-composer-field">
                        <label for="body">Message</label>
                        <textarea
                            id="body"
                            name="body"
                            placeholder="Write a message for {{ strtolower($selectedLabel) }}"
                            data-live-composer-input
                            data-app-mention-input
                            autocomplete="off"
                        >{{ old('body') }}</textarea>
                        <div class="crm-live-composer-hint">
                            <span>Enter to send • Shift+Enter for a new line</span>
                            @if ($mentionsEnabled)
                                <span> • Type {{ '@' }} to mention a user</span>
                            @endif
                        </div>
                        @if ($mentionsEnabled)
                            <div data-app-mention-hidden-inputs></div>
                            <div class="crm-app-mention-menu" data-app-mention-menu hidden></div>
                        @endif
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

@include('crm.discussions.partials.live-composer-shortcuts')

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
            var composerForm = document.querySelector('[data-live-composer-form]');
            var composerTextarea = composerForm ? composerForm.querySelector('[data-app-mention-input]') : null;
            var mentionMenu = composerForm ? composerForm.querySelector('[data-app-mention-menu]') : null;
            var mentionHiddenInputs = composerForm ? composerForm.querySelector('[data-app-mention-hidden-inputs]') : null;
            var mentionEnabled = composerForm && composerForm.getAttribute('data-mention-enabled') === 'true';
            var appShell = document.querySelector('[data-app-shell]');
            var inboxPane = document.querySelector('[data-app-inbox-pane]');
            var inboxBody = document.querySelector('[data-app-inbox-body]');
            var inboxCount = document.querySelector('[data-app-inbox-count]');
            var inboxSections = inboxBody ? Array.prototype.slice.call(inboxBody.querySelectorAll('[data-inbox-section]')) : [];
            var inboxFilterLinks = Array.prototype.slice.call(document.querySelectorAll('[data-inbox-filter-link]'));
            var mentionableUsers = [];
            var selectedMentions = {};
            var activeMentionIndex = 0;
            var activeInboxFilter = inboxBody ? (inboxBody.getAttribute('data-default-inbox-filter') || 'direct') : 'direct';
            var previewModal = modalElement && window.bootstrap && window.bootstrap.Modal
                ? new window.bootstrap.Modal(modalElement, { backdrop: 'static' })
                : null;

            if (!messagesContainer) {
                return;
            }

            var pollUrl = messagesContainer.getAttribute('data-poll-url');
            var lastMessageAt = messagesContainer.getAttribute('data-last-message-at') || '';

            if (mentionEnabled) {
                try {
                    mentionableUsers = JSON.parse(composerForm.getAttribute('data-mentionable-users') || '[]');
                } catch (error) {
                    mentionableUsers = [];
                }
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function filteredInboxCount(filter) {
                if (filter === 'company') {
                    return 1;
                }

                if (filter === 'group') {
                    return {{ $groupThreads->count() }};
                }

                return {{ $directThreads->count() }};
            }

            function shouldShowInboxPane(filter) {
                if (filter === 'company') {
                    return false;
                }

                if (filter === 'group') {
                    return {{ $groupThreads->count() }} > 0;
                }

                return {{ $directThreads->count() }} > 0;
            }

            function applyInboxFilter(filter) {
                if (!inboxBody || inboxSections.length === 0) {
                    return;
                }

                activeInboxFilter = filter;

                var showPane = shouldShowInboxPane(filter);

                inboxSections.forEach(function (section) {
                    section.hidden = section.getAttribute('data-inbox-section') !== filter;
                });

                inboxFilterLinks.forEach(function (link) {
                    link.classList.toggle('active', link.getAttribute('data-inbox-filter') === filter);
                });

                if (inboxPane) {
                    inboxPane.hidden = !showPane;
                }

                if (appShell) {
                    appShell.classList.toggle('is-inbox-hidden', !showPane);
                }

                if (inboxCount) {
                    var count = filteredInboxCount(filter);
                    inboxCount.textContent = count + ' ' + (count === 1 ? 'thread' : 'threads');
                }
            }

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

            function tokenForUser(user) {
                return '@' + (user.name || '');
            }

            function hasMentionToken(body, user) {
                var plainToken = tokenForUser(user);
                var legacyToken = '@[' + (user.name || '') + ']';

                return body.indexOf(plainToken) !== -1 || body.indexOf(legacyToken) !== -1;
            }

            function syncMentionInputs() {
                if (!mentionHiddenInputs) {
                    return;
                }

                mentionHiddenInputs.innerHTML = Object.keys(selectedMentions).map(function (userId) {
                    return '<input type="hidden" name="mention_user_ids[]" value="' + userId + '">';
                }).join('');
            }

            function syncMentionsFromBody() {
                if (!composerTextarea) {
                    return;
                }

                var body = composerTextarea.value;

                Object.keys(selectedMentions).forEach(function (userId) {
                    if (!hasMentionToken(body, selectedMentions[userId])) {
                        delete selectedMentions[userId];
                    }
                });

                syncMentionInputs();
            }

            function mentionQuery() {
                if (!mentionEnabled || !composerTextarea) {
                    return null;
                }

                var cursor = composerTextarea.selectionStart || 0;
                var prefix = composerTextarea.value.slice(0, cursor);
                var match = prefix.match(/(^|[\s\n])@\[?([^\]\n\r]*)$/);

                if (!match) {
                    return null;
                }

                var marker = match[0];
                var query = match[2] || '';
                var start = cursor - query.length - 1 - (marker.indexOf('@[') !== -1 ? 1 : 0);

                return {
                    start: start,
                    end: cursor,
                    query: query.trim().toLowerCase(),
                };
            }

            function matchingMentionUsers(query) {
                return mentionableUsers.filter(function (user) {
                    if (selectedMentions[String(user.id)]) {
                        return false;
                    }

                    var searchText = String((user.name || '') + ' ' + (user.email || '')).toLowerCase();
                    return query === '' || searchText.indexOf(query) !== -1;
                }).slice(0, 6);
            }

            function closeMentionMenu() {
                if (!mentionMenu) {
                    return;
                }

                mentionMenu.hidden = true;
                mentionMenu.innerHTML = '';
                activeMentionIndex = 0;

                if (composerTextarea) {
                    composerTextarea.setAttribute('data-mention-menu-open', 'false');
                }
            }

            function renderMentionMenu() {
                if (!mentionMenu || !mentionEnabled || !composerTextarea) {
                    return;
                }

                var queryData = mentionQuery();

                if (!queryData) {
                    closeMentionMenu();
                    return;
                }

                var matches = matchingMentionUsers(queryData.query);

                if (matches.length === 0) {
                    closeMentionMenu();
                    return;
                }

                if (activeMentionIndex >= matches.length) {
                    activeMentionIndex = 0;
                }

                mentionMenu.hidden = false;
                composerTextarea.setAttribute('data-mention-menu-open', 'true');
                mentionMenu.innerHTML = matches.map(function (user, index) {
                    return '<button type="button" class="crm-app-mention-option' + (index === activeMentionIndex ? ' active' : '') + '" data-mention-user-id="' + user.id + '">' +
                        '<strong>' + escapeHtml(user.name) + '</strong>' +
                        (user.email ? '<span>' + escapeHtml(user.email) + '</span>' : '') +
                    '</button>';
                }).join('');
            }

            function insertMention(user) {
                if (!composerTextarea) {
                    return;
                }

                var queryData = mentionQuery();

                if (!queryData) {
                    return;
                }

                var token = tokenForUser(user) + ' ';
                composerTextarea.value = composerTextarea.value.slice(0, queryData.start) + token + composerTextarea.value.slice(queryData.end);
                composerTextarea.focus();
                composerTextarea.selectionStart = composerTextarea.selectionEnd = queryData.start + token.length;
                selectedMentions[String(user.id)] = user;
                syncMentionInputs();
                closeMentionMenu();
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

                        if (hasNewMessages) {
                            window.dispatchEvent(new CustomEvent('crm:discussion-thread-updated', {
                                detail: {
                                    threadId: {{ $selectedThread->id }},
                                    hasNewMessages: true,
                                    lastMessageAt: lastMessageAt
                                }
                            }));
                        }

                        scrollToBottom(hasNewMessages);
                    })
                    .catch(function () {
                        // Ignore polling failures and rely on the next interval.
                    });
            }

            inboxFilterLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var filter = link.getAttribute('data-inbox-filter');
                    var href = link.getAttribute('href') || '';

                    if (!filter) {
                        return;
                    }

                    if (href && href.charAt(0) !== '#') {
                        return;
                    }

                    event.preventDefault();
                    applyInboxFilter(filter);
                });
            });

            applyInboxFilter(activeInboxFilter);

            messagesContainer.addEventListener('click', function (event) {
                var toggleButton = event.target.closest('[data-attachment-view]');

                if (toggleButton) {
                    event.preventDefault();
                    openAttachmentModal(toggleButton);
                }
            });

            if (mentionEnabled && composerTextarea && mentionMenu) {
                composerTextarea.setAttribute('data-mention-menu-open', 'false');

                mentionableUsers.forEach(function (user) {
                    if (hasMentionToken(composerTextarea.value, user)) {
                        selectedMentions[String(user.id)] = user;
                    }
                });

                syncMentionInputs();

                composerTextarea.addEventListener('input', function () {
                    syncMentionsFromBody();
                    renderMentionMenu();
                });

                composerTextarea.addEventListener('click', renderMentionMenu);
                composerTextarea.addEventListener('keyup', function (event) {
                    if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown' && event.key !== 'Enter') {
                        renderMentionMenu();
                    }
                });

                composerTextarea.addEventListener('keydown', function (event) {
                    if (mentionMenu.hidden) {
                        return;
                    }

                    var queryData = mentionQuery();
                    var matches = matchingMentionUsers(queryData ? queryData.query : '');

                    if (matches.length === 0) {
                        closeMentionMenu();
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeMentionIndex = (activeMentionIndex + 1) % matches.length;
                        renderMentionMenu();
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeMentionIndex = (activeMentionIndex - 1 + matches.length) % matches.length;
                        renderMentionMenu();
                        return;
                    }

                    if (event.key === 'Escape') {
                        event.preventDefault();
                        closeMentionMenu();
                        return;
                    }

                    if (event.key === 'Enter' || event.key === 'Tab') {
                        event.preventDefault();
                        insertMention(matches[activeMentionIndex]);
                    }
                });

                mentionMenu.addEventListener('click', function (event) {
                    var option = event.target.closest('[data-mention-user-id]');

                    if (!option) {
                        return;
                    }

                    event.preventDefault();

                    var userId = String(option.getAttribute('data-mention-user-id'));
                    var selectedUser = mentionableUsers.find(function (user) {
                        return String(user.id) === userId;
                    });

                    if (selectedUser) {
                        insertMention(selectedUser);
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!composerForm.contains(event.target)) {
                        closeMentionMenu();
                    }
                });
            }

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

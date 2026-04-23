@php
    $currentUser = auth()->user();
    $messageReceipt = function ($message) use ($currentUser, $selectedThread) {
        if ((int) $message->user_id !== (int) $currentUser?->id) {
            return null;
        }

        if ($selectedThread->isDirectMessage()) {
            $counterpart = $selectedThread->counterpartFor($currentUser);

            if (! $counterpart) {
                return null;
            }

            $seen = $selectedThread->messageSeenByUser($message, $counterpart->id);

            return [
                'label' => $seen ? 'Seen' : 'Unseen',
                'class' => $seen ? 'is-seen' : 'is-pending',
            ];
        }

        $seenCount = $selectedThread->participantReadCountForMessage($message, (int) $currentUser?->id);

        return [
            'label' => $seenCount > 0 ? 'Seen by ' . $seenCount : 'Unseen',
            'class' => $seenCount > 0 ? 'is-seen' : 'is-pending',
        ];
    };
@endphp

@if ($selectedThread->messages->isEmpty())
    <div class="crm-empty">No messages yet. Start the conversation below.</div>
@else
    <div class="crm-app-message-stream">
        @foreach ($selectedThread->messages as $message)
            @php($isMine = (int) $message->user_id === (int) $currentUser?->id)
            @php($receipt = $messageReceipt($message))
            <div class="crm-app-message-row {{ $isMine ? 'mine' : '' }}">
                <div class="crm-app-message-bubble">
                    <div class="crm-app-message-meta">
                        <strong>{{ $message->user?->name ?: 'System' }}</strong>
                        <span>{{ optional($message->sent_at ?: $message->created_at)->format('d M Y H:i') }}</span>
                    </div>
                    @if (filled($message->body))
                        <p>{!! $message->renderedBody($currentUser) !!}</p>
                    @endif

                    @if ($message->attachments->isNotEmpty())
                        <div class="crm-app-attachment-grid">
                            @foreach ($message->attachments as $attachment)
                                @php($isPreviewable = $attachment->isImage() || $attachment->isPdf() || $attachment->isDocx())
                                <article class="crm-app-attachment">
                                    <div class="crm-app-attachment-icon">
                                        <i class="{{ $attachment->iconClass() }}"></i>
                                    </div>
                                    <div class="crm-app-attachment-copy">
                                        <strong>{{ $attachment->original_name }}</strong>
                                        <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                                    </div>
                                    <div class="crm-action-row crm-app-attachment-actions">
                                        @if ($isPreviewable)
                                            <button
                                                type="button"
                                                class="btn btn-sm crm-icon-btn crm-icon-btn-preview"
                                                data-attachment-view
                                                data-preview-kind="{{ $attachment->isImage() ? 'image' : ($attachment->isPdf() ? 'pdf' : 'docx') }}"
                                                data-preview-url="{{ route('crm.discussions.app.attachments.preview', $attachment) }}"
                                                data-preview-name="{{ $attachment->original_name }}"
                                                title="View attachment"
                                                aria-label="View attachment"
                                            >
                                                <i class="bx bx-show"></i>
                                            </button>
                                        @endif
                                        <a
                                            href="{{ route('crm.discussions.app.attachments.open', $attachment) }}"
                                            class="btn btn-sm crm-icon-btn crm-icon-btn-open"
                                            target="_blank"
                                            rel="noopener"
                                            title="Open attachment"
                                            aria-label="Open attachment"
                                        >
                                            <i class="bx bx-link-external"></i>
                                        </a>
                                        <a
                                            href="{{ route('crm.discussions.app.attachments.download', $attachment) }}"
                                            class="btn btn-sm crm-icon-btn crm-icon-btn-download"
                                            title="Download attachment"
                                            aria-label="Download attachment"
                                        >
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if ($receipt)
                        <div class="crm-message-receipt {{ $receipt['class'] }}">{{ $receipt['label'] }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

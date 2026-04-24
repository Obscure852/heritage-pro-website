@php
    $statusClass = match ($discussionThread->delivery_status) {
        'failed' => 'danger',
        'queued', 'pending_integration' => 'warning',
        default => 'success',
    };
    $currentUser = auth()->user();
    $channelKey = strtolower((string) $channelLabel);
    $recipientLabel = $discussionThread->recipientUser?->name
        ?: ($discussionThread->recipient_email ?: ($discussionThread->recipient_phone ?: 'Manual recipient'));
    $attachmentCount = $discussionThread->messages->sum(fn ($message) => $message->attachments->count());
    $messageReceipt = function ($message) use ($currentUser, $discussionThread) {
        if ($message->direction !== 'outbound' || ! $discussionThread->recipient_user_id) {
            return null;
        }

        if ((int) $message->user_id !== (int) $currentUser?->id) {
            return null;
        }

        $seen = $discussionThread->messageSeenByUser($message, (int) $discussionThread->recipient_user_id);

        return [
            'label' => $seen ? 'Seen in CRM' : 'Unseen in CRM',
            'class' => $seen ? 'is-seen' : 'is-pending',
        ];
    };
@endphp

<div class="crm-external-channel is-{{ $channelKey }} crm-external-thread-page">
    @if ($discussionThread->source_type && $discussionThread->source_id)
        <div class="crm-discussion-source-card">
            <div>
                <strong>{{ ucfirst($discussionThread->source_type) }} linked</strong>
                <span>This thread keeps the commercial document artifact attached to outbound messages for audit history.</span>
            </div>
            <span class="crm-pill primary">{{ ucfirst($discussionThread->source_type) }}</span>
        </div>
    @endif

    @if ($discussionThread->delivery_status === 'pending_integration')
        @include('crm.partials.helper-text', [
            'title' => 'Integration Pending',
            'content' => 'The draft was stored successfully, but this channel still needs a live provider integration before delivery can leave CRM automatically.',
        ])
    @endif

    <div class="crm-external-head is-compact">
        <div class="crm-external-head-copy">
            <p class="crm-kicker">{{ $channelLabel }} thread</p>
            <h2>{{ $discussionThread->subject }}</h2>
            <p>Conversation with {{ $recipientLabel }}, including outbound history, attachments, and the next reply from this dedicated thread.</p>
            <div class="crm-discussion-channel-pills">
                <span class="crm-pill {{ $statusClass }}">{{ $deliveryStatuses[$discussionThread->delivery_status] ?? ucfirst(str_replace('_', ' ', $discussionThread->delivery_status)) }}</span>
                <span class="crm-pill muted">{{ $discussionThread->messages->count() }} message(s)</span>
                <span class="crm-pill muted">{{ $attachmentCount }} attachment(s)</span>
            </div>
        </div>
        <div class="crm-external-stat-grid is-compact">
            <article class="crm-external-stat">
                <span>Status</span>
                <strong>{{ ucfirst(str_replace('_', ' ', $discussionThread->delivery_status)) }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Recipient</span>
                <strong>{{ $discussionThread->recipient_user_id ? 'Internal' : 'External' }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Last activity</span>
                <strong>{{ optional($discussionThread->last_message_at)->format('d M') ?: 'None' }}</strong>
            </article>
        </div>
    </div>

    <div class="crm-discussion-split crm-external-thread-split" data-crm-active-discussion-thread="{{ $discussionThread->id }}">
        <section class="crm-card crm-external-main-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $channelLabel }} timeline</p>
                    <h2>Conversation history</h2>
                </div>
            </div>

            @if ($discussionThread->messages->isEmpty())
                <div class="crm-empty">No messages have been recorded for this thread yet.</div>
            @else
                <div class="crm-discussion-timeline">
                    @foreach ($discussionThread->messages as $message)
                        @php($receipt = $messageReceipt($message))
                        <article class="crm-discussion-timeline-item">
                            <div class="crm-discussion-timeline-head">
                                <div>
                                    <strong>{{ $message->user?->name ?: 'System' }}</strong>
                                    <div class="crm-muted">{{ ucfirst($message->direction) }} · {{ optional($message->sent_at ?: $message->created_at)->format('d M Y H:i') }}</div>
                                </div>
                                <span class="crm-pill {{ $message->delivery_status === 'failed' ? 'danger' : ($message->delivery_status === 'sent' ? 'success' : 'warning') }}">
                                    {{ $deliveryStatuses[$message->delivery_status] ?? ucfirst(str_replace('_', ' ', $message->delivery_status)) }}
                                </span>
                            </div>

                            <div class="crm-discussion-timeline-copy">{!! $message->renderedBody($currentUser) !!}</div>

                            @if ($message->attachments->isNotEmpty())
                                <div class="crm-attachments-grid crm-discussion-attachment-list">
                                    @foreach ($message->attachments as $attachment)
                                        <article class="crm-attachment-card crm-discussion-attachment-row">
                                            <div class="crm-discussion-attachment-file">
                                                <span class="crm-discussion-attachment-badge"><i class="{{ $attachment->iconClass() }}"></i></span>
                                                <div class="crm-attachment-copy crm-discussion-attachment-copy">
                                                    <strong title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</strong>
                                                    <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                                                </div>
                                            </div>
                                            <div class="crm-action-row crm-discussion-attachment-actions">
                                                <a href="{{ route('crm.discussions.app.attachments.open', $attachment) }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}" target="_blank" rel="noopener">
                                                    <i class="bx bx-link-external"></i> Open
                                                </a>
                                                <a href="{{ route('crm.discussions.app.attachments.download', $attachment) }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                                                    <i class="bx bx-download"></i> Download
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif

                            @if ($receipt)
                                <div class="crm-message-receipt {{ $receipt['class'] }}">{{ $receipt['label'] }}</div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="crm-stack crm-external-side-column">
            <section class="crm-card crm-external-side-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Reply</p>
                        <h2>Send another {{ strtolower($channelLabel) }}</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route($routeBase . '.direct.reply', $discussionThread) }}" class="crm-form" enctype="multipart/form-data" data-live-composer-form>
                    @csrf
                    <div class="crm-field">
                        <label for="body">Message body</label>
                        <textarea id="body" name="body" placeholder="Write the reply body" required data-live-composer-input>{{ old('body') }}</textarea>
                        <div class="crm-live-composer-hint">Enter to send • Shift+Enter for a new line</div>
                    </div>

                    @include('crm.discussions.partials.attachment-dropzone', [
                        'inputId' => 'reply-attachments',
                        'title' => 'Attachments',
                        'hint' => 'Attach files for this outbound reply.',
                    ])

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="bx bx-send"></i> Send {{ strtolower($channelLabel) }}</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="crm-card crm-external-side-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Thread summary</p>
                        <h2>Routing and status</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Status</span>
                        <strong><span class="crm-pill {{ $statusClass }}">{{ $deliveryStatuses[$discussionThread->delivery_status] ?? ucfirst(str_replace('_', ' ', $discussionThread->delivery_status)) }}</span></strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Recipient</span>
                        <strong>{{ $recipientLabel }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $discussionThread->initiatedBy?->name ?: 'Unknown sender' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Last activity</span>
                        <strong>{{ optional($discussionThread->last_message_at)->format('d M Y H:i') ?: 'Not available' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Internal notes</span>
                        <strong>{{ $discussionThread->notes ?: 'No notes recorded for this thread.' }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@include('crm.discussions.partials.live-composer-shortcuts')

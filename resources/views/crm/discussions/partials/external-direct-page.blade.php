@php
    $discussionThread = $discussionThread ?? null;
    $draftMessage = $discussionThread?->messages?->last();
    $channelKey = $channelKey ?? strtolower((string) $channelLabel);
    $draftAttachmentCount = $draftMessage?->attachments?->count() ?? 0;
    $recipientLabel = $discussionThread?->recipientUser?->name
        ?: ($discussionThread?->recipient_email ?: ($discussionThread?->recipient_phone ?: 'Recipient pending'));
@endphp

<div class="crm-external-channel is-{{ $channelKey }} crm-external-compose-page">
    @if ($sourceContext)
        <div class="crm-discussion-source-card">
            <div>
                <strong>{{ $sourceContext['title'] }}</strong>
                <span>The latest private PDF is reused automatically when the {{ strtolower($channelLabel) }} message is sent.</span>
            </div>
            <span class="crm-pill primary">{{ ucfirst($sourceContext['type']) }} linked</span>
        </div>
    @endif

    <div class="crm-external-head is-compact">
        <div class="crm-external-head-copy">
            <p class="crm-kicker">{{ $discussionThread ? 'Direct draft' : 'New direct message' }}</p>
            <h2>{{ $discussionThread ? 'Edit ' . $channelLabel . ' draft' : 'Create a ' . strtolower($channelLabel) . ' draft' }}</h2>
            <p>Keep direct {{ strtolower($channelLabel) }} drafts focused on a single recipient, then continue from the thread once the message is sent.</p>
        </div>
        <div class="crm-external-stat-grid is-compact">
            <article class="crm-external-stat">
                <span>Recipient</span>
                <strong>{{ $discussionThread ? '1' : 'Pending' }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Attachments</span>
                <strong>{{ $draftAttachmentCount }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Status</span>
                <strong>{{ $discussionThread ? ucfirst($discussionThread->status) : 'New' }}</strong>
            </article>
        </div>
    </div>

    <div class="crm-discussion-split crm-external-compose-split">
        <section class="crm-card crm-external-main-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $discussionThread ? 'Draft details' : 'Direct compose' }}</p>
                    <h2>{{ $discussionThread ? 'Edit ' . $channelLabel . ' draft' : 'Message setup' }}</h2>
                </div>
            </div>

            @include('crm.discussions.partials.external-direct-form', [
                'action' => $action,
                'method' => $method ?? null,
                'cancelUrl' => $cancelUrl,
                'channelKey' => $channelKey,
                'channelLabel' => $channelLabel,
                'discussionThread' => $discussionThread,
                'sourceContext' => $sourceContext,
                'users' => $users,
                'leads' => $leads,
                'customers' => $customers,
                'contacts' => $contacts,
                'integrations' => $integrations,
            ])
        </section>

        <div class="crm-stack crm-external-side-column">
            <section class="crm-card crm-external-side-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Workflow</p>
                        <h2>How this page works</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Recipient scope</span>
                        <strong>One recipient per draft, with a separate edit page until it is sent.</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Address resolution</span>
                        <strong>CRM records use their stored channel address, and manual entry is reserved for ad hoc recipients.</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Attachments</span>
                        <strong>Files stay on the private documents disk and travel with the outbound message.</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Sent edits</span>
                        <strong>Once sent, the original body stays immutable and follow-ups are added as new outbound messages.</strong>
                    </div>
                </div>
            </section>

            @if ($discussionThread)
                <section class="crm-card crm-external-side-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Draft snapshot</p>
                            <h2>Current state</h2>
                        </div>
                    </div>

                    <div class="crm-meta-list">
                        <div class="crm-meta-row">
                            <span>Status</span>
                            <strong>{{ ucfirst($discussionThread->status) }}</strong>
                        </div>
                        <div class="crm-meta-row">
                            <span>Recipient</span>
                            <strong>{{ $recipientLabel }}</strong>
                        </div>
                        <div class="crm-meta-row">
                            <span>Last edited</span>
                            <strong>{{ optional($discussionThread->metadata_updated_at ?: $discussionThread->updated_at)->format('d M Y H:i') ?: 'Just now' }}</strong>
                        </div>
                    </div>
                </section>

                @if ($draftMessage)
                    <section class="crm-card crm-external-side-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Preview</p>
                                <h2>Latest message copy</h2>
                            </div>
                        </div>

                        <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $draftMessage->body))), 240) }}</div>
                    </section>
                @endif
            @endif
        </div>
    </div>
</div>

@php
    $discussionThread = $discussionThread ?? null;
    $draftMessage = $discussionThread?->messages?->last();
@endphp

@if ($sourceContext)
    <div class="crm-discussion-source-card">
        <div>
            <strong>{{ $sourceContext['title'] }}</strong>
            <span>The latest private PDF is reused automatically when the {{ strtolower($channelLabel) }} message is sent.</span>
        </div>
        <span class="crm-pill primary">{{ ucfirst($sourceContext['type']) }} linked</span>
    </div>
@endif

<div class="crm-discussion-split">
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">{{ $discussionThread ? 'Draft details' : 'New direct message' }}</p>
                <h2>{{ $discussionThread ? 'Edit ' . $channelLabel . ' draft' : 'Create a ' . strtolower($channelLabel) . ' draft' }}</h2>
            </div>
        </div>

        @include('crm.discussions.partials.external-direct-form', [
            'action' => $action,
            'method' => $method ?? null,
            'cancelUrl' => $cancelUrl,
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

    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Channel rules</p>
                    <h2>How this draft behaves</h2>
                </div>
            </div>

            <div class="crm-meta-list">
                <div class="crm-meta-row">
                    <span>Direct recipient</span>
                    <strong>One recipient per draft, with a separate edit page until sent.</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Address resolution</span>
                    <strong>CRM records prefill the best email or phone and manual overrides stay available.</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Attachments</span>
                    <strong>Files uploaded on this page are stored on the private documents disk and sent with the message.</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Sent body changes</span>
                    <strong>Once sent, the body is immutable and replies create new outbound messages instead.</strong>
                </div>
            </div>
        </section>

        @if ($discussionThread)
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Draft summary</p>
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
                        <strong>{{ $discussionThread->recipientUser?->name ?: ($discussionThread->recipient_email ?: ($discussionThread->recipient_phone ?: 'Manual recipient')) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Integration</span>
                        <strong>{{ $discussionThread->integration?->name ?: 'No integration selected' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Last edited</span>
                        <strong>{{ optional($discussionThread->metadata_updated_at ?: $discussionThread->updated_at)->format('d M Y H:i') ?: 'Just now' }}</strong>
                    </div>
                </div>
            </section>

            @if ($draftMessage)
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Draft preview</p>
                            <h2>Latest message copy</h2>
                        </div>
                    </div>

                    <div class="crm-discussion-message-preview">{{ $draftMessage->body }}</div>
                </section>
            @endif
        @endif
    </div>
</div>

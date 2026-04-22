@php
    $routeBase = $routeBase ?? 'crm.discussions.email';
@endphp

@include('crm.partials.helper-text', [
    'title' => $channelLabel . ' Channel',
    'content' => 'Direct pages are for one recipient at a time. Bulk pages resolve recipients from CRM users, leads, customers, and contacts, then fan out channel-specific sends from a dedicated draft.',
])

<div class="crm-choice-grid">
    <section class="crm-discussion-channel-card">
        <div>
            <p class="crm-kicker">Direct messaging</p>
            <h3>One recipient, one thread</h3>
            <p>Create a dedicated {{ strtolower($channelLabel) }} draft for a single CRM user, lead, customer, contact, or manual address.</p>
        </div>
        <div class="crm-discussion-channel-pills">
            <span class="crm-pill primary">Dedicated create page</span>
            <span class="crm-pill muted">Separate draft edit page</span>
        </div>
        <div class="crm-action-row">
            <a href="{{ route($routeBase . '.direct.create') }}" class="btn btn-primary">
                <i class="bx bx-send"></i> New direct {{ strtolower($channelLabel) }}
            </a>
        </div>
    </section>

    <section class="crm-discussion-channel-card">
        <div>
            <p class="crm-kicker">Bulk messaging</p>
            <h3>Audience-based send</h3>
            <p>Build a recipient snapshot from CRM records, save the draft independently, then send the campaign when it is ready.</p>
        </div>
        <div class="crm-discussion-channel-pills">
            <span class="crm-pill primary">Audience snapshot</span>
            <span class="crm-pill muted">{{ $campaigns->count() }} recent campaign(s)</span>
        </div>
        <div class="crm-action-row">
            <a href="{{ route($routeBase . '.bulk.create') }}" class="btn btn-primary">
                <i class="bx bx-layer-plus"></i> New bulk {{ strtolower($channelLabel) }}
            </a>
        </div>
    </section>
</div>

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">{{ $channelLabel }} threads</p>
            <h2>Recent direct conversations</h2>
        </div>
    </div>

    @if ($threads->isEmpty())
        <div class="crm-empty">No {{ strtolower($channelLabel) }} threads are available yet.</div>
    @else
        <div class="crm-stack">
            @foreach ($threads as $thread)
                @php
                    $threadUrl = $thread->status === 'draft'
                        ? route($routeBase . '.direct.edit', $thread)
                        : route($routeBase . '.direct.show', $thread);
                    $recipientLabel = $thread->recipientUser?->name
                        ?: ($thread->recipient_email ?: ($thread->recipient_phone ?: 'Manual recipient'));
                    $statusClass = match ($thread->delivery_status) {
                        'failed' => 'danger',
                        'queued', 'pending_integration' => 'warning',
                        default => 'primary',
                    };
                @endphp

                <article class="crm-discussion-thread-card">
                    <div class="crm-discussion-thread-head">
                        <div>
                            <p class="crm-kicker">{{ $thread->status === 'draft' ? 'Draft' : 'Thread' }}</p>
                            <h3><a href="{{ $threadUrl }}">{{ $thread->subject }}</a></h3>
                        </div>
                        <span class="crm-pill {{ $statusClass }}">{{ $deliveryStatuses[$thread->delivery_status] ?? ucfirst(str_replace('_', ' ', $thread->delivery_status)) }}</span>
                    </div>
                    <div class="crm-discussion-meta-row">
                        <span class="crm-pill muted">{{ $recipientLabel }}</span>
                        @if ($thread->integration)
                            <span class="crm-pill muted">{{ $thread->integration->name }}</span>
                        @endif
                        @if ($thread->source_type && $thread->source_id)
                            <span class="crm-pill primary">{{ ucfirst($thread->source_type) }} linked</span>
                        @endif
                    </div>
                    @if (filled($thread->latestMessage?->body))
                        <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit($thread->latestMessage->body, 220) }}</div>
                    @endif
                    <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <span class="crm-muted">Last activity {{ optional($thread->last_message_at)->format('d M Y H:i') ?: 'Not yet sent' }}</span>
                        <div class="crm-action-row">
                            <a href="{{ $threadUrl }}" class="btn btn-light crm-btn-light">
                                <i class="bx bx-right-arrow-alt"></i> {{ $thread->status === 'draft' ? 'Open draft' : 'Open thread' }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @include('crm.partials.pager', ['paginator' => $threads])
    @endif
</section>

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">{{ $channelLabel }} campaigns</p>
            <h2>Recent bulk drafts and sends</h2>
        </div>
    </div>

    @if ($campaigns->isEmpty())
        <div class="crm-empty">No {{ strtolower($channelLabel) }} campaigns have been saved yet.</div>
    @else
        <div class="crm-stack">
            @foreach ($campaigns as $campaign)
                @php
                    $snapshot = $campaign->audience_snapshot ?? [];
                    $resolvedRecipients = collect($snapshot['resolved'] ?? []);
                    $skippedRecipients = collect($snapshot['skipped'] ?? []);
                    $statusClass = match ($campaign->status) {
                        'failed' => 'danger',
                        'draft' => 'muted',
                        default => 'success',
                    };
                @endphp

                <article class="crm-discussion-campaign-card">
                    <div class="crm-discussion-campaign-head">
                        <div>
                            <p class="crm-kicker">{{ $campaign->status === 'draft' ? 'Draft campaign' : 'Sent campaign' }}</p>
                            <h3><a href="{{ route($routeBase . '.bulk.edit', $campaign) }}">{{ $campaign->subject }}</a></h3>
                        </div>
                        <span class="crm-pill {{ $statusClass }}">{{ ucfirst($campaign->status) }}</span>
                    </div>
                    <div class="crm-discussion-recipient-pills">
                        <span class="crm-discussion-recipient-pill">{{ $resolvedRecipients->count() }} resolved recipient(s)</span>
                        @if ($skippedRecipients->isNotEmpty())
                            <span class="crm-discussion-recipient-pill">{{ $skippedRecipients->count() }} skipped</span>
                        @endif
                        @if ($campaign->integration)
                            <span class="crm-discussion-recipient-pill">{{ $campaign->integration->name }}</span>
                        @endif
                    </div>
                    <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit($campaign->body, 220) }}</div>
                    <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <span class="crm-muted">{{ $campaign->last_sent_at ? 'Sent ' . $campaign->last_sent_at->format('d M Y H:i') : 'Updated ' . $campaign->updated_at?->format('d M Y H:i') }}</span>
                        <div class="crm-action-row">
                            <a href="{{ route($routeBase . '.bulk.edit', $campaign) }}" class="btn btn-light crm-btn-light">
                                <i class="bx bx-right-arrow-alt"></i> Open {{ $campaign->status === 'draft' ? 'draft' : 'campaign' }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

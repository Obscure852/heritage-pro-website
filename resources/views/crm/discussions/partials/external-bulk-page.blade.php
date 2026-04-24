@php
    $discussionCampaign = $discussionCampaign ?? null;
    $snapshot = $discussionCampaign?->audience_snapshot ?? [];
    $resolvedRecipients = collect($snapshot['resolved'] ?? []);
    $skippedRecipients = collect($snapshot['skipped'] ?? []);
    $isDraft = $discussionCampaign?->status !== 'sent' && $discussionCampaign?->status !== 'failed';
    $channelKey = $channelKey ?? strtolower((string) $channelLabel);
@endphp

<div class="crm-external-channel is-{{ $channelKey }} crm-external-compose-page">
    @if ($sourceContext)
        <div class="crm-discussion-source-card">
            <div>
                <strong>{{ $sourceContext['title'] }}</strong>
                <span>The linked commercial PDF is attached automatically to each generated outbound thread when the campaign sends.</span>
            </div>
            <span class="crm-pill primary">{{ ucfirst($sourceContext['type']) }} linked</span>
        </div>
    @endif

    <div class="crm-external-head is-compact">
        <div class="crm-external-head-copy">
            <p class="crm-kicker">{{ $discussionCampaign ? ucfirst($discussionCampaign->status) . ' campaign' : 'New bulk message' }}</p>
            <h2>{{ $discussionCampaign && ! $isDraft ? $channelLabel . ' campaign summary' : 'Build a ' . strtolower($channelLabel) . ' bulk campaign' }}</h2>
            <p>Save an audience snapshot, review channel-ready copy, and send the campaign independently from one-off direct threads.</p>
        </div>
        <div class="crm-external-stat-grid is-compact">
            <article class="crm-external-stat">
                <span>Resolved</span>
                <strong>{{ $resolvedRecipients->count() }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Skipped</span>
                <strong>{{ $skippedRecipients->count() }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Status</span>
                <strong>{{ $discussionCampaign ? ucfirst($discussionCampaign->status) : 'Draft' }}</strong>
            </article>
        </div>
    </div>

    <div class="crm-discussion-split crm-external-compose-split">
        <section class="crm-card crm-external-main-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $discussionCampaign ? ucfirst($discussionCampaign->status) . ' campaign' : 'Bulk compose' }}</p>
                    <h2>{{ $discussionCampaign && ! $isDraft ? 'Campaign summary' : 'Campaign setup' }}</h2>
                </div>
            </div>

            @if (! $discussionCampaign || $isDraft)
                @include('crm.discussions.partials.external-bulk-form', [
                    'action' => $action,
                    'method' => $method ?? null,
                    'cancelUrl' => $cancelUrl,
                    'channelKey' => $channelKey,
                    'channelLabel' => $channelLabel,
                    'discussionCampaign' => $discussionCampaign,
                    'sourceContext' => $sourceContext,
                    'users' => $users,
                    'leads' => $leads,
                    'customers' => $customers,
                    'contacts' => $contacts,
                    'integrations' => $integrations,
                ])
            @else
                <div class="crm-stack">
                    <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $discussionCampaign->body))), 240) }}</div>
                    <div class="crm-meta-list">
                        <div class="crm-meta-row">
                            <span>Status</span>
                            <strong>{{ ucfirst($discussionCampaign->status) }}</strong>
                        </div>
                        <div class="crm-meta-row">
                            <span>Last sent</span>
                            <strong>{{ optional($discussionCampaign->last_sent_at)->format('d M Y H:i') ?: 'Not available' }}</strong>
                        </div>
                        <div class="crm-meta-row">
                            <span>Notes</span>
                            <strong>{{ $discussionCampaign->notes ?: 'No campaign notes were added.' }}</strong>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <div class="crm-stack crm-external-side-column">
            <section class="crm-card crm-external-side-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Audience snapshot</p>
                        <h2>Resolved recipients</h2>
                    </div>
                </div>

                @if ($resolvedRecipients->isEmpty())
                    <div class="crm-empty">Recipients appear here once the campaign draft has been saved.</div>
                @else
                    <div class="crm-stack">
                        @foreach ($resolvedRecipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                                    <strong>{{ $recipient['label'] ?? 'Recipient' }}</strong>
                                    <span class="crm-pill muted">{{ ucfirst($recipient['recipient_type'] ?? 'manual') }}</span>
                                </div>
                                <p>{{ $recipient['address'] ?? ($recipient['email'] ?? ($recipient['phone'] ?? 'No channel address')) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            @if ($skippedRecipients->isNotEmpty())
                <section class="crm-card crm-external-side-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Skipped recipients</p>
                            <h2>Records needing channel details</h2>
                        </div>
                    </div>

                    <div class="crm-stack">
                        @foreach ($skippedRecipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <strong>{{ $recipient['label'] ?? 'Recipient' }}</strong>
                                <p>{{ $recipient['error'] ?? 'This record could not be resolved for the selected channel.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($discussionCampaign && $discussionCampaign->recipients->isNotEmpty())
                <section class="crm-card crm-external-side-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Send results</p>
                            <h2>Generated recipient records</h2>
                        </div>
                    </div>

                    <div class="crm-stack">
                        @foreach ($discussionCampaign->recipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                                    <strong>{{ $recipient->recipient_label ?: 'Recipient' }}</strong>
                                    <span class="crm-pill {{ $recipient->delivery_status === 'failed' ? 'danger' : ($recipient->delivery_status === 'sent' ? 'success' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $recipient->delivery_status ?: 'queued')) }}
                                    </span>
                                </div>
                                <p>{{ $recipient->recipient_address ?: 'Internal recipient' }}</p>
                                @if ($recipient->thread)
                                    <div class="crm-action-row">
                                        <a href="{{ route($routeBase . '.direct.show', $recipient->thread) }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                                            <i class="bx bx-right-arrow-alt"></i> Open thread
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>

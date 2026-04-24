@php
    $routeBase = $routeBase ?? 'crm.discussions.email';
    $channelKey = $channelKey ?? (str_contains($routeBase, '.whatsapp') ? 'whatsapp' : 'email');
    $channelIcon = $channelKey === 'whatsapp' ? 'bx bxl-whatsapp' : 'bx bx-envelope';
    $draftCount = $threads->where('status', 'draft')->count();
    $sentCount = $threads->where('status', 'sent')->count();
    $resolvedCampaignRecipients = $campaigns->sum(fn ($campaign) => count(data_get($campaign->audience_snapshot, 'resolved', [])));
@endphp

<div class="crm-external-channel is-{{ $channelKey }}">
    <div class="crm-external-head">
        <div class="crm-external-head-copy">
            <p class="crm-kicker">{{ $channelLabel }} workspace</p>
            <h2>{{ $channelLabel }}</h2>
            <p>Dedicated direct threads and bulk sends in one calmer channel workspace, with each flow kept on its own draft or thread page.</p>
            <div class="crm-discussion-channel-pills">
                <span class="crm-pill primary">Direct drafts</span>
                <span class="crm-pill muted">Bulk campaigns</span>
                <span class="crm-pill muted">Separate thread history</span>
            </div>
        </div>
        <div class="crm-external-stat-grid" aria-label="{{ $channelLabel }} overview">
            <article class="crm-external-stat">
                <span>Threads</span>
                <strong>{{ $threads->total() }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Campaigns</span>
                <strong>{{ $campaigns->count() }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Drafts</span>
                <strong>{{ $draftCount }}</strong>
            </article>
            <article class="crm-external-stat">
                <span>Sent</span>
                <strong>{{ $sentCount }}</strong>
            </article>
        </div>
    </div>

    <div class="crm-external-index-shell">
        <section class="crm-external-panel">
            <div class="crm-external-panel-head">
                <div>
                    <p class="crm-kicker">Launchpad</p>
                    <h3>Start a {{ strtolower($channelLabel) }} workflow</h3>
                </div>
                <span class="crm-pill muted">{{ $campaigns->count() + $threads->total() }} total items</span>
            </div>
            <div class="crm-external-panel-body crm-external-launchpad">
                <article class="crm-external-launch-card">
                    <span class="crm-external-launch-icon">
                        <i class="{{ $channelIcon }}"></i>
                    </span>
                    <div class="crm-external-launch-copy">
                        <p class="crm-kicker">Direct</p>
                        <h3>One recipient, one thread</h3>
                        <p>Open a dedicated direct {{ strtolower($channelLabel) }} draft for a CRM user, lead, customer, contact, or manual address.</p>
                        <div class="crm-discussion-channel-pills">
                            <span class="crm-pill primary">Dedicated create page</span>
                            <span class="crm-pill muted">{{ $draftCount }} open draft(s)</span>
                        </div>
                    </div>
                    <div class="crm-external-launch-actions">
                        <a href="{{ route($routeBase . '.direct.create') }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                            <i class="{{ $channelIcon }}"></i> New direct
                        </a>
                    </div>
                </article>

                <article class="crm-external-launch-card">
                    <span class="crm-external-launch-icon">
                        <i class="bx bx-layer-plus"></i>
                    </span>
                    <div class="crm-external-launch-copy">
                        <p class="crm-kicker">Bulk</p>
                        <h3>Audience-based send</h3>
                        <p>Build a saved audience snapshot from CRM records, review the message, then send it as a separate campaign run.</p>
                        <div class="crm-discussion-channel-pills">
                            <span class="crm-pill primary">Audience snapshot</span>
                            <span class="crm-pill muted">{{ $resolvedCampaignRecipients }} resolved recipients</span>
                        </div>
                    </div>
                    <div class="crm-external-launch-actions">
                        <a href="{{ route($routeBase . '.bulk.create') }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                            <i class="bx bx-layer-plus"></i> New bulk
                        </a>
                    </div>
                </article>
            </div>
        </section>

        <div class="crm-external-activity-column">
            <section class="crm-external-panel">
                <div class="crm-external-panel-head">
                    <div>
                        <p class="crm-kicker">{{ $channelLabel }} threads</p>
                        <h3>Recent direct conversations</h3>
                    </div>
                </div>

                @if ($threads->isEmpty())
                    <div class="crm-external-panel-body">
                        <div class="crm-empty">No {{ strtolower($channelLabel) }} threads are available yet.</div>
                    </div>
                @else
                    <div class="crm-external-panel-body">
                        <div class="crm-external-activity-list">
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

                                <article class="crm-external-activity-row">
                                    <span class="crm-external-activity-icon">
                                        <i class="{{ $thread->status === 'draft' ? 'bx bx-edit-alt' : $channelIcon }}"></i>
                                    </span>
                                    <div class="crm-external-activity-main">
                                        <div class="crm-external-activity-top">
                                            <div>
                                                <p class="crm-kicker">{{ $thread->status === 'draft' ? 'Draft thread' : 'Conversation' }}</p>
                                                <h3><a href="{{ $threadUrl }}">{{ $thread->subject }}</a></h3>
                                            </div>
                                            <span class="crm-pill {{ $statusClass }}">{{ $deliveryStatuses[$thread->delivery_status] ?? ucfirst(str_replace('_', ' ', $thread->delivery_status)) }}</span>
                                        </div>
                                        <div class="crm-discussion-meta-row">
                                            <span class="crm-discussion-recipient-pill">{{ $recipientLabel }}</span>
                                            @if ($thread->source_type && $thread->source_id)
                                                <span class="crm-discussion-recipient-pill">{{ ucfirst($thread->source_type) }} linked</span>
                                            @endif
                                        </div>
                                        @if (filled($thread->latestMessage?->body))
                                            <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $thread->latestMessage->body))), 180) }}</div>
                                        @endif
                                        <div class="crm-external-activity-foot">
                                            <span class="crm-muted">Last activity {{ optional($thread->last_message_at)->format('d M Y H:i') ?: 'Not yet sent' }}</span>
                                            <a href="{{ $threadUrl }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                                                <i class="bx bx-right-arrow-alt"></i> {{ $thread->status === 'draft' ? 'Open draft' : 'Open thread' }}
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @include('crm.partials.pager', ['paginator' => $threads])
                    </div>
                @endif
            </section>

            <section class="crm-external-panel">
                <div class="crm-external-panel-head">
                    <div>
                        <p class="crm-kicker">{{ $channelLabel }} campaigns</p>
                        <h3>Recent bulk drafts and sends</h3>
                    </div>
                </div>

                @if ($campaigns->isEmpty())
                    <div class="crm-external-panel-body">
                        <div class="crm-empty">No {{ strtolower($channelLabel) }} campaigns have been saved yet.</div>
                    </div>
                @else
                    <div class="crm-external-panel-body">
                        <div class="crm-external-activity-list">
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

                                <article class="crm-external-activity-row">
                                    <span class="crm-external-activity-icon">
                                        <i class="bx bx-layer-plus"></i>
                                    </span>
                                    <div class="crm-external-activity-main">
                                        <div class="crm-external-activity-top">
                                            <div>
                                                <p class="crm-kicker">{{ $campaign->status === 'draft' ? 'Draft campaign' : 'Sent campaign' }}</p>
                                                <h3><a href="{{ route($routeBase . '.bulk.edit', $campaign) }}">{{ $campaign->subject }}</a></h3>
                                            </div>
                                            <span class="crm-pill {{ $statusClass }}">{{ ucfirst($campaign->status) }}</span>
                                        </div>
                                        <div class="crm-discussion-meta-row">
                                            <span class="crm-discussion-recipient-pill">{{ $resolvedRecipients->count() }} resolved recipient(s)</span>
                                            @if ($skippedRecipients->isNotEmpty())
                                                <span class="crm-discussion-recipient-pill">{{ $skippedRecipients->count() }} skipped</span>
                                            @endif
                                        </div>
                                        <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $campaign->body))), 180) }}</div>
                                        <div class="crm-external-activity-foot">
                                            <span class="crm-muted">{{ $campaign->last_sent_at ? 'Sent ' . $campaign->last_sent_at->format('d M Y H:i') : 'Updated ' . $campaign->updated_at?->format('d M Y H:i') }}</span>
                                            <a href="{{ route($routeBase . '.bulk.edit', $campaign) }}" class="btn crm-app-btn {{ $channelKey === 'whatsapp' ? 'crm-app-btn-group' : 'crm-app-btn-open' }}">
                                                <i class="bx bx-right-arrow-alt"></i> Open {{ $campaign->status === 'draft' ? 'draft' : 'campaign' }}
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>

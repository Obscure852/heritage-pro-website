@extends('layouts.crm')

@section('title', 'CRM Discussions')
@section('crm_heading', 'Discussions')
@section('crm_subheading', 'Open the right channel, scan recent conversation activity, and jump back into live work without bouncing between mixed forms.')

@section('crm_actions')
    <div class="crm-action-row crm-discussions-actions">
        <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn crm-app-btn crm-app-btn-direct">
            <i class="bx bx-message-square-dots"></i> New app DM
        </a>
        <a href="{{ route('crm.discussions.email.direct.create') }}" class="btn crm-app-btn crm-app-btn-open">
            <i class="bx bx-envelope"></i> New email
        </a>
        <a href="{{ route('crm.discussions.whatsapp.direct.create') }}" class="btn crm-app-btn crm-app-btn-group">
            <i class="bx bxl-whatsapp"></i> New WhatsApp
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')

    @php
        $previewText = function (?string $value, string $fallback = 'No recent message preview available.'): string {
            $text = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));

            return $text !== '' ? \Illuminate\Support\Str::limit($text, 160) : $fallback;
        };

        $appThreadCount = $recentThreads->where('channel', 'app')->count();
        $externalThreadCount = $recentThreads->whereIn('channel', ['email', 'whatsapp'])->count();
    @endphp

    <div class="crm-discussions-hub">
        <section class="crm-discussions-metric-strip" aria-label="Discussion overview">
            <article class="crm-discussions-metric">
                <span>Channels</span>
                <strong>{{ count($discussionChannels) }}</strong>
            </article>
            <article class="crm-discussions-metric">
                <span>App activity</span>
                <strong>{{ $appThreadCount }}</strong>
            </article>
            <article class="crm-discussions-metric">
                <span>External threads</span>
                <strong>{{ $externalThreadCount }}</strong>
            </article>
            <article class="crm-discussions-metric">
                <span>Recent campaigns</span>
                <strong>{{ $recentCampaigns->count() }}</strong>
            </article>
        </section>

        <div class="crm-discussions-hub-grid">
            <section class="crm-discussions-surface crm-discussions-launchpad">
                <div class="crm-discussions-surface-head">
                    <div>
                        <p class="crm-kicker">Launchpad</p>
                        <h2>Choose a channel workspace</h2>
                    </div>
                    <span class="crm-discussions-surface-note">Direct and bulk work now live on separate surfaces.</span>
                </div>

                <div class="crm-discussions-channel-list">
                    <article class="crm-discussions-channel-row is-primary">
                        <span class="crm-discussions-channel-icon is-app"><i class="bx bx-message-square-dots"></i></span>
                        <div class="crm-discussions-channel-body">
                            <div class="crm-discussions-channel-copy">
                                <div class="crm-discussions-channel-head">
                                    <strong>App Messaging</strong>
                                    <span class="crm-pill primary">Internal workspace</span>
                                </div>
                                <p>Company chat, direct messages, reusable group chats, attachments, and in-app previews in one focused workspace.</p>
                            </div>
                            <div class="crm-discussion-recipient-pills">
                                <span class="crm-discussion-recipient-pill">Company chat</span>
                                <span class="crm-discussion-recipient-pill">Direct messages</span>
                                <span class="crm-discussion-recipient-pill">Group chats</span>
                            </div>
                        </div>
                        <div class="crm-discussions-inline-actions">
                            <a href="{{ route('crm.discussions.app.workspace') }}" class="btn crm-app-btn crm-app-btn-direct">
                                <i class="bx bx-layout"></i> Open workspace
                            </a>
                            <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
                                <i class="bx bx-group"></i> New group
                            </a>
                        </div>
                    </article>

                    <article class="crm-discussions-channel-row">
                        <span class="crm-discussions-channel-icon is-email"><i class="bx bx-envelope"></i></span>
                        <div class="crm-discussions-channel-body">
                            <div class="crm-discussions-channel-copy">
                                <div class="crm-discussions-channel-head">
                                    <strong>Email</strong>
                                    <span class="crm-pill muted">External channel</span>
                                </div>
                                <p>Direct outbound email and audience-based campaigns with CRM-resolved recipients and separate compose flows.</p>
                            </div>
                            <div class="crm-discussion-recipient-pills">
                                <span class="crm-discussion-recipient-pill">Direct compose</span>
                                <span class="crm-discussion-recipient-pill">Bulk campaigns</span>
                                <span class="crm-discussion-recipient-pill">Quote + invoice aware</span>
                            </div>
                        </div>
                        <div class="crm-discussions-inline-actions">
                            <a href="{{ route('crm.discussions.email.index') }}" class="btn crm-app-btn crm-app-btn-open">
                                <i class="bx bx-envelope-open"></i> Open channel
                            </a>
                            <a href="{{ route('crm.discussions.email.bulk.create') }}" class="btn crm-app-btn crm-app-btn-open">
                                <i class="bx bx-layer-plus"></i> New bulk
                            </a>
                        </div>
                    </article>

                    <article class="crm-discussions-channel-row">
                        <span class="crm-discussions-channel-icon is-whatsapp"><i class="bx bxl-whatsapp"></i></span>
                        <div class="crm-discussions-channel-body">
                            <div class="crm-discussions-channel-copy">
                                <div class="crm-discussions-channel-head">
                                    <strong>WhatsApp</strong>
                                    <span class="crm-pill muted">Provider ready</span>
                                </div>
                                <p>Direct and bulk WhatsApp drafts with the same CRM audit trail, ready to queue once live provider integrations are active.</p>
                            </div>
                            <div class="crm-discussion-recipient-pills">
                                <span class="crm-discussion-recipient-pill">Direct compose</span>
                                <span class="crm-discussion-recipient-pill">Bulk campaigns</span>
                                <span class="crm-discussion-recipient-pill">Queued when needed</span>
                            </div>
                        </div>
                        <div class="crm-discussions-inline-actions">
                            <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn crm-app-btn crm-app-btn-group">
                                <i class="bx bxl-whatsapp"></i> Open channel
                            </a>
                            <a href="{{ route('crm.discussions.whatsapp.bulk.create') }}" class="btn crm-app-btn crm-app-btn-group">
                                <i class="bx bx-layer-plus"></i> New bulk
                            </a>
                        </div>
                    </article>
                </div>
            </section>

            <div class="crm-discussions-activity-column">
                <section class="crm-discussions-surface">
                    <div class="crm-discussions-surface-head">
                        <div>
                            <p class="crm-kicker">Recent activity</p>
                            <h2>Latest threads</h2>
                        </div>
                        <span class="crm-discussions-surface-note">{{ $recentThreads->count() }} recent thread(s)</span>
                    </div>

                    @if ($recentThreads->isEmpty())
                        <div class="crm-empty">No discussion activity has been recorded yet.</div>
                    @else
                        <div class="crm-discussions-activity-list">
                            @foreach ($recentThreads as $thread)
                                @php
                                    $recipientLabel = $thread->isCompanyChat()
                                        ? 'Company Chat'
                                        : ($thread->isGroupChat()
                                            ? $thread->subject
                                            : ($thread->counterpartFor(auth()->user())?->name
                                                ?: ($thread->recipientUser?->name ?: ($thread->recipient_email ?: ($thread->recipient_phone ?: 'Manual recipient')))));
                                    $statusClass = match ($thread->delivery_status) {
                                        'failed' => 'danger',
                                        'queued', 'pending_integration' => 'warning',
                                        default => 'primary',
                                    };
                                    $channelClass = match ($thread->channel) {
                                        'app' => 'is-app',
                                        'whatsapp' => 'is-whatsapp',
                                        default => 'is-email',
                                    };
                                @endphp

                                <article class="crm-discussions-activity-row">
                                    <span class="crm-discussions-activity-icon {{ $channelClass }}">
                                        <i class="{{ $thread->channel === 'app' ? 'bx bx-message-square-dots' : ($thread->channel === 'whatsapp' ? 'bx bxl-whatsapp' : 'bx bx-envelope') }}"></i>
                                    </span>
                                    <div class="crm-discussions-activity-copy">
                                        <div class="crm-discussions-activity-head">
                                            <strong>{{ $thread->subject }}</strong>
                                            <span class="crm-discussions-activity-time">{{ optional($thread->last_message_at)->format('d M Y H:i') ?: 'Not available' }}</span>
                                        </div>
                                        <div class="crm-discussions-activity-meta">
                                            <span class="crm-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $thread->delivery_status)) }}</span>
                                            <span class="crm-pill muted">{{ $discussionChannels[$thread->channel] ?? ucfirst($thread->channel) }}</span>
                                            <span class="crm-pill muted">{{ $recipientLabel }}</span>
                                        </div>
                                        <p class="crm-discussions-activity-preview">{{ $previewText($thread->latestMessage?->body, 'Open the thread to see the latest discussion details.') }}</p>
                                    </div>
                                    <a href="{{ route('crm.discussions.show', $thread) }}" class="btn crm-icon-btn crm-icon-btn-open" aria-label="Open thread" title="Open thread">
                                        <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="crm-discussions-surface">
                    <div class="crm-discussions-surface-head">
                        <div>
                            <p class="crm-kicker">Bulk activity</p>
                            <h2>Latest campaigns</h2>
                        </div>
                        <span class="crm-discussions-surface-note">{{ $recentCampaigns->count() }} recent campaign(s)</span>
                    </div>

                    @if ($recentCampaigns->isEmpty())
                        <div class="crm-empty">No bulk campaign activity has been recorded yet.</div>
                    @else
                        <div class="crm-discussions-activity-list">
                            @foreach ($recentCampaigns as $campaign)
                                @php
                                    $snapshot = $campaign->audience_snapshot ?? [];
                                    $resolvedRecipients = collect($snapshot['resolved'] ?? []);
                                    $campaignUrl = match ($campaign->channel) {
                                        'app' => route('crm.discussions.app.bulk.edit', $campaign),
                                        'whatsapp' => route('crm.discussions.whatsapp.bulk.edit', $campaign),
                                        default => route('crm.discussions.email.bulk.edit', $campaign),
                                    };
                                    $statusClass = match ($campaign->status) {
                                        'failed' => 'danger',
                                        'draft' => 'muted',
                                        default => 'success',
                                    };
                                    $channelClass = match ($campaign->channel) {
                                        'app' => 'is-app',
                                        'whatsapp' => 'is-whatsapp',
                                        default => 'is-email',
                                    };
                                @endphp

                                <article class="crm-discussions-activity-row">
                                    <span class="crm-discussions-activity-icon {{ $channelClass }}">
                                        <i class="{{ $campaign->channel === 'app' ? 'bx bx-group' : ($campaign->channel === 'whatsapp' ? 'bx bxl-whatsapp' : 'bx bx-envelope') }}"></i>
                                    </span>
                                    <div class="crm-discussions-activity-copy">
                                        <div class="crm-discussions-activity-head">
                                            <strong>{{ $campaign->subject }}</strong>
                                            <span class="crm-discussions-activity-time">{{ $campaign->last_sent_at ? $campaign->last_sent_at->format('d M Y H:i') : ($campaign->updated_at?->format('d M Y H:i') ?: 'Not available') }}</span>
                                        </div>
                                        <div class="crm-discussions-activity-meta">
                                            <span class="crm-pill {{ $statusClass }}">{{ ucfirst($campaign->status) }}</span>
                                            <span class="crm-pill muted">{{ $discussionChannels[$campaign->channel] ?? ucfirst($campaign->channel) }}</span>
                                            <span class="crm-pill muted">{{ $resolvedRecipients->count() }} resolved</span>
                                        </div>
                                        <p class="crm-discussions-activity-preview">{{ $previewText($campaign->body, 'Open the campaign to review the configured audience.') }}</p>
                                    </div>
                                    <a href="{{ $campaignUrl }}" class="btn crm-icon-btn crm-icon-btn-open" aria-label="Open campaign" title="Open campaign">
                                        <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

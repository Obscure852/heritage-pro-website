@extends('layouts.crm')

@section('title', 'CRM Discussions')
@section('crm_heading', 'Discussions')
@section('crm_subheading', 'Separate channel workspaces for Slack-like app messaging, outbound email, and WhatsApp-ready direct and bulk communication.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => count($discussionChannels), 'label' => 'CHANNELS'])
    @include('crm.partials.header-stat', ['value' => $recentThreads->count(), 'label' => 'RECENT THREADS'])
    @include('crm.partials.header-stat', ['value' => $recentCampaigns->count(), 'label' => 'RECENT CAMPAIGNS'])
@endsection

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.app.direct.create') }}" class="btn btn-primary">
            <i class="bx bx-message-square-dots"></i> New app DM
        </a>
        <a href="{{ route('crm.discussions.email.direct.create') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-envelope"></i> New email
        </a>
        <a href="{{ route('crm.discussions.whatsapp.direct.create') }}" class="btn btn-light crm-btn-light">
            <i class="bx bxl-whatsapp"></i> New WhatsApp
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'hub'])

    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Channel Hub',
            'content' => 'Each channel now has its own direct and bulk create/edit pages. App Messaging opens a persistent company chat plus direct messages and group chats, while email and WhatsApp keep dedicated draft and send flows.',
        ])

        <div class="crm-choice-grid">
            <section class="crm-discussion-channel-card">
                <div>
                    <p class="crm-kicker">App Messaging</p>
                    <h3>Slack-like internal workspace</h3>
                    <p>Open company chat, resume direct messages, create focused group chats, share attachments, and keep previews inside the CRM conversation view.</p>
                </div>
                <div class="crm-discussion-channel-pills">
                    <span class="crm-pill primary">Company chat</span>
                    <span class="crm-pill primary">Direct messages</span>
                    <span class="crm-pill primary">Group chats</span>
                    <span class="crm-pill muted">Attachment previews</span>
                </div>
                <div class="crm-action-row">
                    <a href="{{ route('crm.discussions.app.workspace') }}" class="btn btn-primary">
                        <i class="bx bx-layout"></i> Open app workspace
                    </a>
                    <a href="{{ route('crm.discussions.app.bulk.create') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-group"></i> New group chat
                    </a>
                </div>
            </section>

            <section class="crm-discussion-channel-card">
                <div>
                    <p class="crm-kicker">Email</p>
                    <h3>Dedicated outbound compose flow</h3>
                    <p>Keep single-recipient drafts and audience-based campaigns on separate pages, with channel addresses resolved from CRM records.</p>
                </div>
                <div class="crm-discussion-channel-pills">
                    <span class="crm-pill primary">Direct create/edit</span>
                    <span class="crm-pill primary">Bulk create/edit</span>
                    <span class="crm-pill muted">Quote/invoice aware</span>
                </div>
                <div class="crm-action-row">
                    <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-primary">
                        <i class="bx bx-envelope-open"></i> Open email channel
                    </a>
                    <a href="{{ route('crm.discussions.email.bulk.create') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-layer-plus"></i> New bulk email
                    </a>
                </div>
            </section>

            <section class="crm-discussion-channel-card">
                <div>
                    <p class="crm-kicker">WhatsApp</p>
                    <h3>Provider-ready outbound messaging</h3>
                    <p>Create direct or bulk WhatsApp drafts separately, preserve audit history, and queue delivery when live integrations are available.</p>
                </div>
                <div class="crm-discussion-channel-pills">
                    <span class="crm-pill primary">Direct create/edit</span>
                    <span class="crm-pill primary">Bulk create/edit</span>
                    <span class="crm-pill muted">Queued when needed</span>
                </div>
                <div class="crm-action-row">
                    <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn btn-primary">
                        <i class="bx bxl-whatsapp"></i> Open WhatsApp channel
                    </a>
                    <a href="{{ route('crm.discussions.whatsapp.bulk.create') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-layer-plus"></i> New bulk WhatsApp
                    </a>
                </div>
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Recent activity</p>
                    <h2>Latest discussion threads</h2>
                </div>
            </div>

            @if ($recentThreads->isEmpty())
                <div class="crm-empty">No discussion activity has been recorded yet.</div>
            @else
                <div class="crm-stack">
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
                        @endphp

                        <article class="crm-discussion-thread-card">
                            <div class="crm-discussion-thread-head">
                                <div>
                                    <p class="crm-kicker">{{ $discussionChannels[$thread->channel] ?? ucfirst($thread->channel) }}</p>
                                    <h3><a href="{{ route('crm.discussions.show', $thread) }}">{{ $thread->subject }}</a></h3>
                                </div>
                                <span class="crm-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $thread->delivery_status)) }}</span>
                            </div>
                            <div class="crm-discussion-meta-row">
                                <span class="crm-pill muted">{{ $recipientLabel }}</span>
                                <span class="crm-pill muted">{{ $thread->kind ? ucfirst(str_replace('_', ' ', $thread->kind)) : 'Legacy thread' }}</span>
                                @if ($thread->source_type && $thread->source_id)
                                    <span class="crm-pill primary">{{ ucfirst($thread->source_type) }} linked</span>
                                @endif
                            </div>
                            @if (filled($thread->latestMessage?->body))
                                <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit($thread->latestMessage->body, 220) }}</div>
                            @endif
                            <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                                <span class="crm-muted">Last activity {{ optional($thread->last_message_at)->format('d M Y H:i') ?: 'Not available' }}</span>
                                <div class="crm-action-row">
                                    <a href="{{ route('crm.discussions.show', $thread) }}" class="btn btn-light crm-btn-light">
                                        <i class="bx bx-right-arrow-alt"></i> Open thread
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Bulk activity</p>
                    <h2>Latest campaigns</h2>
                </div>
            </div>

            @if ($recentCampaigns->isEmpty())
                <div class="crm-empty">No bulk campaign activity has been recorded yet.</div>
            @else
                <div class="crm-stack">
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
                        @endphp

                        <article class="crm-discussion-campaign-card">
                            <div class="crm-discussion-campaign-head">
                                <div>
                                    <p class="crm-kicker">{{ $discussionChannels[$campaign->channel] ?? ucfirst($campaign->channel) }}</p>
                                    <h3><a href="{{ $campaignUrl }}">{{ $campaign->subject }}</a></h3>
                                </div>
                                <span class="crm-pill {{ $statusClass }}">{{ ucfirst($campaign->status) }}</span>
                            </div>
                            <div class="crm-discussion-recipient-pills">
                                <span class="crm-discussion-recipient-pill">{{ $resolvedRecipients->count() }} resolved recipient(s)</span>
                                @if ($campaign->source_type && $campaign->source_id)
                                    <span class="crm-discussion-recipient-pill">{{ ucfirst($campaign->source_type) }} linked</span>
                                @endif
                            </div>
                            <div class="crm-discussion-message-preview">{{ \Illuminate\Support\Str::limit($campaign->body, 220) }}</div>
                            <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                                <span class="crm-muted">{{ $campaign->last_sent_at ? 'Sent ' . $campaign->last_sent_at->format('d M Y H:i') : 'Updated ' . $campaign->updated_at?->format('d M Y H:i') }}</span>
                                <div class="crm-action-row">
                                    <a href="{{ $campaignUrl }}" class="btn btn-light crm-btn-light">
                                        <i class="bx bx-right-arrow-alt"></i> Open campaign
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection

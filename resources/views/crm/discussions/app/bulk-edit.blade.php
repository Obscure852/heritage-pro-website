@extends('layouts.crm')

@php
    $snapshot = $discussionCampaign->audience_snapshot ?? [];
    $resolvedRecipients = collect($snapshot['resolved'] ?? []);
    $skippedRecipients = collect($snapshot['skipped'] ?? []);
    $selectedDepartments = collect($snapshot['departments'] ?? []);
    $isDraft = $discussionCampaign->status === 'draft';
@endphp

@section('title', 'Edit App Group Chat')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Keep app group chat drafts separate from direct messages and the live workspace threads.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.app.workspace') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to workspace
        </a>
        @if ($discussionCampaign->thread)
            <a href="{{ route('crm.discussions.app.threads.show', $discussionCampaign->thread) }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-message-square-dots"></i> Open posted thread
            </a>
        @endif
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'app'])

    <div class="crm-discussion-split">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ ucfirst($discussionCampaign->status) }} group chat</p>
                    <h2>{{ $isDraft ? 'Edit group chat draft' : 'Group chat summary' }}</h2>
                </div>
            </div>

            @if ($isDraft)
                @include('crm.discussions.app.partials.bulk-form', [
                    'action' => route('crm.discussions.app.bulk.update', $discussionCampaign),
                    'method' => 'PATCH',
                    'cancelUrl' => route('crm.discussions.app.workspace'),
                    'discussionCampaign' => $discussionCampaign,
                    'crmUsers' => $crmUsers,
                    'departments' => $departments,
                    'sourceContext' => $sourceContext ?? null,
                ])
            @else
                <div class="crm-stack">
                    <div class="crm-discussion-message-preview">{{ $discussionCampaign->body }}</div>
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
                            <strong>{{ $discussionCampaign->notes ?: 'No notes were added to this group chat.' }}</strong>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <div class="crm-stack">
            @if ($selectedDepartments->isNotEmpty())
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Department targeting</p>
                            <h2>Selected departments</h2>
                        </div>
                    </div>

                    <div class="crm-stack">
                        @foreach ($selectedDepartments as $department)
                            <div class="crm-discussion-thread-card">
                                <strong>{{ $department['name'] ?? 'Department' }}</strong>
                                <p>{{ ($department['member_count'] ?? 0) }} member(s) resolved from this department.</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Audience snapshot</p>
                        <h2>Resolved group members</h2>
                    </div>
                </div>

                @if ($resolvedRecipients->isEmpty())
                    <div class="crm-empty">Save the group chat draft first to inspect the resolved member list.</div>
                @else
                    <div class="crm-stack">
                        @foreach ($resolvedRecipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <strong>{{ $recipient['label'] ?? 'Recipient' }}</strong>
                                <p>{{ $recipient['email'] ?? ($recipient['phone'] ?? 'Internal app member') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            @if ($skippedRecipients->isNotEmpty())
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Skipped recipients</p>
                            <h2>Records missing channel data</h2>
                        </div>
                    </div>

                    <div class="crm-stack">
                        @foreach ($skippedRecipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <strong>{{ $recipient['label'] ?? 'Recipient' }}</strong>
                                <p>{{ $recipient['error'] ?? 'This record could not be resolved.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($discussionCampaign->recipients->isNotEmpty())
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Send results</p>
                            <h2>Unread fan-out audit</h2>
                        </div>
                    </div>

                    <div class="crm-stack">
                        @foreach ($discussionCampaign->recipients as $recipient)
                            <div class="crm-discussion-thread-card">
                                <div class="crm-inline" style="justify-content: space-between; gap: 12px;">
                                    <strong>{{ $recipient->recipient_label ?: 'Recipient' }}</strong>
                                    <span class="crm-pill {{ $recipient->delivery_status === 'failed' ? 'danger' : 'success' }}">
                                        {{ ucfirst(str_replace('_', ' ', $recipient->delivery_status ?: 'sent')) }}
                                    </span>
                                </div>
                                <p>{{ $recipient->recipientUser?->name ?: 'Internal group member' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

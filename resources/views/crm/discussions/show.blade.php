@extends('layouts.crm')

@section('title', $discussionThread->subject . ' - Discussion')
@section('crm_heading', $discussionThread->subject)
@section('crm_subheading', 'Thread detail for internal messaging and provider-backed outbound communications.')

@section('crm_actions')
    @include('crm.partials.delete-button', [
        'action' => route('crm.discussions.destroy', $discussionThread),
        'message' => 'Are you sure you want to permanently delete this discussion?',
        'label' => 'Delete discussion',
    ])
@endsection

@section('content')
    <div class="crm-grid cols-2">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Timeline</p>
                    <h2>Messages</h2>
                </div>
            </div>

            @if ($discussionThread->messages->isEmpty())
                <div class="crm-empty">No messages in this thread yet.</div>
            @else
                <div class="crm-timeline">
                    @foreach ($discussionThread->messages as $message)
                        <div class="crm-timeline-item">
                            <div class="crm-timeline-time">
                                {{ optional($message->sent_at)->format('d M') ?: $message->created_at->format('d M') }}<br>
                                {{ optional($message->sent_at)->format('H:i') ?: $message->created_at->format('H:i') }}
                            </div>
                            <div class="crm-timeline-card">
                                <div class="crm-inline" style="justify-content: space-between; margin-bottom: 8px;">
                                    <h4>{{ $message->user?->name ?: 'System' }}</h4>
                                    <span class="crm-pill muted">{{ ucfirst($message->channel) }} · {{ ucfirst($message->direction) }}</span>
                                </div>
                                <p>{{ $message->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="crm-stack">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Reply</p>
                        <h2>Send another message</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.discussions.messages.store', $discussionThread) }}" class="crm-form">
                    @csrf
                    <div class="crm-field">
                        <label for="body">Message</label>
                        <textarea id="body" name="body" placeholder="Write your reply message" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="crm-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="bx bx-send"></i> Send message</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Summary</p>
                        <h2>Thread details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Channel</span>
                        <strong>{{ config('heritage_crm.discussion_channels.' . $discussionThread->channel, ucfirst($discussionThread->channel)) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Initiated by</span>
                        <strong>{{ $discussionThread->initiatedBy?->name ?: 'Unknown' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Recipient</span>
                        <strong>{{ $discussionThread->recipientUser?->name ?: ($discussionThread->recipient_email ?: ($discussionThread->recipient_phone ?: 'External')) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Delivery status</span>
                        <strong>{{ config('heritage_crm.discussion_delivery_statuses.' . $discussionThread->delivery_status, ucfirst(str_replace('_', ' ', $discussionThread->delivery_status))) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Integration</span>
                        <strong>{{ $discussionThread->integration?->name ?: 'None' }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

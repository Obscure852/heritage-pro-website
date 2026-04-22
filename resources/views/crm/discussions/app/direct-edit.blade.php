@extends('layouts.crm')

@php
    $counterpart = $discussionThread->counterpartFor(auth()->user());
@endphp

@section('title', 'Edit App Conversation')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Update the conversation label and internal notes without changing the sent message history.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.app.threads.show', $discussionThread) }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to conversation
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'app'])

    <div class="crm-discussion-split">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Conversation metadata</p>
                    <h2>Edit direct thread details</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('crm.discussions.app.direct.update', $discussionThread) }}" class="crm-form">
                @csrf
                @method('PATCH')

                <div class="crm-field-grid">
                    <div class="crm-field full">
                        <label>Counterpart</label>
                        <input value="{{ $counterpart?->name ?: 'Direct conversation' }}" disabled>
                    </div>

                    <div class="crm-field full">
                        <label for="subject">Conversation label</label>
                        <input id="subject" name="subject" value="{{ old('subject', $discussionThread->subject) }}" placeholder="Internal thread label" required>
                    </div>

                    <div class="crm-field full">
                        <label for="notes">Internal notes</label>
                        <textarea id="notes" name="notes" placeholder="Private notes for this thread">{{ old('notes', $discussionThread->notes) }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.discussions.app.threads.show', $discussionThread) }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save changes</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                    </button>
                </div>
            </form>
        </section>

        <div class="crm-stack">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Conversation summary</p>
                        <h2>Current state</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Participants</span>
                        <strong>{{ $discussionThread->participants->pluck('user.name')->filter()->join(', ') }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Last activity</span>
                        <strong>{{ optional($discussionThread->last_message_at)->format('d M Y H:i') ?: 'No messages yet' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Metadata updated</span>
                        <strong>{{ optional($discussionThread->metadata_updated_at ?: $discussionThread->updated_at)->format('d M Y H:i') ?: 'Not available' }}</strong>
                    </div>
                </div>
            </section>

            @if ($discussionThread->latestMessage)
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Latest message</p>
                            <h2>Recent conversation context</h2>
                        </div>
                    </div>

                    <div class="crm-discussion-message-preview">{{ $discussionThread->latestMessage->body ?: 'The latest activity was attachment-only.' }}</div>
                </section>
            @endif
        </div>
    </div>
@endsection

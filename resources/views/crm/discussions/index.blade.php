@extends('layouts.crm')

@section('title', 'CRM Discussions')
@section('crm_heading', 'Discussions')
@section('crm_subheading', 'Direct internal app messaging plus outbound email and WhatsApp-ready conversation threads from the same communications workspace.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.create') }}" class="btn btn-primary">
        <i class="bx bx-message-square-add"></i> New discussion
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find discussion threads</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.discussions.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Subject, recipient, message body">
                    </div>
                    <div class="crm-field">
                        <label for="channel">Channel</label>
                        <select id="channel" name="channel">
                            <option value="">All channels</option>
                            @foreach ($discussionChannels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['channel'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="delivery_status">Delivery status</label>
                        <select id="delivery_status" name="delivery_status">
                            <option value="">All statuses</option>
                            @foreach ($deliveryStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['delivery_status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="user_id">User</label>
                        <select id="user_id" name="user_id">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($filters['user_id'] !== '' && (int) $filters['user_id'] === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.discussions.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Threads</p>
                    <h2>Recent communications</h2>
                </div>
            </div>

            @if ($threads->isEmpty())
                <div class="crm-empty">No discussion records match the current filters.</div>
            @else
                <div class="crm-list">
                    @foreach ($threads as $thread)
                        <div class="crm-list-item">
                            <div class="crm-inline" style="justify-content: space-between;">
                                <h4><a href="{{ route('crm.discussions.show', $thread) }}">{{ $thread->subject }}</a></h4>
                                <span class="crm-pill {{ $thread->delivery_status === 'failed' ? 'danger' : ($thread->delivery_status === 'pending_integration' ? 'warning' : 'primary') }}">{{ $deliveryStatuses[$thread->delivery_status] ?? ucfirst(str_replace('_', ' ', $thread->delivery_status)) }}</span>
                            </div>
                            <p>{{ $discussionChannels[$thread->channel] ?? ucfirst($thread->channel) }} · {{ $thread->initiatedBy?->name ?: 'Unknown sender' }} to {{ $thread->recipientUser?->name ?: ($thread->recipient_email ?: ($thread->recipient_phone ?: 'External recipient')) }}</p>
                            <div class="crm-inline" style="margin-top: 10px; justify-content: space-between;">
                                <div class="crm-inline">
                                    <span class="crm-muted-copy">{{ optional($thread->last_message_at)->format('d M Y H:i') ?: 'No messages yet' }}</span>
                                    <span class="crm-muted-copy">•</span>
                                    <span class="crm-muted-copy">{{ $thread->messages->count() }} message(s)</span>
                                </div>
                                <div class="crm-action-row">
                                    <a href="{{ route('crm.discussions.show', $thread) }}" class="btn btn-secondary">
                                        <i class="fas fa-eye"></i> Open
                                    </a>
                                    @include('crm.partials.delete-button', [
                                        'action' => route('crm.discussions.destroy', $thread),
                                        'message' => 'Are you sure you want to permanently delete this discussion?',
                                        'label' => 'Delete',
                                    ])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @include('crm.partials.pager', ['paginator' => $threads])
            @endif
        </section>
    </div>
@endsection

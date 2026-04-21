@extends('layouts.crm')

@section('title', $crmRequest->title . ' - Request')
@section('crm_heading', $crmRequest->title)
@section('crm_subheading', 'Request detail with sales or support status, linked account context, and a logged activity timeline for the team.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.requests.edit', $crmRequest) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit request
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.requests.destroy', $crmRequest),
            'message' => 'Are you sure you want to permanently delete this request?',
            'label' => 'Delete request',
        ])
    </div>
@endsection

@section('content')
    <div class="crm-grid cols-2">
        <div class="crm-stack">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Request summary</p>
                        <h2>Current state</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Type</span>
                        <strong>{{ $requestTypes[$crmRequest->type] ?? ucfirst($crmRequest->type) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $crmRequest->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Lead</span>
                        <strong>{{ $crmRequest->lead?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Customer</span>
                        <strong>{{ $crmRequest->customer?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Contact</span>
                        <strong>{{ $crmRequest->contact?->name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Stage / status</span>
                        <strong>{{ $crmRequest->type === 'sales' ? ($crmRequest->salesStage?->name ?: 'No stage') : ($supportStatuses[$crmRequest->support_status] ?? ucfirst(str_replace('_', ' ', $crmRequest->support_status ?: 'open'))) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Outcome</span>
                        <strong>{{ $crmRequest->outcome ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Next action</span>
                        <strong>{{ $crmRequest->next_action ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Next action due</span>
                        <strong>{{ $crmRequest->next_action_at?->format('d M Y H:i') ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Description</span>
                        <strong>{{ $crmRequest->description ?: 'None' }}</strong>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Activity log</p>
                        <h2>Record a call, email, meeting, or note</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.requests.activities.store', $crmRequest) }}" class="crm-form">
                    @csrf
                    <div class="crm-field-grid">
                        <div class="crm-field">
                            <label for="activity_type">Activity type</label>
                            <select id="activity_type" name="activity_type">
                                @foreach ($activityTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('activity_type', 'call') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="crm-field">
                            <label for="occurred_at">Occurred at</label>
                            <input id="occurred_at" name="occurred_at" type="datetime-local" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" placeholder="Select activity date and time">
                        </div>
                        <div class="crm-field full">
                            <label for="subject">Subject</label>
                            <input id="subject" name="subject" value="{{ old('subject') }}" placeholder="Enter activity subject">
                        </div>
                        <div class="crm-field full">
                            <label for="body">Summary</label>
                            <textarea id="body" name="body" placeholder="Summarize the call, email, meeting, or note" required>{{ old('body') }}</textarea>
                        </div>
                    </div>

                    <div class="crm-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="bx bx-plus-circle"></i> Log activity</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Timeline</p>
                    <h2>Logged interactions</h2>
                </div>
            </div>

            @if ($crmRequest->activities->isEmpty())
                <div class="crm-empty">No activity has been logged for this request yet.</div>
            @else
                <div class="crm-timeline">
                    @foreach ($crmRequest->activities as $activity)
                        <div class="crm-timeline-item">
                            <div class="crm-timeline-time">
                                {{ $activity->occurred_at->format('d M') }}<br>
                                {{ $activity->occurred_at->format('H:i') }}
                            </div>
                            <div class="crm-timeline-card">
                                <div class="crm-inline" style="justify-content: space-between; margin-bottom: 8px;">
                                    <h4>{{ $activity->subject ?: ($activityTypes[$activity->activity_type] ?? ucfirst($activity->activity_type)) }}</h4>
                                    <span class="crm-pill muted">{{ $activityTypes[$activity->activity_type] ?? ucfirst($activity->activity_type) }}</span>
                                </div>
                                <p>{{ $activity->body }}</p>
                                <div class="crm-inline" style="margin-top: 10px;">
                                    <span class="crm-muted-copy">{{ $activity->user?->name ?: 'System' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection

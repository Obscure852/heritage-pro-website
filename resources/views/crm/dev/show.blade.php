@extends('layouts.crm')

@section('title', $developmentRequest->title . ' - Dev')
@section('crm_heading', $developmentRequest->title)
@section('crm_subheading', 'Development request detail with client context, requested outcome, delivery state, and ownership.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.dev.edit', $developmentRequest) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit dev item
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.dev.destroy', $developmentRequest),
            'message' => 'Are you sure you want to permanently delete this development request?',
            'label' => 'Delete dev item',
        ])
    </div>
@endsection

@section('content')
    <div class="crm-grid cols-2">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Summary</p>
                    <h2>Request context</h2>
                </div>
            </div>

            <div class="crm-meta-list">
                <div class="crm-meta-row">
                    <span>Requested by</span>
                    <strong>{{ $developmentRequest->requested_by ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Target module</span>
                    <strong>{{ $developmentRequest->target_module ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Priority</span>
                    <strong>{{ $developmentPriorities[$developmentRequest->priority] ?? ucfirst($developmentRequest->priority) }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Status</span>
                    <strong>{{ $developmentStatuses[$developmentRequest->status] ?? ucfirst($developmentRequest->status) }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Customer</span>
                    <strong>{{ $developmentRequest->customer?->company_name ?: 'None' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Lead</span>
                    <strong>{{ $developmentRequest->lead?->company_name ?: 'None' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Contact</span>
                    <strong>{{ $developmentRequest->contact?->name ?: 'None' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Owner</span>
                    <strong>{{ $developmentRequest->owner?->name ?: 'Unassigned' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Due at</span>
                    <strong>{{ $developmentRequest->due_at?->format('d M Y H:i') ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Description</span>
                    <strong>{{ $developmentRequest->description }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Business value</span>
                    <strong>{{ $developmentRequest->business_value ?: 'None' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Next step</span>
                    <strong>{{ $developmentRequest->next_step ?: 'None' }}</strong>
                </div>
            </div>
        </section>
    </div>
@endsection

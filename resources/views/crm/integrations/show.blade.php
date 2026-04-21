@extends('layouts.crm')

@section('title', $integration->name . ' - Integration')
@section('crm_heading', $integration->name)
@section('crm_subheading', 'Integration profile for school APIs and external communication providers.')

@section('crm_actions')
    @if ($canManage)
        <div class="crm-action-row">
            <a href="{{ route('crm.integrations.edit', $integration) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit integration
            </a>
            @include('crm.partials.delete-button', [
                'action' => route('crm.integrations.destroy', $integration),
                'message' => 'Are you sure you want to permanently delete this integration?',
                'label' => 'Delete integration',
            ])
        </div>
    @endif
@endsection

@section('content')
    <div class="crm-grid cols-2">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Connection profile</p>
                    <h2>Integration details</h2>
                </div>
            </div>

            <div class="crm-meta-list">
                <div class="crm-meta-row">
                    <span>Name</span>
                    <strong>{{ $integration->name }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Owner</span>
                    <strong>{{ $integration->owner?->name ?: 'Unassigned' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Kind</span>
                    <strong>{{ $integrationKinds[$integration->kind] ?? ucfirst($integration->kind) }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Status</span>
                    <strong>{{ $integrationStatuses[$integration->status] ?? ucfirst($integration->status) }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>School code</span>
                    <strong>{{ $integration->school_code ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Auth type</span>
                    <strong>{{ $integration->auth_type ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Base URL</span>
                    <strong>{{ $integration->base_url ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Webhook URL</span>
                    <strong>{{ $integration->webhook_url ?: 'Not set' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Last synced</span>
                    <strong>{{ $integration->last_synced_at?->format('d M Y H:i') ?: 'Never' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Notes</span>
                    <strong>{{ $integration->notes ?: 'None' }}</strong>
                </div>
            </div>
        </section>
    </div>
@endsection

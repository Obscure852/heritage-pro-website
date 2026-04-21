@extends('layouts.crm')

@section('title', $integration->name . ' - Edit Integration')
@section('crm_heading', $integration->name)
@section('crm_subheading', 'Update the integration profile on a dedicated edit page while keeping the detail page focused on connection summary.')

@section('crm_actions')
    <a href="{{ route('crm.integrations.show', $integration) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to integration
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Integration details</p>
                <h2>Edit integration</h2>
            </div>
        </div>

        @include('crm.integrations._form', [
            'action' => route('crm.integrations.update', $integration),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.integrations.show', $integration),
            'deleteUrl' => route('crm.integrations.destroy', $integration),
            'integration' => $integration,
        ])
    </section>
@endsection

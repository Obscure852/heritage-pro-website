@extends('layouts.crm')

@section('title', $lead->company_name . ' - Edit Lead')
@section('crm_heading', $lead->company_name)
@section('crm_subheading', 'Update the lead record on its own edit page while keeping the detail page focused on context and history.')

@section('crm_actions')
    <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to lead
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Lead details</p>
                    <h2>Edit lead</h2>
                </div>
            </div>

            @include('crm.leads._form', [
                'action' => route('crm.leads.update', $lead),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.leads.show', $lead),
                'deleteUrl' => route('crm.leads.destroy', $lead),
                'lead' => $lead,
            ])
        </section>
    </div>
@endsection

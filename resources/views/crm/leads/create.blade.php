@extends('layouts.crm')

@section('title', 'Create Lead')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Create a new lead record without mixing form entry into the listing workspace.')

@section('crm_actions')
    <a href="{{ route('crm.leads.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to leads
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New lead</p>
                    <h2>Add a sales lead</h2>
                </div>
            </div>

            @include('crm.leads._form', [
                'action' => route('crm.leads.store'),
                'method' => null,
                'submitLabel' => 'Save lead',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.leads.index'),
            ])
        </section>
    </div>
@endsection

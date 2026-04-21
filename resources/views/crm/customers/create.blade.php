@extends('layouts.crm')

@section('title', 'Create Customer')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Create a standalone customer record on its own page instead of inside the listing workspace.')

@section('crm_actions')
    <a href="{{ route('crm.customers.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to customers
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New customer</p>
                    <h2>Add a customer record</h2>
                </div>
            </div>

            @include('crm.customers._form', [
                'action' => route('crm.customers.store'),
                'method' => null,
                'submitLabel' => 'Save customer',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.customers.index'),
            ])
        </section>
    </div>
@endsection

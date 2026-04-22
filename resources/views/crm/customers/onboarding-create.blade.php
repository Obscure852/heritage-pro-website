@extends('layouts.crm')

@section('title', 'Import Customer')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Admin-only onboarding flow that creates both the customer record and its originating lead in one transaction.')

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
                    <p class="crm-kicker">Admin onboarding</p>
                    <h2>Import customer with source lead</h2>
                    <p>Create the live customer and its originating converted lead in one step for legacy or externally sourced accounts.</p>
                </div>
            </div>

            @include('crm.customers._form', [
                'action' => route('crm.customers.onboarding.store'),
                'method' => null,
                'submitLabel' => 'Import customer',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.customers.index'),
            ])
        </section>
    </div>
@endsection

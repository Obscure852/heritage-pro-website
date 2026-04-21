@extends('layouts.crm')

@section('title', $customer->company_name . ' - Edit Customer')
@section('crm_heading', $customer->company_name)
@section('crm_subheading', 'Update the customer record on a dedicated edit page while keeping the detail page focused on summary and history.')

@section('crm_actions')
    <a href="{{ route('crm.customers.show', $customer) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to customer
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Customer details</p>
                    <h2>Edit customer</h2>
                </div>
            </div>

            @include('crm.customers._form', [
                'action' => route('crm.customers.update', $customer),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.customers.show', $customer),
                'deleteUrl' => route('crm.customers.destroy', $customer),
                'customer' => $customer,
            ])
        </section>
    </div>
@endsection

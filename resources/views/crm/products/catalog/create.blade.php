@extends('layouts.crm')

@section('title', 'Create Product')
@section('crm_heading', 'Products')
@section('crm_subheading', 'Add a new CRM catalog item for licenses, services, support packages, and other billable commercial lines.')

@section('crm_actions')
    <a href="{{ route('crm.products.catalog.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to catalog
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'catalog'])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New catalog item</p>
                    <h2>Create product</h2>
                </div>
            </div>

            @include('crm.products.catalog._form', [
                'action' => route('crm.products.catalog.store'),
                'method' => null,
                'submitLabel' => 'Create product',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.products.catalog.index'),
            ])
        </section>
    </div>
@endsection

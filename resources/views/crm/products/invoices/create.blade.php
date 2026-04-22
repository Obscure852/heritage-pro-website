@extends('layouts.crm')

@section('title', 'Create Invoice')
@section('crm_heading', 'Create Invoice')
@section('crm_subheading', 'Build a finance-owned invoice for a lead or customer using catalog defaults and custom billing lines.')

@section('crm_actions')
    <a href="{{ route('crm.products.invoices.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to invoices
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'invoices'])

        @include('crm.products.invoices._form', [
            'action' => route('crm.products.invoices.store'),
            'method' => null,
            'submitLabel' => 'Save invoice draft',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.products.invoices.index'),
        ])
    </div>
@endsection

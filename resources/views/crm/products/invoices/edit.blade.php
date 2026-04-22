@extends('layouts.crm')

@section('title', $invoice->invoice_number . ' - Edit Invoice')
@section('crm_heading', $invoice->invoice_number)
@section('crm_subheading', 'Update this invoice while it remains in a draft workflow state.')

@section('crm_actions')
    <a href="{{ route('crm.products.invoices.show', $invoice) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to invoice
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'invoices'])

        @include('crm.products.invoices._form', [
            'action' => route('crm.products.invoices.update', $invoice),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.products.invoices.show', $invoice),
        ])
    </div>
@endsection

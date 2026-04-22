@extends('layouts.crm')

@section('title', 'Share ' . $invoice->invoice_number)
@section('crm_heading', 'Share ' . $invoice->invoice_number)
@section('crm_subheading', 'Create an internal, email, or integration-backed discussion and attach the latest private PDF version of this invoice.')

@section('crm_actions')
    <a href="{{ route('crm.products.invoices.show', $invoice) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to invoice
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'invoices'])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Invoice delivery</p>
                    <h2>Share document</h2>
                    <p>Choose the dedicated channel flow for this invoice. The latest private PDF is generated or refreshed automatically and reused by the selected composer.</p>
                </div>
            </div>

            @include('crm.discussions.partials.share-channel-picker', [
                'sourceType' => 'invoice',
                'sourceId' => $invoice->id,
                'sourceLabel' => 'Invoice ' . $invoice->invoice_number,
                'backUrl' => route('crm.products.invoices.show', $invoice),
            ])
        </section>
    </div>
@endsection

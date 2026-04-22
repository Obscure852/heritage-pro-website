@extends('layouts.crm')

@section('title', 'Edit ' . $product->name)
@section('crm_heading', 'Products')
@section('crm_subheading', 'Update catalog defaults used by future quotes and invoices. Existing saved documents remain unchanged because commercial values are snapshotted.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.products.catalog.show', $product) }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to product
        </a>
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'catalog'])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Catalog maintenance</p>
                    <h2>Edit {{ $product->name }}</h2>
                </div>
            </div>

            @include('crm.products.catalog._form', [
                'action' => route('crm.products.catalog.update', $product),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.products.catalog.show', $product),
            ])
        </section>
    </div>
@endsection

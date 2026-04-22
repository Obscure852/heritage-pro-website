@extends('layouts.crm')

@section('title', $product->name . ' - Product')
@section('crm_heading', $product->name)
@section('crm_subheading', 'Commercial catalog detail used by the CRM products workspace.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.products.catalog.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to catalog
        </a>
        @if ($canManageCatalog)
            <a href="{{ route('crm.products.catalog.edit', $product) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit product
            </a>
            <form method="POST" action="{{ route('crm.products.catalog.status', $product) }}" class="crm-inline-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="active" value="{{ $product->active ? 0 : 1 }}">
                <button type="submit" class="btn btn-light crm-btn-light">
                    <i class="bx {{ $product->active ? 'bx-pause-circle' : 'bx-check-circle' }}"></i>
                    {{ $product->active ? 'Deactivate' : 'Reactivate' }}
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'catalog'])

        <section class="crm-card">
            <div class="crm-detail-hero">
                <div class="crm-detail-hero-copy">
                    <p class="crm-kicker">Catalog detail</p>
                    <h2>{{ $product->name }}</h2>
                    <p>{{ $productTypes[$product->type] ?? ucfirst($product->type) }} · {{ $billingFrequencies[$product->billing_frequency] ?? ucfirst($product->billing_frequency) }}</p>
                    <div class="crm-inline">
                        <span class="crm-pill {{ $product->active ? 'success' : 'muted' }}">{{ $product->active ? 'Active' : 'Inactive' }}</span>
                        <span class="crm-pill muted">{{ $product->code ?: 'No code set' }}</span>
                        <span class="crm-pill muted">{{ $product->default_unit_label }}</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="crm-grid cols-3">
            <div class="crm-metric">
                <span>Default unit price</span>
                <strong>{{ number_format((float) $product->default_unit_price, 2) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Linked quote lines</span>
                <strong>{{ number_format($product->quote_items_count) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Linked invoice lines</span>
                <strong>{{ number_format($product->invoice_items_count) }}</strong>
            </div>
        </div>

        <div class="crm-grid cols-2 crm-detail-grid">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Commercial defaults</p>
                        <h2>Pricing and billing</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Product code</span>
                        <strong>{{ $product->code ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Type</span>
                        <strong>{{ $productTypes[$product->type] ?? ucfirst($product->type) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Billing frequency</span>
                        <strong>{{ $billingFrequencies[$product->billing_frequency] ?? ucfirst($product->billing_frequency) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Unit label</span>
                        <strong>{{ $product->default_unit_label }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Unit price</span>
                        <strong>{{ number_format((float) $product->default_unit_price, 2) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Default tax rate</span>
                        <strong>{{ number_format((float) $product->default_tax_rate, 2) }}%</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Created</span>
                        <strong>{{ $product->created_at?->format('d M Y H:i') ?: 'Unknown' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Updated</span>
                        <strong>{{ $product->updated_at?->format('d M Y H:i') ?: 'Unknown' }}</strong>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Commercial copy</p>
                        <h2>Description and notes</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Description</span>
                        <strong>{{ $product->description ?: 'No description has been added yet.' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Internal notes</span>
                        <strong>{{ $product->notes ?: 'No internal notes have been added yet.' }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

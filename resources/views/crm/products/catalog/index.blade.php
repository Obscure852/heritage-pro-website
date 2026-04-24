@extends('layouts.crm')

@section('title', 'CRM Products Catalog')
@section('crm_heading', 'Products')
@section('crm_subheading', 'Manage the CRM catalog that sales and finance will use for quotes, invoices, implementation fees, support packages, and other commercial lines.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => number_format($totalProducts), 'label' => 'Total'])
    @include('crm.partials.header-stat', ['value' => number_format($activeProducts), 'label' => 'Active'])
    @include('crm.partials.header-stat', ['value' => number_format($inactiveProducts), 'label' => 'Inactive'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'catalog'])

        @include('crm.partials.helper-text', [
            'title' => 'Products Workspace',
            'content' => 'Use the tabs to move between commercial areas. On this page, filter the catalog by type, billing frequency, or status before editing reusable defaults.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find catalog items</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.products.catalog.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Code, name, description">
                    </div>
                    <div class="crm-field">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="">All types</option>
                            @foreach ($productTypes as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="billing_frequency">Billing frequency</label>
                        <select id="billing_frequency" name="billing_frequency">
                            <option value="">All frequencies</option>
                            @foreach ($billingFrequencies as $value => $label)
                                <option value="{{ $value }}" @selected($filters['billing_frequency'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="active">Status</label>
                        <select id="active" name="active">
                            <option value="">All items</option>
                            <option value="1" @selected($filters['active'] === '1')>Active</option>
                            <option value="0" @selected($filters['active'] === '0')>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.products.catalog.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    @if ($canManageCatalog)
                        <a href="{{ route('crm.products.catalog.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus-circle"></i> New product
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Catalog</p>
                    <h2>Commercial catalog items</h2>
                    <p>These defaults will feed later quote and invoice authoring while preserving historical snapshots on saved documents.</p>
                </div>
            </div>

            @if ($products->isEmpty())
                <div class="crm-empty">No catalog items match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Billing</th>
                                <th>Pricing default</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.products.catalog.show', $product) }}">{{ $product->name }}</a></strong>
                                        <span class="crm-muted">{{ $product->code ?: 'No code' }} · {{ $product->default_unit_label }} · {{ $product->quote_items_count + $product->invoice_items_count }} linked line(s)</span>
                                    </td>
                                    <td>{{ $productTypes[$product->type] ?? ucfirst($product->type) }}</td>
                                    <td>{{ $billingFrequencies[$product->billing_frequency] ?? ucfirst($product->billing_frequency) }}</td>
                                    <td>
                                        @php($adjustedUnitPrice = (float) $product->default_unit_price * (1 + ((float) $product->cpi_increase_rate / 100)))
                                        {{ number_format($adjustedUnitPrice, 2) }}
                                        <span class="crm-muted">{{ number_format((float) $product->default_unit_price, 2) }} base · {{ number_format((float) $product->cpi_increase_rate, 2) }}% CPI · {{ number_format((float) $product->default_tax_rate, 2) }}% tax</span>
                                    </td>
                                    <td>
                                        <span class="crm-pill {{ $product->active ? 'success' : 'muted' }}">
                                            {{ $product->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.products.catalog.show', $product),
                                                'label' => 'View product',
                                            ])
                                            @if ($canManageCatalog)
                                                <a href="{{ route('crm.products.catalog.edit', $product) }}" class="btn crm-icon-action" title="Edit product" aria-label="Edit product">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $products])
            @endif
        </section>
    </div>
@endsection

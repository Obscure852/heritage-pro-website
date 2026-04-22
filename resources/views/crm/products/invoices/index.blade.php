@extends('layouts.crm')

@section('title', 'CRM Invoices')
@section('crm_heading', 'Products')
@section('crm_subheading', 'Create, issue, and monitor CRM invoices against leads or customers while preserving commercial snapshots.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => number_format($draftCount), 'label' => 'Draft'])
    @include('crm.partials.header-stat', ['value' => number_format($issuedCount), 'label' => 'Issued'])
    @include('crm.partials.header-stat', ['value' => number_format($sentCount), 'label' => 'Sent'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'invoices'])

        @include('crm.partials.helper-text', [
            'title' => 'Invoice Directory',
            'content' => 'Use the filters below to narrow billing records by status or search terms, then open an invoice to review totals, lifecycle actions, or sharing.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find invoices</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.products.invoices.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Number, subject, account, contact">
                    </div>
                    <div class="crm-field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            @foreach ($invoiceStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.products.invoices.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    @if ($canCreateInvoices)
                        <a href="{{ route('crm.products.invoices.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus-circle"></i> New invoice
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Invoice workflow</p>
                    <h2>Commercial invoices</h2>
                    <p>Invoice totals, discounts, tax, and currency are snapshotted at save time so historical billing stays stable even when catalog values change.</p>
                </div>
            </div>

            @if ($invoices->isEmpty())
                <div class="crm-empty">No invoice records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Account</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.products.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></strong>
                                        <span class="crm-muted">{{ $invoice->subject ?: 'No subject' }} · {{ $invoice->items_count }} line(s) · {{ $invoice->invoice_date?->format('d M Y') }}</span>
                                    </td>
                                    <td>{{ $invoice->customer?->company_name ?: $invoice->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $invoice->contact?->name ?: 'No contact' }}</td>
                                    <td>
                                        <span class="crm-pill {{ in_array($invoice->status, ['issued', 'sent'], true) ? 'success' : ($invoice->status === 'draft' ? 'primary' : 'muted') }}">
                                            {{ $invoiceStatuses[$invoice->status] ?? ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $invoice->currency_code }} {{ number_format((float) $invoice->total_amount, 2) }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.products.invoices.show', $invoice),
                                                'label' => 'View invoice',
                                            ])
                                            @if ($canCreateInvoices && $invoice->status === 'draft')
                                                <a href="{{ route('crm.products.invoices.edit', $invoice) }}" class="btn crm-icon-action" title="Edit invoice" aria-label="Edit invoice">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $invoices])
            @endif
        </section>
    </div>
@endsection

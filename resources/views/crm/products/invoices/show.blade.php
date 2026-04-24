@extends('layouts.crm')

@section('title', $invoice->invoice_number . ' - Invoice')
@section('crm_heading', $invoice->invoice_number)
@section('crm_subheading', 'Commercial invoice detail with snapshotted billing lines, account context, and finance-owned lifecycle controls.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.products.invoices.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to invoices
        </a>
        <a href="{{ route('crm.products.invoices.pdf.open', $invoice) }}" class="btn btn-light crm-btn-light">
            <i class="fas fa-file-pdf"></i> Open PDF
        </a>
        <a href="{{ route('crm.products.invoices.pdf.download', $invoice) }}" class="btn btn-light crm-btn-light">
            <i class="fas fa-download"></i> Download PDF
        </a>
        @if ($canShareInvoice)
            <a href="{{ route('crm.products.invoices.share.create', [
                'crmInvoice' => $invoice,
                'subject' => 'Share ' . $invoice->invoice_number,
                'channel' => $invoice->contact?->email ? 'email' : 'app',
                'recipient_email' => $invoice->contact?->email,
                'recipient_phone' => $invoice->contact?->phone,
                'body' => 'Please find attached invoice ' . $invoice->invoice_number . ' for ' . ($invoice->customer?->company_name ?: $invoice->lead?->company_name ?: 'your account') . '.',
                'notes' => $invoice->subject ?: null,
            ]) }}" class="btn btn-primary">
                <i class="bx bx-send"></i> Share invoice
            </a>
        @endif
        @if ($canEditInvoice)
            <a href="{{ route('crm.products.invoices.edit', $invoice) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit invoice
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'invoices'])

        @if (! empty($availableTransitions))
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Lifecycle</p>
                        <h2>Invoice status</h2>
                    </div>
                </div>

                <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                    <div class="crm-inline">
                        <span class="crm-pill {{ in_array($invoice->status, ['issued', 'sent'], true) ? 'success' : ($invoice->status === 'draft' ? 'primary' : 'muted') }}">
                            {{ $invoiceStatuses[$invoice->status] ?? ucfirst($invoice->status) }}
                        </span>
                    </div>

                    <div class="crm-action-row">
                        @foreach ($availableTransitions as $status => $label)
                            <form method="POST" action="{{ route('crm.products.invoices.status', $invoice) }}" class="crm-inline-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="btn btn-light crm-btn-light">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="crm-grid cols-4">
            <div class="crm-metric">
                <span>Status</span>
                <strong>{{ $invoiceStatuses[$invoice->status] ?? ucfirst($invoice->status) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Subtotal</span>
                <strong>{{ $invoice->currency_code }} {{ number_format((float) $invoice->subtotal_amount, 2) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Tax</span>
                <strong>{{ $invoice->currency_code }} {{ number_format((float) $invoice->tax_amount, 2) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Total</span>
                <strong>{{ $invoice->currency_code }} {{ number_format((float) $invoice->total_amount, 2) }}</strong>
            </div>
        </div>

        <div class="crm-grid cols-2 crm-detail-grid">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Invoice summary</p>
                        <h2>Header details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Invoice number</span>
                        <strong>{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Subject</span>
                        <strong>{{ $invoice->subject ?: 'No subject' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $invoice->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Lead</span>
                        <strong>{{ $invoice->lead?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Customer</span>
                        <strong>{{ $invoice->customer?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Recipient contact</span>
                        <strong>{{ $invoice->contact?->name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Linked request</span>
                        <strong>{{ $invoice->request?->title ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Invoice date</span>
                        <strong>{{ $invoice->invoice_date?->format('d M Y') ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Currency</span>
                        <strong>{{ $invoice->currency_code }} · {{ $invoice->currency_symbol }} · {{ ucfirst($invoice->currency_position) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Tax scope</span>
                        <strong>{{ $invoice->tax_scope === 'document' ? 'Document tax · ' . number_format((float) $invoice->document_tax_rate, 2) . '%' : 'Line tax' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Document discount</span>
                        <strong>{{ $discountTypes[$invoice->document_discount_type] ?? ucfirst($invoice->document_discount_type) }} · {{ number_format((float) $invoice->document_discount_value, 2) }}</strong>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Notes and timing</p>
                        <h2>Billing context</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Internal notes</span>
                        <strong>{{ $invoice->notes ?: 'No notes have been added yet.' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Terms</span>
                        <strong>{{ $invoice->terms ?: 'No invoice terms have been added yet.' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Issued at</span>
                        <strong>{{ $invoice->issued_at?->format('d M Y H:i') ?: 'Not issued' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Sent at</span>
                        <strong>{{ $invoice->shared_at?->format('d M Y H:i') ?: 'Not sent' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Cancelled at</span>
                        <strong>{{ $invoice->cancelled_at?->format('d M Y H:i') ?: 'Not cancelled' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Voided at</span>
                        <strong>{{ $invoice->voided_at?->format('d M Y H:i') ?: 'Not voided' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Invoice lines</p>
                    <h2>Snapshotted items</h2>
                </div>
            </div>

            @if ($invoice->items->isEmpty())
                <div class="crm-empty">This invoice has no saved line items.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Discount</th>
                                <th>Tax</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->item_name }}</strong>
                                        <span class="crm-muted">{{ $item->item_description ?: 'No description' }} · {{ $item->unit_label ?: 'unit' }} · {{ $item->source_type === 'catalog' ? 'Catalog line' : 'Custom line' }}</span>
                                    </td>
                                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td>{{ $invoice->currency_code }} {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ $discountTypes[$item->discount_type] ?? ucfirst($item->discount_type) }} · {{ number_format((float) $item->discount_value, 2) }}</td>
                                    <td>{{ $invoice->tax_scope === 'document' ? 'Document' : number_format((float) $item->tax_rate, 2) . '%' }} · {{ $invoice->currency_code }} {{ number_format((float) $item->tax_amount, 2) }}</td>
                                    <td>{{ $invoice->currency_code }} {{ number_format((float) $item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection

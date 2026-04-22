@php
    $quotes = $quotes ?? collect();
    $invoices = $invoices ?? collect();
@endphp

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Commercial records</p>
            <h2>{{ $title ?? 'Quotes and invoices' }}</h2>
            <p>{{ $subtitle ?? 'Review linked commercial documents, then open the CRM detail or latest private PDF version.' }}</p>
        </div>
    </div>

    @if ($quotes->isEmpty() && $invoices->isEmpty())
        <div class="crm-empty">No related quotes or invoices are linked to this record yet.</div>
    @else
        @if ($quotes->isNotEmpty())
            <div class="crm-stack" style="margin-bottom: 18px;">
                <h4 style="margin: 0;">Quotes</h4>
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Quote</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.products.quotes.show', $quote) }}">{{ $quote->quote_number }}</a></strong>
                                        <span class="crm-muted">{{ $quote->subject ?: 'No subject' }} · {{ $quote->items_count ?? $quote->items->count() }} line(s)</span>
                                    </td>
                                    <td>{{ $quoteStatuses[$quote->status] ?? ucfirst($quote->status) }}</td>
                                    <td>{{ $quote->currency_code }} {{ number_format((float) $quote->total_amount, 2) }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.products.quotes.pdf.open', $quote) }}" class="btn crm-icon-action" title="Open PDF" aria-label="Open PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('crm.products.quotes.pdf.download', $quote) }}" class="btn crm-icon-action" title="Download PDF" aria-label="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($invoices->isNotEmpty())
            <div class="crm-stack">
                <h4 style="margin: 0;">Invoices</h4>
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
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
                                        <span class="crm-muted">{{ $invoice->subject ?: 'No subject' }} · {{ $invoice->items_count ?? $invoice->items->count() }} line(s)</span>
                                    </td>
                                    <td>{{ $invoiceStatuses[$invoice->status] ?? ucfirst($invoice->status) }}</td>
                                    <td>{{ $invoice->currency_code }} {{ number_format((float) $invoice->total_amount, 2) }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.products.invoices.pdf.open', $invoice) }}" class="btn crm-icon-action" title="Open PDF" aria-label="Open PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('crm.products.invoices.pdf.download', $invoice) }}" class="btn crm-icon-action" title="Download PDF" aria-label="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</section>

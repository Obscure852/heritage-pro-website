@extends('layouts.crm')

@section('title', $quote->quote_number . ' - Quote')
@section('crm_heading', $quote->quote_number)
@section('crm_subheading', 'Commercial quote detail with snapshotted pricing, recipient context, and lifecycle controls.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.products.quotes.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to quotes
        </a>
        <a href="{{ route('crm.products.quotes.pdf.open', $quote) }}" class="btn btn-light crm-btn-light">
            <i class="fas fa-file-pdf"></i> Open PDF
        </a>
        <a href="{{ route('crm.products.quotes.pdf.download', $quote) }}" class="btn btn-light crm-btn-light">
            <i class="fas fa-download"></i> Download PDF
        </a>
        @if ($canShareQuote)
            <a href="{{ route('crm.products.quotes.share.create', [
                'crmQuote' => $quote,
                'subject' => 'Share ' . $quote->quote_number,
                'channel' => $quote->contact?->email ? 'email' : 'app',
                'recipient_email' => $quote->contact?->email,
                'recipient_phone' => $quote->contact?->phone,
                'body' => 'Please find attached quote ' . $quote->quote_number . ' for ' . ($quote->customer?->company_name ?: $quote->lead?->company_name ?: $quote->contact?->name ?: 'your account') . '.',
                'notes' => $quote->subject ?: null,
            ]) }}" class="btn btn-primary">
                <i class="bx bx-send"></i> Share quote
            </a>
        @endif
        @if ($canEditQuote)
            <a href="{{ route('crm.products.quotes.edit', $quote) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit quote
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'quotes'])

        @if (! empty($availableTransitions))
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Lifecycle</p>
                        <h2>Quote status</h2>
                    </div>
                </div>

                <div class="crm-inline" style="justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                    <div class="crm-inline">
                        <span class="crm-pill {{ in_array($quote->status, ['accepted', 'sent'], true) ? 'success' : ($quote->status === 'draft' ? 'primary' : 'muted') }}">
                            {{ $quoteStatuses[$quote->status] ?? ucfirst($quote->status) }}
                        </span>
                    </div>

                    <div class="crm-action-row">
                        @foreach ($availableTransitions as $status => $label)
                            <form method="POST" action="{{ route('crm.products.quotes.status', $quote) }}" class="crm-inline-form">
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
                <strong>{{ $quoteStatuses[$quote->status] ?? ucfirst($quote->status) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Subtotal</span>
                <strong>{{ $quote->currency_code }} {{ number_format((float) $quote->subtotal_amount, 2) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Tax</span>
                <strong>{{ $quote->currency_code }} {{ number_format((float) $quote->tax_amount, 2) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Total</span>
                <strong>{{ $quote->currency_code }} {{ number_format((float) $quote->total_amount, 2) }}</strong>
            </div>
        </div>

        <div class="crm-grid cols-2 crm-detail-grid">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Quote summary</p>
                        <h2>Header details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Quote number</span>
                        <strong>{{ $quote->quote_number }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Subject</span>
                        <strong>{{ $quote->subject ?: 'No subject' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $quote->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Recipient context</span>
                        <strong>{{ $quote->customer?->company_name ?: $quote->lead?->company_name ?: ($quote->contact ? 'Direct contact' : 'None') }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Lead</span>
                        <strong>{{ $quote->lead?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Customer</span>
                        <strong>{{ $quote->customer?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Recipient contact</span>
                        <strong>{{ $quote->contact?->name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Linked request</span>
                        <strong>{{ $quote->request?->title ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Quote date</span>
                        <strong>{{ $quote->quote_date?->format('d M Y') ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Valid until</span>
                        <strong>{{ $quote->valid_until?->format('d M Y') ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Currency</span>
                        <strong>{{ $quote->currency_code }} · {{ $quote->currency_symbol }} · {{ ucfirst($quote->currency_position) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Tax scope</span>
                        <strong>{{ $quote->tax_scope === 'document' ? 'Document tax · ' . number_format((float) $quote->document_tax_rate, 2) . '%' : 'Line tax' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Document discount</span>
                        <strong>{{ $discountTypes[$quote->document_discount_type] ?? ucfirst($quote->document_discount_type) }} · {{ number_format((float) $quote->document_discount_value, 2) }}</strong>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Notes and timing</p>
                        <h2>Commercial context</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Internal notes</span>
                        <strong>{{ $quote->notes ?: 'No notes have been added yet.' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Terms</span>
                        <strong>{{ $quote->terms ?: 'No quote terms have been added yet.' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Sent at</span>
                        <strong>{{ $quote->shared_at?->format('d M Y H:i') ?: 'Not sent' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Accepted at</span>
                        <strong>{{ $quote->accepted_at?->format('d M Y H:i') ?: 'Not accepted' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Rejected at</span>
                        <strong>{{ $quote->rejected_at?->format('d M Y H:i') ?: 'Not rejected' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Expired at</span>
                        <strong>{{ $quote->expired_at?->format('d M Y H:i') ?: 'Not expired' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Cancelled at</span>
                        <strong>{{ $quote->cancelled_at?->format('d M Y H:i') ?: 'Not cancelled' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Quote lines</p>
                    <h2>Snapshotted items</h2>
                </div>
            </div>

            @if ($quote->items->isEmpty())
                <div class="crm-empty">This quote has no saved line items.</div>
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
                            @foreach ($quote->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->item_name }}</strong>
                                        <span class="crm-muted">{{ $item->item_description ?: 'No description' }} · {{ $item->unit_label ?: 'unit' }} · {{ $item->source_type === 'catalog' ? 'Catalog line' : 'Custom line' }}</span>
                                    </td>
                                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td>{{ $quote->currency_code }} {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ $discountTypes[$item->discount_type] ?? ucfirst($item->discount_type) }} · {{ number_format((float) $item->discount_value, 2) }}</td>
                                    <td>{{ $quote->tax_scope === 'document' ? 'Document' : number_format((float) $item->tax_rate, 2) . '%' }} · {{ $quote->currency_code }} {{ number_format((float) $item->tax_amount, 2) }}</td>
                                    <td>{{ $quote->currency_code }} {{ number_format((float) $item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->quote_number }} - Quote</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.5; }
        .page { padding: 24px 28px; }
        .header { background: linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%); color: #fff; padding: 22px 24px; border-radius: 6px; }
        .header h1 { margin: 0 0 6px; font-size: 24px; }
        .header p { margin: 0; font-size: 12px; opacity: 0.95; }
        .grid { width: 100%; margin-top: 22px; }
        .grid td { width: 50%; vertical-align: top; padding: 0 12px 14px 0; }
        .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 16px; }
        .kicker { font-size: 10px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.08em; margin-bottom: 8px; }
        .meta-row { margin-bottom: 6px; }
        .meta-row strong { display: inline-block; min-width: 120px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px 8px; text-align: left; vertical-align: top; }
        th { font-size: 11px; text-transform: uppercase; color: #6b7280; }
        .totals { width: 320px; margin-left: auto; margin-top: 18px; }
        .totals td { padding: 8px 0; border: none; }
        .totals .grand td { border-top: 2px solid #1f2937; font-weight: 700; padding-top: 10px; }
        .muted { color: #6b7280; }
        .footer-note { margin-top: 24px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Heritage CRM Quote</h1>
            <p>{{ $document->quote_number }} · {{ $accountName }} · {{ $document->quote_date?->format('d M Y') }}</p>
        </div>

        <table class="grid">
            <tr>
                <td>
                    <div class="card">
                        <div class="kicker">Quote Summary</div>
                        <div class="meta-row"><strong>Quote number:</strong> {{ $document->quote_number }}</div>
                        <div class="meta-row"><strong>Status:</strong> {{ ucfirst($document->status) }}</div>
                        <div class="meta-row"><strong>Quote date:</strong> {{ $document->quote_date?->format('d M Y') ?: 'Not set' }}</div>
                        <div class="meta-row"><strong>Valid until:</strong> {{ $document->valid_until?->format('d M Y') ?: 'Not set' }}</div>
                        <div class="meta-row"><strong>Subject:</strong> {{ $document->subject ?: 'No subject' }}</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="kicker">Account Context</div>
                        <div class="meta-row"><strong>Account:</strong> {{ $accountName }}</div>
                        <div class="meta-row"><strong>Contact:</strong> {{ $document->contact?->name ?: 'No contact' }}</div>
                        <div class="meta-row"><strong>Email:</strong> {{ $document->contact?->email ?: 'No email' }}</div>
                        <div class="meta-row"><strong>Phone:</strong> {{ $document->contact?->phone ?: 'No phone' }}</div>
                        <div class="meta-row"><strong>Linked request:</strong> {{ $document->request?->title ?: 'None' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->item_name }}</strong><br>
                            <span class="muted">{{ $item->item_description ?: 'No description' }} · {{ $item->unit_label ?: 'unit' }}</span>
                        </td>
                        <td>{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $document->currency_code }} {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ ucfirst($item->discount_type) }} · {{ number_format((float) $item->discount_value, 2) }}</td>
                        <td>{{ number_format((float) $item->tax_rate, 2) }}%</td>
                        <td>{{ $document->currency_code }} {{ number_format((float) $item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td style="text-align: right;">{{ $document->currency_code }} {{ number_format((float) $document->subtotal_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Document discount</td>
                <td style="text-align: right;">{{ $document->currency_code }} {{ number_format((float) $document->document_discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td style="text-align: right;">{{ $document->currency_code }} {{ number_format((float) $document->tax_amount, 2) }}</td>
            </tr>
            <tr class="grand">
                <td>Total</td>
                <td style="text-align: right;">{{ $document->currency_code }} {{ number_format((float) $document->total_amount, 2) }}</td>
            </tr>
        </table>

        @if ($document->terms || $document->notes)
            <table class="grid">
                <tr>
                    <td>
                        <div class="card footer-note">
                            <div class="kicker">Terms</div>
                            {{ $document->terms ?: 'No terms supplied.' }}
                        </div>
                    </td>
                    <td>
                        <div class="card footer-note">
                            <div class="kicker">Notes</div>
                            {{ $document->notes ?: 'No notes supplied.' }}
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
</body>
</html>

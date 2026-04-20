<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        /* Page setup */
        @page {
            margin: 20mm 15mm;
        }

        /* School header */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .school-address {
            font-size: 10px;
            color: #666;
        }

        /* Invoice title */
        .invoice-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        /* Info sections */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 12px;
            margin-bottom: 8px;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        .items-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }

        .items-table .amount {
            text-align: right;
        }

        /* Totals section */
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }

        .totals-row {
            display: table;
            width: 100%;
            padding: 5px 0;
        }

        .totals-label {
            display: table-cell;
            text-align: right;
            padding-right: 15px;
        }

        .totals-value {
            display: table-cell;
            text-align: right;
            width: 120px;
        }

        .totals-row.total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            padding-top: 10px;
        }

        .totals-row.balance {
            background: #f5f5f5;
            padding: 10px;
            font-weight: bold;
        }

        /* Status */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-issued {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-partial {
            background: #fff3e0;
            color: #e65100;
        }

        .status-paid {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-overdue {
            background: #ffebee;
            color: #c62828;
        }

        .status-draft {
            background: #f5f5f5;
            color: #666;
        }

        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Notes */
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-left: 3px solid #666;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notes ul {
            margin-left: 20px;
            margin-top: 5px;
        }

        .notes li {
            margin-bottom: 3px;
        }
    </style>
</head>

<body>
    <!-- School Header -->
    <div class="school-header">
        <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
        <div class="school-address">
            {{ $school->postal_address ?? '' }}
            @if($school->telephone ?? null)
                | Tel: {{ $school->telephone }}
            @endif
            @if($school->email_address ?? null)
                | Email: {{ $school->email_address }}
            @endif
        </div>
    </div>

    <div class="invoice-title">Fee Invoice</div>

    <!-- Invoice & Student Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Bill To:</div>
            <div class="info-value" style="font-weight: bold;">
                {{ $invoice->student->sponsor->name ?? 'Parent/Guardian' }}
            </div>
            <div class="info-value">
                Student: {{ $invoice->student->full_name }}<br>
                Student #: {{ $invoice->student->student_number }}<br>
                Grade: {{ $invoice->student->currentGrade->name ?? 'N/A' }}
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-label">Invoice Details:</div>
            <div class="info-value">
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date:</strong> {{ $invoice->issued_at->format('d M Y') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}<br>
                <strong>Term:</strong> {{ $invoice->term->name }}<br>
                <strong>Status:</strong>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Current Year Fee Items -->
    @php
        $feeItems = $invoice->getFeeItems();
        $carryoverItems = $invoice->getCarryoverItems();
    @endphp
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="amount">Amount</th>
                <th class="amount">Discount</th>
                <th class="amount">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="amount">{{ format_currency($item->amount) }}</td>
                    <td class="amount">{{ format_currency($item->discount_amount) }}</td>
                    <td class="amount">{{ format_currency($item->net_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; border: none; padding-top: 8px;"><strong>Subtotal:</strong></td>
                <td class="amount" style="border: none; padding-top: 8px;"><strong>{{ format_currency($invoice->getFeeSubtotal()) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Previous Years Outstanding (if any) -->
    @if($carryoverItems->isNotEmpty())
        <div style="margin-top: 20px; margin-bottom: 10px; font-weight: bold; color: #c62828;">Previous Years Outstanding</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Year</th>
                    <th style="width: 50%;">Description</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carryoverItems as $item)
                    <tr>
                        <td>{{ $item->source_year }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="amount">{{ format_currency($item->net_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; border: none; padding-top: 8px;"><strong>Total Previous Balance:</strong></td>
                    <td class="amount" style="border: none; padding-top: 8px;"><strong>{{ format_currency($invoice->getTotalCarryover()) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Totals -->
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Fee Subtotal:</div>
            <div class="totals-value">{{ format_currency($invoice->getFeeSubtotal()) }}</div>
        </div>
        @if($invoice->discount_amount > 0)
            <div class="totals-row">
                <div class="totals-label">Discount:</div>
                <div class="totals-value">- {{ format_currency($invoice->discount_amount) }}</div>
            </div>
        @endif
        @if($carryoverItems->isNotEmpty())
            <div class="totals-row">
                <div class="totals-label">Previous Years:</div>
                <div class="totals-value">{{ format_currency($invoice->getTotalCarryover()) }}</div>
            </div>
        @endif
        <div class="totals-row total">
            <div class="totals-label">Grand Total:</div>
            <div class="totals-value">{{ format_currency($invoice->total_amount) }}</div>
        </div>
        @if($invoice->amount_paid > 0)
            <div class="totals-row">
                <div class="totals-label">Amount Paid:</div>
                <div class="totals-value">{{ format_currency($invoice->amount_paid) }}</div>
            </div>
        @endif
        <div class="totals-row balance">
            <div class="totals-label">Balance Due:</div>
            <div class="totals-value">{{ format_currency($invoice->balance) }}</div>
        </div>
    </div>

    @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes:</div>
            {{ $invoice->notes }}
        </div>
    @endif

    <!-- Payment Instructions -->
    <div class="notes" style="margin-top: 30px;">
        <div class="notes-title">Payment Instructions:</div>
        <p>Please make payment to {{ $school->school_name ?? 'School' }} via:</p>
        <ul>
            <li>Bank Transfer: First National Bank, Acc: 1234567890</li>
            <li>Mobile Money: Orange Money 7X XXX XXX</li>
            <li>Cash: School Bursar's Office</li>
        </ul>
        <p style="margin-top: 10px;">Reference: {{ $invoice->invoice_number }}</p>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }} | Invoice {{ $invoice->invoice_number }}
    </div>
</body>

</html>

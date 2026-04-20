<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
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

        /* Receipt title */
        .receipt-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
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

        /* Payment table */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .payment-table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        .payment-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }

        .payment-table .amount {
            text-align: right;
        }

        .payment-table .label-cell {
            background: #fafafa;
            font-weight: 500;
            width: 40%;
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

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-voided {
            background: #ffebee;
            color: #c62828;
        }

        /* Void watermark */
        .void-watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(200, 0, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 10px;
            z-index: -1;
        }

        .void-details {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            padding: 15px;
            margin-top: 20px;
            border-radius: 3px;
        }

        .void-details-title {
            font-weight: bold;
            color: #c62828;
            margin-bottom: 10px;
        }

        /* Signature section */
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-line {
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .signature-box {
            border-top: 1px solid #333;
            width: 200px;
            padding-top: 5px;
            margin-top: 40px;
        }

        .signature-name {
            font-size: 11px;
            color: #666;
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

        /* Payment method badge */
        .method-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e3f2fd;
            color: #1565c0;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    @if($payment->voided_at)
        <div class="void-watermark">VOIDED</div>
    @endif

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

    <div class="receipt-title">Official Receipt</div>

    <!-- Receipt & Payer Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Received From:</div>
            <div class="info-value" style="font-weight: bold;">
                {{ $payment->invoice->student->sponsor->full_name ?? 'Parent/Guardian' }}
            </div>
            <div class="info-value">
                Student: {{ $payment->invoice->student->full_name ?? 'N/A' }}<br>
                Student #: {{ $payment->invoice->student->student_number ?? 'N/A' }}<br>
                Grade: {{ $payment->invoice->student->klass->name ?? 'N/A' }}
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-label">Receipt Details:</div>
            <div class="info-value">
                <strong>Receipt #:</strong> {{ $payment->receipt_number }}<br>
                <strong>Date:</strong> {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : now()->format('d M Y') }}<br>
                <strong>Invoice #:</strong> {{ $payment->invoice->invoice_number ?? 'N/A' }}<br>
                <strong>Year:</strong> {{ $payment->invoice->year }}<br>
                <strong>Status:</strong>
                <span class="status-badge {{ $payment->voided_at ? 'status-voided' : 'status-active' }}">
                    {{ $payment->voided_at ? 'Voided' : 'Active' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Payment Details Table -->
    <table class="payment-table">
        <thead>
            <tr>
                <th style="width: 60%;">Description</th>
                <th style="width: 20%;">Payment Method</th>
                <th class="amount" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Payment for Invoice {{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                <td>
                    <span class="method-badge">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Cash')) }}</span>
                </td>
                <td class="amount">{{ format_currency($payment->amount) }}</td>
            </tr>
            @if($payment->reference_number)
                <tr>
                    <td class="label-cell">Reference Number</td>
                    <td colspan="2">{{ $payment->reference_number }}</td>
                </tr>
            @endif
            @if($payment->payment_method === 'cheque' && $payment->cheque_number)
                <tr>
                    <td class="label-cell">Cheque Number</td>
                    <td colspan="2">{{ $payment->cheque_number }}</td>
                </tr>
            @endif
            @if($payment->bank_name)
                <tr>
                    <td class="label-cell">Bank</td>
                    <td colspan="2">{{ $payment->bank_name }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-row total">
            <div class="totals-label">Amount Received:</div>
            <div class="totals-value">{{ format_currency($payment->amount) }}</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Invoice Total:</div>
            <div class="totals-value">{{ format_currency($payment->invoice->total_amount ?? 0) }}</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Previous Payments:</div>
            @php
                $previousPayments = ($payment->invoice->amount_paid ?? 0) - $payment->amount;
                if ($payment->voided_at) {
                    $previousPayments = $payment->invoice->amount_paid ?? 0;
                }
            @endphp
            <div class="totals-value">{{ format_currency(max(0, $previousPayments)) }}</div>
        </div>
        <div class="totals-row balance">
            <div class="totals-label">Balance After Payment:</div>
            <div class="totals-value">{{ format_currency($payment->invoice->balance ?? 0) }}</div>
        </div>
    </div>

    @if($payment->voided_at)
        <div class="void-details">
            <div class="void-details-title">Payment Voided</div>
            <p><strong>Voided on:</strong> {{ $payment->voided_at->format('d M Y H:i') }}</p>
            <p><strong>Voided by:</strong> {{ $payment->voidedBy->name ?? 'N/A' }}</p>
            <p><strong>Reason:</strong> {{ $payment->void_reason }}</p>
        </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-line">
            <div class="signature-col">
                <div class="signature-box">
                    <div class="signature-name">Received by: {{ $payment->receivedBy->name ?? 'Bursar' }}</div>
                </div>
            </div>
            <div class="signature-col" style="text-align: right;">
                <div class="signature-box" style="margin-left: auto;">
                    <div class="signature-name">Date: {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : now()->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }} | Receipt {{ $payment->receipt_number }}<br>
        This is a computer-generated receipt.
    </div>
</body>

</html>

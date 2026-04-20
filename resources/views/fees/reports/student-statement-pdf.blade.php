<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Statement - {{ $student->student_number }}</title>
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

        /* Statement title */
        .statement-title {
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

        /* Account summary card */
        .summary-card {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .summary-label {
            display: table-cell;
            width: 60%;
        }

        .summary-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }

        .summary-row.balance {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .balance-positive {
            color: #c62828;
        }

        .balance-zero {
            color: #2e7d32;
        }

        /* Section titles */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 20px 0;
        }

        .data-table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 11px;
        }

        .data-table .amount {
            text-align: right;
        }

        .data-table .center {
            text-align: center;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-partial {
            background: #fff3e0;
            color: #e65100;
        }

        .status-issued {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-outstanding {
            background: #ffebee;
            color: #c62828;
        }

        .status-overdue {
            background: #ffebee;
            color: #c62828;
        }

        /* Transaction history */
        .transaction-table th,
        .transaction-table td {
            font-size: 10px;
            padding: 5px 8px;
        }

        /* No data message */
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #eee;
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

    <div class="statement-title">Fee Statement</div>

    <!-- Student & Statement Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Student Details:</div>
            <div class="info-value" style="font-weight: bold;">
                {{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}
            </div>
            <div class="info-value">
                Student #: {{ $student->student_number }}<br>
                Grade: {{ $student->currentGrade->name ?? 'N/A' }}
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-label">Statement Details:</div>
            <div class="info-value">
                <strong>Date:</strong> {{ $generatedAt->format('d M Y') }}<br>
                <strong>Period:</strong> {{ $termName ?? 'All-time' }}<br>
                <strong>Parent/Sponsor:</strong> {{ $student->sponsor->name ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Account Summary Card -->
    <div class="summary-card">
        <div class="summary-row">
            <div class="summary-label">Total Invoiced:</div>
            <div class="summary-value">{{ format_currency($balance['total_invoiced']) }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Total Paid:</div>
            <div class="summary-value">{{ format_currency($balance['total_paid']) }}</div>
        </div>
        <div class="summary-row balance">
            <div class="summary-label">Current Balance:</div>
            <div class="summary-value {{ (float)$balance['balance'] > 0 ? 'balance-positive' : 'balance-zero' }}">
                {{ format_currency($balance['balance']) }}
            </div>
        </div>
    </div>

    <!-- Invoices Section -->
    <div class="section-title">Invoices Issued</div>
    @if($invoices->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Term</th>
                    <th>Date</th>
                    <th class="amount">Total Amount</th>
                    <th class="amount">Paid</th>
                    <th class="amount">Balance</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->term->name ?? 'N/A' }}</td>
                        <td>{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : 'N/A' }}</td>
                        <td class="amount">{{ format_currency($invoice->total_amount) }}</td>
                        <td class="amount">{{ format_currency($invoice->amount_paid) }}</td>
                        <td class="amount">{{ format_currency($invoice->balance) }}</td>
                        <td class="center">
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No invoices issued</div>
    @endif

    <!-- Payments Section -->
    <div class="section-title">Payments Received</div>
    @if($payments->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Date</th>
                    <th class="amount">Amount</th>
                    <th>Method</th>
                    <th>Invoice #</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->receipt_number }}</td>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                        <td class="amount">{{ format_currency($payment->amount) }}</td>
                        <td>{{ $payment->payment_method_label ?? ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No payments recorded</div>
    @endif

    <!-- Transaction History Section -->
    <div class="section-title">Transaction History</div>
    @php
        // Combine invoices and payments into a single timeline
        $transactions = collect();

        // Add invoices as debits
        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->issued_at ?? $invoice->created_at,
                'description' => 'Invoice #' . $invoice->invoice_number . ' (' . ($invoice->term->name ?? 'N/A') . ')',
                'debit' => (float)$invoice->total_amount,
                'credit' => 0,
                'type' => 'invoice',
            ]);
        }

        // Add payments as credits
        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date ?? $payment->created_at,
                'description' => 'Payment #' . $payment->receipt_number,
                'debit' => 0,
                'credit' => (float)$payment->amount,
                'type' => 'payment',
            ]);
        }

        // Sort by date ascending
        $transactions = $transactions->sortBy('date')->values();

        // Calculate running balance
        $runningBalance = 0;
        foreach ($transactions as $index => $transaction) {
            $runningBalance = $runningBalance + $transaction['debit'] - $transaction['credit'];
            $transactions[$index]['balance'] = $runningBalance;
        }
    @endphp

    @if($transactions->count() > 0)
        <table class="data-table transaction-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="amount">Debit</th>
                    <th class="amount">Credit</th>
                    <th class="amount">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction['date'] ? \Carbon\Carbon::parse($transaction['date'])->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $transaction['description'] }}</td>
                        <td class="amount">{{ $transaction['debit'] > 0 ? format_currency($transaction['debit']) : '-' }}</td>
                        <td class="amount">{{ $transaction['credit'] > 0 ? format_currency($transaction['credit']) : '-' }}</td>
                        <td class="amount">{{ format_currency($transaction['balance']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No transactions recorded</div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated on {{ $generatedAt->format('d M Y') }} at {{ $generatedAt->format('H:i') }} | This is a computer-generated document
    </div>
</body>

</html>

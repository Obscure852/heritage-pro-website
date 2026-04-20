<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Fine Receipt LF-{{ $fine->id }}</title>
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

        /* Waiver details */
        .waiver-details {
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 3px;
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

    <div class="receipt-title">Library Fine Receipt</div>

    <!-- Receipt & Borrower Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Borrower:</div>
            <div class="info-value" style="font-weight: bold;">
                {{ optional($fine->borrower)->full_name ?? optional($fine->borrower)->name ?? '-' }}
            </div>
            <div class="info-value">
                Type: {{ ucfirst($fine->borrower_type) }}<br>
                @if($fine->borrower_type === 'student')
                    Student #: {{ optional($fine->borrower)->student_number ?? 'N/A' }}<br>
                    Grade: {{ optional(optional($fine->borrower)->klass)->name ?? 'N/A' }}
                @else
                    Staff #: {{ optional($fine->borrower)->employee_number ?? 'N/A' }}
                @endif
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-label">Receipt Details:</div>
            <div class="info-value">
                <strong>Reference:</strong> LF-{{ $fine->id }}<br>
                <strong>Date:</strong> {{ now()->format('d M Y') }}<br>
                <strong>Fine Type:</strong> {{ ucfirst(str_replace('_', ' ', $fine->fine_type)) }}<br>
                <strong>Fine Date:</strong> {{ $fine->fine_date ? $fine->fine_date->format('d M Y') : '-' }}<br>
                <strong>Status:</strong> {{ ucfirst($fine->status) }}
            </div>
        </div>
    </div>

    <!-- Fine Details Table -->
    <table class="payment-table">
        <thead>
            <tr>
                <th style="width: 60%;">Description</th>
                <th class="amount" style="width: 20%;">Details</th>
                <th class="amount" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ optional(optional(optional($fine->transaction)->copy)->book)->title ?? 'Unknown Book' }}
                    @if($fine->transaction && $fine->transaction->copy)
                        <br><small style="color: #666;">Accession #: {{ $fine->transaction->copy->accession_number ?? '-' }}</small>
                    @endif
                </td>
                <td class="amount">
                    @if($fine->fine_type === 'overdue')
                        {{ $fine->daily_rate ? 'P'.number_format($fine->daily_rate, 2).'/day' : '-' }}
                    @else
                        {{ $fine->notes ?? '-' }}
                    @endif
                </td>
                <td class="amount">{{ format_currency($fine->amount) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Fine Amount:</div>
            <div class="totals-value">{{ format_currency($fine->amount) }}</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Amount Paid:</div>
            <div class="totals-value">{{ format_currency($fine->amount_paid) }}</div>
        </div>
        @if($fine->amount_waived > 0)
        <div class="totals-row">
            <div class="totals-label">Amount Waived:</div>
            <div class="totals-value">{{ format_currency($fine->amount_waived) }}</div>
        </div>
        @endif
        <div class="totals-row balance">
            <div class="totals-label">Outstanding Balance:</div>
            <div class="totals-value">{{ format_currency($fine->outstanding) }}</div>
        </div>
    </div>

    @if($fine->waived_by)
    <div class="waiver-details">
        <strong>Waiver Details:</strong><br>
        Waived by: {{ optional($fine->waivedBy)->name ?? 'N/A' }}<br>
        Reason: {{ $fine->waiver_reason ?? '-' }}
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-line">
            <div class="signature-col">
                <div class="signature-box">
                    <div class="signature-name">Received by: {{ $generatedBy ?? 'Librarian' }}</div>
                </div>
            </div>
            <div class="signature-col" style="text-align: right;">
                <div class="signature-box" style="margin-left: auto;">
                    <div class="signature-name">Date: {{ now()->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }} | Reference: LF-{{ $fine->id }}<br>
        This is a computer-generated receipt.
    </div>
</body>

</html>

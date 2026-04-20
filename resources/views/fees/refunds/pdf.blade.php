<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }} {{ $refund->refund_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid {{ $refund->isCreditNote() ? '#3b82f6' : '#ef4444' }};
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .school-address {
            font-size: 11px;
            color: #6b7280;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: {{ $refund->isCreditNote() ? '#3b82f6' : '#ef4444' }};
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-number {
            font-size: 14px;
            color: #374151;
            margin-top: 5px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 4px 0;
            width: 50%;
            vertical-align: top;
        }

        .info-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 12px;
            font-weight: 500;
            color: #1f2937;
        }

        .amount-box {
            background: {{ $refund->isCreditNote() ? '#eff6ff' : '#fef2f2' }};
            border: 2px solid {{ $refund->isCreditNote() ? '#3b82f6' : '#ef4444' }};
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .amount-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: {{ $refund->isCreditNote() ? '#1e40af' : '#991b1b' }};
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 11px;
            text-transform: uppercase;
        }

        .reason-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }

        .reason-label {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .reason-text {
            color: #4b5563;
        }

        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .signature-block {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            margin-top: 40px;
            padding-top: 8px;
        }

        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }

        .signature-name {
            font-size: 11px;
            font-weight: 500;
            color: #374151;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if ($school)
                <div class="school-name">{{ $school->name ?? 'School Name' }}</div>
                <div class="school-address">
                    @if ($school->address){{ $school->address }}@endif
                    @if ($school->phone) | Tel: {{ $school->phone }}@endif
                    @if ($school->email) | {{ $school->email }}@endif
                </div>
            @endif
            <div class="document-title">{{ $refund->isCreditNote() ? 'Credit Note' : 'Refund Voucher' }}</div>
            <div class="document-number">{{ $refund->refund_number }}</div>
        </div>

        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Student Name</div>
                        <div class="info-value">{{ $refund->invoice->student->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Date</div>
                        <div class="info-value">{{ $refund->refund_date->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Student Number</div>
                        <div class="info-value">{{ $refund->invoice->student->student_number ?? 'N/A' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Invoice Number</div>
                        <div class="info-value">{{ $refund->invoice->invoice_number ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Grade</div>
                        <div class="info-value">{{ $refund->invoice->student->currentGrade->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Year</div>
                        <div class="info-value">{{ $refund->year }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="amount-box">
            <div class="amount-label">{{ $refund->isCreditNote() ? 'Credit Amount' : 'Refund Amount' }}</div>
            <div class="amount-value">{{ format_currency($refund->amount) }}</div>
        </div>

        <table class="details-table">
            <tr>
                <th>Type</th>
                <td>{{ $refund->refund_type_label }}</td>
            </tr>
            <tr>
                <th>Method</th>
                <td>{{ $refund->refund_method_label }}</td>
            </tr>
            @if ($refund->payment)
                <tr>
                    <th>Original Payment</th>
                    <td>{{ $refund->payment->receipt_number }} ({{ format_currency($refund->payment->amount) }})</td>
                </tr>
            @endif
            @if ($refund->reference_number)
                <tr>
                    <th>Reference Number</th>
                    <td>{{ $refund->reference_number }}</td>
                </tr>
            @endif
            <tr>
                <th>Status</th>
                <td><span class="status-badge">{{ $refund->status_label }}</span></td>
            </tr>
        </table>

        <div class="reason-box">
            <div class="reason-label">Reason</div>
            <div class="reason-text">{{ $refund->reason }}</div>
        </div>

        @if ($refund->notes)
            <div class="reason-box">
                <div class="reason-label">Notes</div>
                <div class="reason-text">{{ $refund->notes }}</div>
            </div>
        @endif

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">
                    <div class="signature-name">{{ $refund->requestedBy->name ?? 'N/A' }}</div>
                    <div class="signature-label">Requested By</div>
                </div>
            </div>
            <div class="signature-block">
                <div class="signature-line">
                    <div class="signature-name">{{ $refund->approvedBy->name ?? 'N/A' }}</div>
                    <div class="signature-label">Approved By</div>
                </div>
            </div>
            <div class="signature-block">
                <div class="signature-line">
                    <div class="signature-name">{{ $refund->processedBy->name ?? 'N/A' }}</div>
                    <div class="signature-label">Processed By</div>
                </div>
            </div>
        </div>

        <div class="footer">
            Generated on {{ now()->format('d M Y H:i') }} | {{ $refund->refund_number }}
        </div>
    </div>
</body>
</html>

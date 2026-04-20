<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>End of Day Report - {{ $report['date'] }}</title>
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

        /* Report title */
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }

        .report-date {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        /* Report metadata */
        .report-meta {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            font-size: 10px;
        }

        .report-meta p {
            margin: 2px 0;
        }

        /* Summary section */
        .summary-section {
            margin-bottom: 25px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-card {
            display: table-cell;
            width: 25%;
            background: #f5f5f5;
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }

        /* Balance flow */
        .balance-flow {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }

        .balance-flow-table {
            width: 100%;
            border-collapse: collapse;
        }

        .balance-flow-table td {
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }

        .balance-flow-item {
            text-align: center;
        }

        .balance-flow-item .value {
            font-size: 14px;
            font-weight: bold;
        }

        .balance-flow-item .label {
            font-size: 9px;
            color: #666;
        }

        .balance-flow-arrow {
            font-size: 16px;
            color: #999;
        }

        /* Section titles */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        /* Breakdown tables */
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .breakdown-table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        .breakdown-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 11px;
        }

        .breakdown-table td.amount {
            text-align: right;
            font-weight: bold;
        }

        .breakdown-table td.count {
            text-align: center;
        }

        /* Data table for payments */
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
            font-size: 10px;
        }

        .data-table td.amount {
            text-align: right;
        }

        .data-table td.time {
            white-space: nowrap;
        }

        .data-table tfoot td {
            font-weight: bold;
            background: #f5f5f5;
        }

        /* Method badges */
        .method-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-bank_transfer { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-mobile_money { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #666;
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        /* Two-column layout for breakdowns */
        .two-column {
            display: table;
            width: 100%;
        }

        .two-column .col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .two-column .spacer {
            display: table-cell;
            width: 4%;
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <!-- School Header -->
    <div class="school-header">
        <div class="school-name">{{ $school->name ?? 'Heritage Junior Secondary School' }}</div>
        <div class="school-address">
            {{ $school->address ?? '' }}
            @if($school->phone ?? false) | Tel: {{ $school->phone }} @endif
            @if($school->email ?? false) | {{ $school->email }} @endif
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">End of Day Report</div>
    <div class="report-date">{{ \Carbon\Carbon::parse($report['date'])->format('l, d F Y') }}</div>

    <!-- Report Metadata -->
    <div class="report-meta">
        <p><strong>Generated:</strong> {{ $report['generated_at'] }} @if($report['generated_by']) by {{ $report['generated_by'] }} @endif</p>
        @if($year)
            <p><strong>Year:</strong> {{ $year }}</p>
        @endif
    </div>

    <!-- Balance Flow Summary -->
    <div class="section-title">Daily Balance Summary</div>
    <div class="balance-flow">
        <table class="balance-flow-table">
            <tr>
                <td>
                    <div class="balance-flow-item">
                        <div class="value">{{ format_currency($report['summary']['opening_balance']) }}</div>
                        <div class="label">Opening Balance</div>
                    </div>
                </td>
                <td class="balance-flow-arrow">+</td>
                <td>
                    <div class="balance-flow-item">
                        <div class="value">{{ format_currency($report['summary']['invoiced_today']) }}</div>
                        <div class="label">Invoiced ({{ $report['summary']['invoice_count_today'] }})</div>
                    </div>
                </td>
                <td class="balance-flow-arrow">-</td>
                <td>
                    <div class="balance-flow-item">
                        <div class="value" style="color: #2e7d32;">{{ format_currency($report['summary']['collected_today']) }}</div>
                        <div class="label">Collected ({{ $report['summary']['payment_count_today'] }})</div>
                    </div>
                </td>
                <td class="balance-flow-arrow">=</td>
                <td>
                    <div class="balance-flow-item">
                        <div class="value" style="color: #c62828;">{{ format_currency($report['summary']['closing_balance']) }}</div>
                        <div class="label">Closing Balance</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Breakdowns Side by Side -->
    <div class="two-column no-break">
        <div class="col">
            <div class="section-title">Collections by Payment Method</div>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th style="text-align: center;">Count</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['by_method'] as $method => $info)
                        <tr>
                            <td>
                                <span class="method-badge method-{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                            </td>
                            <td class="count">{{ $info['count'] }}</td>
                            <td class="amount">{{ format_currency($info['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #666;">No payments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="spacer"></div>
        <div class="col">
            <div class="section-title">Collections by Collector</div>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Collector</th>
                        <th style="text-align: center;">Count</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['by_collector'] as $collector)
                        <tr>
                            <td>{{ $collector['name'] }}</td>
                            <td class="count">{{ $collector['count'] }}</td>
                            <td class="amount">{{ format_currency($collector['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #666;">No payments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="section-title">Payment Details</div>
    @if(empty($report['payments']))
        <p style="text-align: center; color: #666; padding: 20px;">No payments recorded for this date</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Receipt #</th>
                    <th>Student</th>
                    <th>Student #</th>
                    <th style="text-align: right;">Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['payments'] as $payment)
                    <tr>
                        <td class="time">{{ $payment['created_at'] }}</td>
                        <td>{{ $payment['receipt_number'] }}</td>
                        <td>{{ $payment['student_name'] }}</td>
                        <td>{{ $payment['student_number'] }}</td>
                        <td class="amount">{{ format_currency($payment['amount']) }}</td>
                        <td>
                            <span class="method-badge method-{{ $payment['payment_method'] }}">
                                {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}
                            </span>
                        </td>
                        <td>{{ $payment['reference_number'] ?? '-' }}</td>
                        <td>{{ $payment['received_by'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"><strong>Total ({{ count($report['payments']) }} payments)</strong></td>
                    <td class="amount"><strong>{{ format_currency($report['summary']['collected_today']) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Computer Generated Report - {{ now()->format('d M Y H:i:s') }}</p>
        <p>{{ $school->name ?? 'Heritage Junior Secondary School' }} | End of Day Report</p>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Statement - {{ $statement['year'] }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
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
            font-size: 18px;
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
            font-size: 16px;
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

        /* Section titles */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 8px 10px;
            margin: 20px 0 10px 0;
            border-left: 3px solid #4e73df;
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
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 10px;
        }

        .data-table .text-right {
            text-align: right;
        }

        .data-table .text-center {
            text-align: center;
        }

        .data-table tfoot td {
            font-weight: bold;
            background: #f5f5f5;
        }

        /* Balance colors */
        .balance-positive {
            color: #059669;
        }

        .balance-negative {
            color: #dc2626;
        }

        /* Status badges */
        .status-approved {
            color: #059669;
            font-weight: bold;
        }

        .status-pending {
            color: #f59e0b;
            font-weight: bold;
        }

        .status-rejected {
            color: #dc2626;
            font-weight: bold;
        }

        .status-cancelled {
            color: #6b7280;
            font-weight: bold;
        }

        /* Summary card */
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
        }

        .summary-item .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
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
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            font-size: 9px;
            color: #666;
            text-align: center;
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

    <div class="statement-title">Leave Statement</div>

    <!-- Employee & Statement Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Employee Details</div>
            <div class="info-value" style="font-weight: bold;">
                {{ $statement['user']->name }}
            </div>
            <div class="info-value">
                @if($statement['user']->staff_id ?? null)
                    Employee ID: {{ $statement['user']->staff_id }}<br>
                @endif
                @if($statement['user']->department ?? null)
                    Department: {{ $statement['user']->department }}<br>
                @endif
                @if($statement['user']->email ?? null)
                    Email: {{ $statement['user']->email }}
                @endif
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-label">Statement Details</div>
            <div class="info-value">
                <strong>Leave Year:</strong> {{ $statement['year'] }}<br>
                <strong>Generated:</strong> {{ $statement['generatedAt']->format('d M Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="summary-card">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Entitled</div>
                <div class="value">{{ number_format($statement['summary']['total_entitled'], 1) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Used</div>
                <div class="value">{{ number_format($statement['summary']['total_used'], 1) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Pending</div>
                <div class="value">{{ number_format($statement['summary']['total_pending'], 1) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Available</div>
                <div class="value {{ $statement['summary']['total_available'] >= 0 ? 'balance-positive' : 'balance-negative' }}">
                    {{ number_format($statement['summary']['total_available'], 1) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balance Summary -->
    <div class="section-title">Leave Balance Summary</div>
    @if($statement['balances']->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th class="text-right">Entitled</th>
                    <th class="text-right">Carried</th>
                    <th class="text-right">Accrued</th>
                    <th class="text-right">Adjusted</th>
                    <th class="text-right">Used</th>
                    <th class="text-right">Pending</th>
                    <th class="text-right">Available</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement['balances'] as $balance)
                    @php
                        $available = $balance->entitled + $balance->carried_over + $balance->accrued + $balance->adjusted - $balance->used - $balance->pending;
                    @endphp
                    <tr>
                        <td>{{ $balance->leaveType->name ?? 'Unknown' }}</td>
                        <td class="text-right">{{ number_format($balance->entitled, 1) }}</td>
                        <td class="text-right">{{ number_format($balance->carried_over, 1) }}</td>
                        <td class="text-right">{{ number_format($balance->accrued, 1) }}</td>
                        <td class="text-right">{{ number_format($balance->adjusted, 1) }}</td>
                        <td class="text-right">{{ number_format($balance->used, 1) }}</td>
                        <td class="text-right">{{ number_format($balance->pending, 1) }}</td>
                        <td class="text-right {{ $available >= 0 ? 'balance-positive' : 'balance-negative' }}">
                            {{ number_format($available, 1) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right">{{ number_format($statement['summary']['total_entitled'], 1) }}</td>
                    <td class="text-right">{{ number_format($statement['summary']['total_carried'], 1) }}</td>
                    <td class="text-right">{{ number_format($statement['summary']['total_accrued'], 1) }}</td>
                    <td class="text-right">{{ number_format($statement['summary']['total_adjusted'], 1) }}</td>
                    <td class="text-right">{{ number_format($statement['summary']['total_used'], 1) }}</td>
                    <td class="text-right">{{ number_format($statement['summary']['total_pending'], 1) }}</td>
                    <td class="text-right {{ $statement['summary']['total_available'] >= 0 ? 'balance-positive' : 'balance-negative' }}">
                        {{ number_format($statement['summary']['total_available'], 1) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No leave balances found for this year</div>
    @endif

    <!-- Leave Request History -->
    <div class="section-title">Leave Request History</div>
    @if($statement['requests']->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th class="text-right">Days</th>
                    <th class="text-center">Status</th>
                    <th>Approved By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement['requests'] as $request)
                    <tr>
                        <td>{{ substr($request->ulid, 0, 8) }}...</td>
                        <td>{{ $request->leaveType->name ?? 'N/A' }}</td>
                        <td>{{ $request->start_date->format('d M Y') }}</td>
                        <td>{{ $request->end_date->format('d M Y') }}</td>
                        <td class="text-right">{{ number_format($request->total_days, 1) }}</td>
                        <td class="text-center status-{{ strtolower($request->status) }}">
                            {{ ucfirst($request->status) }}
                        </td>
                        <td>{{ $request->approver->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No leave requests found for this year</div>
    @endif

    <!-- Request Summary by Status -->
    @if($statement['requests']->count() > 0)
        <div class="section-title">Request Summary by Status</div>
        <table class="data-table" style="width: 50%;">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Total Days</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusSummary = [
                        'approved' => ['label' => 'Approved', 'count' => $statement['requestsByStatus']['approved']->count(), 'days' => $statement['requestsByStatus']['approved']->sum('total_days')],
                        'pending' => ['label' => 'Pending', 'count' => $statement['requestsByStatus']['pending']->count(), 'days' => $statement['requestsByStatus']['pending']->sum('total_days')],
                        'rejected' => ['label' => 'Rejected', 'count' => $statement['requestsByStatus']['rejected']->count(), 'days' => $statement['requestsByStatus']['rejected']->sum('total_days')],
                        'cancelled' => ['label' => 'Cancelled', 'count' => $statement['requestsByStatus']['cancelled']->count(), 'days' => $statement['requestsByStatus']['cancelled']->sum('total_days')],
                    ];
                @endphp
                @foreach($statusSummary as $status => $data)
                    @if($data['count'] > 0)
                        <tr>
                            <td class="status-{{ $status }}">{{ $data['label'] }}</td>
                            <td class="text-right">{{ $data['count'] }}</td>
                            <td class="text-right">{{ number_format($data['days'], 1) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
        <p>Generated from {{ config('app.name') }} on {{ $statement['generatedAt']->format('d M Y H:i:s') }}</p>
    </div>
</body>

</html>

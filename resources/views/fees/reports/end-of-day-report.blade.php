@extends('layouts.master')
@section('title')
    End of Day Report
@endsection
@section('css')
    <style>
        .fee-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fee-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
            padding: 24px;
        }

        .report-meta {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border-left: 4px solid #4e73df;
        }

        .report-meta p {
            margin: 0;
            color: #374151;
            font-size: 14px;
        }

        .report-meta strong {
            color: #1f2937;
        }

        .summary-card {
            border-radius: 8px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 100px;
            margin-bottom: 16px;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .summary-card-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .summary-card-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .summary-card-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .summary-card-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .summary-card-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .breakdown-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .breakdown-row:last-child {
            border-bottom: none;
        }

        .method-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-bank_transfer { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-mobile_money { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .balance-flow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .balance-flow-item {
            text-align: center;
            flex: 1;
        }

        .balance-flow-item .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e40af;
        }

        .balance-flow-item .label {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }

        .balance-flow-arrow {
            font-size: 24px;
            color: #9ca3af;
            padding: 0 16px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .fee-container {
                box-shadow: none;
            }

            .fee-header {
                background: #4e73df !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-card {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 768px) {
            .summary-card {
                margin-bottom: 16px;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .balance-flow {
                flex-direction: column;
            }

            .balance-flow-arrow {
                transform: rotate(90deg);
                padding: 8px 0;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.reports.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            End of Day Report
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">End of Day Report</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">{{ \Carbon\Carbon::parse($report['date'])->format('l, d F Y') }}</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $report['summary']['payment_count_today'] ?? 0 }}</h4>
                                <small class="opacity-75">Payments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($report['summary']['collected_today'] ?? 0, 0) }}</h4>
                                <small class="opacity-75">Collected</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($report['summary']['closing_balance'] ?? 0, 0) }}</h4>
                                <small class="opacity-75">Closing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <!-- Report Metadata -->
            <div class="report-meta">
                <p><strong>Generated:</strong> {{ $report['generated_at'] }} @if($report['generated_by']) by {{ $report['generated_by'] }} @endif</p>
                @if(isset($filters['year']) && $filters['year'])
                    <p><strong>Year:</strong> {{ $filters['year'] }}</p>
                @endif
            </div>

            <!-- Filters (no-print) -->
            <form method="GET" action="{{ route('fees.reports.end-of-day') }}" id="filterForm" class="no-print">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <input type="date" name="date" class="form-control" value="{{ $filters['date'] }}">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select name="year" class="form-select">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.end-of-day') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <button type="button" onclick="window.print()" class="btn btn-light me-2">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <a href="{{ route('fees.reports.end-of-day.pdf', $filters) }}" class="btn btn-success">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            </form>

            <!-- Balance Flow Summary -->
            <h5 class="section-title"><i class="fas fa-exchange-alt me-2"></i>Daily Balance Summary</h5>
            <div class="balance-flow">
                <div class="balance-flow-item">
                    <div class="value">{{ format_currency($report['summary']['opening_balance']) }}</div>
                    <div class="label">Opening Balance</div>
                </div>
                <div class="balance-flow-arrow">+</div>
                <div class="balance-flow-item">
                    <div class="value">{{ format_currency($report['summary']['invoiced_today']) }}</div>
                    <div class="label">Invoiced Today ({{ $report['summary']['invoice_count_today'] }})</div>
                </div>
                <div class="balance-flow-arrow">-</div>
                <div class="balance-flow-item">
                    <div class="value" style="color: #059669;">{{ format_currency($report['summary']['collected_today']) }}</div>
                    <div class="label">Collected Today ({{ $report['summary']['payment_count_today'] }})</div>
                </div>
                <div class="balance-flow-arrow">=</div>
                <div class="balance-flow-item">
                    <div class="value" style="color: #dc2626;">{{ format_currency($report['summary']['closing_balance']) }}</div>
                    <div class="label">Closing Balance</div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card summary-card-primary">
                        <div class="summary-value">{{ format_currency($report['summary']['opening_balance']) }}</div>
                        <div class="summary-label">Opening Outstanding</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card summary-card-warning">
                        <div class="summary-value">{{ format_currency($report['summary']['invoiced_today']) }}</div>
                        <div class="summary-label">Invoiced Today</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card summary-card-success">
                        <div class="summary-value">{{ format_currency($report['summary']['collected_today']) }}</div>
                        <div class="summary-label">Collected Today</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card summary-card-danger">
                        <div class="summary-value">{{ format_currency($report['summary']['closing_balance']) }}</div>
                        <div class="summary-label">Closing Outstanding</div>
                    </div>
                </div>
            </div>

            <!-- Breakdowns -->
            <div class="row">
                <div class="col-md-6">
                    <div class="breakdown-section">
                        <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Collections by Payment Method</h6>
                        @forelse($report['by_method'] as $method => $info)
                            <div class="breakdown-row">
                                <span>
                                    <span class="method-badge method-{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                                </span>
                                <span><strong>{{ format_currency($info['total']) }}</strong> ({{ $info['count'] }} payments)</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments recorded</p>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="breakdown-section">
                        <h6 class="mb-3"><i class="fas fa-user me-2"></i>Collections by Collector</h6>
                        @forelse($report['by_collector'] as $collector)
                            <div class="breakdown-row">
                                <span>{{ $collector['name'] }}</span>
                                <span><strong>{{ format_currency($collector['total']) }}</strong> ({{ $collector['count'] }} payments)</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments recorded</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <h5 class="section-title"><i class="fas fa-list me-2"></i>Payment Details</h5>
            @if(empty($report['payments']))
                <div class="empty-state">
                    <i class="fas fa-receipt d-block"></i>
                    <p class="mt-3 mb-0">No payments recorded for this date</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Receipt #</th>
                                <th>Student</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['payments'] as $payment)
                                <tr>
                                    <td>{{ $payment['created_at'] }}</td>
                                    <td><code>{{ $payment['receipt_number'] }}</code></td>
                                    <td>
                                        {{ $payment['student_name'] }}
                                        <br><small class="text-muted">{{ $payment['student_number'] }}</small>
                                    </td>
                                    <td class="text-end amount-cell">{{ format_currency($payment['amount']) }}</td>
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
                            <tr class="table-light">
                                <td colspan="3"><strong>Total</strong></td>
                                <td class="text-end"><strong>{{ format_currency($report['summary']['collected_today']) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
        });

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }
    </script>
@endsection

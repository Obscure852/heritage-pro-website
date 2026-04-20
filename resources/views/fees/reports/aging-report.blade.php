@extends('layouts.master')
@section('title')
    Aging Report
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

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Aging bucket cards */
        .aging-card {
            border-radius: 8px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }

        .aging-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .aging-card-current {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .aging-card-30 {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .aging-card-60 {
            background: linear-gradient(135deg, #fd7e14 0%, #dc6b0f 100%);
        }

        .aging-card-90 {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .aging-title {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .aging-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .aging-count {
            font-size: 0.9rem;
            opacity: 0.9;
        }

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

        .balance-cell {
            font-weight: 600;
            color: #dc2626;
        }

        .grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-current { background: #d1fae5; color: #065f46; }
        .status-overdue-30 { background: #fef3c7; color: #92400e; }
        .status-overdue-60 { background: #fed7aa; color: #9a3412; }
        .status-overdue-90 { background: #fee2e2; color: #991b1b; }

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

        .chart-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .chart-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .chart-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .chart-card-body {
            padding: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
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

        @media (max-width: 768px) {
            .aging-card {
                margin-bottom: 16px;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
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
            Aging Report
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
                    <h3 style="margin:0;">Aging Report</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Outstanding invoices categorized by age from due date</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalOutstanding = ($agingSummary['current']['total_amount'] ?? 0) +
                                           ($agingSummary['30_days']['total_amount'] ?? 0) +
                                           ($agingSummary['60_days']['total_amount'] ?? 0) +
                                           ($agingSummary['90_days']['total_amount'] ?? 0);
                        $totalInvoices = ($agingSummary['current']['count'] ?? 0) +
                                        ($agingSummary['30_days']['count'] ?? 0) +
                                        ($agingSummary['60_days']['count'] ?? 0) +
                                        ($agingSummary['90_days']['count'] ?? 0);
                    @endphp
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalInvoices }}</h4>
                                <small class="opacity-75">Total Invoices</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totalOutstanding, 0) }}</h4>
                                <small class="opacity-75">Total Outstanding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Aging Report</div>
                <div class="help-content">
                    Analyze outstanding invoices by how long they have been overdue. Invoices are categorized into aging buckets: Current (0-30 days), 31-60 days, 61-90 days, and 90+ days overdue. Focus collection efforts on the oldest debts first.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.aging-report') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select class="form-select" name="year">
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
                                        <a href="{{ route('fees.reports.aging-report') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.export.aging-report', request()->query()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            <!-- Aging Bucket Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="aging-card aging-card-current">
                        <div class="aging-title">Current (0-30 days)</div>
                        <div class="aging-value">{{ format_currency($agingSummary['current']['total_amount'] ?? 0) }}</div>
                        <div class="aging-count">{{ $agingSummary['current']['count'] ?? 0 }} invoices</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="aging-card aging-card-30">
                        <div class="aging-title">31-60 Days</div>
                        <div class="aging-value">{{ format_currency($agingSummary['30_days']['total_amount'] ?? 0) }}</div>
                        <div class="aging-count">{{ $agingSummary['30_days']['count'] ?? 0 }} invoices</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="aging-card aging-card-60">
                        <div class="aging-title">61-90 Days</div>
                        <div class="aging-value">{{ format_currency($agingSummary['60_days']['total_amount'] ?? 0) }}</div>
                        <div class="aging-count">{{ $agingSummary['60_days']['count'] ?? 0 }} invoices</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="aging-card aging-card-90">
                        <div class="aging-title">90+ Days</div>
                        <div class="aging-value">{{ format_currency($agingSummary['90_days']['total_amount'] ?? 0) }}</div>
                        <div class="aging-count">{{ $agingSummary['90_days']['count'] ?? 0 }} invoices</div>
                    </div>
                </div>
            </div>

            <!-- Aging Chart -->
            <div class="chart-card mb-4">
                <div class="chart-card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Aging Buckets Distribution</h5>
                </div>
                <div class="chart-card-body">
                    <div id="agingChart"></div>
                </div>
            </div>

            <!-- Details Table -->
            <h5 class="section-title"><i class="fas fa-list me-2"></i>Overdue Invoice Details</h5>
            @if (count($agingDetails ?? []) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Grade</th>
                                <th>Invoice #</th>
                                <th>Due Date</th>
                                <th class="text-center">Days Overdue</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agingDetails as $invoice)
                                @php
                                    $daysOverdue = $invoice['days_overdue'] ?? 0;
                                    $statusClass = match(true) {
                                        $daysOverdue <= 30 => 'status-current',
                                        $daysOverdue <= 60 => 'status-overdue-30',
                                        $daysOverdue <= 90 => 'status-overdue-60',
                                        default => 'status-overdue-90'
                                    };
                                    $statusText = match(true) {
                                        $daysOverdue <= 30 => 'Current',
                                        $daysOverdue <= 60 => '31-60 days',
                                        $daysOverdue <= 90 => '61-90 days',
                                        default => '90+ days'
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $invoice['student_name'] ?? 'N/A' }}</td>
                                    <td><span class="grade-badge">{{ $invoice['grade_name'] ?? 'N/A' }}</span></td>
                                    <td>{{ $invoice['invoice_number'] ?? 'N/A' }}</td>
                                    <td>{{ isset($invoice['due_date']) ? \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') : 'N/A' }}</td>
                                    <td class="text-center">{{ $daysOverdue }}</td>
                                    <td class="text-end balance-cell">{{ format_currency($invoice['balance'] ?? 0) }}</td>
                                    <td class="text-center">
                                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            @if(isset($invoice['invoice_id']))
                                                <a href="{{ route('fees.collection.invoices.show', $invoice['invoice_id']) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View Invoice">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            @endif
                                            @if(isset($invoice['student_id']))
                                                <a href="{{ route('fees.collection.students.account', ['student' => $invoice['student_id']]) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="View Account">
                                                    <i class="bx bx-user"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-check-circle d-block" style="color: #10b981;"></i>
                    <p class="mt-3 mb-0">No Overdue Invoices</p>
                    <p class="text-muted" style="font-size: 13px;">All invoices are current or paid</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var currencySymbol = @json(get_currency_symbol());

        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeCharts();
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

        function initializeCharts() {
            var agingSummary = @json($agingSummary ?? []);

            var buckets = ['Current (0-30)', '31-60 Days', '61-90 Days', '90+ Days'];
            var amounts = [
                parseFloat(agingSummary.current?.total_amount || 0),
                parseFloat(agingSummary['30_days']?.total_amount || 0),
                parseFloat(agingSummary['60_days']?.total_amount || 0),
                parseFloat(agingSummary['90_days']?.total_amount || 0)
            ];

            if (amounts.some(function(a) { return a > 0; })) {
                var chartOptions = {
                    series: [{
                        name: 'Outstanding Amount',
                        data: amounts
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            distributed: true,
                            barHeight: '60%'
                        }
                    },
                    colors: ['#1cc88a', '#f6c23e', '#fd7e14', '#e74a3b'],
                    xaxis: {
                        categories: buckets,
                        labels: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString();
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return currencySymbol + ' ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#agingChart"), chartOptions);
                chart.render();
            } else {
                document.querySelector("#agingChart").innerHTML = '<div class="empty-state"><i class="fas fa-chart-bar d-block"></i><p>No aging data available</p></div>';
            }
        }
    </script>
@endsection

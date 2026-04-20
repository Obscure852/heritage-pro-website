@extends('layouts.master')
@section('title')
    Collector Performance
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

        .collector-name {
            font-weight: 600;
            color: #374151;
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

        .method-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

        .method-breakdown {
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            margin-top: 8px;
        }

        .method-breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 12px;
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .performance-card {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 8px;
            padding: 20px;
            color: white;
            margin-bottom: 24px;
        }

        .performance-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .performance-card .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
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
            Collector Performance
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
                    <h3 style="margin:0;">Collector Performance</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Fee collection performance by staff member</p>
                </div>
                <div class="col-md-6">
                    @php
                        $headerTotalCollected = collect($collectors ?? [])->sum(function($c) { return (float)$c['total_collected']; });
                        $headerTotalPayments = collect($collectors ?? [])->sum('payment_count');
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ count($collectors ?? []) }}</h4>
                                <small class="opacity-75">Collectors</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $headerTotalPayments }}</h4>
                                <small class="opacity-75">Payments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($headerTotalCollected, 0) }}</h4>
                                <small class="opacity-75">Collected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Collector Performance Report</div>
                <div class="help-content">
                    Track fee collection performance by individual staff members. View total amounts collected, payment counts, and average payment amounts for each collector. Filter by year and date range to analyze specific periods.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.collector-performance') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="year">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="start_date" value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="end_date" value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                                </div>
                                <div class="col-lg-4 col-md-3 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.collector-performance') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.export.collector-performance', request()->query()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            @if (count($collectors ?? []) > 0)
                @php
                    $totalCollected = collect($collectors)->sum(function($c) { return (float)$c['total_collected']; });
                    $totalPayments = collect($collectors)->sum('payment_count');
                @endphp

                <!-- Summary Card -->
                <div class="performance-card">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-value">{{ count($collectors) }}</div>
                            <div class="stat-label">Active Collectors</div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-value">{{ format_currency($totalCollected, 0) }}</div>
                            <div class="stat-label">Total Collected</div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-value">{{ $totalPayments }}</div>
                            <div class="stat-label">Total Payments</div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="chart-card mb-4">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i>Collection by Collector</h5>
                    </div>
                    <div class="chart-card-body">
                        <div id="collectorsChart"></div>
                    </div>
                </div>

                <!-- Details Table -->
                <h5 class="section-title"><i class="fas fa-users me-2"></i>Collector Details</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Collector Name</th>
                                <th class="text-center">Payment Count</th>
                                <th class="text-end">Total Collected</th>
                                <th class="text-end">Average Payment</th>
                                <th>Method Breakdown</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($collectors as $collector)
                                <tr>
                                    <td class="collector-name">{{ $collector['collector_name'] }}</td>
                                    <td class="text-center">{{ $collector['payment_count'] }}</td>
                                    <td class="text-end amount-cell">{{ format_currency($collector['total_collected']) }}</td>
                                    <td class="text-end">{{ format_currency($collector['average_payment']) }}</td>
                                    <td>
                                        @if (!empty($collector['by_method']))
                                            <div class="method-breakdown">
                                                @foreach ($collector['by_method'] as $method)
                                                    @php
                                                        $methodName = $method['payment_method'] ?? 'unknown';
                                                        $methodClass = match($methodName) {
                                                            'cash' => 'method-cash',
                                                            'bank_transfer' => 'method-bank',
                                                            'mobile_money' => 'method-mobile',
                                                            'cheque' => 'method-cheque',
                                                            default => 'method-cash'
                                                        };
                                                    @endphp
                                                    <div class="method-breakdown-item">
                                                        <span class="method-badge {{ $methodClass }}">
                                                            {{ ucfirst(str_replace('_', ' ', $methodName)) }}
                                                        </span>
                                                        <span>{{ format_currency($method['total_amount'] ?? 0) }} ({{ $method['count'] ?? 0 }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-user-clock d-block"></i>
                    <p class="mt-3 mb-0">No Collection Data</p>
                    <p class="text-muted" style="font-size: 13px;">No payments recorded for the selected period</p>
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
            var collectorsData = @json($collectors ?? []);

            if (collectorsData.length > 0) {
                var collectorNames = collectorsData.map(function(c) { return c.collector_name; });
                var collectorAmounts = collectorsData.map(function(c) { return parseFloat(c.total_collected); });

                var chartOptions = {
                    series: [{
                        name: 'Amount Collected',
                        data: collectorAmounts
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
                            horizontal: false,
                            columnWidth: '60%'
                        }
                    },
                    colors: ['#4e73df'],
                    xaxis: {
                        categories: collectorNames,
                        labels: {
                            style: {
                                fontSize: '12px'
                            },
                            rotate: -45,
                            rotateAlways: collectorNames.length > 5
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString();
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#collectorsChart"), chartOptions);
                chart.render();
            }
        }
    </script>
@endsection

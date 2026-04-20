@extends('layouts.master')
@section('title')
    Collection Summary
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

        .stat-card {
            border-radius: 8px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 100px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .stat-card-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .stat-card-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .stat-card-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .method-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

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

        @media (max-width: 768px) {
            .stat-card {
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
            Collection Summary
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
            <h3 style="margin:0;">Collection Summary</h3>
            <p style="margin:6px 0 0 0; opacity:.9;">Summary of fee collections by period and payment method</p>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Collection Summary Report</div>
                <div class="help-content">
                    View fee collection statistics for a specific date range. Filter by year and date range to analyze collection trends and payment method distribution.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.collection-summary') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="year">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="start_date" value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="end_date" value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.collection-summary') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.export.collection-summary', request()->query()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            <!-- Summary Stats -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-value">{{ format_currency($summary['total_collected'] ?? 0) }}</div>
                        <div class="stat-label">Total Collected</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="stat-card stat-card-success">
                        <div class="stat-value">{{ $summary['payment_count'] ?? 0 }}</div>
                        <div class="stat-label">Payment Count</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="stat-card stat-card-info">
                        <div class="stat-value">{{ format_currency($summary['average_payment'] ?? 0) }}</div>
                        <div class="stat-label">Average Payment</div>
                    </div>
                </div>
            </div>

            <!-- Collections by Method -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h5><i class="fas fa-chart-pie me-2"></i>Collections by Payment Method</h5>
                        </div>
                        <div class="chart-card-body">
                            @if (count($collectionsByMethod ?? []) > 0)
                                <div id="methodsChart"></div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-chart-pie d-block"></i>
                                    <p class="mb-0">No collection data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h5><i class="fas fa-list me-2"></i>Breakdown by Method</h5>
                        </div>
                        <div class="chart-card-body p-0">
                            @if (count($collectionsByMethod ?? []) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Payment Method</th>
                                                <th class="text-center">Count</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($collectionsByMethod as $method)
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
                                                <tr>
                                                    <td>
                                                        <span class="method-badge {{ $methodClass }}">
                                                            {{ ucfirst(str_replace('_', ' ', $methodName)) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">{{ $method['payment_count'] ?? 0 }}</td>
                                                    <td class="text-end amount-cell">{{ format_currency($method['total_amount'] ?? 0) }}</td>
                                                    <td class="text-end">{{ number_format((float)($method['percentage'] ?? 0), 1) }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-table d-block"></i>
                                    <p class="mb-0">No collection data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown -->
            <h5 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Daily Breakdown</h5>
            @if (count($dailyBreakdown ?? []) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Payment Count</th>
                                <th class="text-end">Amount Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyBreakdown as $day)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                                    <td class="text-center">{{ $day['payment_count'] ?? 0 }}</td>
                                    <td class="text-end amount-cell">{{ format_currency($day['total_amount'] ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-times d-block"></i>
                    <p class="mt-3 mb-0">No daily collection data available</p>
                    <p class="text-muted" style="font-size: 13px;">Select a date range to view daily breakdown</p>
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
            var methodsData = @json($collectionsByMethod ?? []);

            if (methodsData.length > 0) {
                var methodLabels = methodsData.map(function(d) {
                    var method = d.payment_method || 'unknown';
                    return method.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                });
                var methodValues = methodsData.map(function(d) { return parseFloat(d.total_amount); });

                var methodsOptions = {
                    series: methodValues,
                    chart: {
                        type: 'pie',
                        height: 280
                    },
                    labels: methodLabels,
                    colors: ['#1cc88a', '#4e73df', '#9b59b6', '#f6c23e'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                };

                var methodsChart = new ApexCharts(document.querySelector("#methodsChart"), methodsOptions);
                methodsChart.render();
            }
        }
    </script>
@endsection

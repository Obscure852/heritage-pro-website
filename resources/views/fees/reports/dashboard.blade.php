@extends('layouts.master')
@section('title')
    Fee Dashboard
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

        /* Stat cards */
        .stat-card {
            border-radius: 8px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .stat-card-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .stat-card-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .stat-card-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .stat-card-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Chart containers */
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

        /* Lists */
        .data-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .data-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .data-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .data-card-body {
            padding: 0;
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

        .balance-cell {
            font-weight: 600;
            color: #dc2626;
        }

        .student-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .student-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .method-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .method-cash {
            background: #d1fae5;
            color: #065f46;
        }

        .method-bank {
            background: #dbeafe;
            color: #1e40af;
        }

        .method-mobile {
            background: #ede9fe;
            color: #5b21b6;
        }

        .method-cheque {
            background: #ffedd5;
            color: #9a3412;
        }

        .term-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .view-all-link {
            font-size: 13px;
            color: #3b82f6;
            text-decoration: none;
        }

        .view-all-link:hover {
            text-decoration: underline;
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

        /* Term Select */
        .term-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .term-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Reports Dropdown Styling */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 280px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        .reports-dropdown .dropdown-divider {
            margin: 8px 0;
        }

        .reports-dropdown .dropdown-header {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 16px 4px 16px;
        }

        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 16px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .reports-dropdown .dropdown-menu {
                right: auto;
                left: 0;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Fee Administration
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

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-9"></div>
        <div class="col-3 d-flex justify-content-end">
            <form method="GET" action="{{ route('fees.reports.dashboard') }}">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach ($years ?? [] as $year)
                        <option value="{{ $year }}"
                            {{ ($selectedYear ?? '') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Fee Dashboard</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Overview of fee collection and outstanding balances</p>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Dashboard Overview</div>
                <div class="help-content">
                    View key fee metrics at a glance. Use the year filter to see data for a specific year or view all-time
                    totals.
                    Click the Reports dropdown to access detailed reports.
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <div class="btn-group reports-dropdown">
                        <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-alt me-2"></i>Financial Reports<i class="fas fa-chevron-down ms-2"
                                style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">Collection Reports</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.collection-summary') }}">
                                    <i class="fas fa-chart-bar"></i> Collection Summary</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.daily-collections') }}">
                                    <i class="fas fa-calendar-day"></i> Daily Collections</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.end-of-day') }}">
                                    <i class="fas fa-clock"></i> End-of-Day Report</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.payment-trends') }}">
                                    <i class="fas fa-chart-line"></i> Payment Trends</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <h6 class="dropdown-header">Outstanding Reports</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.outstanding-by-grade') }}">
                                    <i class="fas fa-layer-group"></i> Outstanding by Grade</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.aging-report') }}">
                                    <i class="fas fa-hourglass-half"></i> Aging Report</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.debtors-list') }}">
                                    <i class="fas fa-user-clock"></i> Debtors List</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <h6 class="dropdown-header">Other Reports</h6>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.collector-performance') }}">
                                    <i class="fas fa-user-tie"></i> Collector Performance</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('fees.reports.student-statement') }}">
                                    <i class="fas fa-file-invoice"></i> Student Statement</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="stat-value">{{ format_currency($stats['total_invoiced'] ?? 0) }}</div>
                        <div class="stat-label">Total Invoiced</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <div class="stat-card stat-card-success">
                        <div class="stat-icon"><i class="fas fa-coins"></i></div>
                        <div class="stat-value">{{ format_currency($stats['total_collected'] ?? 0) }}</div>
                        <div class="stat-label">Total Collected</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <div class="stat-card stat-card-danger">
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="stat-value">{{ format_currency($stats['total_outstanding'] ?? 0) }}</div>
                        <div class="stat-label">Outstanding</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card stat-card-info">
                        <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                        <div class="stat-value">{{ number_format((float) ($stats['collection_rate'] ?? 0), 1) }}%</div>
                        <div class="stat-label">Collection Rate</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Payment Trends</h5>
                        </div>
                        <div class="chart-card-body">
                            <div id="paymentTrendsChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h5><i class="fas fa-chart-pie me-2"></i>Collections by Method</h5>
                        </div>
                        <div class="chart-card-body">
                            <div id="collectionsByMethodChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments and Top Debtors -->
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="data-card">
                        <div class="data-card-header">
                            <h5><i class="fas fa-clock me-2"></i>Recent Payments</h5>
                            <a href="{{ route('fees.collection.invoices.index') }}" class="view-all-link">View All</a>
                        </div>
                        <div class="data-card-body">
                            @if (count($recentPayments ?? []) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Receipt #</th>
                                                <th>Student</th>
                                                <th class="text-end">Amount</th>
                                                <th>Method</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentPayments as $payment)
                                                <tr>
                                                    <td>{{ $payment['receipt_number'] }}</td>
                                                    <td>{{ $payment['student_name'] }}</td>
                                                    <td class="text-end amount-cell">{{ format_currency($payment['amount']) }}</td>
                                                    <td>
                                                        @php
                                                            $methodClass = match ($payment['payment_method']) {
                                                                'cash' => 'method-cash',
                                                                'bank_transfer' => 'method-bank',
                                                                'mobile_money' => 'method-mobile',
                                                                'cheque' => 'method-cheque',
                                                                default => 'method-cash',
                                                            };
                                                        @endphp
                                                        <span
                                                            class="method-badge {{ $methodClass }}">{{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}</span>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d M') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-receipt d-block"></i>
                                    <p class="mb-0">No recent payments</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="data-card">
                        <div class="data-card-header">
                            <h5><i class="fas fa-user-clock me-2"></i>Top Debtors</h5>
                            <a href="{{ route('fees.reports.debtors-list') }}" class="view-all-link">View All</a>
                        </div>
                        <div class="data-card-body">
                            @if (count($topDebtors ?? []) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Grade</th>
                                                <th class="text-end">Balance</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topDebtors as $debtor)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('fees.collection.students.account', ['student' => $debtor['student_id']]) }}"
                                                            class="student-link">
                                                            {{ $debtor['student_name'] }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $debtor['grade_name'] ?? 'N/A' }}</td>
                                                    <td class="text-end balance-cell">{{ format_currency($debtor['balance']) }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('fees.collection.students.account', ['student' => $debtor['student_id']]) }}"
                                                            class="btn btn-sm btn-outline-primary" title="View Account">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-check-circle d-block" style="color: #10b981;"></i>
                                    <p class="mb-0">No outstanding balances</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Currency symbol for JavaScript formatting
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
            // Payment Trends Chart Data
            var trendsData = @json($paymentTrends ?? []);

            if (trendsData.length > 0) {
                var trendsOptions = {
                    series: [{
                        name: 'Collections',
                        data: trendsData.map(function(d) {
                            return parseFloat(d.total_amount);
                        })
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    xaxis: {
                        categories: trendsData.map(function(d) {
                            return d.period;
                        }),
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString();
                            }
                        }
                    },
                    colors: ['#4e73df'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.2,
                            stops: [0, 90, 100]
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                };

                var trendsChart = new ApexCharts(document.querySelector("#paymentTrendsChart"), trendsOptions);
                trendsChart.render();
            } else {
                document.querySelector("#paymentTrendsChart").innerHTML =
                    '<div class="empty-state"><i class="fas fa-chart-line d-block"></i><p>No payment data available</p></div>';
            }

            // Collections by Method Chart Data
            var methodsData = @json($collectionsByMethod ?? []);

            if (methodsData.length > 0) {
                var methodLabels = methodsData.map(function(d) {
                    var method = d.payment_method || 'unknown';
                    return method.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                        return l.toUpperCase();
                    });
                });
                var methodValues = methodsData.map(function(d) {
                    return parseFloat(d.total_amount);
                });

                var methodsOptions = {
                    series: methodValues,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: methodLabels,
                    colors: ['#1cc88a', '#4e73df', '#9b59b6', '#f6c23e'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 280
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                var methodsChart = new ApexCharts(document.querySelector("#collectionsByMethodChart"), methodsOptions);
                methodsChart.render();
            } else {
                document.querySelector("#collectionsByMethodChart").innerHTML =
                    '<div class="empty-state"><i class="fas fa-chart-pie d-block"></i><p>No payment method data</p></div>';
            }
        }
    </script>
@endsection

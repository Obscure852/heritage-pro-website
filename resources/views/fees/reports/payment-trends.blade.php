@extends('layouts.master')
@section('title')
    Payment Trends
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
            Payment Trends
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
                    <h3 style="margin:0;">Payment Trends</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Analyze fee collection patterns over time</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($summary['total_collected'] ?? 0, 0) }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($summary['highest_day'] ?? 0, 0) }}</h4>
                                <small class="opacity-75">Highest Day</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($summary['average_daily'] ?? 0, 0) }}</h4>
                                <small class="opacity-75">Avg Daily</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Payment Trends Report</div>
                <div class="help-content">
                    Visualize payment collection patterns over time. Use the group by filter to view trends by day, week, or month. Compare collection performance across different grades to identify areas needing attention.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.payment-trends') }}" id="filterForm">
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
                                    <select class="form-select" name="group_by">
                                        <option value="day" {{ ($filters['group_by'] ?? 'day') == 'day' ? 'selected' : '' }}>Day</option>
                                        <option value="week" {{ ($filters['group_by'] ?? '') == 'week' ? 'selected' : '' }}>Week</option>
                                        <option value="month" {{ ($filters['group_by'] ?? '') == 'month' ? 'selected' : '' }}>Month</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="start_date" value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" class="form-control" name="end_date" value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.payment-trends') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.payment-trends', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            <!-- Payment Trends Chart -->
            <div class="chart-card mb-4">
                <div class="chart-card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Payment Trends</h5>
                </div>
                <div class="chart-card-body">
                    @if (count($paymentTrends ?? []) > 0)
                        <div id="trendsChart"></div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-chart-line d-block"></i>
                            <p class="mb-0">No payment data available for the selected period</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grade Comparison Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Collection by Grade</h5>
                </div>
                <div class="chart-card-body">
                    @if (count($gradeComparison ?? []) > 0)
                        <div id="gradeChart"></div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-chart-bar d-block"></i>
                            <p class="mb-0">No grade comparison data available</p>
                        </div>
                    @endif
                </div>
            </div>
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
            // Payment Trends Chart
            var trendsData = @json($paymentTrends ?? []);

            if (trendsData.length > 0) {
                var trendsOptions = {
                    series: [{
                        name: 'Collections',
                        data: trendsData.map(function(d) { return parseFloat(d.total_amount); })
                    }],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: false,
                                reset: true
                            }
                        }
                    },
                    xaxis: {
                        categories: trendsData.map(function(d) { return d.period; }),
                        labels: {
                            rotate: -45,
                            rotateAlways: trendsData.length > 10,
                            style: {
                                fontSize: '11px'
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
                        width: 3
                    },
                    markers: {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return currencySymbol + ' ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    }
                };

                var trendsChart = new ApexCharts(document.querySelector("#trendsChart"), trendsOptions);
                trendsChart.render();
            }

            // Grade Comparison Chart
            var gradeData = @json($gradeComparison ?? []);

            if (gradeData.length > 0) {
                var gradeOptions = {
                    series: [{
                        name: 'Collections',
                        data: gradeData.map(function(g) { return parseFloat(g.total_collected); })
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
                            columnWidth: '60%',
                            distributed: true
                        }
                    },
                    colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#6610f2'],
                    xaxis: {
                        categories: gradeData.map(function(g) { return g.grade_name; }),
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
                    dataLabels: {
                        enabled: false
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

                var gradeChart = new ApexCharts(document.querySelector("#gradeChart"), gradeOptions);
                gradeChart.render();
            }
        }
    </script>
@endsection

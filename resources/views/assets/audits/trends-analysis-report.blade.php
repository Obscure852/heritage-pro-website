@extends('layouts.master')
@section('title')
    Audit Trends Analysis Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#" onclick="event.preventDefault(); 
                if (document.referrer) {
                history.back();
                } else {
                window.location = '{{ route('audits.index') }}';
                }   
            ">Back</a>
        @endslot
        @slot('title')
            Audit Trends Analysis
        @endslot
    @endcomponent
    
    <style>
        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 12px;
        }

        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-stable { color: #6c757d; }
        
        .performance-card {
            transition: transform 0.2s;
        }
        
        .performance-card:hover {
            transform: translateY(-2px);
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }

            .card {
                box-shadow: none;
            }
        }
    </style>
    
    <!-- Filter Controls -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-filter-alt me-2"></i>Analysis Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('audits.trend-analysis') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" 
                                       value="{{ $startDate }}" onchange="submitForm()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" 
                                       value="{{ $endDate }}" onchange="submitForm()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category Filter</label>
                                <select class="form-select form-select-sm" name="category_id" onchange="submitForm()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Location Filter</label>
                                <select class="form-select form-select-sm" name="venue_id" onchange="submitForm()">
                                    <option value="">All Locations</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Controls -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <!-- Main Report Card -->
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="border border-primary rounded p-3  bg-primary">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="text-white mb-3">
                                            Audit Trend Analysis Overview
                                        </h4>
                                        <p class="text-white mb-1">Analysis Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
                                        <p class="text-white mb-1">Duration: {{ $trendAnalysis['analysis_period'] ?? 'N/A' }}</p>
                                        <p class="text-white mb-0">Report Generated: {{ now()->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h2 class="text-white mb-1">{{ $trendAnalysis['total_audits'] ?? 0 }}</h2>
                                        <p class="text-white mb-0">Total Audits Analyzed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($trendAnalysis['time_series_data']) && count($trendAnalysis['time_series_data']) > 0)
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="border border-secondary rounded p-3 text-center performance-card">
                                <h6 class="text-muted">Asset Recovery Trend</h6>
                                <h4 class="mb-1 trend-{{ $trendAnalysis['performance_trends']['asset_recovery_trend'] === 'improving' ? 'up' : ($trendAnalysis['performance_trends']['asset_recovery_trend'] === 'declining' ? 'down' : 'stable') }}">
                                    {{ ucfirst($trendAnalysis['performance_trends']['asset_recovery_trend']) }}
                                </h4>
                                <p class="text-muted mb-0 small">Overall Performance</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border border-secondary rounded p-3 text-center performance-card">
                                <h6 class="text-muted">Missing Assets Trend</h6>
                                <h4 class="mb-1 trend-{{ $trendAnalysis['performance_trends']['missing_assets_trend'] === 'improving' ? 'up' : ($trendAnalysis['performance_trends']['missing_assets_trend'] === 'declining' ? 'down' : 'stable') }}">
                                    {{ ucfirst($trendAnalysis['performance_trends']['missing_assets_trend']) }}
                                </h4>
                                <p class="text-muted mb-0 small">Asset Security</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border border-secondary rounded p-3 text-center performance-card">
                                <h6 class="text-muted">Maintenance Trend</h6>
                                <h4 class="mb-1 trend-{{ $trendAnalysis['performance_trends']['maintenance_trend'] === 'improving' ? 'up' : ($trendAnalysis['performance_trends']['maintenance_trend'] === 'declining' ? 'down' : 'stable') }}">
                                    {{ ucfirst($trendAnalysis['performance_trends']['maintenance_trend']) }}
                                </h4>
                                <p class="text-muted mb-0 small">Asset Health</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border border-secondary rounded p-3 text-center performance-card">
                                <h6 class="text-muted">Financial Health</h6>
                                <h4 class="mb-1 trend-{{ $trendAnalysis['performance_trends']['financial_health_trend'] === 'improving' ? 'up' : ($trendAnalysis['performance_trends']['financial_health_trend'] === 'declining' ? 'down' : 'stable') }}">
                                    {{ ucfirst($trendAnalysis['performance_trends']['financial_health_trend']) }}
                                </h4>
                                <p class="text-muted mb-0 small">Value Protection</p>
                            </div>
                        </div>
                    </div>

                    <!-- Trend Metrics Summary -->
                    @if(isset($trendAnalysis['trend_metrics']) && !isset($trendAnalysis['trend_metrics']['trend']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="bx bx-line-chart me-2"></i>Trend Metrics Summary
                            </h5>
                            <div class="border border-secondary rounded p-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="mb-1 {{ $trendAnalysis['trend_metrics']['assets_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $trendAnalysis['trend_metrics']['assets_change'] >= 0 ? '+' : '' }}{{ $trendAnalysis['trend_metrics']['assets_change'] }}
                                            </h4>
                                            <p class="text-muted mb-0">Assets Change</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="mb-1 {{ $trendAnalysis['trend_metrics']['recovery_rate_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $trendAnalysis['trend_metrics']['recovery_rate_change'] >= 0 ? '+' : '' }}{{ $trendAnalysis['trend_metrics']['recovery_rate_change'] }}%
                                            </h4>
                                            <p class="text-muted mb-0">Recovery Rate Change</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="mb-1 {{ $trendAnalysis['trend_metrics']['missing_rate_change'] <= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $trendAnalysis['trend_metrics']['missing_rate_change'] >= 0 ? '+' : '' }}{{ $trendAnalysis['trend_metrics']['missing_rate_change'] }}%
                                            </h4>
                                            <p class="text-muted mb-0">Missing Rate Change</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="mb-1 {{ $trendAnalysis['trend_metrics']['financial_value_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                P {{ number_format($trendAnalysis['trend_metrics']['financial_value_change'], 0) }}
                                            </h4>
                                            <p class="text-muted mb-0">Value Change</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Time Series Performance Chart Data -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                Performance Over Time
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Audit Date</th>
                                            <th>Audit Code</th>
                                            <th>Total Assets</th>
                                            <th>Present</th>
                                            <th>Missing</th>
                                            <th>Maintenance</th>
                                            <th>Recovery Rate</th>
                                            <th>Total Value</th>
                                            <th>Health Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trendAnalysis['time_series_data'] as $data)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($data['audit_date'])->format('M d, Y') }}</td>
                                                <td><strong>{{ $data['audit_code'] }}</strong></td>
                                                <td>{{ $data['total_assets'] }}</td>
                                                <td><span class="badge bg-success">{{ $data['present_assets'] }}</span></td>
                                                <td>
                                                    @if($data['missing_assets'] > 0)
                                                        <span class="badge bg-danger">{{ $data['missing_assets'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($data['maintenance_needed'] > 0)
                                                        <span class="badge bg-warning">{{ $data['maintenance_needed'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $data['present_percentage'] >= 95 ? 'success' : ($data['present_percentage'] >= 85 ? 'info' : ($data['present_percentage'] >= 75 ? 'warning' : 'danger')) }}">
                                                        {{ $data['present_percentage'] }}%
                                                    </span>
                                                </td>
                                                <td>P {{ number_format($data['financial_total_value'], 0) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $data['health_score'] >= 80 ? 'success' : ($data['health_score'] >= 60 ? 'warning' : 'danger') }}">
                                                        {{ $data['health_score'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Aggregated Data -->
                    @if(isset($trendAnalysis['monthly_data']) && count($trendAnalysis['monthly_data']) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                Monthly Performance Summary
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>Audits</th>
                                            <th>Total Assets</th>
                                            <th>Present Assets</th>
                                            <th>Missing Assets</th>
                                            <th>Maintenance Needed</th>
                                            <th>Recovery Rate</th>
                                            <th>Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trendAnalysis['monthly_data'] as $month)
                                            @php
                                                $recoveryRate = $month['total_assets'] > 0 ? round(($month['present_assets'] / $month['total_assets']) * 100, 1) : 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ \Carbon\Carbon::parse($month['month'] . '-01')->format('M Y') }}</strong></td>
                                                <td>{{ $month['audit_count'] }}</td>
                                                <td>{{ $month['total_assets'] }}</td>
                                                <td><span class="badge bg-success">{{ $month['present_assets'] }}</span></td>
                                                <td>
                                                    @if($month['missing_assets'] > 0)
                                                        <span class="badge bg-danger">{{ $month['missing_assets'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($month['maintenance_needed'] > 0)
                                                        <span class="badge bg-warning">{{ $month['maintenance_needed'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $recoveryRate >= 95 ? 'success' : ($recoveryRate >= 85 ? 'info' : ($recoveryRate >= 75 ? 'warning' : 'danger')) }}">
                                                        {{ $recoveryRate }}%
                                                    </span>
                                                </td>
                                                <td>P {{ number_format($month['total_value'], 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Category Trends -->
                    @if(isset($trendAnalysis['category_trends']) && count($trendAnalysis['category_trends']) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">
                                Category Performance Trends
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category</th>
                                            <th>Trend Direction</th>
                                            <th>Current Missing Rate</th>
                                            <th>Data Points</th>
                                            <th>Performance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trendAnalysis['category_trends'] as $category => $trend)
                                            <tr>
                                                <td><strong>{{ $category }}</strong></td>
                                                <td>
                                                    <span class="badge bg-{{ $trend['trend'] === 'improving' ? 'success' : ($trend['trend'] === 'declining' ? 'danger' : 'secondary') }}">
                                                        <i class="bx bx-trending-{{ $trend['trend'] === 'improving' ? 'up' : ($trend['trend'] === 'declining' ? 'down' : 'flat') }} me-1"></i>
                                                        {{ ucfirst($trend['trend']) }}
                                                    </span>
                                                </td>
                                                <td>{{ round($trend['current_missing_rate'], 1) }}%</td>
                                                <td>{{ $trend['data_points'] }} audits</td>
                                                <td>
                                                    @if($trend['current_missing_rate'] <= 5)
                                                        <span class="badge bg-success">Excellent</span>
                                                    @elseif($trend['current_missing_rate'] <= 10)
                                                        <span class="badge bg-info">Good</span>
                                                    @elseif($trend['current_missing_rate'] <= 20)
                                                        <span class="badge bg-warning">Fair</span>
                                                    @else
                                                        <span class="badge bg-danger">Poor</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Predictive Insights -->
                    @if(isset($trendAnalysis['predictive_insights']) && !isset($trendAnalysis['predictive_insights']['message']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="border border-info rounded p-3">
                                <h5 class="mb-3">
                                    Predictive Insights & Forecast
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Missing Assets Prediction</h6>
                                        <p class="mb-3">
                                            <span class="badge bg-{{ $trendAnalysis['predictive_insights']['missing_assets_prediction'] === 'improving' ? 'success' : ($trendAnalysis['predictive_insights']['missing_assets_prediction'] === 'declining' ? 'danger' : 'secondary') }}">
                                                {{ ucfirst($trendAnalysis['predictive_insights']['missing_assets_prediction']) }}
                                            </span>
                                            <span class="ms-2 text-muted">Based on recent trend analysis</span>
                                        </p>
                                        
                                        <h6 class="text-info">Maintenance Prediction</h6>
                                        <p class="mb-3">
                                            <span class="badge bg-{{ $trendAnalysis['predictive_insights']['maintenance_prediction'] === 'improving' ? 'success' : ($trendAnalysis['predictive_insights']['maintenance_prediction'] === 'declining' ? 'danger' : 'secondary') }}">
                                                {{ ucfirst($trendAnalysis['predictive_insights']['maintenance_prediction']) }}
                                            </span>
                                            <span class="ms-2 text-muted">Maintenance needs forecast</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info">Recommended Actions</h6>
                                        @if(isset($trendAnalysis['predictive_insights']['recommended_actions']) && count($trendAnalysis['predictive_insights']['recommended_actions']) > 0)
                                            <ul class="list-unstyled">
                                                @foreach($trendAnalysis['predictive_insights']['recommended_actions'] as $action)
                                                    <li class="mb-1">• {{ $action }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted">Continue monitoring current trends and maintain existing procedures.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Trend Analysis Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="border border-secondary rounded p-3">
                                <h5 class="mb-3">
                                    Trend Analysis Summary & Recommendations
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Performance Overview</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">• <strong>{{ $trendAnalysis['total_audits'] }}</strong> audits analyzed</li>
                                            <li class="mb-1">• Analysis period: <strong>{{ $trendAnalysis['analysis_period'] ?? 'N/A' }}</strong></li>
                                            <li class="mb-1">• Overall trend: 
                                                @if(isset($trendAnalysis['trend_metrics']['overall_trend']))
                                                    <span class="badge bg-{{ $trendAnalysis['trend_metrics']['overall_trend'] === 'improving' ? 'success' : ($trendAnalysis['trend_metrics']['overall_trend'] === 'declining' ? 'danger' : 'secondary') }}">
                                                        {{ ucfirst($trendAnalysis['trend_metrics']['overall_trend']) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Stable</span>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Key Findings</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">• Asset recovery trending: <strong>{{ ucfirst($trendAnalysis['performance_trends']['asset_recovery_trend']) }}</strong></li>
                                            <li class="mb-1">• Missing assets: <strong>{{ ucfirst($trendAnalysis['performance_trends']['missing_assets_trend']) }}</strong></li>
                                            <li class="mb-1">• Maintenance needs: <strong>{{ ucfirst($trendAnalysis['performance_trends']['maintenance_trend']) }}</strong></li>
                                            <li class="mb-1">• Financial health: <strong>{{ ucfirst($trendAnalysis['performance_trends']['financial_health_trend']) }}</strong></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Strategic Recommendations</h6>
                                        <ul class="list-unstyled">
                                            @if($trendAnalysis['performance_trends']['asset_recovery_trend'] === 'declining')
                                                <li class="mb-1">• Review security protocols</li>
                                                <li class="mb-1">• Increase audit frequency</li>
                                            @endif
                                            @if($trendAnalysis['performance_trends']['maintenance_trend'] === 'declining')
                                                <li class="mb-1">• Enhance preventive maintenance</li>
                                                <li class="mb-1">• Budget for equipment replacement</li>
                                            @endif
                                            @if($trendAnalysis['performance_trends']['asset_recovery_trend'] === 'improving')
                                                <li class="mb-1">• Maintain current best practices</li>
                                                <li class="mb-1">• Document successful procedures</li>
                                            @endif
                                            <li class="mb-1">• Continue regular trend monitoring</li>
                                            <li class="mb-1">• Consider predictive maintenance programs</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @else
                    <!-- No Data Message -->
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="avatar-xl mx-auto mb-4">
                                    <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-trending-flat"></i>
                                    </div>
                                </div>
                                <h4>No Trend Data Available</h4>
                                <p class="text-muted mb-4">No audit data found for the selected date range and filters. Please adjust your filters or ensure audits have been conducted during this period.</p>
                                <div class="mt-4">
                                    <a href="{{ route('audits.index') }}" class="btn btn-sm btn-primary me-2">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="resetFilters()">
                                        <i class="bx bx-refresh me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="text-center border-top pt-3">
                                <p class="text-muted small">
                                    <strong>Audit Trend Analysis Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                    <br>
                                    Analysis Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                    @if(isset($trendAnalysis['total_audits']))
                                    <br>
                                    Total Audits Analyzed: <strong>{{ $trendAnalysis['total_audits'] }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }
        
        function submitForm() {
            document.getElementById('filterForm').submit();
        }
        
        function resetFilters() {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - 12);
            
            const url = new URL(window.location.href);
            url.search = '';
            url.searchParams.set('start_date', startDate.toISOString().split('T')[0]);
            url.searchParams.set('end_date', endDate.toISOString().split('T')[0]);
            
            window.location.href = url.toString();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select[onchange="submitForm()"]');
            const inputs = document.querySelectorAll('input[onchange="submitForm()"]');
            
            [...selects, ...inputs].forEach(element => {
                element.addEventListener('change', function() {
                    setTimeout(() => {
                        document.getElementById('filterForm').submit();
                    }, 300);
                });
            });
        });
    </script>
@endsection
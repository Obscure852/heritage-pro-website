@extends('layouts.master')
@section('title')
    Asset Condition Report - {{ $audit->audit_code }}
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
            Asset Condition Report
        @endslot
    @endcomponent
    
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        
        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 12px;
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

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 10mm;
            }

            .card-header img {
                width: 300px;
                height: 120px;
            }

            .table {
                width: 100%;
                table-layout: fixed;
            }

            .table th,
            .table td {
                width: auto;
                overflow: visible;
                word-wrap: break-word;
            }

            .card {
                box-shadow: none;
            }
        }
    </style>
    
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>
    
    <div class="row printable">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-start mb-4">
                                    <h5>Asset Condition Analysis Report</h5>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>
                                    <span><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                <!-- Executive Summary -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    <i class="bx bx-heart-pulse me-2"></i>
                                                    Asset Condition Overview
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $conditionSummary['total_assets'] }}</h2>
                                                            <p class="mb-0">Total Assets Assessed</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $conditionSummary['health_metrics']['overall_health_score'] }}</h2>
                                                            <p class="mb-0">Overall Health Score</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $conditionSummary['health_metrics']['assets_needing_replacement'] }}</h2>
                                                            <p class="mb-0">Need Replacement</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $conditionSummary['health_metrics']['assets_needing_maintenance'] }}</h2>
                                                            <p class="mb-0">Need Maintenance</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Health Metrics Dashboard -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Overall Health Assessment</h6>
                                                <p class="mb-1"><strong>Health Score:</strong> 
                                                    <span class="badge bg-{{ $conditionSummary['health_metrics']['overall_health_score'] >= 75 ? 'success' : ($conditionSummary['health_metrics']['overall_health_score'] >= 50 ? 'warning' : 'danger') }}">
                                                        {{ $conditionSummary['health_metrics']['overall_health_score'] }}/100
                                                    </span>
                                                </p>
                                                <p class="mb-1"><strong>Condition Trend:</strong> 
                                                    <span class="badge bg-{{ $conditionSummary['health_metrics']['condition_trend'] === 'Excellent' ? 'success' : ($conditionSummary['health_metrics']['condition_trend'] === 'Stable' ? 'info' : 'warning') }}">
                                                        {{ $conditionSummary['health_metrics']['condition_trend'] }}
                                                    </span>
                                                </p>
                                                <p class="mb-1"><strong>Assets in Good Condition:</strong> 
                                                    {{ $conditionSummary['health_metrics']['assets_in_good_condition'] }} 
                                                    ({{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['health_metrics']['assets_in_good_condition'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}%)
                                                </p>
                                                <p class="mb-0"><strong>Replacement Priority:</strong> 
                                                    @if($conditionSummary['health_metrics']['assets_needing_replacement'] > 5)
                                                        <span class="text-danger">High</span>
                                                    @elseif($conditionSummary['health_metrics']['assets_needing_replacement'] > 2)
                                                        <span class="text-warning">Medium</span>
                                                    @else
                                                        <span class="text-success">Low</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Financial Impact</h6>
                                                <p class="mb-1"><strong>Replacement Cost:</strong> 
                                                    P {{ number_format($conditionSummary['health_metrics']['estimated_replacement_cost'], 2) }}
                                                </p>
                                                <p class="mb-1"><strong>Maintenance Cost:</strong> 
                                                    P {{ number_format($conditionSummary['health_metrics']['estimated_maintenance_cost'], 2) }}
                                                </p>
                                                <p class="mb-1"><strong>Average Asset Age:</strong> 
                                                    {{ $conditionSummary['age_analysis']['average_age'] }} months
                                                </p>
                                                <p class="mb-0"><strong>Audit Status:</strong> 
                                                    <span class="badge bg-{{ $conditionSummary['audit_status_class'] }}">{{ $conditionSummary['audit_status'] }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Condition Breakdown -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Asset Condition Analysis</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Condition</th>
                                                        <th>Count</th>
                                                        <th>Percentage</th>
                                                        <th>Total Value</th>
                                                        <th>Avg Age (Months)</th>
                                                        <th>Replacement Priority</th>
                                                        <th>Action Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-success">New</span></td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['new']['count'] }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['new']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionSummary['condition_breakdown']['new']['value'], 2) }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['new']['average_age_months'] }}</td>
                                                        <td><span class="badge bg-success">{{ $conditionSummary['condition_breakdown']['new']['replacement_priority'] }}</span></td>
                                                        <td class="text-success">Monitor and maintain</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-info">Good</span></td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['good']['count'] }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['good']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionSummary['condition_breakdown']['good']['value'], 2) }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['good']['average_age_months'] }}</td>
                                                        <td><span class="badge bg-info">{{ $conditionSummary['condition_breakdown']['good']['replacement_priority'] }}</span></td>
                                                        <td class="text-info">Regular maintenance</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-warning">Fair</span></td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['fair']['count'] }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['fair']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionSummary['condition_breakdown']['fair']['value'], 2) }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['fair']['average_age_months'] }}</td>
                                                        <td><span class="badge bg-warning">{{ $conditionSummary['condition_breakdown']['fair']['replacement_priority'] }}</span></td>
                                                        <td class="text-warning">Schedule maintenance</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-danger">Poor</span></td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['poor']['count'] }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['poor']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionSummary['condition_breakdown']['poor']['value'], 2) }}</td>
                                                        <td>{{ $conditionSummary['condition_breakdown']['poor']['average_age_months'] }}</td>
                                                        <td><span class="badge bg-danger">{{ $conditionSummary['condition_breakdown']['poor']['replacement_priority'] }}</span></td>
                                                        <td class="text-danger">Replace immediately</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Condition Analysis -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Condition Analysis by Category</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Total Assets</th>
                                                        <th>Health Score</th>
                                                        <th>New</th>
                                                        <th>Good</th>
                                                        <th>Fair</th>
                                                        <th>Poor</th>
                                                        <th>Priority Level</th>
                                                        <th>Total Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conditionSummary['category_condition_breakdown'] as $category => $data)
                                                        <tr>
                                                            <td><strong>{{ $category }}</strong></td>
                                                            <td>{{ $data['total_count'] }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['health_score'] >= 75 ? 'success' : ($data['health_score'] >= 50 ? 'warning' : 'danger') }}">
                                                                    {{ $data['health_score'] }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $data['condition_counts']['new'] }}</td>
                                                            <td>{{ $data['condition_counts']['good'] }}</td>
                                                            <td>{{ $data['condition_counts']['fair'] }}</td>
                                                            <td>{{ $data['condition_counts']['poor'] }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['priority_level'] === 'High' ? 'danger' : ($data['priority_level'] === 'Medium' ? 'warning' : 'success') }}">
                                                                    {{ $data['priority_level'] }}
                                                                </span>
                                                            </td>
                                                            <td>P {{ number_format($data['total_value'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Age Analysis -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Asset Age Distribution</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Age Range</th>
                                                                <th>Count</th>
                                                                <th>Percentage</th>
                                                                <th>Expected Condition</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>0-12 months</td>
                                                                <td>{{ $conditionSummary['age_analysis']['age_ranges']['0-12_months'] }}</td>
                                                                <td>{{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['age_analysis']['age_ranges']['0-12_months'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}%</td>
                                                                <td><span class="badge bg-success">New/Good</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td>1-3 years</td>
                                                                <td>{{ $conditionSummary['age_analysis']['age_ranges']['1-3_years'] }}</td>
                                                                <td>{{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['age_analysis']['age_ranges']['1-3_years'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}%</td>
                                                                <td><span class="badge bg-info">Good</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td>3-5 years</td>
                                                                <td>{{ $conditionSummary['age_analysis']['age_ranges']['3-5_years'] }}</td>
                                                                <td>{{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['age_analysis']['age_ranges']['3-5_years'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}%</td>
                                                                <td><span class="badge bg-warning">Fair</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td>5+ years</td>
                                                                <td>{{ $conditionSummary['age_analysis']['age_ranges']['5_plus_years'] }}</td>
                                                                <td>{{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['age_analysis']['age_ranges']['5_plus_years'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}%</td>
                                                                <td><span class="badge bg-danger">Fair/Poor</span></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card border shadow-none bg-light">
                                                    <div class="card-body">
                                                        <h6 class="mb-3">Age Analysis Insights</h6>
                                                        <p class="mb-2"><strong>Average Asset Age:</strong> {{ $conditionSummary['age_analysis']['average_age'] }} months</p>
                                                        <p class="mb-2"><strong>Aging Assets (3+ years):</strong> 
                                                            {{ $conditionSummary['age_analysis']['age_ranges']['3-5_years'] + $conditionSummary['age_analysis']['age_ranges']['5_plus_years'] }} assets
                                                        </p>
                                                        <p class="mb-2"><strong>New Assets (0-1 year):</strong> 
                                                            {{ $conditionSummary['age_analysis']['age_ranges']['0-12_months'] }} assets
                                                        </p>
                                                        <p class="mb-0"><strong>Fleet Renewal Rate:</strong> 
                                                            {{ $conditionSummary['total_assets'] > 0 ? round(($conditionSummary['age_analysis']['age_ranges']['0-12_months'] / $conditionSummary['total_assets']) * 100, 1) : 0 }}% annually
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Replacement Planning -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-warning text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    <i class="bx bx-refresh me-2"></i>
                                                    Replacement Planning
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="text-center">
                                                            <h4 class="text-white">{{ $conditionSummary['replacement_planning']['immediate_replacement']['count'] }}</h4>
                                                            <p class="mb-0">Immediate Replacement</p>
                                                            <small>P {{ number_format($conditionSummary['replacement_planning']['immediate_replacement']['value'], 2) }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-center">
                                                            <h4 class="text-white">{{ $conditionSummary['replacement_planning']['planned_replacement']['count'] }}</h4>
                                                            <p class="mb-0">Planned Replacement (1-2 years)</p>
                                                            <small>P {{ number_format($conditionSummary['replacement_planning']['planned_replacement']['value'], 2) }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recommendations -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h5 class="mb-3">
                                                    Condition Management Recommendations
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <h6 class="text-danger">Immediate Actions</h6>
                                                        <ul class="list-unstyled">
                                                            @if($conditionSummary['condition_breakdown']['poor']['count'] > 0)
                                                                <li>• Replace {{ $conditionSummary['condition_breakdown']['poor']['count'] }} assets in poor condition</li>
                                                                <li>• Budget P {{ number_format($conditionSummary['condition_breakdown']['poor']['value'], 2) }} for replacements</li>
                                                                <li>• Safety assessment of critical poor-condition assets</li>
                                                            @else
                                                                <li>• No immediate replacements required</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-warning">Short-term Planning (3-6 months)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($conditionSummary['condition_breakdown']['fair']['count'] > 0)
                                                                <li>• Schedule maintenance for {{ $conditionSummary['condition_breakdown']['fair']['count'] }} fair-condition assets</li>
                                                                <li>• Budget P {{ number_format($conditionSummary['health_metrics']['estimated_maintenance_cost'], 2) }} for maintenance</li>
                                                            @endif
                                                            <li>• Develop replacement timeline for aging assets</li>
                                                            <li>• Review warranty status for newer assets</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-info">Long-term Strategy</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• Implement preventive maintenance program</li>
                                                            <li>• Establish asset replacement lifecycle</li>
                                                            <li>• Consider bulk purchasing for better pricing</li>
                                                            <li>• Train staff on proper asset care</li>
                                                            <li>• Regular condition monitoring schedule</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="text-center border-top pt-3">
                                            <p class="text-muted small">
                                                <strong>Asset Condition Analysis Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                Audit: {{ $audit->audit_code }} | Conducted by: {{ $conditionSummary['conducted_by'] }}
                                                <br>
                                                Overall Health Score: <strong>{{ $conditionSummary['health_metrics']['overall_health_score'] }}/100</strong> | 
                                                Condition Trend: <strong>{{ $conditionSummary['health_metrics']['condition_trend'] }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
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
    </script>
@endsection
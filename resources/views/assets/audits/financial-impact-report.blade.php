@extends('layouts.master')
@section('title')
    Financial Impact Report - {{ $audit->audit_code }}
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
            Financial Impact Report
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
                                <div class="text-start mb-2">
                                    <h4>Financial Impact Analysis Report</h4>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>
                                    <span><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                <!-- Executive Financial Summary -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    Financial Overview
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">P {{ number_format($financialSummary['financial_metrics']['total_asset_value'], 2) }}</h2>
                                                            <p class="mb-0">Total Asset Value</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">P {{ number_format($financialSummary['financial_metrics']['value_at_risk'], 2) }}</h2>
                                                            <p class="mb-0">Value at Risk</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $financialSummary['financial_metrics']['value_retention_rate'] }}%</h2>
                                                            <p class="mb-0">Value Retention</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $financialSummary['financial_metrics']['financial_health_score'] }}</h2>
                                                            <p class="mb-0">Financial Health Score</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Key Financial Metrics -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Asset Portfolio Summary</h6>
                                                <p class="mb-1"><strong>Total Assets:</strong> {{ $financialSummary['total_assets'] }}</p>
                                                <p class="mb-1"><strong>Total Value:</strong> P {{ number_format($financialSummary['financial_metrics']['total_asset_value'], 2) }}</p>
                                                <p class="mb-1"><strong>Missing Asset Value:</strong> 
                                                    <span class="text-danger">P {{ number_format($financialSummary['financial_metrics']['missing_asset_value'], 2) }}</span>
                                                </p>
                                                <p class="mb-0"><strong>Assets Needing Maintenance:</strong> 
                                                    <span class="text-warning">P {{ number_format($financialSummary['financial_metrics']['assets_needing_maintenance_value'], 2) }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Financial Health Assessment</h6>
                                                <p class="mb-1"><strong>Health Score:</strong> 
                                                    <span class="badge bg-{{ $financialSummary['financial_metrics']['financial_health_score'] >= 80 ? 'success' : ($financialSummary['financial_metrics']['financial_health_score'] >= 60 ? 'warning' : 'danger') }}">
                                                        {{ $financialSummary['financial_metrics']['financial_health_score'] }}/100
                                                    </span>
                                                </p>
                                                <p class="mb-1"><strong>Value Retention:</strong> {{ $financialSummary['financial_metrics']['value_retention_rate'] }}%</p>
                                                <p class="mb-1"><strong>Value at Risk:</strong> P {{ number_format($financialSummary['financial_metrics']['value_at_risk'], 2) }}</p>
                                                <p class="mb-0"><strong>Audit Status:</strong> 
                                                    <span class="badge bg-{{ $financialSummary['audit_status_class'] }}">{{ $financialSummary['audit_status'] }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Analysis -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Detailed Cost Analysis</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Cost Category</th>
                                                        <th>Amount</th>
                                                        <th>Percentage of Total Value</th>
                                                        <th>Priority</th>
                                                        <th>Timeline</th>
                                                        <th>Impact</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Immediate Replacement Cost</strong></td>
                                                        <td class="text-danger">P {{ number_format($financialSummary['cost_analysis']['immediate_replacement_cost'], 2) }}</td>
                                                        <td>{{ $financialSummary['financial_metrics']['total_asset_value'] > 0 ? round(($financialSummary['cost_analysis']['immediate_replacement_cost'] / $financialSummary['financial_metrics']['total_asset_value']) * 100, 1) : 0 }}%</td>
                                                        <td><span class="badge bg-danger">Critical</span></td>
                                                        <td>Immediate</td>
                                                        <td>High</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Estimated Maintenance Cost</strong></td>
                                                        <td class="text-warning">P {{ number_format($financialSummary['cost_analysis']['estimated_maintenance_cost'], 2) }}</td>
                                                        <td>{{ $financialSummary['financial_metrics']['total_asset_value'] > 0 ? round(($financialSummary['cost_analysis']['estimated_maintenance_cost'] / $financialSummary['financial_metrics']['total_asset_value']) * 100, 1) : 0 }}%</td>
                                                        <td><span class="badge bg-warning">High</span></td>
                                                        <td>3-6 months</td>
                                                        <td>Medium</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Insurance Claim Potential</strong></td>
                                                        <td class="text-info">P {{ number_format($financialSummary['cost_analysis']['insurance_claim_potential'], 2) }}</td>
                                                        <td>{{ $financialSummary['financial_metrics']['total_asset_value'] > 0 ? round(($financialSummary['cost_analysis']['insurance_claim_potential'] / $financialSummary['financial_metrics']['total_asset_value']) * 100, 1) : 0 }}%</td>
                                                        <td><span class="badge bg-info">Recovery</span></td>
                                                        <td>30-90 days</td>
                                                        <td>Positive</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Depreciation Impact</strong></td>
                                                        <td class="text-secondary">P {{ number_format($financialSummary['cost_analysis']['depreciation_impact'], 2) }}</td>
                                                        <td>{{ $financialSummary['financial_metrics']['total_asset_value'] > 0 ? round(($financialSummary['cost_analysis']['depreciation_impact'] / $financialSummary['financial_metrics']['total_asset_value']) * 100, 1) : 0 }}%</td>
                                                        <td><span class="badge bg-secondary">Normal</span></td>
                                                        <td>Ongoing</td>
                                                        <td>Expected</td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <td><strong>Total Financial Exposure</strong></td>
                                                        <td><strong>P {{ number_format($financialSummary['cost_analysis']['total_financial_exposure'], 2) }}</strong></td>
                                                        <td><strong>{{ $financialSummary['financial_metrics']['total_asset_value'] > 0 ? round(($financialSummary['cost_analysis']['total_financial_exposure'] / $financialSummary['financial_metrics']['total_asset_value']) * 100, 1) : 0 }}%</strong></td>
                                                        <td><span class="badge bg-dark">Total</span></td>
                                                        <td>-</td>
                                                        <td>Combined</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget Impact Analysis -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-info text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    Budget Impact Projections
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($financialSummary['budget_impact']['immediate_budget_need'], 2) }}</h4>
                                                            <p class="mb-0">Immediate Budget Need</p>
                                                            <small>(50% of replacement cost)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($financialSummary['budget_impact']['quarterly_budget_impact'], 2) }}</h4>
                                                            <p class="mb-0">Quarterly Budget Impact</p>
                                                            <small>(Maintenance costs)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($financialSummary['budget_impact']['annual_budget_projection'], 2) }}</h4>
                                                            <p class="mb-0">Annual Budget Projection</p>
                                                            <small>(Full year estimate)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($financialSummary['budget_impact']['cost_avoidance_opportunity'], 2) }}</h4>
                                                            <p class="mb-0">Cost Avoidance</p>
                                                            <small>(Preventive vs corrective)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Financial Breakdown -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Financial Impact by Asset Category</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Total Value</th>
                                                        <th>Missing Value</th>
                                                        <th>Maintenance Value</th>
                                                        <th>Value at Risk</th>
                                                        <th>Risk %</th>
                                                        <th>Financial Priority</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($financialSummary['category_financial_breakdown'] as $category => $data)
                                                        <tr>
                                                            <td><strong>{{ $category }}</strong></td>
                                                            <td>P {{ number_format($data['total_value'], 2) }}</td>
                                                            <td>
                                                                @if($data['missing_value'] > 0)
                                                                    <span class="text-danger">P {{ number_format($data['missing_value'], 2) }}</span>
                                                                @else
                                                                    <span class="text-success">P 0.00</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($data['maintenance_value'] > 0)
                                                                    <span class="text-warning">P {{ number_format($data['maintenance_value'], 2) }}</span>
                                                                @else
                                                                    <span class="text-success">P 0.00</span>
                                                                @endif
                                                            </td>
                                                            <td>P {{ number_format($data['value_at_risk'], 2) }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['risk_percentage'] > 20 ? 'danger' : ($data['risk_percentage'] > 10 ? 'warning' : 'success') }}">
                                                                    {{ $data['risk_percentage'] }}%
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['financial_priority'] === 'Critical' ? 'dark' : ($data['financial_priority'] === 'High' ? 'danger' : ($data['financial_priority'] === 'Medium' ? 'warning' : 'success')) }}">
                                                                    {{ $data['financial_priority'] }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- ROI Analysis -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Return on Investment Analysis</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card border shadow-none bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h3 class="text-white">{{ $financialSummary['roi_analysis']['asset_management_roi'] }}%</h3>
                                                        <p class="mb-1">Asset Management ROI</p>
                                                        <small>Value protected vs program cost</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card border shadow-none bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h3 class="text-white">{{ $financialSummary['roi_analysis']['preventive_maintenance_roi'] }}%</h3>
                                                        <p class="mb-1">Preventive Maintenance ROI</p>
                                                        <small>Savings from preventive vs emergency</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card border shadow-none bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <h3 class="text-white">P {{ number_format($financialSummary['roi_analysis']['audit_program_value'], 2) }}</h3>
                                                        <p class="mb-1">Audit Program Value</p>
                                                        <small>Annual value of audit findings</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Recommendations -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h5 class="mb-3">
                                                    Financial Management Recommendations
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <h6 class="text-danger">Immediate Financial Actions</h6>
                                                        <ul class="list-unstyled">
                                                            @if($financialSummary['cost_analysis']['immediate_replacement_cost'] > 0)
                                                                <li>• Secure P {{ number_format($financialSummary['budget_impact']['immediate_budget_need'], 2) }} for immediate replacements</li>
                                                                <li>• File insurance claims for missing assets</li>
                                                                <li>• Approve emergency replacement budget</li>
                                                            @else
                                                                <li>• No immediate financial actions required</li>
                                                            @endif
                                                            <li>• Review and update insurance coverage</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-warning">Budget Planning (Next 12 months)</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• Plan P {{ number_format($financialSummary['budget_impact']['annual_budget_projection'], 2) }} for annual asset needs</li>
                                                            <li>• Establish maintenance reserve fund</li>
                                                            <li>• Negotiate bulk purchasing agreements</li>
                                                            <li>• Consider equipment leasing options</li>
                                                            <li>• Plan for technology refresh cycles</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-info">Long-term Financial Strategy</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• Implement total cost of ownership tracking</li>
                                                            <li>• Develop asset lifecycle costing models</li>
                                                            <li>• Establish depreciation schedules</li>
                                                            <li>• Create financial dashboard for assets</li>
                                                            <li>• Regular financial health assessments</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost-Benefit Summary -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-secondary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    Cost-Benefit Summary
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-white">Costs Identified</h6>
                                                        <p class="text-white mb-1">Immediate Replacement: P {{ number_format($financialSummary['cost_analysis']['immediate_replacement_cost'], 2) }}</p>
                                                        <p class="text-white mb-1">Maintenance Required: P {{ number_format($financialSummary['cost_analysis']['estimated_maintenance_cost'], 2) }}</p>
                                                        <p class="text-white mb-1">Total Exposure: P {{ number_format($financialSummary['cost_analysis']['total_financial_exposure'], 2) }}</p>
                                                        <p class="text-white mb-0"><strong>Investment Needed: P {{ number_format($financialSummary['budget_impact']['annual_budget_projection'], 2) }}</strong></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-white">Benefits & Savings</h6>
                                                        <p class="text-white mb-1">Insurance Recovery: P {{ number_format($financialSummary['cost_analysis']['insurance_claim_potential'], 2) }}</p>
                                                        <p class="text-white mb-1">Cost Avoidance: P {{ number_format($financialSummary['budget_impact']['cost_avoidance_opportunity'], 2) }}</p>
                                                        <p class="text-white mb-1">Asset Management ROI: {{ $financialSummary['roi_analysis']['asset_management_roi'] }}%</p>
                                                        <p class="text-white mb-0"><strong>Net Financial Impact: 
                                                            @php 
                                                                $netImpact = $financialSummary['cost_analysis']['insurance_claim_potential'] + $financialSummary['budget_impact']['cost_avoidance_opportunity'] - $financialSummary['cost_analysis']['total_financial_exposure'];
                                                            @endphp
                                                            <span class="{{ $netImpact >= 0 ? 'text-success' : 'text-warning' }}">P {{ number_format($netImpact, 2) }}</span>
                                                        </strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="text-center border-top pt-3">
                                            <p class="text-muted small">
                                                <strong>Financial Impact Analysis Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                Audit: {{ $audit->audit_code }} | Conducted by: {{ $financialSummary['conducted_by'] }}
                                                <br>
                                                Total Asset Value: <strong>P {{ number_format($financialSummary['financial_metrics']['total_asset_value'], 2) }}</strong> | 
                                                Value at Risk: <strong>P {{ number_format($financialSummary['financial_metrics']['value_at_risk'], 2) }}</strong> | 
                                                Health Score: <strong>{{ $financialSummary['financial_metrics']['financial_health_score'] }}/100</strong>
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
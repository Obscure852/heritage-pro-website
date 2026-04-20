@extends('layouts.master')
@section('title')
    Location Analysis Report - {{ $audit->audit_code }}
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
            Location Analysis Report
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
                                    <h4>Location Performance Analysis Report</h4>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>    
                                    <span><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                <!-- Executive Summary -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                   Location Performance Overview
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $locationSummary['total_locations'] }}</h2>
                                                            <p class="mb-0">Total Locations</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $locationSummary['security_analysis']['high_risk_locations'] }}</h2>
                                                            <p class="mb-0">High Risk Locations</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $locationSummary['security_analysis']['locations_with_issues'] }}</h2>
                                                            <p class="mb-0">Locations with Issues</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">P {{ number_format($locationSummary['security_analysis']['total_missing_value'], 2) }}</h2>
                                                            <p class="mb-0">Total Missing Value</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Highlights -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Performance Highlights</h6>
                                                <p class="mb-1"><strong>Best Performing Location:</strong> 
                                                    <span class="badge bg-success">{{ $locationSummary['security_analysis']['best_performing_location'] }}</span>
                                                </p>
                                                <p class="mb-1"><strong>Worst Performing Location:</strong> 
                                                    <span class="badge bg-danger">{{ $locationSummary['security_analysis']['worst_performing_location'] }}</span>
                                                </p>
                                                <p class="mb-1"><strong>Total Assets:</strong> {{ $locationSummary['total_assets'] }}</p>
                                                <p class="mb-0"><strong>Audit Status:</strong> 
                                                    <span class="badge bg-{{ $locationSummary['audit_status_class'] }}">{{ $locationSummary['audit_status'] }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Security Analysis</h6>
                                                <p class="mb-1"><strong>High Risk Locations:</strong> 
                                                    {{ $locationSummary['security_analysis']['high_risk_locations'] }}
                                                </p>
                                                <p class="mb-1"><strong>Locations Needing Attention:</strong> 
                                                    {{ $locationSummary['security_analysis']['locations_with_issues'] }}
                                                </p>
                                                <p class="mb-1"><strong>Total Missing Value:</strong> 
                                                    P {{ number_format($locationSummary['security_analysis']['total_missing_value'], 2) }}
                                                </p>
                                                <p class="mb-0"><strong>Conducted By:</strong> {{ $locationSummary['conducted_by'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Performance Details -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Location Performance Breakdown</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Total Assets</th>
                                                        <th>Present</th>
                                                        <th>Missing</th>
                                                        <th>Maintenance</th>
                                                        <th>Performance Score</th>
                                                        <th>Security Risk</th>
                                                        <th>Total Value</th>
                                                        <th>Priority</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locationSummary['location_breakdown'] as $location => $data)
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
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
                                                                <span class="badge bg-{{ $data['performance_score'] >= 90 ? 'success' : ($data['performance_score'] >= 75 ? 'info' : ($data['performance_score'] >= 50 ? 'warning' : 'danger')) }}">
                                                                    {{ $data['performance_score'] }}%
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['security_risk']['class'] }}">
                                                                    {{ $data['security_risk']['level'] }}
                                                                </span>
                                                            </td>
                                                            <td>P {{ number_format($data['total_value'], 2) }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['priority_level'] === 'Critical' ? 'dark' : ($data['priority_level'] === 'High' ? 'danger' : ($data['priority_level'] === 'Medium' ? 'warning' : 'success')) }}">
                                                                    {{ $data['priority_level'] }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Asset Distribution by Location -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Asset Distribution & Utilization</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Asset Density</th>
                                                        <th>Utilization Rate</th>
                                                        <th>Issues Count</th>
                                                        <th>Missing Value</th>
                                                        <th>Space Efficiency</th>
                                                        <th>Recommendations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locationSummary['location_breakdown'] as $location => $data)
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
                                                            <td>{{ $data['asset_density'] }} assets</td>
                                                            <td>
                                                                <span class="badge bg-{{ $data['utilization_rate'] >= 90 ? 'success' : ($data['utilization_rate'] >= 75 ? 'info' : 'warning') }}">
                                                                    {{ $data['utilization_rate'] }}%
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($data['issues_count'] > 0)
                                                                    <span class="badge bg-warning">{{ $data['issues_count'] }}</span>
                                                                @else
                                                                    <span class="text-success">0</span>
                                                                @endif
                                                            </td>
                                                            <td>P {{ number_format($data['missing_value'], 2) }}</td>
                                                            <td>
                                                                @if($data['asset_density'] > 20)
                                                                    <span class="badge bg-danger">High Density</span>
                                                                @elseif($data['asset_density'] > 10)
                                                                    <span class="badge bg-warning">Medium Density</span>
                                                                @else
                                                                    <span class="badge bg-success">Optimal</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($data['issues_count'] > 2)
                                                                    <span class="text-danger">Multiple issues</span>
                                                                @elseif($data['missing_assets'] > 0)
                                                                    <span class="text-warning">Security review</span>
                                                                @else
                                                                    <span class="text-success">Maintain standards</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category-Location Matrix -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Asset Category Distribution by Location</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        @php
                                                            $allCategories = collect($locationSummary['category_location_matrix'])->flatten(1)->keys()->unique();
                                                        @endphp
                                                        @foreach($allCategories as $category)
                                                            <th>{{ $category }}</th>
                                                        @endforeach
                                                        <th>Issues Summary</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locationSummary['category_location_matrix'] as $location => $categories)
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
                                                            @foreach($allCategories as $category)
                                                                <td>
                                                                    @if(isset($categories[$category]))
                                                                        <div class="small">
                                                                            <strong>{{ $categories[$category]['count'] }}</strong>
                                                                            @if($categories[$category]['missing'] > 0)
                                                                                <br><span class="text-danger">{{ $categories[$category]['missing'] }} missing</span>
                                                                            @endif
                                                                            @if($categories[$category]['maintenance'] > 0)
                                                                                <br><span class="text-warning">{{ $categories[$category]['maintenance'] }} maint.</span>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                            <td>
                                                                @php
                                                                    $totalMissing = collect($categories)->sum('missing');
                                                                    $totalMaintenance = collect($categories)->sum('maintenance');
                                                                @endphp
                                                                @if($totalMissing > 0 || $totalMaintenance > 0)
                                                                    <small>
                                                                        @if($totalMissing > 0)
                                                                            <span class="text-danger">{{ $totalMissing }} missing</span><br>
                                                                        @endif
                                                                        @if($totalMaintenance > 0)
                                                                            <span class="text-warning">{{ $totalMaintenance }} maintenance</span>
                                                                        @endif
                                                                    </small>
                                                                @else
                                                                    <span class="text-success small">No issues</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Footer -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="text-center border-top pt-3">
                                            <p class="text-muted small">
                                                <strong>Location Performance Analysis Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                Audit: {{ $audit->audit_code }} | Conducted by: {{ $locationSummary['conducted_by'] }}
                                                <br>
                                                Best Performer: <strong>{{ $locationSummary['security_analysis']['best_performing_location'] }}</strong> | 
                                                Needs Attention: <strong>{{ $locationSummary['security_analysis']['worst_performing_location'] }}</strong>
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
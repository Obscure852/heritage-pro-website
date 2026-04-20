@extends('layouts.master')
@section('title')
    Audit Summary Report - {{ $audit->audit_code }}
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
            Audit Summary Report
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
                                    <h4>Comprehensive Asset Audit Summary</h4>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>    
                                    <span><strong>Audit Period:</strong> {{ $audit->audit_date->format('M d, Y') }} - {{ $audit->updated_at->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                <!-- Executive Summary -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    Executive Summary
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $audit->auditItems->count() }}</h2>
                                                            <p class="mb-0">Total Assets Audited</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $audit->auditItems->where('is_present', true)->count() }}</h2>
                                                            <p class="mb-0">Assets Found</p>
                                                            <small>({{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $audit->auditItems->where('is_present', false)->count() }}</h2>
                                                            <p class="mb-0">Missing Assets</p>
                                                            <small>({{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->where('is_present', false)->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $audit->auditItems->where('needs_maintenance', true)->count() }}</h2>
                                                            <p class="mb-0">Need Maintenance</p>
                                                            <small>({{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->where('needs_maintenance', true)->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Audit Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Audit Information</h6>
                                                <p class="mb-1"><strong>Conducted By:</strong> {{ $audit->conductedByUser->name ?? 'System' }}</p>
                                                <p class="mb-1"><strong>Start Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</p>
                                                <p class="mb-1"><strong>Completion Date:</strong> {{ $audit->updated_at->format('M d, Y') }}</p>
                                                <p class="mb-1"><strong>Duration:</strong> {{ $audit->audit_date->diffInDays($audit->updated_at) }} days</p>
                                                <p class="mb-0"><strong>Status:</strong> 
                                                    <span class="badge bg-success">{{ $audit->status }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Financial Overview</h6>
                                                @php
                                                    $totalValue = $audit->auditItems->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                    $missingValue = $audit->auditItems->where('is_present', false)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                    $maintenanceValue = $audit->auditItems->where('needs_maintenance', true)->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                @endphp
                                                <p class="mb-1"><strong>Total Assets Value:</strong> P {{ number_format($totalValue, 2) }}</p>
                                                <p class="mb-1"><strong>Missing Assets Value:</strong> P {{ number_format($missingValue, 2) }}</p>
                                                <p class="mb-1"><strong>Assets Needing Maintenance:</strong> P {{ number_format($maintenanceValue, 2) }}</p>
                                                <p class="mb-0"><strong>Assets at Risk:</strong> P {{ number_format($missingValue + $maintenanceValue, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Asset Condition Analysis -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Asset Condition Analysis</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Condition</th>
                                                        <th>Count</th>
                                                        <th>Percentage</th>
                                                        <th>Value</th>
                                                        <th>Action Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $conditions = ['New', 'Good', 'Fair', 'Poor'];
                                                        $conditionData = [];
                                                        foreach($conditions as $condition) {
                                                            $items = $audit->auditItems->where('condition', $condition);
                                                            $conditionData[$condition] = [
                                                                'count' => $items->count(),
                                                                'percentage' => $audit->auditItems->count() > 0 ? round(($items->count() / $audit->auditItems->count()) * 100, 1) : 0,
                                                                'value' => $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0)
                                                            ];
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td><span class="badge bg-success">New</span></td>
                                                        <td>{{ $conditionData['New']['count'] }}</td>
                                                        <td>{{ $conditionData['New']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionData['New']['value'], 2) }}</td>
                                                        <td class="text-success">No action required</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-info">Good</span></td>
                                                        <td>{{ $conditionData['Good']['count'] }}</td>
                                                        <td>{{ $conditionData['Good']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionData['Good']['value'], 2) }}</td>
                                                        <td class="text-info">Routine maintenance</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-warning">Fair</span></td>
                                                        <td>{{ $conditionData['Fair']['count'] }}</td>
                                                        <td>{{ $conditionData['Fair']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionData['Fair']['value'], 2) }}</td>
                                                        <td class="text-warning">Schedule maintenance</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-danger">Poor</span></td>
                                                        <td>{{ $conditionData['Poor']['count'] }}</td>
                                                        <td>{{ $conditionData['Poor']['percentage'] }}%</td>
                                                        <td>P {{ number_format($conditionData['Poor']['value'], 2) }}</td>
                                                        <td class="text-danger">Immediate action required</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Breakdown -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Assets by Category</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Total</th>
                                                        <th>Present</th>
                                                        <th>Missing</th>
                                                        <th>Need Maintenance</th>
                                                        <th>Condition Rate</th>
                                                        <th>Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $categoryBreakdown = $audit->auditItems->groupBy(fn($item) => $item->asset->category->name ?? 'Uncategorized');
                                                    @endphp
                                                    @foreach($categoryBreakdown as $category => $items)
                                                        @php
                                                            $present = $items->where('is_present', true)->count();
                                                            $missing = $items->where('is_present', false)->count();
                                                            $maintenance = $items->where('needs_maintenance', true)->count();
                                                            $conditionRate = $items->count() > 0 ? round(($present / $items->count()) * 100, 1) : 0;
                                                            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $category }}</strong></td>
                                                            <td>{{ $items->count() }}</td>
                                                            <td><span class="badge bg-success">{{ $present }}</span></td>
                                                            <td><span class="badge bg-danger">{{ $missing }}</span></td>
                                                            <td><span class="badge bg-warning">{{ $maintenance }}</span></td>
                                                            <td>{{ $conditionRate }}%</td>
                                                            <td>P {{ number_format($categoryValue, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Breakdown -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Assets by Location</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Total</th>
                                                        <th>Present</th>
                                                        <th>Missing</th>
                                                        <th>Need Maintenance</th>
                                                        <th>Condition Rate</th>
                                                        <th>Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $locationBreakdown = $audit->auditItems->groupBy(fn($item) => $item->asset->venue->name ?? 'Unknown Location');
                                                    @endphp
                                                    @foreach($locationBreakdown as $location => $items)
                                                        @php
                                                            $present = $items->where('is_present', true)->count();
                                                            $missing = $items->where('is_present', false)->count();
                                                            $maintenance = $items->where('needs_maintenance', true)->count();
                                                            $conditionRate = $items->count() > 0 ? round(($present / $items->count()) * 100, 1) : 0;
                                                            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
                                                            <td>{{ $items->count() }}</td>
                                                            <td><span class="badge bg-success">{{ $present }}</span></td>
                                                            <td><span class="badge bg-danger">{{ $missing }}</span></td>
                                                            <td><span class="badge bg-warning">{{ $maintenance }}</span></td>
                                                            <td>{{ $conditionRate }}%</td>
                                                            <td>P {{ number_format($locationValue, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recommendations -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h5 class="mb-3">
                                                    Recommendations & Action Items
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <h6 class="text-danger">Immediate Actions (High Priority)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($audit->auditItems->where('is_present', false)->count() > 0)
                                                                <li>• Investigate {{ $audit->auditItems->where('is_present', false)->count() }} missing assets</li>
                                                                <li>• File necessary incident reports</li>
                                                                <li>• Contact insurance for missing assets</li>
                                                            @endif
                                                            @if($audit->auditItems->where('condition', 'Poor')->count() > 0)
                                                                <li>• Schedule urgent maintenance for {{ $audit->auditItems->where('condition', 'Poor')->count() }} assets</li>
                                                            @endif
                                                            @if($audit->auditItems->where('is_present', false)->count() == 0 && $audit->auditItems->where('condition', 'Poor')->count() == 0)
                                                                <li>• No immediate actions required</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-warning">Short-term Actions (30 days)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($audit->auditItems->where('needs_maintenance', true)->count() > 0)
                                                                <li>• Schedule maintenance for {{ $audit->auditItems->where('needs_maintenance', true)->count() }} assets</li>
                                                            @endif
                                                            @if($audit->auditItems->where('condition', 'Fair')->count() > 0)
                                                                <li>• Review {{ $audit->auditItems->where('condition', 'Fair')->count() }} assets in fair condition</li>
                                                            @endif
                                                            <li>• Update asset management procedures</li>
                                                            <li>• Review security protocols</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-info">Long-term Improvements</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• Implement regular audit schedule</li>
                                                            <li>• Consider asset tracking technology</li>
                                                            <li>• Staff training on asset management</li>
                                                            <li>• Review insurance coverage</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Key Performance Indicators -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Key Performance Indicators</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card border shadow-none text-center">
                                                    <div class="card-body">
                                                        <h4 class="text-success">{{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%</h4>
                                                        <p class="mb-0">Asset Recovery Rate</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border shadow-none text-center">
                                                    <div class="card-body">
                                                        <h4 class="text-info">{{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->whereIn('condition', ['New', 'Good'])->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%</h4>
                                                        <p class="mb-0">Assets in Good Condition</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border shadow-none text-center">
                                                    <div class="card-body">
                                                        <h4 class="text-warning">{{ $audit->auditItems->count() > 0 ? round(($audit->auditItems->where('needs_maintenance', true)->count() / $audit->auditItems->count()) * 100, 1) : 0 }}%</h4>
                                                        <p class="mb-0">Maintenance Required</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border shadow-none text-center">
                                                    <div class="card-body">
                                                        <h4 class="text-primary">{{ $totalValue > 0 ? round((($totalValue - $missingValue) / $totalValue) * 100, 1) : 0 }}%</h4>
                                                        <p class="mb-0">Value Retention Rate</p>
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
                                                <strong>Audit Summary Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                This comprehensive report covers {{ $audit->auditItems->count() }} assets with total value of P {{ number_format($totalValue, 2) }}.
                                                <br>
                                                Next audit recommended date: {{ $audit->next_audit_date ? $audit->next_audit_date->format('M d, Y') : 'Not scheduled' }}
                                            </p>
                                            <div class="mt-3 mb-2">
                                                <strong>Audit Status: </strong>
                                                @if($audit->auditItems->where('is_present', false)->count() == 0 && $audit->auditItems->where('needs_maintenance', true)->count() == 0)
                                                    <span class="badge bg-success fs-6">Excellent - No issues found</span>
                                                @elseif($audit->auditItems->where('is_present', false)->count() == 0)
                                                    <span class="badge bg-info fs-6">Good - Minor maintenance needed</span>
                                                @elseif($audit->auditItems->where('is_present', false)->count() <= 2)
                                                    <span class="badge bg-warning fs-6">Fair - Some issues require attention</span>
                                                @else
                                                    <span class="badge bg-danger fs-6">Poor - Immediate action required</span>
                                                @endif
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
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
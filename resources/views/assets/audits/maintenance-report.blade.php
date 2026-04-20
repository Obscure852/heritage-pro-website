@extends('layouts.master')
@section('title')
    Maintenance Report - {{ $audit->audit_code }}
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
            Maintenance Report
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
                                    <h4>Asset Maintenance Report</h4>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>
                                    <span><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                @php
                                    $maintenanceAssets = $audit->auditItems;
                                    $totalMaintenanceValue = $maintenanceAssets->sum(function($item) {
                                        return $item->asset->current_value ?? $item->asset->purchase_price ?? 0;
                                    });
                                    $categoryBreakdown = $maintenanceAssets->groupBy(function($item) {
                                        return $item->asset->category->name ?? 'Uncategorized';
                                    });
                                    $locationBreakdown = $maintenanceAssets->groupBy(function($item) {
                                        return $item->asset->venue->name ?? 'Unknown Location';
                                    });
                                    $conditionBreakdown = $maintenanceAssets->groupBy(function($item) {
                                        return $item->condition ?? $item->asset->condition ?? 'Unknown';
                                    });
                                @endphp

                                <!-- Executive Summary -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    <i class="bx bx-wrench me-2"></i>
                                                   Maintenance Requirements Summary
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $maintenanceAssets->count() }}</h2>
                                                            <p class="mb-0">Assets Need Maintenance</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">P {{ number_format($totalMaintenanceValue, 2) }}</h2>
                                                            <p class="mb-0">Total Asset Value</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $categoryBreakdown->count() }}</h2>
                                                            <p class="mb-0">Categories Affected</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $locationBreakdown->count() }}</h2>
                                                            <p class="mb-0">Locations Affected</p>
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
                                                <p class="mb-1"><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</p>
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    <span class="badge bg-{{ $audit->status === 'Completed' ? 'success' : 'warning' }}">{{ $audit->status }}</span>
                                                </p>
                                                <p class="mb-0"><strong>Maintenance Rate:</strong> 
                                                    <span class="badge bg-warning">{{ $audit->auditItems()->count() > 0 ? round(($maintenanceAssets->count() / $audit->auditItems()->count()) * 100, 1) : 0 }}%</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Maintenance Priority</h6>
                                                @php
                                                    $criticalCount = $maintenanceAssets->where('condition', 'Poor')->count();
                                                    $urgentCount = $maintenanceAssets->where('condition', 'Fair')->count();
                                                    $routineCount = $maintenanceAssets->whereIn('condition', ['Good', 'New'])->count();
                                                @endphp
                                                <p class="mb-1"><strong>Critical (Poor Condition):</strong> 
                                                    <span class="badge bg-danger">{{ $criticalCount }}</span>
                                                </p>
                                                <p class="mb-1"><strong>Urgent (Fair Condition):</strong> 
                                                    <span class="badge bg-warning">{{ $urgentCount }}</span>
                                                </p>
                                                <p class="mb-1"><strong>Routine (Good/New):</strong> 
                                                    <span class="badge bg-info">{{ $routineCount }}</span>
                                                </p>
                                                <p class="mb-0"><strong>Estimated Cost:</strong> 
                                                    P {{ number_format($totalMaintenanceValue * 0.1, 2) }} - P {{ number_format($totalMaintenanceValue * 0.3, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($maintenanceAssets->count() > 0)
                                <!-- Maintenance by Priority Level -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Maintenance by Priority Level</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Priority Level</th>
                                                        <th>Asset Count</th>
                                                        <th>Total Value</th>
                                                        <th>Timeframe</th>
                                                        <th>Estimated Cost</th>
                                                        <th>Action Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if($criticalCount > 0)
                                                    <tr>
                                                        <td><span class="badge bg-danger">Critical</span></td>
                                                        <td>{{ $criticalCount }}</td>
                                                        <td>P {{ number_format($maintenanceAssets->where('condition', 'Poor')->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0), 2) }}</td>
                                                        <td class="text-danger">Immediate (1-3 days)</td>
                                                        <td>P {{ number_format($maintenanceAssets->where('condition', 'Poor')->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0) * 0.2, 2) }}</td>
                                                        <td>Emergency maintenance required</td>
                                                    </tr>
                                                    @endif
                                                    @if($urgentCount > 0)
                                                    <tr>
                                                        <td><span class="badge bg-warning">Urgent</span></td>
                                                        <td>{{ $urgentCount }}</td>
                                                        <td>P {{ number_format($maintenanceAssets->where('condition', 'Fair')->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0), 2) }}</td>
                                                        <td class="text-warning">1-2 weeks</td>
                                                        <td>P {{ number_format($maintenanceAssets->where('condition', 'Fair')->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0) * 0.15, 2) }}</td>
                                                        <td>Schedule maintenance soon</td>
                                                    </tr>
                                                    @endif
                                                    @if($routineCount > 0)
                                                    <tr>
                                                        <td><span class="badge bg-info">Routine</span></td>
                                                        <td>{{ $routineCount }}</td>
                                                        <td>P {{ number_format($maintenanceAssets->whereIn('condition', ['Good', 'New'])->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0), 2) }}</td>
                                                        <td class="text-info">1-3 months</td>
                                                        <td>P {{ number_format($maintenanceAssets->whereIn('condition', ['Good', 'New'])->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0) * 0.05, 2) }}</td>
                                                        <td>Preventive maintenance</td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Maintenance by Category -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Maintenance Requirements by Category</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Asset Count</th>
                                                        <th>Total Value</th>
                                                        <th>Critical</th>
                                                        <th>Urgent</th>
                                                        <th>Routine</th>
                                                        <th>Estimated Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($categoryBreakdown as $category => $items)
                                                        @php
                                                            $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                            $categoryCritical = $items->where('condition', 'Poor')->count();
                                                            $categoryUrgent = $items->where('condition', 'Fair')->count();
                                                            $categoryRoutine = $items->whereIn('condition', ['Good', 'New'])->count();
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $category }}</strong></td>
                                                            <td>{{ $items->count() }}</td>
                                                            <td>P {{ number_format($categoryValue, 2) }}</td>
                                                            <td>
                                                                @if($categoryCritical > 0)
                                                                    <span class="badge bg-danger">{{ $categoryCritical }}</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($categoryUrgent > 0)
                                                                    <span class="badge bg-warning">{{ $categoryUrgent }}</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($categoryRoutine > 0)
                                                                    <span class="badge bg-info">{{ $categoryRoutine }}</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>P {{ number_format($categoryValue * 0.15, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Maintenance by Location -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Maintenance Requirements by Location</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Asset Count</th>
                                                        <th>Total Value</th>
                                                        <th>Priority Level</th>
                                                        <th>Coordination Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locationBreakdown as $location => $items)
                                                        @php
                                                            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                            $hasCritical = $items->where('condition', 'Poor')->count() > 0;
                                                            $hasUrgent = $items->where('condition', 'Fair')->count() > 0;
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
                                                            <td>{{ $items->count() }}</td>
                                                            <td>P {{ number_format($locationValue, 2) }}</td>
                                                            <td>
                                                                @if($hasCritical)
                                                                    <span class="badge bg-danger">Critical</span>
                                                                @elseif($hasUrgent)
                                                                    <span class="badge bg-warning">Urgent</span>
                                                                @else
                                                                    <span class="badge bg-info">Routine</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($items->count() > 3)
                                                                    <span class="text-warning">Schedule coordination</span>
                                                                @elseif($hasCritical)
                                                                    <span class="text-danger">Immediate access</span>
                                                                @else
                                                                    <span class="text-info">Standard scheduling</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Maintenance List -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Detailed Maintenance Requirements</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Priority</th>
                                                        <th>Asset Code</th>
                                                        <th>Asset Name</th>
                                                        <th>Category</th>
                                                        <th>Location</th>
                                                        <th>Condition</th>
                                                        <th>Value</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $sortedAssets = $maintenanceAssets->sortBy(function($item) {
                                                            $condition = $item->condition ?? $item->asset->condition ?? 'Unknown';
                                                            return $condition === 'Poor' ? 1 : ($condition === 'Fair' ? 2 : 3);
                                                        });
                                                    @endphp
                                                    @foreach($sortedAssets as $index => $auditItem)
                                                        @php
                                                            $condition = $auditItem->condition ?? $auditItem->asset->condition ?? 'Unknown';
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                @if($condition === 'Poor')
                                                                    <span class="badge bg-danger">Critical</span>
                                                                @elseif($condition === 'Fair')
                                                                    <span class="badge bg-warning">Urgent</span>
                                                                @else
                                                                    <span class="badge bg-info">Routine</span>
                                                                @endif
                                                            </td>
                                                            <td><strong>{{ $auditItem->asset->asset_code }}</strong></td>
                                                            <td>{{ $auditItem->asset->name }}</td>
                                                            <td>{{ $auditItem->asset->category->name ?? 'N/A' }}</td>
                                                            <td>{{ $auditItem->asset->venue->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $condition === 'Poor' ? 'danger' : ($condition === 'Fair' ? 'warning' : 'info') }}">
                                                                    {{ $condition }}
                                                                </span>
                                                            </td>
                                                            <td>P {{ number_format($auditItem->asset->current_value ?? $auditItem->asset->purchase_price ?? 0, 2) }}</td>
                                                            <td>{{ $auditItem->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <!-- No Maintenance Required -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-success text-white">
                                            <div class="card-body text-center py-5">
                                                <div class="avatar-xl mx-auto mb-4">
                                                    <div class="avatar-title bg-white text-success rounded-circle font-size-32">
                                                        <i class="bx bx-check-circle"></i>
                                                    </div>
                                                </div>
                                                <h4 class="text-white">Excellent! No Maintenance Required</h4>
                                                <p class="mb-0">All assets are in good condition and do not require immediate maintenance.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Maintenance Schedule and Recommendations -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h5 class="mb-3">
                                                    <i class="bx bx-calendar-check me-2"></i>
                                                    Maintenance Schedule & Recommendations
                                                </h5>
                                                <div class="row">
                                                    @if($maintenanceAssets->count() > 0)
                                                    <div class="col-md-4">
                                                        <h6 class="text-danger">Immediate (1-3 days)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($criticalCount > 0)
                                                            <li>• <strong>Critical maintenance</strong> for {{ $criticalCount }} assets</li>
                                                            <li>• <strong>Contact vendors</strong> for emergency service</li>
                                                            <li>• <strong>Budget approval</strong> for urgent repairs</li>
                                                            <li>• <strong>Safety assessment</strong> of critical assets</li>
                                                            @else
                                                            <li>• No critical maintenance required</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-warning">Short-term (1-4 weeks)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($urgentCount > 0)
                                                            <li>• <strong>Schedule maintenance</strong> for {{ $urgentCount }} urgent assets</li>
                                                            <li>• <strong>Obtain quotes</strong> from service providers</li>
                                                            <li>• <strong>Plan downtime</strong> for maintenance activities</li>
                                                            @endif
                                                            <li>• <strong>Review maintenance contracts</strong></li>
                                                            <li>• <strong>Stock spare parts</strong> if needed</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-info">Long-term (1-3 months)</h6>
                                                        <ul class="list-unstyled">
                                                            @if($routineCount > 0)
                                                            <li>• <strong>Preventive maintenance</strong> for {{ $routineCount }} assets</li>
                                                            @endif
                                                            <li>• <strong>Annual maintenance contracts</strong></li>
                                                            <li>• <strong>Staff training</strong> on basic maintenance</li>
                                                            <li>• <strong>Maintenance budget planning</strong></li>
                                                            <li>• <strong>Asset replacement planning</strong></li>
                                                        </ul>
                                                    </div>
                                                    @else
                                                    <div class="col-12">
                                                        <div class="text-center">
                                                            <h6 class="text-success">Current Status: Excellent</h6>
                                                            <p>No immediate maintenance required. Continue with:</p>
                                                            <ul class="list-unstyled">
                                                                <li>• Regular preventive maintenance schedule</li>
                                                                <li>• Quarterly condition assessments</li>
                                                                <li>• Maintenance contract reviews</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget Estimation -->
                                @if($maintenanceAssets->count() > 0)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-info text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    <i class="bx bx-money me-2"></i>
                                                    Maintenance Budget Estimation
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($totalMaintenanceValue * 0.05, 2) }}</h4>
                                                            <p class="mb-0">Routine Maintenance</p>
                                                            <small>(5% of asset value)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($totalMaintenanceValue * 0.15, 2) }}</h4>
                                                            <p class="mb-0">Corrective Maintenance</p>
                                                            <small>(15% of asset value)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($totalMaintenanceValue * 0.3, 2) }}</h4>
                                                            <p class="mb-0">Emergency Repairs</p>
                                                            <small>(30% of asset value)</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h4 class="text-white">P {{ number_format($totalMaintenanceValue * 0.15, 2) }}</h4>
                                                            <p class="mb-0">Recommended Budget</p>
                                                            <small>(Average estimate)</small>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                <strong>Asset Maintenance Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                Audit: {{ $audit->audit_code }} | Conducted by: {{ $audit->conductedByUser->full_name ?? 'System' }}
                                                @if($maintenanceAssets->count() > 0)
                                                <br>
                                                <span class="text-warning"><strong>ACTION REQUIRED:</strong> {{ $maintenanceAssets->count() }} assets need maintenance (Total value: P {{ number_format($totalMaintenanceValue, 2) }})</span>
                                                @endif
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
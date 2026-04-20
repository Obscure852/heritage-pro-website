@extends('layouts.master')
@section('title')
    Missing Assets Report - {{ $audit->audit_code }}
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
            Missing Assets Report
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
                                    <h4>Missing Assets Report</h4>
                                    <span><strong>Audit Code:</strong> {{ $audit->audit_code }}</span>
                                    <br>
                                    <span><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</span>
                                    <br>
                                    <span><strong>Report Generated:</strong> {{ now()->format('M d, Y H:i') }}</span>
                                </div>
                                
                                @php
                                    $missingAssets = $audit->auditItems;
                                    $totalMissingValue = $missingAssets->sum(function($item) {
                                        return $item->asset->current_value ?? $item->asset->purchase_price ?? 0;
                                    });
                                    $categoryBreakdown = $missingAssets->groupBy(function($item) {
                                        return $item->asset->category->name ?? 'Uncategorized';
                                    });
                                    $locationBreakdown = $missingAssets->groupBy(function($item) {
                                        return $item->asset->venue->name ?? 'Unknown Location';
                                    });
                                @endphp

                                <!-- Executive Summary -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="text-white mb-3">
                                                    Missing Assets Summary
                                                </h5>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">{{ $missingAssets->count() }}</h2>
                                                            <p class="mb-0">Total Missing Assets</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h2 class="text-white">P {{ number_format($totalMissingValue, 2) }}</h2>
                                                            <p class="mb-0">Total Value Lost</p>
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
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Audit Information</h6>
                                                <p class="mb-1"><strong>Conducted By:</strong> {{ $audit->conductedByUser->name ?? 'System' }}</p>
                                                <p class="mb-1"><strong>Audit Date:</strong> {{ $audit->audit_date->format('M d, Y') }}</p>
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    <span class="badge bg-{{ $audit->status === 'Completed' ? 'success' : 'warning' }}">{{ $audit->status }}</span>
                                                </p>
                                                <p class="mb-0"><strong>Missing Rate:</strong> 
                                                    <span class="badge bg-danger">{{ $audit->auditItems()->count() > 0 ? round(($missingAssets->count() / $audit->auditItems()->count()) * 100, 1) : 0 }}%</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-2">Impact Assessment</h6>
                                                <p class="mb-1"><strong>Priority Level:</strong> 
                                                    @if($missingAssets->count() == 0)
                                                        <span class="badge bg-success">No Issues</span>
                                                    @elseif($missingAssets->count() <= 2)
                                                        <span class="badge bg-warning">Low Priority</span>
                                                    @elseif($missingAssets->count() <= 5)
                                                        <span class="badge bg-danger">High Priority</span>
                                                    @else
                                                        <span class="badge bg-dark">Critical</span>
                                                    @endif
                                                </p>
                                                <p class="mb-1"><strong>Insurance Claim:</strong> 
                                                    {{ $totalMissingValue > 1000 ? 'Required' : 'Optional' }}
                                                </p>
                                                <p class="mb-1"><strong>Investigation:</strong> 
                                                    {{ $missingAssets->count() > 0 ? 'Required' : 'Not Required' }}
                                                </p>
                                                <p class="mb-0"><strong>Security Review:</strong> 
                                                    {{ $missingAssets->count() > 3 ? 'Urgent' : 'Standard' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($missingAssets->count() > 0)    
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <h5 class="mb-3">Missing Assets by Category</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Category</th>
                                                            <th>Missing Count</th>
                                                            <th>Total Value</th>
                                                            <th>Average Value</th>
                                                            <th>Impact Level</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($categoryBreakdown as $category => $items)
                                                            @php
                                                                $categoryValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                                $avgValue = $items->count() > 0 ? $categoryValue / $items->count() : 0;
                                                            @endphp
                                                            <tr>
                                                                <td><strong>{{ $category }}</strong></td>
                                                                <td><span class="badge bg-danger">{{ $items->count() }}</span></td>
                                                                <td>P {{ number_format($categoryValue, 2) }}</td>
                                                                <td>P {{ number_format($avgValue, 2) }}</td>
                                                                <td>
                                                                    @if($categoryValue > 5000)
                                                                        <span class="badge bg-dark">Critical</span>
                                                                    @elseif($categoryValue > 2000)
                                                                        <span class="badge bg-danger">High</span>
                                                                    @elseif($categoryValue > 500)
                                                                        <span class="badge bg-warning">Medium</span>
                                                                    @else
                                                                        <span class="badge bg-info">Low</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Missing Assets by Location</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Missing Count</th>
                                                        <th>Total Value</th>
                                                        <th>Security Level</th>
                                                        <th>Action Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($locationBreakdown as $location => $items)
                                                        @php
                                                            $locationValue = $items->sum(fn($item) => $item->asset->current_value ?? $item->asset->purchase_price ?? 0);
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $location }}</strong></td>
                                                            <td><span class="badge bg-danger">{{ $items->count() }}</span></td>
                                                            <td>P {{ number_format($locationValue, 2) }}</td>
                                                            <td>
                                                                @if($items->count() > 3)
                                                                    <span class="badge bg-danger">High Risk</span>
                                                                @elseif($items->count() > 1)
                                                                    <span class="badge bg-warning">Medium Risk</span>
                                                                @else
                                                                    <span class="badge bg-info">Low Risk</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($items->count() > 2)
                                                                    <span class="text-danger">Security Review</span>
                                                                @else
                                                                    <span class="text-warning">Investigation</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Missing Assets List -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <h5 class="mb-3">Detailed Missing Assets List</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Asset Code</th>
                                                        <th>Asset Name</th>
                                                        <th>Category</th>
                                                        <th>Location</th>
                                                        <th>Purchase Date</th>
                                                        <th>Value</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($missingAssets as $index => $auditItem)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td><strong>{{ $auditItem->asset->asset_code }}</strong></td>
                                                            <td>{{ $auditItem->asset->name }}</td>
                                                            <td>{{ $auditItem->asset->category->name ?? 'N/A' }}</td>
                                                            <td>{{ $auditItem->asset->venue->name ?? 'N/A' }}</td>
                                                            <td>{{ $auditItem->asset->purchase_date ? $auditItem->asset->purchase_date->format('M d, Y') : 'N/A' }}</td>
                                                            <td>P {{ number_format($auditItem->asset->current_value ?? $auditItem->asset->purchase_price ?? 0, 2) }}</td>
                                                            <td>{{ $auditItem->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- Action Items and Recommendations -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="card border shadow-none bg-light">
                                            <div class="card-body">
                                                <h5 class="mb-3">
                                                    Action Items & Recommendations
                                                </h5>
                                                <div class="row">
                                                    @if($missingAssets->count() > 0)
                                                    <div class="col-md-6">
                                                        <h6 class="text-danger">Immediate Actions Required</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• <strong>File incident reports</strong> for all {{ $missingAssets->count() }} missing assets</li>
                                                            <li>• <strong>Contact insurance provider</strong> for claims (Total: P {{ number_format($totalMissingValue, 2) }})</li>
                                                            <li>• <strong>Conduct thorough investigation</strong> of disappearances</li>
                                                            <li>• <strong>Review security footage</strong> if available</li>
                                                            <li>• <strong>Interview staff</strong> who had access to missing items</li>
                                                            @if($missingAssets->count() > 3)
                                                            <li>• <strong>Emergency security review</strong> - multiple assets missing</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-warning">Preventive Measures</h6>
                                                        <ul class="list-unstyled">
                                                            <li>• <strong>Enhance security protocols</strong> in affected locations</li>
                                                            <li>• <strong>Implement asset tracking system</strong> (RFID/Barcode)</li>
                                                            <li>• <strong>Increase audit frequency</strong> for high-risk areas</li>
                                                            <li>• <strong>Staff training</strong> on asset security</li>
                                                            <li>• <strong>Review access controls</strong> and permissions</li>
                                                            <li>• <strong>Update insurance coverage</strong> if necessary</li>
                                                        </ul>
                                                    </div>
                                                    @else
                                                    <div class="col-12">
                                                        <div class="text-center">
                                                            <h6 class="text-success">Congratulations!</h6>
                                                            <p>No missing assets were found during this audit. Continue with:</p>
                                                            <ul class="list-unstyled">
                                                                <li>• Regular monitoring and audits</li>
                                                                <li>• Maintaining current security protocols</li>
                                                                <li>• Staff awareness programs</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @endif
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
                                                <strong>Missing Assets Report</strong> - Generated on {{ now()->format('M d, Y') }} at {{ now()->format('H:i') }}
                                                <br>
                                                Audit: {{ $audit->audit_code }} | Conducted by: {{ $audit->conductedByUser->full_name ?? 'System' }}
                                                @if($missingAssets->count() > 0)
                                                <br>
                                                <span class="text-danger"><strong>Urgent:</strong> {{ $missingAssets->count() }} assets missing with total value of P {{ number_format($totalMissingValue, 2) }}</span>
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
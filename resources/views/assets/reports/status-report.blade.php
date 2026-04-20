@extends('layouts.master')
@section('title', 'Assets by Status Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Assets by Status Report
        @endslot
    @endcomponent

    <div class="container-fluid">
        <!-- Report Header -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex justify-content-end align-items-center">
                    <div class="d-flex gap-2">
                        <a href="#" onclick="window.print(0)" class="text-muted"> <i class="bx bx-printer me-1 font-size-18"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#available-assets" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-check-circle"></i></span>
                                    <span class="d-none d-sm-block">Available Assets</span>
                                    <span class="badge bg-success ms-2">{{ $availableAssets->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#assigned-assets" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-user-check"></i></span>
                                    <span class="d-none d-sm-block">Assigned Assets</span>
                                    <span class="badge bg-primary ms-2">{{ $assignedAssets->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#maintenance-assets" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-tools"></i></span>
                                    <span class="d-none d-sm-block">In Maintenance</span>
                                    <span class="badge bg-warning ms-2">{{ $maintenanceAssets->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#disposed-assets" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-trash"></i></span>
                                    <span class="d-none d-sm-block">Disposed Assets</span>
                                    <span class="badge bg-danger ms-2">{{ $disposedAssets->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#analysis" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-chart-bar"></i></span>
                                    <span class="d-none d-sm-block">Analysis</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-3">
                            <!-- Available Assets Tab -->
                            <div class="tab-pane active" id="available-assets" role="tabpanel">
                                @if($healthIndicators['idle_available_assets'] > 0 || $healthIndicators['poor_condition_available'] > 0)
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Attention Required</h6>
                                    @if($healthIndicators['idle_available_assets'] > 0)
                                        <p class="mb-1">• <strong>{{ $healthIndicators['idle_available_assets'] }}</strong> assets have been idle for over 90 days</p>
                                    @endif
                                    @if($healthIndicators['poor_condition_available'] > 0)
                                        <p class="mb-0">• <strong>{{ $healthIndicators['poor_condition_available'] }}</strong> available assets are in poor condition</p>
                                    @endif
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>Condition</th>
                                                <th>Value</th>
                                                <th>Days Available</th>
                                                <th>Last Assignment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($availableAssets as $item)
                                                <tr class="{{ $item['days_available'] > 90 ? 'table-warning' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $item['asset']->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                        <i class="fas fa-box fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['asset']->id) }}" class="text-dark">
                                                                        {{ $item['asset']->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['asset']->asset_code }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $item['asset']->category->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>{{ $item['asset']->venue->name ?? 'No Location' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['asset']->condition == 'New' ? 'success' : 
                                                            ($item['asset']->condition == 'Good' ? 'info' : 
                                                            ($item['asset']->condition == 'Fair' ? 'warning' : 'danger')) 
                                                        }}">
                                                            {{ $item['asset']->condition }}
                                                        </span>
                                                        <br><small class="text-muted">Score: {{ $item['condition_score'] }}%</small>
                                                    </td>
                                                    <td>
                                                        @if($item['asset']->purchase_price)
                                                            <span class="fw-medium">P {{ number_format($item['asset']->purchase_price, 2) }}</span>
                                                            <br><small class="text-muted">{{ $item['value_category'] }}</small>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $item['days_available'] > 90 ? 'warning' : 'light' }} text-{{ $item['days_available'] > 90 ? 'dark' : 'muted' }}">
                                                            {{ $item['days_available'] }} days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['last_assignment'])
                                                            <small class="text-muted">
                                                                {{ $item['last_assignment']['date']->format('M d, Y') }}<br>
                                                                {{ $item['last_assignment']['assigned_to'] }} ({{ $item['last_assignment']['duration'] }} days)
                                                            </small>
                                                        @else
                                                            <span class="text-muted">Never assigned</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                                        <h5>No Available Assets</h5>
                                                        <p class="text-muted">All assets are currently assigned or in other statuses.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Assigned Assets Tab -->
                            <div class="tab-pane" id="assigned-assets" role="tabpanel">
                                @if($healthIndicators['overdue_assignments'] > 0)
                                <div class="alert alert-danger mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-clock me-2"></i>Overdue Assignments</h6>
                                    <p class="mb-0"><strong>{{ $healthIndicators['overdue_assignments'] }}</strong> assets have overdue return dates and require immediate attention.</p>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Assigned To</th>
                                                <th>Assignment Date</th>
                                                <th>Expected Return</th>
                                                <th>Days Assigned</th>
                                                <th>Condition Risk</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assignedAssets as $item)
                                                <tr class="{{ $item['is_overdue'] ? 'table-danger' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $item['asset']->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                        <i class="fas fa-box fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['asset']->id) }}" class="text-dark">
                                                                        {{ $item['asset']->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['asset']->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['asset']->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item['assigned_to_name'] }}</h6>
                                                            <small class="text-muted">{{ $item['assigned_to_type'] }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {{ $item['assignment']->assigned_date->format('M d, Y') }}
                                                        @if($item['assignment']->assignedByUser)
                                                            <br><small class="text-muted">by {{ $item['assignment']->assignedByUser->name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['assignment']->expected_return_date)
                                                            {{ $item['assignment']->expected_return_date->format('M d, Y') }}
                                                            @if($item['is_overdue'])
                                                                <br><span class="badge bg-danger">{{ $item['overdue_days'] }} days overdue</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No return date</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $item['days_assigned'] }} days</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['condition_change_risk'] == 'High' ? 'danger' : 
                                                            ($item['condition_change_risk'] == 'Medium' ? 'warning' : 'success') 
                                                        }}">
                                                            {{ $item['condition_change_risk'] }} Risk
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('assets.return-asset', $item['asset']->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-undo me-1"></i> Return
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="fas fa-user-slash fa-2x text-muted mb-3"></i>
                                                        <h5>No Assigned Assets</h5>
                                                        <p class="text-muted">No assets are currently assigned to staff members.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Maintenance Assets Tab -->
                            <div class="tab-pane" id="maintenance-assets" role="tabpanel">
                                @if($healthIndicators['long_term_maintenance'] > 0)
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-tools me-2"></i>Long-term Maintenance</h6>
                                    <p class="mb-0"><strong>{{ $healthIndicators['long_term_maintenance'] }}</strong> assets have been in maintenance for over 30 days.</p>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Maintenance Type</th>
                                                <th>Business Contact</th>
                                                <th>Days in Maintenance</th>
                                                <th>Cost</th>
                                                <th>Est. Completion</th>
                                                <th>Priority</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($maintenanceAssets as $item)
                                                <tr class="{{ $item['days_in_maintenance'] > 30 ? 'table-warning' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $item['asset']->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                                                        <i class="fas fa-tools fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['asset']->id) }}" class="text-dark">
                                                                        {{ $item['asset']->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['asset']->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['asset']->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">{{ $item['maintenance_type'] }}</span>
                                                    </td>
                                                    <td>{{ $item['maintenance_vendor'] }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $item['days_in_maintenance'] > 30 ? 'danger' : 'light' }} text-{{ $item['days_in_maintenance'] > 30 ? 'white' : 'muted' }}">
                                                            {{ $item['days_in_maintenance'] }} days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance_cost'] > 0)
                                                            <span class="fw-medium">P {{ number_format($item['maintenance_cost'], 2) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['estimated_completion'])
                                                            {{ $item['estimated_completion']->format('M d, Y') }}
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['maintenance_priority'] == 'High' ? 'danger' : 
                                                            ($item['maintenance_priority'] == 'Medium' ? 'warning' : 'success') 
                                                        }}">
                                                            {{ $item['maintenance_priority'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                                        <h5>No Assets in Maintenance</h5>
                                                        <p class="text-muted">All assets are in good working condition.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Disposed Assets Tab -->
                            <div class="tab-pane" id="disposed-assets" role="tabpanel">
                                @if($healthIndicators['high_value_disposed'] > 0)
                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>High-Value Disposals</h6>
                                    <p class="mb-0"><strong>{{ $healthIndicators['high_value_disposed'] }}</strong> high-value assets (>P10,000) have been disposed recently.</p>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Disposal Date</th>
                                                <th>Method</th>
                                                <th>Original Value</th>
                                                <th>Disposal Amount</th>
                                                <th>Value Recovery</th>
                                                <th>Age at Disposal</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($disposedAssets as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $item['asset']->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                                                        <i class="fas fa-box fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">{{ $item['asset']->name }}</h6>
                                                                <p class="text-muted mb-0 small">{{ $item['asset']->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['asset']->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($item['disposal_date'])
                                                            {{ $item['disposal_date']->format('M d, Y') }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['disposal_method'] == 'Sold' ? 'success' : 
                                                            ($item['disposal_method'] == 'Donated' ? 'info' : 
                                                            ($item['disposal_method'] == 'Recycled' ? 'warning' : 'secondary')) 
                                                        }}">
                                                            {{ $item['disposal_method'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['asset']->purchase_price)
                                                            <span class="fw-medium">P {{ number_format($item['asset']->purchase_price, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['disposal_amount'] > 0)
                                                            <span class="fw-medium text-success">P {{ number_format($item['disposal_amount'], 2) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['value_recovery_percentage'] > 0)
                                                            <span class="badge bg-{{ 
                                                                $item['value_recovery_percentage'] >= 50 ? 'success' : 
                                                                ($item['value_recovery_percentage'] >= 25 ? 'warning' : 'danger') 
                                                            }}">
                                                                {{ $item['value_recovery_percentage'] }}%
                                                            </span>
                                                        @else
                                                            <span class="text-muted">0%</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['age_at_disposal'] > 0)
                                                            {{ round($item['age_at_disposal'] / 12, 1) }} years
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ Str::limit($item['disposal_reason'], 30) }}</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-recycle fa-2x text-muted mb-3"></i>
                                                        <h5>No Disposed Assets</h5>
                                                        <p class="text-muted">No assets have been disposed recently.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Analysis Tab -->
                            <div class="tab-pane" id="analysis" role="tabpanel">
                                <!-- Value Analysis by Status -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    Value Analysis by Status
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Status</th>
                                                                <th class="text-center">Asset Count</th>
                                                                <th class="text-end">Total Value</th>
                                                                <th class="text-end">Average Value</th>
                                                                <th class="text-end">Highest Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($valueAnalysis as $status => $analysis)
                                                                <tr>
                                                                    <td>
                                                                        <span class="badge bg-{{ 
                                                                            $status == 'Available' ? 'success' : 
                                                                            ($status == 'Assigned' ? 'primary' : 
                                                                            ($status == 'In Maintenance' ? 'warning' : 'danger')) 
                                                                        }}">
                                                                            {{ $status }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">{{ $analysis['count'] }}</td>
                                                                    <td class="text-end">P {{ number_format($analysis['total_value'], 2) }}</td>
                                                                    <td class="text-end">P {{ number_format($analysis['average_value'], 2) }}</td>
                                                                    <td class="text-end">P {{ number_format($analysis['highest_value'], 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Age Analysis by Status -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    Age Analysis by Status (in months)
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Status</th>
                                                                <th class="text-center">Average Age</th>
                                                                <th class="text-center">Oldest Asset</th>
                                                                <th class="text-center">Newest Asset</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($ageAnalysis as $status => $analysis)
                                                                <tr>
                                                                    <td>
                                                                        <span class="badge bg-{{ 
                                                                            $status == 'Available' ? 'success' : 
                                                                            ($status == 'Assigned' ? 'primary' : 
                                                                            ($status == 'In Maintenance' ? 'warning' : 'danger')) 
                                                                        }}">
                                                                            {{ $status }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">{{ round($analysis['average_age']) }} months</td>
                                                                    <td class="text-center">{{ round($analysis['oldest_asset']) }} months</td>
                                                                    <td class="text-center">{{ round($analysis['newest_asset']) }} months</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Distribution by Status -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    Top Categories by Status
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($categoryDistribution as $status => $categories)
                                                        <div class="col-md-6 col-lg-3 mb-3">
                                                            <h6 class="text-{{ 
                                                                $status == 'Available' ? 'success' : 
                                                                ($status == 'Assigned' ? 'primary' : 
                                                                ($status == 'In Maintenance' ? 'warning' : 'danger')) 
                                                            }}">{{ $status }}</h6>
                                                            @forelse($categories->take(5) as $category)
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <small class="text-muted">{{ $category['name'] }}</small>
                                                                    <span class="badge bg-light text-dark">{{ $category['count'] }}</span>
                                                                </div>
                                                            @empty
                                                                <small class="text-muted">No assets in this status</small>
                                                            @endforelse
                                                        </div>
                                                    @endforeach
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

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bx bx-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function saveActiveTab(tabId) {
            localStorage.setItem('assetStatusActiveTab', tabId);
        }

        function loadActiveTab() {
            const activeTabId = localStorage.getItem('assetStatusActiveTab');
            if (activeTabId) {
                const tabElement = document.querySelector(`a[href="#${activeTabId}"]`);
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }
        }

        loadActiveTab();

        const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabLinks.forEach(tabLink => {
            tabLink.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('href').substring(1);
                saveActiveTab(targetId);
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            @media print {
                .btn, .dropdown, [onclick*="print"] { display: none !important; }
                .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
                .table { font-size: 11px; }
                body { font-size: 12px; }
                .nav-tabs { display: none !important; }
                .tab-content { display: block !important; }
                .tab-pane { display: block !important; page-break-before: always; }
                .tab-pane:first-child { page-break-before: auto; }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection

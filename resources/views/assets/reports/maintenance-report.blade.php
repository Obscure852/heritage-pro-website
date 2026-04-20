@extends('layouts.master')
@section('title', 'Asset Maintenance History Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Maintenance History Report
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

        <!-- Maintenance Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#maintenance-history" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-history"></i></span>
                                    <span class="d-none d-sm-block">Maintenance History</span>
                                    <span class="badge bg-success ms-2">{{ $maintenanceHistory->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#active-maintenance" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-tools"></i></span>
                                    <span class="d-none d-sm-block">Active Maintenance</span>
                                    <span class="badge bg-warning ms-2">{{ $activeMaintenance->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#scheduled-maintenance" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-calendar"></i></span>
                                    <span class="d-none d-sm-block">Scheduled</span>
                                    <span class="badge bg-info ms-2">{{ $scheduledMaintenance->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#asset-analysis" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-box"></i></span>
                                    <span class="d-none d-sm-block">By Asset</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#vendor-performance" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-store"></i></span>
                                    <span class="d-none d-sm-block">Business Contact Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cost-trends" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-chart-line"></i></span>
                                    <span class="d-none d-sm-block">Cost & Trends</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-3">
                            <!-- Maintenance History Tab (Primary Focus) -->
                            <div class="tab-pane active" id="maintenance-history" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Maintenance Type</th>
                                                <th>Date</th>
                                                <th>Duration</th>
                                                <th>Business Contact</th>
                                                <th>Cost</th>
                                                <th>Cost/Day</th>
                                                <th>Performed By</th>
                                                <th>Description</th>
                                                <th>Results</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($maintenanceHistory as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['maintenance']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['maintenance']->asset->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-success text-success rounded">
                                                                        <i class="fas fa-tools fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['maintenance']->asset->id) }}" class="text-dark">
                                                                        {{ $item['maintenance']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['maintenance']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['maintenance']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['maintenance']->maintenance_type === 'Preventive' ? 'success' : 
                                                            ($item['maintenance']->maintenance_type === 'Corrective' ? 'warning' : 'info') 
                                                        }}">
                                                            {{ $item['maintenance']->maintenance_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $item['maintenance']->maintenance_date->format('M d, Y') }}
                                                        <br><small class="text-muted">{{ $item['maintenance']->maintenance_date->diffForHumans() }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $item['duration_days'] }} days</span>
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->vendor)
                                                            <div>
                                                                <h6 class="mb-1">{{ $item['maintenance']->vendor->name }}</h6>
                                                                @if($item['maintenance']->vendor->contact_person)
                                                                    <small class="text-muted">{{ $item['maintenance']->vendor->contact_person }}</small>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Internal</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->cost > 0)
                                                            <span class="fw-medium">P {{ number_format($item['maintenance']->cost, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['cost_per_day'] > 0)
                                                            <span class="text-muted">P {{ number_format($item['cost_per_day'], 2) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->performedByUser)
                                                            {{ $item['maintenance']->performedByUser->full_name }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->description)
                                                            <small class="text-muted">{{ Str::limit($item['maintenance']->description, 50) }}</small>
                                                        @else
                                                            <span class="text-muted">No description</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->results)
                                                            <small class="text-muted">{{ Str::limit($item['maintenance']->results, 40) }}</small>
                                                        @else
                                                            <span class="text-muted">No results</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center py-4">
                                                        <i class="fas fa-clipboard-list fa-2x text-muted mb-3"></i>
                                                        <h5>No Maintenance History</h5>
                                                        <p class="text-muted">No completed maintenance records found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Active Maintenance Tab -->
                            <div class="tab-pane" id="active-maintenance" role="tabpanel">
                                @if($healthIndicators['high_priority_active'] > 0 || $healthIndicators['long_duration_maintenance'] > 0)
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Active Maintenance Alerts</h6>
                                    @if($healthIndicators['high_priority_active'] > 0)
                                        <p class="mb-1">• <strong>{{ $healthIndicators['high_priority_active'] }}</strong> high-priority maintenance items require attention</p>
                                    @endif
                                    @if($healthIndicators['long_duration_maintenance'] > 0)
                                        <p class="mb-0">• <strong>{{ $healthIndicators['long_duration_maintenance'] }}</strong> maintenance items have been active for over 30 days</p>
                                    @endif
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Maintenance Type</th>
                                                <th>Start Date</th>
                                                <th>Days Active</th>
                                                <th>Priority</th>
                                                <th>Business Contact</th>
                                                <th>Estimated Cost</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activeMaintenance as $item)
                                                <tr class="{{ $item['days_in_maintenance'] > 30 ? 'table-warning' : '' }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['maintenance']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['maintenance']->asset->image_path) }}" 
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
                                                                    <a href="{{ route('assets.show', $item['maintenance']->asset->id) }}" class="text-dark">
                                                                        {{ $item['maintenance']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['maintenance']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['maintenance']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['maintenance']->maintenance_type === 'Preventive' ? 'success' : 
                                                            ($item['maintenance']->maintenance_type === 'Corrective' ? 'warning' : 'info') 
                                                        }}">
                                                            {{ $item['maintenance']->maintenance_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $item['maintenance']->maintenance_date->format('M d, Y') }}
                                                        <br><small class="text-muted">{{ $item['maintenance']->maintenance_date->diffForHumans() }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $item['days_in_maintenance'] > 30 ? 'danger' : ($item['days_in_maintenance'] > 14 ? 'warning' : 'light') }} text-{{ $item['days_in_maintenance'] > 30 ? 'white' : ($item['days_in_maintenance'] > 14 ? 'dark' : 'muted') }}">
                                                            {{ $item['days_in_maintenance'] }} days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['priority_level'] === 'Critical' ? 'danger' : 
                                                            ($item['priority_level'] === 'High' ? 'warning' : 
                                                            ($item['priority_level'] === 'Medium' ? 'info' : 'success')) 
                                                        }}">
                                                            {{ $item['priority_level'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->vendor)
                                                            {{ $item['maintenance']->vendor->name }}
                                                        @else
                                                            <span class="text-muted">Internal</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->cost > 0)
                                                            <span class="fw-medium">P {{ number_format($item['maintenance']->cost, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">TBD</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->description)
                                                            <small class="text-muted">{{ Str::limit($item['maintenance']->description, 60) }}</small>
                                                        @else
                                                            <span class="text-muted">No description</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary">In Progress</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                                        <h5>No Active Maintenance</h5>
                                                        <p class="text-muted">All maintenance work has been completed.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Scheduled Maintenance Tab -->
                            <div class="tab-pane" id="scheduled-maintenance" role="tabpanel">
                                @if($healthIndicators['overdue_scheduled_critical'] > 0)
                                <div class="alert alert-danger mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-clock me-2"></i>Overdue Scheduled Maintenance</h6>
                                    <p class="mb-0"><strong>{{ $healthIndicators['overdue_scheduled_critical'] }}</strong> critical maintenance items are overdue and require immediate attention.</p>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Maintenance Type</th>
                                                <th>Scheduled Date</th>
                                                <th>Days Until Due</th>
                                                <th>Urgency</th>
                                                <th>Business Contact</th>
                                                <th>Estimated Cost</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($scheduledMaintenance as $item)
                                                <tr class="{{ $item['is_overdue'] ? 'table-danger' : ($item['urgency_level'] === 'Critical' ? 'table-warning' : '') }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['maintenance']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['maintenance']->asset->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-info text-info rounded">
                                                                        <i class="fas fa-calendar fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['maintenance']->asset->id) }}" class="text-dark">
                                                                        {{ $item['maintenance']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['maintenance']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['maintenance']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['maintenance']->maintenance_type === 'Preventive' ? 'success' : 
                                                            ($item['maintenance']->maintenance_type === 'Corrective' ? 'warning' : 'info') 
                                                        }}">
                                                            {{ $item['maintenance']->maintenance_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $item['maintenance']->maintenance_date->format('M d, Y') }}
                                                        <br><small class="text-muted">{{ $item['maintenance']->maintenance_date->diffForHumans() }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($item['is_overdue'])
                                                            <span class="badge bg-danger">{{ $item['days_until_due'] }} days overdue</span>
                                                        @else
                                                            <span class="badge bg-light text-dark">{{ $item['days_until_due'] }} days</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['urgency_level'] === 'Overdue' ? 'danger' : 
                                                            ($item['urgency_level'] === 'Critical' ? 'warning' : 
                                                            ($item['urgency_level'] === 'High' ? 'info' : 'success')) 
                                                        }}">
                                                            {{ $item['urgency_level'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->vendor)
                                                            {{ $item['maintenance']->vendor->name }}
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->cost > 0)
                                                            <span class="fw-medium">P {{ number_format($item['maintenance']->cost, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">TBD</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['maintenance']->description)
                                                            <small class="text-muted">{{ Str::limit($item['maintenance']->description, 50) }}</small>
                                                        @else
                                                            <span class="text-muted">No description</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-{{ $item['is_overdue'] ? 'danger' : 'primary' }}">
                                                            <i class="fas fa-play me-1"></i> Start
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-calendar-check fa-2x text-muted mb-3"></i>
                                                        <h5>No Scheduled Maintenance</h5>
                                                        <p class="text-muted">No maintenance is currently scheduled.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Asset Analysis Tab -->
                            <div class="tab-pane" id="asset-analysis" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th class="text-center">Total Maintenance</th>
                                                <th class="text-center">Completed</th>
                                                <th class="text-center">Active</th>
                                                <th class="text-end">Total Cost</th>
                                                <th class="text-end">Average Cost</th>
                                                <th>Last Maintenance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assetMaintenanceAnalysis as $analysis)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($analysis['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $analysis['asset']->image_path) }}" 
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
                                                                    <a href="{{ route('assets.show', $analysis['asset']->id) }}" class="text-dark">
                                                                        {{ $analysis['asset']->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $analysis['asset']->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $analysis['asset']->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $analysis['total_maintenance_count'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">{{ $analysis['completed_count'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($analysis['active_count'] > 0)
                                                            <span class="badge bg-warning">{{ $analysis['active_count'] }}</span>
                                                        @else
                                                            <span class="badge bg-light text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-medium">P {{ number_format($analysis['total_cost'], 2) }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">P {{ number_format($analysis['average_cost'], 2) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($analysis['last_maintenance_date'])
                                                            {{ $analysis['last_maintenance_date']->format('M d, Y') }}
                                                            <br><small class="text-muted">{{ $analysis['last_maintenance_date']->diffForHumans() }}</small>
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="fas fa-box fa-2x text-muted mb-3"></i>
                                                        <h5>No Asset Maintenance Data</h5>
                                                        <p class="text-muted">No maintenance records found for any assets.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Business Contact Performance Tab -->
                            <div class="tab-pane" id="vendor-performance" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Business Contact</th>
                                                <th class="text-center">Total Jobs</th>
                                                <th class="text-center">Completed</th>
                                                <th class="text-center">In Progress</th>
                                                <th class="text-end">Total Cost</th>
                                                <th class="text-end">Average Cost</th>
                                                <th>Last Job</th>
                                                <th>Contact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($vendorPerformanceAnalysis as $vendor)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $vendor['vendor']->name }}</h6>
                                                            @if($vendor['vendor']->contact_person)
                                                                <small class="text-muted">{{ $vendor['vendor']->contact_person }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $vendor['total_jobs'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">{{ $vendor['completed_jobs'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($vendor['in_progress_jobs'] > 0)
                                                            <span class="badge bg-warning">{{ $vendor['in_progress_jobs'] }}</span>
                                                        @else
                                                            <span class="badge bg-light text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-medium">P {{ number_format($vendor['total_cost'], 2) }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">P {{ number_format($vendor['average_cost'], 2) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($vendor['last_job_date'])
                                                            {{ $vendor['last_job_date']->format('M d, Y') }}
                                                            <br><small class="text-muted">{{ $vendor['last_job_date']->diffForHumans() }}</small>
                                                        @else
                                                            <span class="text-muted">No jobs</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($vendor['vendor']->phone || $vendor['vendor']->email)
                                                            <small class="text-muted">
                                                                @if($vendor['vendor']->phone)
                                                                    {{ $vendor['vendor']->phone }}<br>
                                                                @endif
                                                                @if($vendor['vendor']->email)
                                                                    {{ $vendor['vendor']->email }}
                                                                @endif
                                                            </small>
                                                        @else
                                                            <span class="text-muted">No contact info</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-store fa-2x text-muted mb-3"></i>
                                                        <h5>No Business Contact Data</h5>
                                                        <p class="text-muted">No business-contact maintenance records found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cost & Trends Tab -->
                            <div class="tab-pane" id="cost-trends" role="tabpanel">
                                <!-- Cost Analysis -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-dollar-sign me-2"></i>Maintenance Cost Analysis
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Metric</th>
                                                                <th class="text-end">Amount</th>
                                                                <th>Details</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Total Maintenance Spend</strong></td>
                                                                <td class="text-end"><span class="fw-bold text-primary">P {{ number_format($costAnalysis['total_spend'], 2) }}</span></td>
                                                                <td><small class="text-muted">Across all maintenance activities</small></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average Cost per Maintenance</td>
                                                                <td class="text-end">P {{ number_format($costAnalysis['average_cost'], 2) }}</td>
                                                                <td><small class="text-muted">Mean cost across all records</small></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Highest Single Cost</td>
                                                                <td class="text-end">P {{ number_format($costAnalysis['highest_cost'], 2) }}</td>
                                                                <td><small class="text-muted">Most expensive maintenance item</small></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Lowest Cost</td>
                                                                <td class="text-end">P {{ number_format($costAnalysis['lowest_cost'], 2) }}</td>
                                                                <td><small class="text-muted">Least expensive maintenance item</small></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Ranges -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-chart-bar me-2"></i>Cost Distribution
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted">Under P500</span>
                                                            <span class="badge bg-success">{{ $costAnalysis['cost_ranges']['under_500'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted">P500 - P2,000</span>
                                                            <span class="badge bg-info">{{ $costAnalysis['cost_ranges']['500_2000'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted">P2,000 - P5,000</span>
                                                            <span class="badge bg-warning">{{ $costAnalysis['cost_ranges']['2000_5000'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted">P5,000 - P10,000</span>
                                                            <span class="badge bg-danger">{{ $costAnalysis['cost_ranges']['5000_10000'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="text-muted">Over P10,000</span>
                                                            <span class="badge bg-dark">{{ $costAnalysis['cost_ranges']['over_10000'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Maintenance Types -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-cogs me-2"></i>Maintenance Types Analysis
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h5 class="text-success">{{ $typeAnalysis['preventive']['count'] }}</h5>
                                                            <p class="text-muted mb-1">Preventive</p>
                                                            <p class="text-muted mb-0 small">P {{ number_format($typeAnalysis['preventive']['cost'], 2) }} ({{ $typeAnalysis['preventive']['percentage'] }}%)</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h5 class="text-warning">{{ $typeAnalysis['corrective']['count'] }}</h5>
                                                            <p class="text-muted mb-1">Corrective</p>
                                                            <p class="text-muted mb-0 small">P {{ number_format($typeAnalysis['corrective']['cost'], 2) }} ({{ $typeAnalysis['corrective']['percentage'] }}%)</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h5 class="text-info">{{ $typeAnalysis['upgrade']['count'] }}</h5>
                                                            <p class="text-muted mb-1">Upgrade</p>
                                                            <p class="text-muted mb-0 small">P {{ number_format($typeAnalysis['upgrade']['cost'], 2) }} ({{ $typeAnalysis['upgrade']['percentage'] }}%)</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly Trends -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-chart-line me-2"></i>Monthly Maintenance Trends (Last 12 Months)
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Month</th>
                                                                <th class="text-center">Total Maintenance</th>
                                                                <th class="text-center">Preventive</th>
                                                                <th class="text-center">Corrective</th>
                                                                <th class="text-end">Total Cost</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($monthlyTrends as $trend)
                                                                <tr>
                                                                    <td>{{ $trend['month'] }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-light text-dark">{{ $trend['total_maintenance'] }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-success">{{ $trend['preventive_count'] }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-warning">{{ $trend['corrective_count'] }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="fw-medium">P {{ number_format($trend['total_cost'], 2) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
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
                    <i class="bx bx-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function saveActiveTab(tabId) {
                localStorage.setItem('assetMaintenanceActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('assetMaintenanceActiveTab');
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
                    .table { font-size: 10px; }
                    body { font-size: 11px; }
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

@extends('layouts.master')
@section('title', 'Assets by Location Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Assets Location Report
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

        <!-- Overall Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md flex-shrink-0 me-3">
                                <span class="avatar-title bg-primary text-white rounded-circle fs-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Locations</p>
                                <h4 class="mb-0">{{ $overallStats['total_locations'] }}</h4>
                                <small class="text-muted">{{ $overallStats['locations_with_assets'] }} with assets</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md flex-shrink-0 me-3">
                                <span class="avatar-title bg-success text-white rounded-circle fs-4">
                                    <i class="fas fa-boxes"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Assets</p>
                                <h4 class="mb-0">{{ number_format($overallStats['total_assets']) }}</h4>
                                <small class="text-muted">Across all locations</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md flex-shrink-0 me-3">
                                <span class="avatar-title bg-info text-white rounded-circle fs-4">
                                    <i class="fas fa-calculator"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Avg per Location</p>
                                <h4 class="mb-0">{{ $overallStats['average_assets_per_location'] }}</h4>
                                <small class="text-muted">Assets per location</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md flex-shrink-0 me-3">
                                <span class="avatar-title bg-warning text-white rounded-circle fs-4">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Utilization Rate</p>
                                <h4 class="mb-0">{{ $overallStats['overall_utilization'] }}%</h4>
                                <small class="text-muted">Assets in use</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Available</p>
                                <h5 class="mb-0 text-success">{{ number_format($overallStats['total_available']) }}</h5>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-success text-success rounded-circle">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Assigned</p>
                                <h5 class="mb-0 text-primary">{{ number_format($overallStats['total_assigned']) }}</h5>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                    <i class="fas fa-user-check"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">In Maintenance</p>
                                <h5 class="mb-0 text-warning">{{ number_format($overallStats['total_maintenance']) }}</h5>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-warning text-warning rounded-circle">
                                    <i class="fas fa-tools"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Disposed</p>
                                <h5 class="mb-0 text-danger">{{ number_format($overallStats['total_disposed']) }}</h5>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-danger text-danger rounded-circle">
                                    <i class="fas fa-trash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Details Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building me-2"></i>Location Breakdown
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Location</th>
                                        <th class="border-0 text-center">Total Assets</th>
                                        <th class="border-0 text-center">Available</th>
                                        <th class="border-0 text-center">Assigned</th>
                                        <th class="border-0 text-center">Maintenance</th>
                                        <th class="border-0 text-end">Total Value</th>
                                        <th class="border-0 text-center">Utilization</th>
                                        <th class="border-0 text-center">Condition Score</th>
                                        <th class="border-0">Top Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locationStats as $stat)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title bg-soft-secondary text-secondary rounded-circle fs-6">
                                                            <i class="fas fa-building"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $stat['location']->name }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $stat['total_assets'] }} assets</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark fs-6">{{ $stat['total_assets'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $stat['available_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $stat['assigned_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $stat['maintenance_count'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($stat['total_purchase_value'], 2) }}</span>
                                                @if($stat['depreciation_percentage'] > 0)
                                                    <br><small class="text-muted">-{{ $stat['depreciation_percentage'] }}%</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-info" 
                                                             style="width: {{ $stat['utilization_rate'] }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $stat['utilization_rate'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ 
                                                    $stat['condition_score'] >= 80 ? 'success' : 
                                                    ($stat['condition_score'] >= 60 ? 'info' : 
                                                    ($stat['condition_score'] >= 40 ? 'warning' : 'danger')) 
                                                }}">{{ $stat['condition_score'] }}%</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $stat['most_common_category'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-map-marker-alt fa-2x text-muted mb-3"></i>
                                                    <h5>No Locations Found</h5>
                                                    <p class="text-muted">No locations with assets are available for reporting.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unassigned Assets Section -->
        @if($unassignedStats && $unassignedStats['total_assets'] > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Assets Without Location
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-warning">{{ $unassignedStats['total_assets'] }}</h4>
                                            <p class="text-muted mb-0">Total Assets</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-success">{{ $unassignedStats['available_count'] }}</h5>
                                            <p class="text-muted mb-0">Available</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-primary">{{ $unassignedStats['assigned_count'] }}</h5>
                                            <p class="text-muted mb-0">Assigned</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-warning">{{ $unassignedStats['maintenance_count'] }}</h5>
                                            <p class="text-muted mb-0">Maintenance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-end">
                                    <h5 class="mb-1">P {{ number_format($unassignedStats['total_purchase_value'], 2) }}</h5>
                                    <p class="text-muted mb-0">Total Value</p>
                                    <div class="mt-2">
                                        <a href="{{ route('assets.index', ['venue_id' => '']) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-eye me-1"></i> View Assets
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Top Performers Section -->
        <div class="row mb-4">
            <div class="col-xl-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-trophy me-2"></i>Top 5 Locations by Asset Count
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($topLocations['most_assets'] as $index => $location)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }} me-3">{{ $index + 1 }}</span>
                                    <div>
                                        <h6 class="mb-1">{{ $location['location']->name }}</h6>
                                        <small class="text-muted">{{ $location['utilization_rate'] }}% utilized</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">{{ $location['total_assets'] }}</h6>
                                    <small class="text-muted">assets</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>Top 5 Locations by Value
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($topLocations['highest_value'] as $index => $location)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }} me-3">{{ $index + 1 }}</span>
                                    <div>
                                        <h6 class="mb-1">{{ $location['location']->name }}</h6>
                                        <small class="text-muted">{{ $location['total_assets'] }} assets</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">P {{ number_format($location['total_purchase_value'], 0) }}</h6>
                                    <small class="text-muted">value</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Best Utilization Rates
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($topLocations['best_utilization'] as $index => $location)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index < 3 ? 'info' : 'secondary' }} me-3">{{ $index + 1 }}</span>
                                    <div>
                                        <h6 class="mb-1">{{ $location['location']->name }}</h6>
                                        <small class="text-muted">{{ $location['total_assets'] }} assets</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">{{ $location['utilization_rate'] }}%</h6>
                                    <small class="text-muted">in use</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>Best Condition Scores
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($topLocations['best_condition'] as $index => $location)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }} me-3">{{ $index + 1 }}</span>
                                    <div>
                                        <h6 class="mb-1">{{ $location['location']->name }}</h6>
                                        <small class="text-muted">{{ $location['total_assets'] }} assets</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">{{ $location['condition_score'] }}%</h6>
                                    <small class="text-muted">condition</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Distribution Analysis -->
        @if($categoryPresence->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sitemap me-2"></i>Category Distribution Across Locations
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Category</th>
                                        <th class="border-0 text-center">Total Assets</th>
                                        <th class="border-0 text-center">Locations</th>
                                        <th class="border-0">Location Distribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryPresence as $category)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-6">
                                                            <i class="fas fa-tag"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="mb-0">{{ $category['name'] }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $category['total_assets'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $category['total_locations'] }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach(collect($category['locations'])->take(5) as $locationInfo)
                                                        <span class="badge bg-light text-dark small">
                                                            {{ $locationInfo['location'] }} ({{ $locationInfo['count'] }})
                                                        </span>
                                                    @endforeach
                                                    @if(count($category['locations']) > 5)
                                                        <span class="badge bg-secondary small">
                                                            +{{ count($category['locations']) - 5 }} more
                                                        </span>
                                                    @endif
                                                </div>
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
        @endif
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                .btn, .dropdown, [onclick*="print"] { display: none !important; }
                .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
                .table { font-size: 11px; }
                .progress { background-color: #e9ecef !important; }
                body { font-size: 12px; }
                .avatar-md, .avatar-sm, .avatar-xs { display: none; }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection
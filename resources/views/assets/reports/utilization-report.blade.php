@extends('layouts.master')
@section('title', 'Asset Utilization Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Utilization Report
        @endslot
    @endcomponent

    @if(session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <div class="d-flex gap-2">
                    <a href="#" onclick="window.print(0)" class="text-muted"> <i class="bx bx-printer me-1 font-size-18"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-4">
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Assets</p>
                            <h4 class="mb-0">{{ $overallUtilization['total_assets'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-soft-success text-success rounded-circle fs-4">
                                <i class="bx bx-user-check"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Currently Assigned</p>
                            <h4 class="mb-0">{{ $overallUtilization['currently_assigned'] }}</h4>
                            <small class="text-success">{{ $overallUtilization['overall_assignment_percentage'] }}% of total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-soft-info text-info rounded-circle fs-4">
                                <i class="bx bx-bar-chart-alt-2"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Average Utilization</p>
                            <h4 class="mb-0">{{ $overallUtilization['average_utilization_rate'] }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-soft-warning text-warning rounded-circle fs-4">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Available Assets</p>
                            <h4 class="mb-0">{{ $overallUtilization['currently_available'] }}</h4>
                            <small class="text-warning">{{ 100 - $overallUtilization['overall_assignment_percentage'] }}% available</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Utilization Distribution -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Utilization Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Excellent (80%+)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['excellent'] }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-info rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Good (60-79%)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['good'] }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-warning rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Moderate (40-59%)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['moderate'] }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-secondary rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Low (20-39%)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['low'] }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-danger rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Poor (1-19%)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['poor'] }}</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-dark rounded me-2" style="width: 12px; height: 12px;"></div>
                                <span class="text-muted">Unused (0%)</span>
                            </div>
                            <h5 class="mb-0">{{ $utilizationRanges['unused'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Utilization Categories</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-success text-success rounded-circle">
                                        <i class="bx bx-trending-up"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $overallUtilization['high_utilization_assets'] }}</h5>
                                <p class="text-muted mb-0">High Usage</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-info text-info rounded-circle">
                                        <i class="bx bx-trending-up"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $overallUtilization['medium_utilization_assets'] }}</h5>
                                <p class="text-muted mb-0">Medium Usage</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-warning text-warning rounded-circle">
                                        <i class="bx bx-trending-down"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $overallUtilization['low_utilization_assets'] }}</h5>
                                <p class="text-muted mb-0">Low Usage</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-danger text-danger rounded-circle">
                                        <i class="bx bx-pause"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $overallUtilization['idle_assets'] }}</h5>
                                <p class="text-muted mb-0">Idle Assets</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#category-analysis" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-category"></i></span>
                                <span class="d-none d-sm-block">Category Analysis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#individual-assets" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-package"></i></span>
                                <span class="d-none d-sm-block">Individual Assets</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#high-utilization" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-trending-up"></i></span>
                                <span class="d-none d-sm-block">High Utilization</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#idle-assets" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-time-five"></i></span>
                                <span class="d-none d-sm-block">Long Idle Assets</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- Category Analysis Tab -->
                        <div class="tab-pane active" id="category-analysis" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Total Assets</th>
                                            <th>Currently Assigned</th>
                                            <th>Available</th>
                                            <th>Assignment Rate</th>
                                            <th>Utilization Rate</th>
                                            <th>Avg Assignments/Asset</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categoryUtilization as $category)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                <i class="bx bx-category-alt"></i>
                                                            </span>
                                                        </div>
                                                        <span class="fw-medium">{{ $category['category_name'] }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $category['total_assets'] }}</td>
                                                <td>
                                                    <span class="badge bg-success">{{ $category['currently_assigned'] }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $category['currently_available'] }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar bg-primary" 
                                                                 style="width: {{ $category['assigned_percentage'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted">{{ $category['assigned_percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                @if($category['category_utilization_rate'] >= 80) bg-success
                                                                @elseif($category['category_utilization_rate'] >= 60) bg-info
                                                                @elseif($category['category_utilization_rate'] >= 40) bg-warning
                                                                @else bg-danger
                                                                @endif" 
                                                                 style="width: {{ $category['category_utilization_rate'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted">{{ $category['category_utilization_rate'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>{{ $category['average_assignments_per_asset'] }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($category['utilization_category'] === 'High') bg-success
                                                        @elseif($category['utilization_category'] === 'Medium') bg-info
                                                        @elseif($category['utilization_category'] === 'Low') bg-warning
                                                        @else bg-danger
                                                        @endif">
                                                        {{ $category['utilization_category'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No category data available</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Individual Assets Tab -->
                        <div class="tab-pane" id="individual-assets" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Category</th>
                                            <th>Days Since Creation</th>
                                            <th>Days Assigned</th>
                                            <th>Days Idle</th>
                                            <th>Utilization Rate</th>
                                            <th>Current Status</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($individualUtilization->take(50) as $assetData)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($assetData['asset']->image_path)
                                                            <img src="{{ asset('storage/' . $assetData['asset']->image_path) }}" 
                                                                 alt="" class="rounded me-2" height="32">
                                                        @else
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                    <i class="bx bx-package"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <a href="{{ route('assets.show', $assetData['asset']->id) }}" 
                                                                   class="text-dark">{{ $assetData['asset']->name }}</a>
                                                            </h6>
                                                            <small class="text-muted">{{ $assetData['asset']->asset_code }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $assetData['asset']->category->name ?? 'Uncategorized' }}</td>
                                                <td>{{ $assetData['total_days_since_creation'] }}</td>
                                                <td>{{ $assetData['total_days_assigned'] }}</td>
                                                <td>{{ $assetData['total_days_idle'] }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                @if($assetData['utilization_rate'] >= 80) bg-success
                                                                @elseif($assetData['utilization_rate'] >= 60) bg-info
                                                                @elseif($assetData['utilization_rate'] >= 40) bg-warning
                                                                @else bg-danger
                                                                @endif" 
                                                                 style="width: {{ $assetData['utilization_rate'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted">{{ $assetData['utilization_rate'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($assetData['is_currently_assigned'])
                                                        <span class="badge bg-success">Assigned</span>
                                                    @else
                                                        <span class="badge bg-warning">Available</span>
                                                        @if($assetData['current_idle_days'] > 0)
                                                            <br><small class="text-muted">{{ $assetData['current_idle_days'] }} days idle</small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        @if($assetData['utilization_category'] === 'High') bg-success
                                                        @elseif($assetData['utilization_category'] === 'Medium') bg-info
                                                        @elseif($assetData['utilization_category'] === 'Low') bg-warning
                                                        @else bg-danger
                                                        @endif">
                                                        {{ $assetData['utilization_category'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No asset data available</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($individualUtilization->count() > 50)
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Showing top 50 assets. Export for complete data.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- High Utilization Tab -->
                        <div class="tab-pane" id="high-utilization" role="tabpanel">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">High Utilization Assets (80%+ Usage)</h6>
                                <p class="mb-0">These assets are heavily used and may need attention for maintenance or potential replacement planning.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Category</th>
                                            <th>Utilization Rate</th>
                                            <th>Total Assignments</th>
                                            <th>Current Status</th>
                                            <th>Action Needed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($highUtilizationAssets as $assetData)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($assetData['asset']->image_path)
                                                            <img src="{{ asset('storage/' . $assetData['asset']->image_path) }}" 
                                                                 alt="" class="rounded me-2" height="32">
                                                        @else
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-success text-success rounded">
                                                                    <i class="bx bx-package"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <a href="{{ route('assets.show', $assetData['asset']->id) }}" 
                                                                   class="text-dark">{{ $assetData['asset']->name }}</a>
                                                            </h6>
                                                            <small class="text-muted">{{ $assetData['asset']->asset_code }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $assetData['asset']->category->name ?? 'Uncategorized' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ $assetData['utilization_rate'] }}%"></div>
                                                        </div>
                                                        <span class="fw-medium text-success">{{ $assetData['utilization_rate'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>{{ $assetData['total_assignments'] }}</td>
                                                <td>
                                                    @if($assetData['is_currently_assigned'])
                                                        <span class="badge bg-success">Currently Assigned</span>
                                                    @else
                                                        <span class="badge bg-info">Available</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($assetData['utilization_rate'] >= 90)
                                                        <span class="badge bg-danger">Consider Replacement</span>
                                                    @else
                                                        <span class="badge bg-warning">Monitor Condition</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-smile fs-1 mb-2"></i>
                                                        <p>No high utilization assets found</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Long Idle Assets Tab -->
                        <div class="tab-pane" id="idle-assets" role="tabpanel">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">Long Idle Assets (90+ Days Unused)</h6>
                                <p class="mb-0">These assets haven't been assigned for extended periods and may need review for reallocation or disposal.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Category</th>
                                            <th>Days Idle</th>
                                            <th>Utilization Rate</th>
                                            <th>Last Assignment</th>
                                            <th>Recommendation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($longIdleAssets as $assetData)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($assetData['asset']->image_path)
                                                            <img src="{{ asset('storage/' . $assetData['asset']->image_path) }}" 
                                                                 alt="" class="rounded me-2" height="32">
                                                        @else
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-warning text-warning rounded">
                                                                    <i class="bx bx-package"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <a href="{{ route('assets.show', $assetData['asset']->id) }}" 
                                                                   class="text-dark">{{ $assetData['asset']->name }}</a>
                                                            </h6>
                                                            <small class="text-muted">{{ $assetData['asset']->asset_code }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $assetData['asset']->category->name ?? 'Uncategorized' }}</td>
                                                <td>
                                                    <span class="badge bg-danger">{{ $assetData['current_idle_days'] }} days</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar bg-danger" 
                                                                 style="width: {{ max($assetData['utilization_rate'], 5) }}%"></div>
                                                        </div>
                                                        <span class="text-muted">{{ $assetData['utilization_rate'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $lastAssignment = $assetData['asset']->assignments->where('status', 'Returned')->first();
                                                    @endphp
                                                    @if($lastAssignment && $lastAssignment->actual_return_date)
                                                        {{ $lastAssignment->actual_return_date->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Never assigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($assetData['current_idle_days'] > 365)
                                                        <span class="badge bg-danger">Consider Disposal</span>
                                                    @elseif($assetData['current_idle_days'] > 180)
                                                        <span class="badge bg-warning">Review Need</span>
                                                    @else
                                                        <span class="badge bg-info">Monitor Usage</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-check-circle fs-1 mb-2"></i>
                                                        <p>No long idle assets found</p>
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
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function saveActiveTab(tabId) {
                localStorage.setItem('utilizationReportActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('utilizationReportActiveTab');
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
        });
    </script>
@endsection
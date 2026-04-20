@extends('layouts.master')
@section('title', 'Assets by Category Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Assets by Category Report
        @endslot
    @endcomponent

    <div class="container-fluid">
        <!-- Report Header -->
        <div class="row mb-4">
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
                                    <i class="fas fa-tags"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Categories</p>
                                <h4 class="mb-0">{{ $overallStats['total_categories'] }}</h4>
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
                                    <i class="fas fa-box"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Assets</p>
                                <h4 class="mb-0">{{ number_format($overallStats['total_assets']) }}</h4>
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
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-0">P {{ number_format($overallStats['total_current_value'], 2) }}</h4>
                                <small class="text-muted">Current Value</small>
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
                                <h4 class="mb-0">{{ $overallStats['overall_utilization_rate'] }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Overview Cards -->
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

        <!-- Category Details Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Category Breakdown
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Category</th>
                                        <th class="border-0 text-center">Total Assets</th>
                                        <th class="border-0 text-center">Available</th>
                                        <th class="border-0 text-center">Assigned</th>
                                        <th class="border-0 text-center">Maintenance</th>
                                        <th class="border-0 text-center">Disposed</th>
                                        <th class="border-0 text-end">Purchase Value</th>
                                        <th class="border-0 text-end">Current Value</th>
                                        <th class="border-0 text-center">Utilization</th>
                                        <th class="border-0 text-center">Avg Age</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryStats as $stat)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-6">
                                                            <i class="fas fa-tag"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $stat['category']->name }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $stat['category']->code }}</p>
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
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $stat['disposed_count'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($stat['total_purchase_value'], 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($stat['total_current_value'], 2) }}</span>
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
                                                <span class="text-muted">{{ round($stat['average_asset_age']) }} months</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-folder-open fa-2x text-muted mb-3"></i>
                                                    <h5>No Categories Found</h5>
                                                    <p class="text-muted">No asset categories are available for reporting.</p>
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

        @if($categoryStats->isNotEmpty())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Asset Condition Analysis by Category
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($categoryStats as $stat)
                                <div class="col-xl-6 col-lg-6 mb-4">
                                    <div class="card border shadow-none">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $stat['category']->name }}</h6>
                                            <div class="row text-center">
                                                <div class="col-3">
                                                    <p class="text-muted mb-1 small">New</p>
                                                    <h6 class="text-success mb-0">{{ $stat['condition_breakdown']['new'] }}</h6>
                                                </div>
                                                <div class="col-3">
                                                    <p class="text-muted mb-1 small">Good</p>
                                                    <h6 class="text-info mb-0">{{ $stat['condition_breakdown']['good'] }}</h6>
                                                </div>
                                                <div class="col-3">
                                                    <p class="text-muted mb-1 small">Fair</p>
                                                    <h6 class="text-warning mb-0">{{ $stat['condition_breakdown']['fair'] }}</h6>
                                                </div>
                                                <div class="col-3">
                                                    <p class="text-muted mb-1 small">Poor</p>
                                                    <h6 class="text-danger mb-0">{{ $stat['condition_breakdown']['poor'] }}</h6>
                                                </div>
                                            </div>
                                            
                                            <!-- Progress bars for visual representation -->
                                            <div class="mt-3">
                                                @php
                                                    $total = $stat['total_assets'];
                                                    $conditions = $stat['condition_breakdown'];
                                                @endphp
                                                @if($total > 0)
                                                    <div class="progress" style="height: 8px;">
                                                        @if($conditions['new'] > 0)
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ ($conditions['new'] / $total) * 100 }}%"
                                                                 title="New: {{ $conditions['new'] }}"></div>
                                                        @endif
                                                        @if($conditions['good'] > 0)
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: {{ ($conditions['good'] / $total) * 100 }}%"
                                                                 title="Good: {{ $conditions['good'] }}"></div>
                                                        @endif
                                                        @if($conditions['fair'] > 0)
                                                            <div class="progress-bar bg-warning" 
                                                                 style="width: {{ ($conditions['fair'] / $total) * 100 }}%"
                                                                 title="Fair: {{ $conditions['fair'] }}"></div>
                                                        @endif
                                                        @if($conditions['poor'] > 0)
                                                            <div class="progress-bar bg-danger" 
                                                                 style="width: {{ ($conditions['poor'] / $total) * 100 }}%"
                                                                 title="Poor: {{ $conditions['poor'] }}"></div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                    .table { font-size: 12px; }
                    .progress { background-color: #e9ecef !important; }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endsection
@extends('layouts.master')
@section('title', 'Asset Value Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Value Report
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

        <!-- Overall Financial Statistics -->
        <div class="row mb-2">
            <div class="col-xl-3 col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md flex-shrink-0 me-3">
                                <span class="avatar-title bg-primary text-white rounded-circle">
                                    <i class="fas fa-boxes"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-small mb-1">Valued Assets</p>
                                <h4 class="mb-0">{{ number_format($overallStats['total_assets_with_value']) }}</h4>
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
                                    <i class="fas fa-shopping-cart"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Investment</p>
                                <h4 class="mb-0">P {{ number_format($overallStats['total_purchase_value'], 2) }}</h4>
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
                                    <i class="fas fa-chart-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Current Value</p>
                                <h4 class="mb-0">P {{ number_format($overallStats['total_current_value'], 2) }}</h4>
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
                                <span class="avatar-title bg-{{ $overallStats['depreciation_percentage'] > 20 ? 'danger' : ($overallStats['depreciation_percentage'] > 10 ? 'warning' : 'secondary') }} text-white rounded-circle fs-4">
                                    <i class="fas fa-chart-line-down"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Depreciation</p>
                                <h4 class="mb-0">{{ $overallStats['depreciation_percentage'] }}%</h4>
                                <small class="text-muted">P {{ number_format($overallStats['total_depreciation'], 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Value Cards -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Average Purchase Value</h6>
                                <h4 class="mb-0 text-primary">P {{ number_format($overallStats['average_asset_value'], 2) }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                    <i class="fas fa-calculator"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Average Current Value</h6>
                                <h4 class="mb-0 text-info">P {{ number_format($overallStats['average_current_value'], 2) }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-info text-info rounded-circle">
                                    <i class="fas fa-balance-scale"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Value Distribution by Category -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i>Value Distribution by Category
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Category</th>
                                        <th class="border-0 text-center">Assets</th>
                                        <th class="border-0 text-end">Purchase Value</th>
                                        <th class="border-0 text-end">Current Value</th>
                                        <th class="border-0 text-end">Depreciation</th>
                                        <th class="border-0 text-end">Avg Value</th>
                                        <th class="border-0 text-center">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryValues as $categoryValue)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-6">
                                                            <i class="fas fa-tag"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $categoryValue['category']->name }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $categoryValue['category']->code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $categoryValue['asset_count'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($categoryValue['total_purchase_value'], 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($categoryValue['total_current_value'], 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-{{ $categoryValue['depreciation_percentage'] > 20 ? 'danger' : ($categoryValue['depreciation_percentage'] > 10 ? 'warning' : 'muted') }}">
                                                    {{ $categoryValue['depreciation_percentage'] }}%
                                                </span>
                                                <br>
                                                <small class="text-muted">P {{ number_format($categoryValue['depreciation'], 2) }}</small>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-muted">P {{ number_format($categoryValue['average_value'], 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-primary" 
                                                             style="width: {{ $categoryValue['percentage_of_total'] }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $categoryValue['percentage_of_total'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-folder-open fa-2x text-muted mb-3"></i>
                                                <h5>No Categories Found</h5>
                                                <p class="text-muted">No asset categories with values are available.</p>
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

        <!-- High-Value Assets -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-crown me-2"></i>Top 10 High-Value Assets
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">#</th>
                                        <th class="border-0">Asset</th>
                                        <th class="border-0">Category</th>
                                        <th class="border-0">Location</th>
                                        <th class="border-0 text-end">Purchase Value</th>
                                        <th class="border-0 text-end">Current Value</th>
                                        <th class="border-0 text-center">Status</th>
                                        <th class="border-0 text-center">Condition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($highValueAssets as $index => $asset)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($asset->image_path)
                                                        <img src="{{ URL::asset($asset->image_path) }}" 
                                                             alt="" class="rounded me-3" height="40" width="40" style="object-fit: cover;">
                                                    @else
                                                        <div class="avatar-sm me-3">
                                                            <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                <i class="fas fa-box"></i>
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a href="{{ route('assets.show', $asset->id) }}" class="text-dark">
                                                                {{ $asset->name }}
                                                            </a>
                                                        </h6>
                                                        <p class="text-muted mb-0 small">{{ $asset->asset_code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $asset->category->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $asset->venue->name ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($asset->purchase_price, 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($asset->current_value ?: $asset->purchase_price, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ 
                                                    $asset->status == 'Available' ? 'success' : 
                                                    ($asset->status == 'Assigned' ? 'primary' : 
                                                    ($asset->status == 'In Maintenance' ? 'warning' : 'danger')) 
                                                }}">
                                                    {{ $asset->status }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ 
                                                    $asset->condition == 'New' ? 'success' : 
                                                    ($asset->condition == 'Good' ? 'info' : 
                                                    ($asset->condition == 'Fair' ? 'warning' : 'danger')) 
                                                }}">
                                                    {{ $asset->condition }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-gem fa-2x text-muted mb-3"></i>
                                                <h5>No High-Value Assets</h5>
                                                <p class="text-muted">No assets with purchase values found.</p>
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

        <!-- Value Analysis by Status and Condition -->
        <div class="row mb-2">
            <!-- Value by Status -->
            <div class="col-lg-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Value Distribution by Status
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($statusStats as $statusStat)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-3">
                                        <span class="avatar-title bg-soft-{{ 
                                            $statusStat['status'] == 'Available' ? 'success' : 
                                            ($statusStat['status'] == 'Assigned' ? 'primary' : 
                                            ($statusStat['status'] == 'Maintenance' ? 'warning' : 'danger')) 
                                        }} text-{{ 
                                            $statusStat['status'] == 'Available' ? 'success' : 
                                            ($statusStat['status'] == 'Assigned' ? 'primary' : 
                                            ($statusStat['status'] == 'Maintenance' ? 'warning' : 'danger')) 
                                        }} rounded-circle fs-6">
                                            <i class="fas fa-circle"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $statusStat['status'] }}</h6>
                                        <small class="text-muted">{{ $statusStat['count'] }} assets</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">P {{ number_format($statusStat['total_purchase_value'], 2) }}</h6>
                                    <small class="text-muted">{{ $statusStat['percentage_of_total'] }}%</small>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-{{ 
                                    $statusStat['status'] == 'Available' ? 'success' : 
                                    ($statusStat['status'] == 'Assigned' ? 'primary' : 
                                    ($statusStat['status'] == 'Maintenance' ? 'warning' : 'danger')) 
                                }}" style="width: {{ $statusStat['percentage_of_total'] }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Value by Condition -->
            <div class="col-lg-6">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Value Distribution by Condition
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($conditionStats as $conditionStat)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-3">
                                        <span class="avatar-title bg-soft-{{ 
                                            $conditionStat['condition'] == 'New' ? 'success' : 
                                            ($conditionStat['condition'] == 'Good' ? 'info' : 
                                            ($conditionStat['condition'] == 'Fair' ? 'warning' : 'danger')) 
                                        }} text-{{ 
                                            $conditionStat['condition'] == 'New' ? 'success' : 
                                            ($conditionStat['condition'] == 'Good' ? 'info' : 
                                            ($conditionStat['condition'] == 'Fair' ? 'warning' : 'danger')) 
                                        }} rounded-circle fs-6">
                                            <i class="fas fa-circle"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $conditionStat['condition'] }}</h6>
                                        <small class="text-muted">{{ $conditionStat['count'] }} assets</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-1">P {{ number_format($conditionStat['total_purchase_value'], 2) }}</h6>
                                    <small class="text-muted">{{ $conditionStat['percentage_of_total'] }}%</small>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-{{ 
                                    $conditionStat['condition'] == 'New' ? 'success' : 
                                    ($conditionStat['condition'] == 'Good' ? 'info' : 
                                    ($conditionStat['condition'] == 'Fair' ? 'warning' : 'danger')) 
                                }}" style="width: {{ $conditionStat['percentage_of_total'] }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Age-Based Depreciation Analysis -->
        @if($ageStats->sum('count') > 0)
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Age-Based Depreciation Analysis
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Age Group</th>
                                        <th class="border-0 text-center">Assets</th>
                                        <th class="border-0 text-end">Purchase Value</th>
                                        <th class="border-0 text-end">Current Value</th>
                                        <th class="border-0 text-end">Depreciation</th>
                                        <th class="border-0 text-center">% of Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ageStats as $ageStat)
                                        @if($ageStat['count'] > 0)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-3">
                                                            <span class="avatar-title bg-soft-{{ 
                                                                $ageStat['age_group'] == 'new' ? 'success' : 
                                                                ($ageStat['age_group'] == 'recent' ? 'info' : 
                                                                ($ageStat['age_group'] == 'mature' ? 'warning' : 'danger')) 
                                                            }} text-{{ 
                                                                $ageStat['age_group'] == 'new' ? 'success' : 
                                                                ($ageStat['age_group'] == 'recent' ? 'info' : 
                                                                ($ageStat['age_group'] == 'mature' ? 'warning' : 'danger')) 
                                                            }} rounded-circle fs-6">
                                                                <i class="fas fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                @if($ageStat['age_group'] == 'new')
                                                                    New (0-12 months)
                                                                @elseif($ageStat['age_group'] == 'recent')
                                                                    Recent (1-3 years)
                                                                @elseif($ageStat['age_group'] == 'mature')
                                                                    Mature (3-5 years)
                                                                @else
                                                                    Old (5+ years)
                                                                @endif
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark">{{ $ageStat['count'] }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-medium">P {{ number_format($ageStat['total_purchase_value'], 2) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-medium">P {{ number_format($ageStat['total_current_value'], 2) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-{{ $ageStat['depreciation_percentage'] > 30 ? 'danger' : ($ageStat['depreciation_percentage'] > 15 ? 'warning' : 'muted') }}">
                                                        {{ $ageStat['depreciation_percentage'] }}%
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">P {{ number_format($ageStat['depreciation'], 2) }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="progress me-2" style="width: 60px; height: 8px;">
                                                            <div class="progress-bar bg-{{ 
                                                                $ageStat['age_group'] == 'new' ? 'success' : 
                                                                ($ageStat['age_group'] == 'recent' ? 'info' : 
                                                                ($ageStat['age_group'] == 'mature' ? 'warning' : 'danger')) 
                                                            }}" style="width: {{ $ageStat['percentage_of_total'] }}%"></div>
                                                        </div>
                                                        <span class="small">{{ $ageStat['percentage_of_total'] }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Value Distribution by Location -->
        @if($venueValues->isNotEmpty())
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Value Distribution by Location
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Location</th>
                                        <th class="border-0 text-center">Assets</th>
                                        <th class="border-0 text-end">Purchase Value</th>
                                        <th class="border-0 text-end">Current Value</th>
                                        <th class="border-0 text-end">Depreciation</th>
                                        <th class="border-0 text-center">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($venueValues as $venueValue)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <span class="avatar-title bg-soft-secondary text-secondary rounded-circle fs-6">
                                                            <i class="fas fa-building"></i>
                                                        </span>
                                                    </div>
                                                    <h6 class="mb-0">{{ $venueValue['venue']->name }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $venueValue['asset_count'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($venueValue['total_purchase_value'], 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium">P {{ number_format($venueValue['total_current_value'], 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-muted">P {{ number_format($venueValue['depreciation'], 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-secondary" 
                                                             style="width: {{ $venueValue['percentage_of_total'] }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $venueValue['percentage_of_total'] }}%</span>
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
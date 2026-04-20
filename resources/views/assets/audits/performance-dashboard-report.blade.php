@extends('layouts.master')
@section('title', 'Audit Performance Dashboard')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('audits.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Audits
        @endslot
        @slot('title')
            Performance Analytics Dashboard
        @endslot
    @endcomponent
    @section('css')
    <style>
        @media print {
            .btn, .dropdown, .breadcrumb, .alert {
                display: none !important;
            }
            
            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
            
            .row {
                break-inside: avoid;
            }
            
            .table {
                font-size: 0.8rem;
            }
            
            .badge {
                border: 1px solid #000;
            }
            
            .avatar-title {
                background-color: #f8f9fa !important;
                color: #000 !important;
            }
        }
        
        .card-header.bg-warning,
        .card-header.bg-info {
            background-color: var(--bs-warning) !important;
        }
        
        .progress {
            background-color: #e9ecef;
        }
        
        .avatar-title {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
    </style>
    @endsection

    @if(session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Dashboard Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="btn-group d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-primary" onclick="refreshDashboard()">
                                <i class="bx bx-refresh me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('audits.performance-dashboard') }}" id="dashboardForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Time Period</label>
                                <select class="form-select form-select-sm" name="period" onchange="this.form.submit()">
                                    <option value="1_month" {{ $period === '1_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="3_months" {{ $period === '3_months' ? 'selected' : '' }}>Last 3 Months</option>
                                    <option value="6_months" {{ $period === '6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                    <option value="1_year" {{ $period === '1_year' ? 'selected' : '' }}>Last Year</option>
                                    <option value="2_years" {{ $period === '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                                    <option value="all_time" {{ $period === 'all_time' ? 'selected' : '' }}>All Time</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Filter by Category</label>
                                <select class="form-select form-select-sm" name="category_id" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Filter by Location</label>
                                <select class="form-select form-select-sm" name="venue_id" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-primary text-white rounded-circle">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Audits</p>
                            <h4 class="mb-0">{{ $audits->count() }}</h4>
                            @if(isset($dashboardData['audit_trend']))
                                <small class="text-{{ $dashboardData['audit_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="bx bx-{{ $dashboardData['audit_trend'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                                    {{ $dashboardData['audit_trend'] >= 0 ? '+' : '' }}{{ $dashboardData['audit_trend'] }}%
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-success text-white rounded-circle">
                                <i class="bx bx-target-lock"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Average Accuracy</p>
                            <h4 class="mb-0">
                                @if($audits->count() > 0)
                                    {{ number_format($audits->avg(function($audit) { 
                                        return $audit->auditItems->count() > 0 ? 
                                            ($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100 : 0; 
                                    }), 1) }}%
                                @else
                                    0%
                                @endif
                            </h4>
                            @if(isset($dashboardData['accuracy_trend']))
                                <small class="text-{{ $dashboardData['accuracy_trend'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="bx bx-{{ $dashboardData['accuracy_trend'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                                    {{ $dashboardData['accuracy_trend'] >= 0 ? '+' : '' }}{{ number_format($dashboardData['accuracy_trend'], 1) }}%
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-danger text-white rounded-circle">
                                <i class="bx bx-error-alt"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Missing Assets</p>
                            <h4 class="mb-0">{{ $audits->sum(function($audit) { return $audit->auditItems->where('is_present', false)->count(); }) }}</h4>
                            @if(isset($dashboardData['missing_trend']))
                                <small class="text-{{ $dashboardData['missing_trend'] <= 0 ? 'success' : 'danger' }}">
                                    <i class="bx bx-{{ $dashboardData['missing_trend'] <= 0 ? 'trending-down' : 'trending-up' }}"></i>
                                    {{ $dashboardData['missing_trend'] >= 0 ? '+' : '' }}{{ $dashboardData['missing_trend'] }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-warning text-white rounded-circle">
                                <i class="bx bx-wrench"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Need Maintenance</p>
                            <h4 class="mb-0">{{ $audits->sum(function($audit) { return $audit->auditItems->where('needs_maintenance', true)->count(); }) }}</h4>
                            @if(isset($dashboardData['maintenance_trend']))
                                <small class="text-{{ $dashboardData['maintenance_trend'] <= 0 ? 'success' : 'warning' }}">
                                    <i class="bx bx-{{ $dashboardData['maintenance_trend'] <= 0 ? 'trending-down' : 'trending-up' }}"></i>
                                    {{ $dashboardData['maintenance_trend'] >= 0 ? '+' : '' }}{{ $dashboardData['maintenance_trend'] }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-line-chart me-2"></i>Audit Performance Trends
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <!-- Performance Chart Placeholder -->
                            <div id="performanceChart" style="min-height: 300px;">
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <div class="text-center">
                                        <div class="avatar-lg mx-auto mb-4">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="bx bx-line-chart font-size-24"></i>
                                            </div>
                                        </div>
                                        <h5>Performance Trend Chart</h5>
                                        <p class="text-muted">Chart showing audit accuracy, missing assets, and maintenance needs over time</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-pie-chart me-2"></i>Asset Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalItems = $audits->sum(function($audit) { return $audit->auditItems->count(); });
                        $presentItems = $audits->sum(function($audit) { return $audit->auditItems->where('is_present', true)->count(); });
                        $missingItems = $audits->sum(function($audit) { return $audit->auditItems->where('is_present', false)->count(); });
                        $maintenanceItems = $audits->sum(function($audit) { return $audit->auditItems->where('needs_maintenance', true)->count(); });
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-success">Present Assets</span>
                            <span class="fw-bold">{{ $presentItems }} ({{ $totalItems > 0 ? number_format(($presentItems / $totalItems) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $totalItems > 0 ? ($presentItems / $totalItems) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger">Missing Assets</span>
                            <span class="fw-bold">{{ $missingItems }} ({{ $totalItems > 0 ? number_format(($missingItems / $totalItems) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: {{ $totalItems > 0 ? ($missingItems / $totalItems) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-warning">Need Maintenance</span>
                            <span class="fw-bold">{{ $maintenanceItems }} ({{ $totalItems > 0 ? number_format(($maintenanceItems / $totalItems) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalItems > 0 ? ($maintenanceItems / $totalItems) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Performance Tables -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-category me-2"></i>Performance by Category
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $categoryPerformance = collect();
                        foreach($audits as $audit) {
                            foreach($audit->auditItems->groupBy('asset.category.name') as $categoryName => $items) {
                                $categoryName = $categoryName ?: 'Uncategorized';
                                $existing = $categoryPerformance->where('name', $categoryName)->first();
                                if ($existing) {
                                    $existing['total'] += $items->count();
                                    $existing['present'] += $items->where('is_present', true)->count();
                                    $existing['missing'] += $items->where('is_present', false)->count();
                                } else {
                                    $categoryPerformance->push([
                                        'name' => $categoryName,
                                        'total' => $items->count(),
                                        'present' => $items->where('is_present', true)->count(),
                                        'missing' => $items->where('is_present', false)->count(),
                                    ]);
                                }
                            }
                        }
                        $categoryPerformance = $categoryPerformance->sortByDesc('total');
                    @endphp
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Accuracy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categoryPerformance->take(10) as $category)
                                    <tr>
                                        <td>{{ $category['name'] }}</td>
                                        <td class="text-center">{{ $category['total'] }}</td>
                                        <td class="text-center text-success">{{ $category['present'] }}</td>
                                        <td class="text-center">
                                            @php $accuracy = $category['total'] > 0 ? ($category['present'] / $category['total']) * 100 : 0; @endphp
                                            <span class="badge bg-{{ $accuracy >= 95 ? 'success' : ($accuracy >= 85 ? 'warning' : 'danger') }}">
                                                {{ number_format($accuracy, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-map me-2"></i>Performance by Location
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $locationPerformance = collect();
                        foreach($audits as $audit) {
                            foreach($audit->auditItems->groupBy('asset.venue.name') as $locationName => $items) {
                                $locationName = $locationName ?: 'Unassigned';
                                $existing = $locationPerformance->where('name', $locationName)->first();
                                if ($existing) {
                                    $existing['total'] += $items->count();
                                    $existing['present'] += $items->where('is_present', true)->count();
                                    $existing['missing'] += $items->where('is_present', false)->count();
                                } else {
                                    $locationPerformance->push([
                                        'name' => $locationName,
                                        'total' => $items->count(),
                                        'present' => $items->where('is_present', true)->count(),
                                        'missing' => $items->where('is_present', false)->count(),
                                    ]);
                                }
                            }
                        }
                        $locationPerformance = $locationPerformance->sortByDesc('total');
                    @endphp
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Location</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Accuracy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locationPerformance->take(10) as $location)
                                    <tr>
                                        <td>{{ $location['name'] }}</td>
                                        <td class="text-center">{{ $location['total'] }}</td>
                                        <td class="text-center text-success">{{ $location['present'] }}</td>
                                        <td class="text-center">
                                            @php $accuracy = $location['total'] > 0 ? ($location['present'] / $location['total']) * 100 : 0; @endphp
                                            <span class="badge bg-{{ $accuracy >= 95 ? 'success' : ($accuracy >= 85 ? 'warning' : 'danger') }}">
                                                {{ number_format($accuracy, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Audit Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-2"></i>Recent Audit Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Audit Code</th>
                                    <th>Date</th>
                                    <th>Conducted By</th>
                                    <th class="text-center">Total Items</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Missing</th>
                                    <th class="text-center">Need Maintenance</th>
                                    <th class="text-center">Accuracy</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($audits->take(10) as $audit)
                                    @php
                                        $totalItems = $audit->auditItems->count();
                                        $presentItems = $audit->auditItems->where('is_present', true)->count();
                                        $missingItems = $audit->auditItems->where('is_present', false)->count();
                                        $maintenanceItems = $audit->auditItems->where('needs_maintenance', true)->count();
                                        $accuracy = $totalItems > 0 ? ($presentItems / $totalItems) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('audits.show', $audit->id) }}" class="fw-bold">
                                                {{ $audit->audit_code }}
                                            </a>
                                        </td>
                                        <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                                        <td>{{ $audit->conductedByUser->full_name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $totalItems }}</td>
                                        <td class="text-center text-success">{{ $presentItems }}</td>
                                        <td class="text-center text-danger">{{ $missingItems }}</td>
                                        <td class="text-center text-warning">{{ $maintenanceItems }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $accuracy >= 95 ? 'success' : ($accuracy >= 85 ? 'warning' : 'danger') }}">
                                                {{ number_format($accuracy, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($accuracy >= 95)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($accuracy >= 85)
                                                <span class="badge bg-warning">Good</span>
                                            @elseif($accuracy >= 70)
                                                <span class="badge bg-info">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Poor</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="avatar-lg mx-auto mb-4">
                                                <div class="avatar-title bg-light text-primary rounded-circle">
                                                    <i class="bx bx-search-alt font-size-24"></i>
                                                </div>
                                            </div>
                                            <h5>No Audits Found</h5>
                                            <p class="text-muted">No audits found for the selected period and filters.</p>
                                            <a href="{{ route('assets.audits.create') }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i>Create New Audit
                                            </a>
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

    <!-- Action Items and Recommendations -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-error-alt me-2"></i>Action Items Required
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $actionItems = [];
                        $totalMissing = $audits->sum(function($audit) { return $audit->auditItems->where('is_present', false)->count(); });
                        $totalMaintenance = $audits->sum(function($audit) { return $audit->auditItems->where('needs_maintenance', true)->count(); });
                        $avgAccuracy = $audits->count() > 0 ? $audits->avg(function($audit) { 
                            return $audit->auditItems->count() > 0 ? 
                                ($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100 : 0; 
                        }) : 0;
                        
                        if ($totalMissing > 0) {
                            $actionItems[] = [
                                'priority' => 'high',
                                'title' => 'Locate Missing Assets',
                                'description' => "$totalMissing assets are currently marked as missing and need to be located or written off.",
                                'icon' => 'bx-search-alt'
                            ];
                        }
                        
                        if ($totalMaintenance > 0) {
                            $actionItems[] = [
                                'priority' => 'medium',
                                'title' => 'Schedule Maintenance',
                                'description' => "$totalMaintenance assets require maintenance attention.",
                                'icon' => 'bx-wrench'
                            ];
                        }
                        
                        if ($avgAccuracy < 85) {
                            $actionItems[] = [
                                'priority' => 'high',
                                'title' => 'Improve Asset Tracking',
                                'description' => "Average audit accuracy is " . number_format($avgAccuracy, 1) . "%. Consider improving asset tagging and tracking processes.",
                                'icon' => 'bx-target-lock'
                            ];
                        }
                        
                        if ($audits->count() === 0) {
                            $actionItems[] = [
                                'priority' => 'high',
                                'title' => 'Conduct Regular Audits',
                                'description' => "No audits found for the selected period. Regular audits are essential for asset management.",
                                'icon' => 'bx-calendar-check'
                            ];
                        }
                    @endphp
                    
                    @forelse($actionItems as $item)
                        <div class="d-flex align-items-start mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-{{ $item['priority'] === 'high' ? 'danger' : ($item['priority'] === 'medium' ? 'warning' : 'info') }} text-white rounded-circle">
                                    <i class="bx {{ $item['icon'] }}"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $item['title'] }}</h6>
                                <p class="text-muted mb-0 small">{{ $item['description'] }}</p>
                                <span class="badge bg-{{ $item['priority'] === 'high' ? 'danger' : ($item['priority'] === 'medium' ? 'warning' : 'info') }} mt-1">
                                    {{ ucfirst($item['priority']) }} Priority
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <div class="avatar-md mx-auto mb-3">
                                <div class="avatar-title bg-success text-white rounded-circle">
                                    <i class="bx bx-check-circle font-size-18"></i>
                                </div>
                            </div>
                            <h6>All Good!</h6>
                            <p class="text-muted mb-0">No immediate action items identified. Keep up the good work!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bulb me-2"></i>Performance Insights
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $insights = [];
                        
                        if ($avgAccuracy >= 95) {
                            $insights[] = [
                                'type' => 'success',
                                'title' => 'Excellent Accuracy',
                                'description' => 'Your audit accuracy is outstanding. Consider extending audit intervals to optimize resources.',
                                'icon' => 'bx-trophy'
                            ];
                        }
                        
                        if ($audits->count() > 3) {
                            $recentAccuracy = $audits->take(3)->avg(function($audit) { 
                                return $audit->auditItems->count() > 0 ? 
                                    ($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100 : 0; 
                            });
                            $olderAccuracy = $audits->skip(3)->avg(function($audit) { 
                                return $audit->auditItems->count() > 0 ? 
                                    ($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100 : 0; 
                            });
                            
                            if ($recentAccuracy > $olderAccuracy + 5) {
                                $insights[] = [
                                    'type' => 'success',
                                    'title' => 'Improving Trend',
                                    'description' => 'Recent audits show significant improvement in accuracy. Your asset management processes are working well.',
                                    'icon' => 'bx-trending-up'
                                ];
                            }
                        }
                        
                        if ($categoryPerformance->count() > 0) {
                            $bestCategory = $categoryPerformance->sortByDesc(function($cat) {
                                return $cat['total'] > 0 ? ($cat['present'] / $cat['total']) : 0;
                            })->first();
                            
                            if ($bestCategory && $bestCategory['total'] > 5) {
                                $accuracy = ($bestCategory['present'] / $bestCategory['total']) * 100;
                                if ($accuracy >= 95) {
                                    $insights[] = [
                                        'type' => 'info',
                                        'title' => 'Top Performing Category',
                                        'description' => $bestCategory['name'] . ' category shows excellent management with ' . number_format($accuracy, 1) . '% accuracy.',
                                        'icon' => 'bx-award'
                                    ];
                                }
                            }
                        }
                        
                        if (empty($insights)) {
                            $insights[] = [
                                'type' => 'info',
                                'title' => 'Keep Monitoring',
                                'description' => 'Continue regular audits to maintain visibility of your asset inventory.',
                                'icon' => 'bx-time'
                            ];
                        }
                    @endphp
                    
                    @foreach($insights as $insight)
                        <div class="d-flex align-items-start {{ !$loop->last ? 'mb-3 border-bottom pb-3' : '' }}">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-{{ $insight['type'] }} text-white rounded-circle">
                                    <i class="bx {{ $insight['icon'] }}"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $insight['title'] }}</h6>
                                <p class="text-muted mb-0 small">{{ $insight['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function refreshDashboard() {
        window.location.reload();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(function() {
            if (Date.now() - window.lastActivity < 120000) {
                refreshDashboard();
            }
        }, 300000);
        
        window.lastActivity = Date.now();
        document.addEventListener('click', function() {
            window.lastActivity = Date.now();
        });
        
        window.addEventListener('beforeprint', function() {
            document.title = 'Audit Performance Dashboard - {{ $school_data["school_name"] ?? "School" }}';
        });
    });
</script>
@endsection
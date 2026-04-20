@extends('layouts.master')
@section('title', 'Assignment History Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.assignments.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Assignment History Report
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-4">
                                <i class="bx bx-history"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Returns</p>
                            <h4 class="mb-0">{{ $historyMetrics['total_completed_assignments'] }}</h4>
                            <small class="text-primary">{{ $historyMetrics['total_unique_assets'] }} unique assets</small>
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
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">On-Time Returns</p>
                            <h4 class="mb-0">{{ $historyMetrics['on_time_returns'] }}</h4>
                            <small class="text-success">{{ $historyMetrics['on_time_percentage'] }}% success rate</small>
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
                            <span class="avatar-title bg-soft-danger text-danger rounded-circle fs-4">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Late Returns</p>
                            <h4 class="mb-0">{{ $historyMetrics['late_returns'] }}</h4>
                            <small class="text-danger">{{ 100 - $historyMetrics['on_time_percentage'] }}% of total</small>
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
                            <p class="text-muted mb-1">Avg Duration</p>
                            <h4 class="mb-0">{{ $historyMetrics['average_assignment_duration'] }}</h4>
                            <small class="text-info">days per assignment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Monthly Trends -->
    @if($monthlyTrends->count() > 0 && $historyMetrics['total_completed_assignments'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Return Trends</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total Returns</th>
                                    <th>On-Time</th>
                                    <th>Late</th>
                                    <th>Success Rate</th>
                                    <th>Avg Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyTrends as $trend)
                                    <tr>
                                        <td>{{ $trend['month_name'] }}</td>
                                        <td>{{ $trend['total_returns'] }}</td>
                                        <td><span class="badge bg-success">{{ $trend['on_time_returns'] }}</span></td>
                                        <td><span class="badge bg-danger">{{ $trend['late_returns'] }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $trend['on_time_percentage'] }}%"></div>
                                                </div>
                                                <span class="text-muted small">{{ $trend['on_time_percentage'] }}%</span>
                                            </div>
                                        </td>
                                        <td>{{ $trend['average_duration'] }} days</td>
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

    <!-- Navigation Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#recent-history" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-history"></i></span>
                                <span class="d-none d-sm-block">Recent History</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#performance-analysis" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-user-check"></i></span>
                                <span class="d-none d-sm-block">Performance Analysis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#category-analysis" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-category"></i></span>
                                <span class="d-none d-sm-block">Category Analysis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#asset-analysis" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-package"></i></span>
                                <span class="d-none d-sm-block">Asset Analysis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#insights" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-bulb"></i></span>
                                <span class="d-none d-sm-block">Insights</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- Recent History Tab -->
                        <div class="tab-pane active" id="recent-history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Assigned To</th>
                                            <th>Assignment Period</th>
                                            <th>Duration</th>
                                            <th>Return Status</th>
                                            <th>Condition Change</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($completedAssignments->take(50) as $assignment)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($assignment->asset->image_path)
                                                            <img src="{{ asset('storage/' . $assignment->asset->image_path) }}" 
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
                                                                <a href="{{ route('assets.show', $assignment->asset->id) }}" 
                                                                   class="text-dark">{{ $assignment->asset->name }}</a>
                                                            </h6>
                                                            <small class="text-muted">{{ $assignment->asset->asset_code }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($assignment->assignable_type === 'App\\Models\\User')
                                                        {{ $assignment->assignable->firstname ?? '' }} {{ $assignment->assignable->lastname ?? '' }}
                                                    @else
                                                        {{ $assignment->assignable->name ?? 'Department' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <small class="text-muted">{{ $assignment->assigned_date->format('M d, Y') }}</small>
                                                        <br>
                                                        <small class="text-muted">to {{ $assignment->actual_return_date->format('M d, Y') }}</small>
                                                    </div>
                                                </td>
                                                <td>{{ $assignment->assigned_date->diffInDays($assignment->actual_return_date) }} days</td>
                                                <td>
                                                    @if($assignment->expected_return_date && $assignment->actual_return_date > $assignment->expected_return_date)
                                                        <span class="badge bg-danger">
                                                            Late ({{ $assignment->expected_return_date->diffInDays($assignment->actual_return_date) }}d)
                                                        </span>
                                                    @elseif($assignment->expected_return_date && $assignment->actual_return_date < $assignment->expected_return_date)
                                                        <span class="badge bg-success">
                                                            Early ({{ $assignment->actual_return_date->diffInDays($assignment->expected_return_date) }}d)
                                                        </span>
                                                    @elseif($assignment->expected_return_date)
                                                        <span class="badge bg-info">On Time</span>
                                                    @else
                                                        <span class="badge bg-secondary">No Due Date</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge {{ 
                                                                $assignment->condition_on_assignment === 'New' ? 'bg-success' : 
                                                                ($assignment->condition_on_assignment === 'Good' ? 'bg-info' : 
                                                                ($assignment->condition_on_assignment === 'Fair' ? 'bg-warning' : 'bg-danger')) 
                                                            }} me-2">
                                                            {{ $assignment->condition_on_assignment }}
                                                        </span>
                                                        <i class="bx bx-right-arrow-alt"></i>
                                                        <span class="badge {{ 
                                                                $assignment->condition_on_return === 'New' ? 'bg-success' : 
                                                                ($assignment->condition_on_return === 'Good' ? 'bg-info' : 
                                                                ($assignment->condition_on_return === 'Fair' ? 'bg-warning' : 'bg-danger')) 
                                                            }} ms-2">
                                                            {{ $assignment->condition_on_return }}
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-history fs-1 mb-2"></i>
                                                        <h5>No Assignment History</h5>
                                                        <p>No completed assignments found in the selected date range.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($completedAssignments->count() > 50)
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Showing most recent 50 assignments. Export for complete data.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Performance Analysis Tab -->
                        <div class="tab-pane" id="performance-analysis" role="tabpanel">
                            @if($assigneePerformance->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="card bg-soft-success">
                                        <div class="card-body text-center">
                                            <h5 class="text-success">{{ $summaryStats['top_performers_count'] }}</h5>
                                            <p class="text-success mb-0">Top Performers</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-soft-warning">
                                        <div class="card-body text-center">
                                            <h5 class="text-warning">{{ $assigneePerformance->count() - $summaryStats['top_performers_count'] - $summaryStats['needs_attention_count'] }}</h5>
                                            <p class="text-warning mb-0">Average Performers</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-soft-danger">
                                        <div class="card-body text-center">
                                            <h5 class="text-danger">{{ $summaryStats['needs_attention_count'] }}</h5>
                                            <p class="text-danger mb-0">Need Attention</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Assignee</th>
                                            <th>Type</th>
                                            <th>Total Returns</th>
                                            <th>On-Time Rate</th>
                                            <th>Avg Duration</th>
                                            <th>Condition Care</th>
                                            <th>Performance Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assigneePerformance as $assignee)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title 
                                                                {{ $assignee['assignee_type'] === 'User' ? 'bg-soft-primary text-primary' : 'bg-soft-info text-info' }} 
                                                                rounded-circle">
                                                                <i class="bx {{ $assignee['assignee_type'] === 'User' ? 'bx-user' : 'bx-building' }}"></i>
                                                            </span>
                                                        </div>
                                                        <span class="fw-medium">{{ substr($assignee['assignee_name'], strpos($assignee['assignee_name'], ': ') + 2) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $assignee['assignee_type'] === 'User' ? 'bg-primary' : 'bg-info' }}">
                                                        {{ $assignee['assignee_type'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $assignee['total_assignments'] }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                {{ $assignee['on_time_percentage'] >= 90 ? 'bg-success' : 
                                                                   ($assignee['on_time_percentage'] >= 80 ? 'bg-info' : 
                                                                   ($assignee['on_time_percentage'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                 style="width: {{ $assignee['on_time_percentage'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted small">{{ $assignee['on_time_percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>{{ $assignee['average_duration'] }} days</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                {{ $assignee['condition_care_score'] >= 90 ? 'bg-success' : 
                                                                   ($assignee['condition_care_score'] >= 80 ? 'bg-info' : 
                                                                   ($assignee['condition_care_score'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                 style="width: {{ $assignee['condition_care_score'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted small">{{ $assignee['condition_care_score'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        {{ $assignee['performance_rating'] === 'Excellent' ? 'bg-success' : 
                                                           ($assignee['performance_rating'] === 'Good' ? 'bg-info' : 
                                                           ($assignee['performance_rating'] === 'Average' ? 'bg-warning' : 'bg-danger')) }}">
                                                        {{ $assignee['performance_rating'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-user fs-1 mb-2"></i>
                                                        <h5>No Performance Data</h5>
                                                        <p>No assignee performance data available.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-user-check"></i>
                                    </div>
                                </div>
                                <h5>No Performance Data Available</h5>
                                <p class="text-muted">No completed assignments found to analyze assignee performance.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Category Analysis Tab -->
                        <div class="tab-pane" id="category-analysis" role="tabpanel">
                            @if($categoryPerformance->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Total Returns</th>
                                            <th>Unique Assets</th>
                                            <th>Avg Duration</th>
                                            <th>On-Time Rate</th>
                                            <th>Condition Impact</th>
                                            <th>Total Usage Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categoryPerformance as $category)
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
                                                <td>{{ $category['total_assignments'] }}</td>
                                                <td>{{ $category['unique_assets'] }}</td>
                                                <td>{{ $category['average_duration'] }} days</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                {{ $category['on_time_percentage'] >= 90 ? 'bg-success' : 
                                                                   ($category['on_time_percentage'] >= 80 ? 'bg-info' : 
                                                                   ($category['on_time_percentage'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                 style="width: {{ $category['on_time_percentage'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted small">{{ $category['on_time_percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $category['condition_deterioration_rate'] <= 10 ? 'bg-success' : 
                                                                         ($category['condition_deterioration_rate'] <= 25 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $category['condition_deterioration_rate'] }}% deterioration
                                                    </span>
                                                </td>
                                                <td>{{ number_format($category['total_days_utilized']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-category fs-1 mb-2"></i>
                                                        <h5>No Category Data</h5>
                                                        <p>No category performance data available.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-category"></i>
                                    </div>
                                </div>
                                <h5>No Category Data Available</h5>
                                <p class="text-muted">No completed assignments found to analyze category performance.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Asset Analysis Tab -->
                        <div class="tab-pane" id="asset-analysis" role="tabpanel">
                            @if($frequentlyAssignedAssets->count() > 0)
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Most Frequently Assigned Assets</h6>
                                <p class="mb-0">These assets have the highest historical usage and may require closer monitoring.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Category</th>
                                            <th>Assignment Count</th>
                                            <th>Avg Duration</th>
                                            <th>Total Usage Days</th>
                                            <th>Condition Impact</th>
                                            <th>Last Return</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($frequentlyAssignedAssets as $assetData)
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
                                                <td>
                                                    <span class="badge bg-primary">{{ $assetData['assignment_count'] }}</span>
                                                </td>
                                                <td>{{ $assetData['average_duration'] }} days</td>
                                                <td>{{ $assetData['total_days_assigned'] }} days</td>
                                                <td>
                                                    @if($assetData['condition_deterioration_count'] > 0)
                                                        <span class="badge bg-warning">
                                                            {{ $assetData['condition_deterioration_count'] }} deteriorations
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">Well maintained</span>
                                                    @endif
                                                </td>
                                                <td>{{ $assetData['last_assignment_date']->format('M d, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-package fs-1 mb-2"></i>
                                                        <h5>No Asset Data</h5>
                                                        <p>No frequently assigned assets found.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-package"></i>
                                    </div>
                                </div>
                                <h5>No Asset Usage Data Available</h5>
                                <p class="text-muted">No frequently assigned assets found in the selected date range.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Insights Tab -->
                        <div class="tab-pane" id="insights" role="tabpanel">
                            @if($historyMetrics['total_completed_assignments'] > 0)
                            <div class="row">
                                <!-- Return Patterns -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Return Behavior Patterns</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <div class="text-center">
                                                        <div class="avatar-sm mx-auto mb-2">
                                                            <span class="avatar-title bg-soft-success text-success rounded-circle">
                                                                <i class="bx bx-check-double"></i>
                                                            </span>
                                                        </div>
                                                        <h6 class="mb-1">{{ $returnPatterns['early_returns'] }}</h6>
                                                        <p class="text-muted mb-0 small">Early Returns</p>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-center">
                                                        <div class="avatar-sm mx-auto mb-2">
                                                            <span class="avatar-title bg-soft-info text-info rounded-circle">
                                                                <i class="bx bx-check"></i>
                                                            </span>
                                                        </div>
                                                        <h6 class="mb-1">{{ $returnPatterns['on_time_returns'] }}</h6>
                                                        <p class="text-muted mb-0 small">Exact On-Time</p>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-center">
                                                        <div class="avatar-sm mx-auto mb-2">
                                                            <span class="avatar-title bg-soft-danger text-danger rounded-circle">
                                                                <i class="bx bx-time-five"></i>
                                                            </span>
                                                        </div>
                                                        <h6 class="mb-1">{{ $returnPatterns['late_returns'] }}</h6>
                                                        <p class="text-muted mb-0 small">Late Returns</p>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="text-center">
                                                        <div class="avatar-sm mx-auto mb-2">
                                                            <span class="avatar-title bg-soft-secondary text-secondary rounded-circle">
                                                                <i class="bx bx-calendar-x"></i>
                                                            </span>
                                                        </div>
                                                        <h6 class="mb-1">{{ $returnPatterns['no_due_date_returns'] }}</h6>
                                                        <p class="text-muted mb-0 small">No Due Date</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Seasonal Analysis -->
                                @if($seasonalAnalysis->count() > 0)
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Seasonal Return Patterns</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Season</th>
                                                            <th>Returns</th>
                                                            <th>Avg Duration</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($seasonalAnalysis as $season)
                                                            <tr>
                                                                <td>{{ $season['season'] }}</td>
                                                                <td>{{ $season['total_returns'] }}</td>
                                                                <td>{{ $season['average_duration'] }} days</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Key Insights -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Key Historical Insights</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-success">Strengths</h6>
                                                    <ul class="list-unstyled">
                                                        @if($historyMetrics['on_time_percentage'] >= 80)
                                                            <li class="mb-2">
                                                                <i class="bx bx-check-circle text-success me-2"></i>
                                                                High on-time return rate ({{ $historyMetrics['on_time_percentage'] }}%)
                                                            </li>
                                                        @endif
                                                        @if($conditionChangeStats['deteriorated'] / $historyMetrics['total_completed_assignments'] <= 0.2)
                                                            <li class="mb-2">
                                                                <i class="bx bx-check-circle text-success me-2"></i>
                                                                Good asset condition maintenance
                                                            </li>
                                                        @endif
                                                        @if($summaryStats['top_performers_count'] > 0)
                                                            <li class="mb-2">
                                                                <i class="bx bx-check-circle text-success me-2"></i>
                                                                {{ $summaryStats['top_performers_count'] }} excellent performers identified
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-warning">Areas for Improvement</h6>
                                                    <ul class="list-unstyled">
                                                        @if($historyMetrics['late_returns'] > 0)
                                                            <li class="mb-2">
                                                                <i class="bx bx-error-circle text-warning me-2"></i>
                                                                {{ $historyMetrics['late_returns'] }} late returns need attention
                                                            </li>
                                                        @endif
                                                        @if($summaryStats['needs_attention_count'] > 0)
                                                            <li class="mb-2">
                                                                <i class="bx bx-error-circle text-warning me-2"></i>
                                                                {{ $summaryStats['needs_attention_count'] }} assignees need performance improvement
                                                            </li>
                                                        @endif
                                                        @if($conditionChangeStats['deteriorated'] > 0)
                                                            <li class="mb-2">
                                                                <i class="bx bx-error-circle text-warning me-2"></i>
                                                                {{ $conditionChangeStats['deteriorated'] }} assets showed condition deterioration
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-history"></i>
                                    </div>
                                </div>
                                <h5>No Historical Data Available</h5>
                                <p class="text-muted">No completed assignments found in the selected date range to generate insights.</p>
                                <p class="text-muted">Try adjusting the date range or check back once assignments have been completed.</p>
                            </div>
                            @endif
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
                localStorage.setItem('assignmentHistoryReportActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('assignmentHistoryReportActiveTab');
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
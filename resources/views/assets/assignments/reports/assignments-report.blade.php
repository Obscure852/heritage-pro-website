@extends('layouts.master')
@section('title', 'Current Assignments Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.assignments.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Current Assignments Report
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
                                <i class="bx bx-clipboard"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Assignments</p>
                            <h4 class="mb-0">{{ $assignmentMetrics['total_current_assignments'] }}</h4>
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
                            <p class="text-muted mb-1">Overdue</p>
                            <h4 class="mb-0">{{ $assignmentMetrics['overdue_assignments'] }}</h4>
                            <small class="text-danger">
                                {{ $assignmentMetrics['total_current_assignments'] > 0 ? 
                                   round(($assignmentMetrics['overdue_assignments'] / $assignmentMetrics['total_current_assignments']) * 100, 1) : 0 }}% of total
                            </small>
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
                                <i class="bx bx-calendar-exclamation"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Due Soon</p>
                            <h4 class="mb-0">{{ $assignmentMetrics['due_soon_assignments'] }}</h4>
                            <small class="text-warning">Next 7 days</small>
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
                                <i class="bx bx-calendar-minus"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">No Due Date</p>
                            <h4 class="mb-0">{{ $assignmentMetrics['no_due_date_assignments'] }}</h4>
                            <small class="text-info">Open-ended</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row mb-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assignment Duration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <h6 class="mb-1">{{ $assignmentMetrics['average_assignment_duration'] }}</h6>
                                <p class="text-muted mb-0 small">Average Days</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <h6 class="mb-1">{{ $assignmentMetrics['longest_assignment_days'] }}</h6>
                                <p class="text-muted mb-0 small">Longest Days</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Duration Categories</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-success text-success rounded-circle">
                                        <i class="bx bx-time"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $durationCategories['short_term'] }}</h6>
                                <p class="text-muted mb-0 small">≤30 Days</p>
                            </div>
                        </div>
                        <div class="col-3 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-info text-info rounded-circle">
                                        <i class="bx bx-timer"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $durationCategories['medium_term'] }}</h6>
                                <p class="text-muted mb-0 small">31-90 Days</p>
                            </div>
                        </div>
                        <div class="col-3 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-warning text-warning rounded-circle">
                                        <i class="bx bx-hourglass"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $durationCategories['long_term'] }}</h6>
                                <p class="text-muted mb-0 small">3-12 Months</p>
                            </div>
                        </div>
                        <div class="col-3 mb-3">
                            <div class="text-center">
                                <div class="avatar-sm mx-auto mb-2">
                                    <span class="avatar-title bg-soft-danger text-danger rounded-circle">
                                        <i class="bx bx-history"></i>
                                    </span>
                                </div>
                                <h6 class="mb-1">{{ $durationCategories['extended'] }}</h6>
                                <p class="text-muted mb-0 small">Over 1 Year</p>
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
                            <a class="nav-link active" data-bs-toggle="tab" href="#overdue-assignments" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-time-five"></i></span>
                                <span class="d-none d-sm-block">
                                    Overdue <span class="badge bg-danger ms-1">{{ $assignmentMetrics['overdue_assignments'] }}</span>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#all-assignments" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-list-ul"></i></span>
                                <span class="d-none d-sm-block">All Current</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#by-category" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-category"></i></span>
                                <span class="d-none d-sm-block">By Category</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#by-assignee" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                                <span class="d-none d-sm-block">By Assignee</span>
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
                        <!-- Overdue Assignments Tab -->
                        <div class="tab-pane active" id="overdue-assignments" role="tabpanel">
                            @if($assignmentsByStatus['overdue']->count() > 0)
                                <div class="alert alert-danger">
                                    <h6 class="alert-heading">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $assignmentsByStatus['overdue']->count() }} Overdue Assignments Require Immediate Attention
                                    </h6>
                                    <p class="mb-0">These assignments have passed their expected return dates and need follow-up.</p>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Assigned To</th>
                                            <th>Assigned Date</th>
                                            <th>Expected Return</th>
                                            <th>Days Overdue</th>
                                            <th>Condition</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assignmentsByStatus['overdue'] as $assignment)
                                            <tr class="table-danger">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($assignment->asset->image_path)
                                                            <img src="{{ asset('storage/' . $assignment->asset->image_path) }}" 
                                                                 alt="" class="rounded me-2" height="32">
                                                        @else
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-danger text-danger rounded">
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
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                    <i class="bx bx-user"></i>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="fw-medium">{{ $assignment->assignable->firstname ?? '' }} {{ $assignment->assignable->lastname ?? '' }}</span>
                                                                <br><small class="text-muted">{{ $assignment->assignable->position ?? 'Staff' }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-info text-info rounded-circle">
                                                                    <i class="bx bx-building"></i>
                                                                </span>
                                                            </div>
                                                            <span class="fw-medium">{{ $assignment->assignable->name ?? 'Department' }}</span>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                                <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('M d, Y') : 'No date set' }}</td>
                                                <td>
                                                    @if($assignment->expected_return_date)
                                                        <span class="badge bg-danger">
                                                            {{ now()->diffInDays($assignment->expected_return_date) }} days
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        @if($assignment->condition_on_assignment === 'New') bg-success
                                                        @elseif($assignment->condition_on_assignment === 'Good') bg-info  
                                                        @elseif($assignment->condition_on_assignment === 'Fair') bg-warning
                                                        @else bg-danger
                                                        @endif">
                                                        {{ $assignment->condition_on_assignment }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('assets.return-asset', $assignment->asset->id) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="bx bx-undo"></i> Return
                                                        </a>
                                                        <a href="{{ route('assets.show', $assignment->asset->id) }}" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-success">
                                                        <i class="bx bx-check-circle fs-1 mb-2"></i>
                                                        <h5>No Overdue Assignments!</h5>
                                                        <p>All current assignments are on track.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- All Assignments Tab -->
                        <div class="tab-pane" id="all-assignments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Category</th>
                                            <th>Assigned To</th>
                                            <th>Assigned Date</th>
                                            <th>Expected Return</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($currentAssignments->take(100) as $assignment)
                                            <tr class="{{ $assignment->isOverdue() ? 'table-danger' : '' }}">
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
                                                <td>{{ $assignment->asset->category->name ?? 'Uncategorized' }}</td>
                                                <td>
                                                    @if($assignment->assignable_type === 'App\\Models\\User')
                                                        {{ $assignment->assignable->firstname ?? '' }} {{ $assignment->assignable->lastname ?? '' }}
                                                    @else
                                                        {{ $assignment->assignable->name ?? 'Department' }}
                                                    @endif
                                                </td>
                                                <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                                <td>
                                                    @if($assignment->expected_return_date)
                                                        {{ $assignment->expected_return_date->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td>{{ $assignment->assigned_date->diffInDays(now()) }} days</td>
                                                <td>
                                                    @if($assignment->isOverdue())
                                                        <span class="badge bg-danger">
                                                            Overdue {{ now()->diffInDays($assignment->expected_return_date) }}d
                                                        </span>
                                                    @elseif($assignment->expected_return_date && now()->diffInDays($assignment->expected_return_date, false) <= 7 && now()->diffInDays($assignment->expected_return_date, false) >= 0)
                                                        <span class="badge bg-warning">
                                                            Due in {{ now()->diffInDays($assignment->expected_return_date, false) }}d
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">On Track</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-package fs-1 mb-2"></i>
                                                        <h5>No Current Assignments</h5>
                                                        <p>No assets are currently assigned.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($currentAssignments->count() > 100)
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Showing first 100 assignments. Export for complete data.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- By Category Tab -->
                        <div class="tab-pane" id="by-category" role="tabpanel">
                            <div class="row">
                                @forelse($assignmentsByCategory as $category)
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">{{ $category['category_name'] }}</h6>
                                                <div>
                                                    <span class="badge bg-primary">{{ $category['total_assignments'] }} assigned</span>
                                                    @if($category['overdue_count'] > 0)
                                                        <span class="badge bg-danger">{{ $category['overdue_count'] }} overdue</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tbody>
                                                            @foreach($category['assignments']->take(5) as $assignment)
                                                                <tr class="{{ $assignment->isOverdue() ? 'table-danger' : '' }}">
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="avatar-xs me-2">
                                                                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                                    <i class="bx bx-package"></i>
                                                                                </span>
                                                                            </div>
                                                                            <div>
                                                                                <h6 class="mb-0 font-size-12">{{ $assignment->asset->name }}</h6>
                                                                                <small class="text-muted">{{ $assignment->asset->asset_code }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        @if($assignment->isOverdue())
                                                                            <span class="badge bg-danger">Overdue</span>
                                                                        @else
                                                                            <span class="text-muted">{{ $assignment->assigned_date->diffInDays(now()) }}d</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            @if($category['assignments']->count() > 5)
                                                                <tr>
                                                                    <td colspan="2" class="text-center">
                                                                        <small class="text-muted">+ {{ $category['assignments']->count() - 5 }} more assets</small>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-category fs-1 mb-2"></i>
                                                <h5>No Categories Found</h5>
                                                <p>No assignment data available by category.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- By Assignee Tab -->
                        <div class="tab-pane" id="by-assignee" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Assignee</th>
                                            <th>Type</th>
                                            <th>Total Assignments</th>
                                            <th>Overdue</th>
                                            <th>Overdue Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topAssignees as $assignee)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title 
                                                                {{ strpos($assignee['assignee_name'], 'User:') === 0 ? 'bg-soft-primary text-primary' : 'bg-soft-info text-info' }} 
                                                                rounded-circle">
                                                                <i class="bx {{ strpos($assignee['assignee_name'], 'User:') === 0 ? 'bx-user' : 'bx-building' }}"></i>
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
                                                    @if($assignee['overdue_assignments'] > 0)
                                                        <span class="badge bg-danger">{{ $assignee['overdue_assignments'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar 
                                                                {{ $assignee['overdue_percentage'] > 50 ? 'bg-danger' : 
                                                                   ($assignee['overdue_percentage'] > 25 ? 'bg-warning' : 'bg-success') }}" 
                                                                 style="width: {{ $assignee['overdue_percentage'] }}%"></div>
                                                        </div>
                                                        <span class="text-muted">{{ $assignee['overdue_percentage'] }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-user fs-1 mb-2"></i>
                                                        <h5>No Assignees Found</h5>
                                                        <p>No assignment data available by assignee.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Insights Tab -->
                        <div class="tab-pane" id="insights" role="tabpanel">
                            <div class="row">
                                <!-- Recent Assignments -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-calendar-plus me-1"></i>
                                                Recent Assignments (Last 30 Days)
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($recentAssignments->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tbody>
                                                            @foreach($recentAssignments->take(5) as $assignment)
                                                                <tr>
                                                                    <td>{{ $assignment->asset->name }}</td>
                                                                    <td>{{ $assignment->assigned_date->format('M d') }}</td>
                                                                    <td>
                                                                        <span class="badge bg-success">
                                                                            {{ $assignment->assigned_date->diffForHumans() }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted text-center">No recent assignments</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Long-term Assignments -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-history me-1"></i>
                                                Long-term Assignments (Over 1 Year)
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($longTermAssignments->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tbody>
                                                            @foreach($longTermAssignments->take(5) as $assignment)
                                                                <tr>
                                                                    <td>{{ $assignment->asset->name }}</td>
                                                                    <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                                                    <td>
                                                                        <span class="badge bg-warning">
                                                                            {{ $assignment->assigned_date->diffInDays(now()) }} days
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted text-center">No long-term assignments</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Condition Concerns -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-error-circle me-1"></i>
                                                Assets with Condition Concerns
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($conditionConcerns->count() > 0)
                                                <div class="alert alert-warning">
                                                    <p class="mb-0">{{ $conditionConcerns->count() }} assets assigned in Fair or Poor condition may need attention.</p>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Asset</th>
                                                                <th>Assigned To</th>
                                                                <th>Condition</th>
                                                                <th>Days Assigned</th>
                                                                <th>Recommendation</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($conditionConcerns as $assignment)
                                                                <tr>
                                                                    <td>{{ $assignment->asset->name }}</td>
                                                                    <td>
                                                                        @if($assignment->assignable_type === 'App\\Models\\User')
                                                                            {{ $assignment->assignable->firstname ?? '' }} {{ $assignment->assignable->lastname ?? '' }}
                                                                        @else
                                                                            {{ $assignment->assignable->name ?? 'Department' }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge {{ $assignment->condition_on_assignment === 'Poor' ? 'bg-danger' : 'bg-warning' }}">
                                                                            {{ $assignment->condition_on_assignment }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $assignment->assigned_date->diffInDays(now()) }} days</td>
                                                                    <td>
                                                                        @if($assignment->condition_on_assignment === 'Poor')
                                                                            <span class="badge bg-danger">Immediate inspection needed</span>
                                                                        @else
                                                                            <span class="badge bg-warning">Monitor condition</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="text-success">
                                                        <i class="bx bx-check-circle fs-1 mb-2"></i>
                                                        <h5>All Assets in Good Condition</h5>
                                                        <p>No assigned assets have condition concerns.</p>
                                                    </div>
                                                </div>
                                            @endif
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
        document.addEventListener('DOMContentLoaded', function() {
            function saveActiveTab(tabId) {
                localStorage.setItem('currentAssignmentsReportActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('currentAssignmentsReportActiveTab');
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

            const viewAssigneeButtons = document.querySelectorAll('.view-assignee-details');
            viewAssigneeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const assigneeName = this.getAttribute('data-assignee');
                });
            });
        });
    </script>
@endsection
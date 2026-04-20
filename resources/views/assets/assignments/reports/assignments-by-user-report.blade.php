@extends('layouts.master')
@section('title', 'Assignments by User Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.assignments.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Assignments by User Report
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
                                <i class="bx bx-users"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Users with Assignments</p>
                            <h4 class="mb-0">{{ $overallStats['total_users_with_assignments'] }}</h4>
                            <small class="text-primary">{{ $overallStats['total_active_users'] }} currently active</small>
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
                            <p class="text-muted mb-1">Perfect Record Users</p>
                            <h4 class="mb-0">{{ $overallStats['users_with_perfect_record'] }}</h4>
                            <small class="text-success">100% on-time, no damage</small>
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
                            <p class="text-muted mb-1">Users with Overdue</p>
                            <h4 class="mb-0">{{ $overallStats['users_with_overdue'] }}</h4>
                            <small class="text-warning">Need immediate attention</small>
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
                            <p class="text-muted mb-1">Overall On-Time Rate</p>
                            <h4 class="mb-0">{{ $overallStats['overall_on_time_rate'] }}%</h4>
                            <small class="text-info">Across all users</small>
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
                            <a class="nav-link active" data-bs-toggle="tab" href="#all-users" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-users"></i></span>
                                <span class="d-none d-sm-block">All Users</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#top-performers" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-trophy"></i></span>
                                <span class="d-none d-sm-block">Top Performers</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#needs-attention" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-error-circle"></i></span>
                                <span class="d-none d-sm-block">
                                    Needs Attention
                                    @if($overallStats['users_with_overdue'] > 0)
                                        <span class="badge bg-danger ms-1">{{ $overallStats['users_with_overdue'] }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#department-analysis" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-building"></i></span>
                                <span class="d-none d-sm-block">Department Analysis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#activity-trends" role="tab">
                                <span class="d-block d-sm-none"><i class="bx bx-trending-up"></i></span>
                                <span class="d-none d-sm-block">Activity Trends</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- All Users Tab -->
                        <div class="tab-pane active" id="all-users" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Department</th>
                                            <th>Current</th>
                                            <th>Completed</th>
                                            <th>On-Time Rate</th>
                                            <th>Overdue</th>
                                            <th>Condition Care</th>
                                            <th>Asset Variety</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userAnalysis as $userData)
                                            <tr class="{{ $userData['overdue_current'] > 0 ? 'table-warning' : '' }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                                <i class="bx bx-user"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</h6>
                                                            <small class="text-muted">{{ $userData['user']->position ?? 'Staff' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $userData['user']->department ?? 'N/A' }}</td>
                                                <td>
                                                    @if($userData['current_assignments'] > 0)
                                                        <span class="badge bg-info">{{ $userData['current_assignments'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td>{{ $userData['completed_assignments'] }}</td>
                                                <td>
                                                    @if($userData['completed_assignments'] > 0)
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                                <div class="progress-bar {{ $userData['on_time_percentage'] >= 90 ? 'bg-success' : 
                                                                           ($userData['on_time_percentage'] >= 80 ? 'bg-info' : 
                                                                           ($userData['on_time_percentage'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                     style="width: {{ $userData['on_time_percentage'] }}%"></div>
                                                            </div>
                                                            <span class="text-muted small">{{ $userData['on_time_percentage'] }}%</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($userData['overdue_current'] > 0)
                                                        <span class="badge bg-danger">{{ $userData['overdue_current'] }}</span>
                                                    @else
                                                        <span class="text-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($userData['completed_assignments'] > 0)
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                                <div class="progress-bar {{ $userData['condition_care_percentage'] >= 90 ? 'bg-success' : 
                                                                           ($userData['condition_care_percentage'] >= 80 ? 'bg-info' : 
                                                                           ($userData['condition_care_percentage'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                     style="width: {{ $userData['condition_care_percentage'] }}%"></div>
                                                            </div>
                                                            <span class="text-muted small">{{ $userData['condition_care_percentage'] }}%</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $userData['unique_assets'] }} assets</span>
                                                    <br>
                                                    <small class="text-muted">{{ $userData['unique_categories'] }} categories</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary view-user-details" 
                                                                data-user-id="{{ $userData['user']->id }}"
                                                                data-user-name="{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                        @if($userData['current_assignments'] > 0)
                                                            <button class="btn btn-sm btn-outline-info view-current-assignments" 
                                                                    data-user-id="{{ $userData['user']->id }}">
                                                                <i class="bx bx-list-ul"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-users fs-1 mb-2"></i>
                                                        <h5>No User Assignment Data</h5>
                                                        <p>No users found with assignments in the selected date range.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Top Performers Tab -->
                        <div class="tab-pane" id="top-performers" role="tab‍panel">
                            <div class="row">
                                <!-- Most Assignments -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-trophy me-1 text-warning"></i>
                                                Most Active Users
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Rank</th>
                                                            <th>User</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topUsers['most_assignments']->take(5) as $index => $userData)
                                                            <tr>
                                                                <td>
                                                                    @if($index === 0)
                                                                        <i class="bx bx-trophy text-warning"></i>
                                                                    @elseif($index === 1)
                                                                        <i class="bx bx-medal text-secondary"></i>
                                                                    @elseif($index === 2)
                                                                        <i class="bx bx-award text-warning"></i>
                                                                    @else
                                                                        {{ $index + 1 }}
                                                                    @endif
                                                                </td>
                                                                <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                <td><span class="badge bg-primary">{{ $userData['total_assignments'] }}</span></td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Best On-Time Rate -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-check-circle me-1 text-success"></i>
                                                Best On-Time Record
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Rank</th>
                                                            <th>User</th>
                                                            <th>Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topUsers['best_on_time_rate']->take(5) as $index => $userData)
                                                            <tr>
                                                                <td>
                                                                    @if($index === 0)
                                                                        <i class="bx bx-trophy text-warning"></i>
                                                                    @elseif($index === 1)
                                                                        <i class="bx bx-medal text-secondary"></i>
                                                                    @elseif($index === 2)
                                                                        <i class="bx bx-award text-warning"></i>
                                                                    @else
                                                                        {{ $index + 1 }}
                                                                    @endif
                                                                </td>
                                                                <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                <td><span class="badge bg-success">{{ $userData['on_time_percentage'] }}%</span></td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Most Current Assignments -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-package me-1 text-info"></i>
                                                Highest Current Workload
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>User</th>
                                                            <th>Current</th>
                                                            <th>Overdue</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topUsers['most_current_assignments']->take(5) as $userData)
                                                            <tr class="{{ $userData['overdue_current'] > 0 ? 'table-warning' : '' }}">
                                                                <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                <td><span class="badge bg-info">{{ $userData['current_assignments'] }}</span></td>
                                                                <td>
                                                                    @if($userData['overdue_current'] > 0)
                                                                        <span class="badge bg-danger">{{ $userData['overdue_current'] }}</span>
                                                                    @else
                                                                        <span class="text-success">0</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Most Asset Variety -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-grid-alt me-1 text-secondary"></i>
                                                Most Asset Variety
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>User</th>
                                                            <th>Assets</th>
                                                            <th>Categories</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topUsers['most_asset_variety']->take(5) as $userData)
                                                            <tr>
                                                                <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                <td><span class="badge bg-secondary">{{ $userData['unique_assets'] }}</span></td>
                                                                <td><span class="badge bg-primary">{{ $userData['unique_categories'] }}</span></td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Needs Attention Tab -->
                        <div class="tab-pane" id="needs-attention" role="tabpanel">
                            <div class="row">
                                <!-- Users with Overdue -->
                                <div class="col-12 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-error-circle me-1 text-danger"></i>
                                                Users with Overdue Assignments
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($usersNeedingAttention['with_overdue']->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>User</th>
                                                                <th>Department</th>
                                                                <th>Total Current</th>
                                                                <th>Overdue Count</th>
                                                                <th>Overdue Percentage</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($usersNeedingAttention['with_overdue'] as $userData)
                                                                <tr class="table-danger">
                                                                    <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                    <td>{{ $userData['user']->department ?? 'N/A' }}</td>
                                                                    <td>{{ $userData['current_assignments'] }}</td>
                                                                    <td><span class="badge bg-danger">{{ $userData['overdue_current'] }}</span></td>
                                                                    <td>{{ $userData['overdue_percentage'] }}%</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning contact-user" 
                                                                                data-user-email="{{ $userData['user']->email }}"
                                                                                data-user-name="{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}">
                                                                            <i class="bx bx-envelope"></i> Contact
                                                                        </button>
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
                                                        <h5>No Overdue Issues!</h5>
                                                        <p>All users are managing their assignments well.</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Frequent Late Returns -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-time me-1 text-warning"></i>
                                                Frequent Late Returns
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($usersNeedingAttention['frequent_late_returns']->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tbody>
                                                            @foreach($usersNeedingAttention['frequent_late_returns']->take(5) as $userData)
                                                                <tr>
                                                                    <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                    <td>
                                                                        <span class="badge bg-warning">{{ $userData['on_time_percentage'] }}% on-time</span>
                                                                    </td>
                                                                    <td>{{ $userData['late_returns'] }} late</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted text-center">No users with frequent late returns</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Condition Deterioration -->
                                <div class="col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-trending-down me-1 text-danger"></i>
                                                Asset Condition Issues
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($usersNeedingAttention['condition_deterioration']->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tbody>
                                                            @foreach($usersNeedingAttention['condition_deterioration']->take(5) as $userData)
                                                                <tr>
                                                                    <td>{{ $userData['user']->firstname }} {{ $userData['user']->lastname }}</td>
                                                                    <td>
                                                                        <span class="badge bg-danger">{{ $userData['condition_deteriorated'] }} deteriorated</span>
                                                                    </td>
                                                                    <td>{{ $userData['condition_care_percentage'] }}% care</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted text-center">No condition deterioration issues</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Department Analysis Tab -->
                        <div class="tab-pane" id="department-analysis" role="tabpanel">
                            @if($departmentAnalysis->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Total Users</th>
                                                <th>Active Users</th>
                                                <th>Current Assignments</th>
                                                <th>Completed Assignments</th>
                                                <th>On-Time Rate</th>
                                                <th>Users with Overdue</th>
                                                <th>Avg per User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($departmentAnalysis as $dept)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title bg-soft-info text-info rounded-circle">
                                                                    <i class="bx bx-building"></i>
                                                                </span>
                                                            </div>
                                                            <span class="fw-medium">{{ $dept['department_name'] }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $dept['total_users'] }}</td>
                                                    <td>{{ $dept['active_users'] }}</td>
                                                    <td><span class="badge bg-info">{{ $dept['total_current_assignments'] }}</span></td>
                                                    <td>{{ $dept['total_completed_assignments'] }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                                <div class="progress-bar {{ $dept['department_on_time_rate'] >= 90 ? 'bg-success' : 
                                                                           ($dept['department_on_time_rate'] >= 80 ? 'bg-info' : 
                                                                           ($dept['department_on_time_rate'] >= 70 ? 'bg-warning' : 'bg-danger')) }}" 
                                                                     style="width: {{ $dept['department_on_time_rate'] }}%"></div>
                                                            </div>
                                                            <span class="text-muted small">{{ $dept['department_on_time_rate'] }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($dept['users_with_overdue'] > 0)
                                                            <span class="badge bg-warning">{{ $dept['users_with_overdue'] }}</span>
                                                        @else
                                                            <span class="text-success">0</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $dept['avg_assignments_per_user'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="avatar-lg mx-auto mb-4">
                                        <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                            <i class="bx bx-building"></i>
                                        </div>
                                    </div>
                                    <h5>No Department Data Available</h5>
                                    <p class="text-muted">Department information is not configured for users.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Activity Trends Tab -->
                        <div class="tab-pane" id="activity-trends" role="tabpanel">
                            @if($monthlyActivity->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Total Assignments</th>
                                                <th>Unique Users</th>
                                                <th>Avg per User</th>
                                                <th>Activity Level</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($monthlyActivity as $month)
                                                <tr>
                                                    <td>{{ $month['month_name'] }}</td>
                                                    <td>{{ $month['total_assignments'] }}</td>
                                                    <td>{{ $month['unique_users'] }}</td>
                                                    <td>{{ $month['unique_users'] > 0 ? round($month['total_assignments'] / $month['unique_users'], 1) : 0 }}</td>
                                                    <td>
                                                        @php
                                                            $level = $month['total_assignments'];
                                                            if ($level >= 20) $class = 'bg-success';
                                                            elseif ($level >= 10) $class = 'bg-info';
                                                            elseif ($level >= 5) $class = 'bg-warning';
                                                            else $class = 'bg-secondary';
                                                        @endphp
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar {{ $class }}" 
                                                                 style="width: {{ min(($level / 30) * 100, 100) }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="avatar-lg mx-auto mb-4">
                                        <div class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                    </div>
                                    <h5>No Activity Trends Available</h5>
                                    <p class="text-muted">Not enough historical data to show activity trends.</p>
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
                localStorage.setItem('assignmentsByUserReportActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('assignmentsByUserReportActiveTab');
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

            const viewUserDetailsButtons = document.querySelectorAll('.view-user-details');
            viewUserDetailsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                });
            });

            const viewCurrentAssignmentsButtons = document.querySelectorAll('.view-current-assignments');
            viewCurrentAssignmentsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                });
            });

            const contactUserButtons = document.querySelectorAll('.contact-user');
            contactUserButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userEmail = this.getAttribute('data-user-email');
                    const userName = this.getAttribute('data-user-name');
                    
                    if (userEmail) {
                        const subject = encodeURIComponent('Overdue Asset Assignment Reminder');
                        const body = encodeURIComponent(`Dear ${userName},\n\nThis is a reminder that you have overdue asset assignments that need to be returned.\n\nPlease check your assignments and return any overdue items as soon as possible.\n\nBest regards,\nAsset Management Team`);
                        window.location.href = `mailto:${userEmail}?subject=${subject}&body=${body}`;
                    }
                });
            });
        });
    </script>
@endsection
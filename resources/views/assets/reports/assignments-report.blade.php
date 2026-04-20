@extends('layouts.master')
@section('title', 'Asset Assignment History Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Assignment History Report
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

        <!-- Assignment Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card border shadow-none">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#active-assignments" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-clock"></i></span>
                                    <span class="d-none d-sm-block">Active Assignments</span>
                                    <span class="badge bg-primary ms-2">{{ $activeAssignments->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#assignment-history" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-history"></i></span>
                                    <span class="d-none d-sm-block">Assignment History</span>
                                    <span class="badge bg-success ms-2">{{ $assignmentHistory->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#overdue-assignments" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-exclamation-triangle"></i></span>
                                    <span class="d-none d-sm-block">Overdue</span>
                                    <span class="badge bg-danger ms-2">{{ $overdueAssignments->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#user-performance" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-users"></i></span>
                                    <span class="d-none d-sm-block">User Performance</span>
                                    <span class="badge bg-info ms-2">{{ $userAssignmentPatterns->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#asset-frequency" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-chart-bar"></i></span>
                                    <span class="d-none d-sm-block">Asset Frequency</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#trends-analysis" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-chart-line"></i></span>
                                    <span class="d-none d-sm-block">Trends & Analysis</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-3">
                            <!-- Active Assignments Tab -->
                            <div class="tab-pane active" id="active-assignments" role="tabpanel">
                                @if($riskIndicators['high_risk_assignments'] > 0 || $riskIndicators['no_return_date_assignments'] > 0)
                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Risk Alerts</h6>
                                    @if($riskIndicators['high_risk_assignments'] > 0)
                                        <p class="mb-1">• <strong>{{ $riskIndicators['high_risk_assignments'] }}</strong> high-risk assignments require immediate attention</p>
                                    @endif
                                    @if($riskIndicators['no_return_date_assignments'] > 0)
                                        <p class="mb-1">• <strong>{{ $riskIndicators['no_return_date_assignments'] }}</strong> assignments have no expected return date</p>
                                    @endif
                                    @if($riskIndicators['long_term_assignments'] > 0)
                                        <p class="mb-0">• <strong>{{ $riskIndicators['long_term_assignments'] }}</strong> assignments have been active for over 1 year</p>
                                    @endif
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
                                                <th>Urgency</th>
                                                <th>Condition Risk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activeAssignments as $item)
                                                <tr class="{{ $item['urgency_level'] === 'Critical' ? 'table-danger' : ($item['urgency_level'] === 'High' ? 'table-warning' : '') }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['assignment']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['assignment']->asset->image_path) }}" 
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
                                                                    <a href="{{ route('assets.show', $item['assignment']->asset->id) }}" class="text-dark">
                                                                        {{ $item['assignment']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['assignment']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['assignment']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item['assignee_name'] }}</h6>
                                                            <small class="text-muted">{{ $item['assignee_type'] }}</small>
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
                                                                <br><span class="badge bg-danger small">{{ $item['overdue_days'] }} days overdue</span>
                                                            @endif
                                                        @else
                                                            <span class="text-warning">No return date set</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $item['days_assigned'] > 365 ? 'warning' : 'light' }} text-{{ $item['days_assigned'] > 365 ? 'dark' : 'muted' }}">
                                                            {{ $item['days_assigned'] }} days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['urgency_level'] === 'Critical' ? 'danger' : 
                                                            ($item['urgency_level'] === 'High' ? 'warning' : 
                                                            ($item['urgency_level'] === 'Medium' ? 'info' : 'success')) 
                                                        }}">
                                                            {{ $item['urgency_level'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['condition_risk'] === 'High' ? 'danger' : 
                                                            ($item['condition_risk'] === 'Medium' ? 'warning' : 'success') 
                                                        }}">
                                                            {{ $item['condition_risk'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-clipboard-check fa-2x text-muted mb-3"></i>
                                                        <h5>No Active Assignments</h5>
                                                        <p class="text-muted">All assets have been returned.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Assignment History Tab -->
                            <div class="tab-pane" id="assignment-history" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Assigned To</th>
                                                <th>Assignment Period</th>
                                                <th>Duration</th>
                                                <th>Return Status</th>
                                                <th>Condition Change</th>
                                                <th>Success Score</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assignmentHistory as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['assignment']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['assignment']->asset->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-success text-success rounded">
                                                                        <i class="fas fa-box fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['assignment']->asset->id) }}" class="text-dark">
                                                                        {{ $item['assignment']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['assignment']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['assignment']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item['assignee_name'] }}</h6>
                                                            <small class="text-muted">{{ $item['assignee_type'] }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>From:</strong> {{ $item['assignment']->assigned_date->format('M d, Y') }}<br>
                                                            <strong>To:</strong> {{ $item['assignment']->actual_return_date->format('M d, Y') }}
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $item['duration_days'] }} days</span>
                                                    </td>
                                                    <td>
                                                        @if($item['was_on_time'])
                                                            <span class="badge bg-success">On Time</span>
                                                        @else
                                                            <span class="badge bg-danger">Late</span>
                                                            <br><small class="text-muted">{{ $item['days_late'] }} days late</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['condition_change']['changed'])
                                                            @if($item['condition_change']['improved'])
                                                                <span class="badge bg-success">Improved</span>
                                                                <br><small class="text-muted">{{ $item['condition_change']['start_condition'] }} → {{ $item['condition_change']['end_condition'] }}</small>
                                                            @else
                                                                <span class="badge bg-warning">Degraded</span>
                                                                <br><small class="text-muted">{{ $item['condition_change']['start_condition'] }} → {{ $item['condition_change']['end_condition'] }}</small>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-info">No Change</span>
                                                            <br><small class="text-muted">{{ $item['condition_change']['start_condition'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                                <div class="progress-bar bg-{{ 
                                                                    $item['assignment_success_score'] >= 80 ? 'success' : 
                                                                    ($item['assignment_success_score'] >= 60 ? 'info' : 
                                                                    ($item['assignment_success_score'] >= 40 ? 'warning' : 'danger')) 
                                                                }}" style="width: {{ $item['assignment_success_score'] }}%"></div>
                                                            </div>
                                                            <span class="small">{{ $item['assignment_success_score'] }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($item['assignment']->return_notes)
                                                            <small class="text-muted">{{ Str::limit($item['assignment']->return_notes, 50) }}</small>
                                                        @else
                                                            <span class="text-muted">No notes</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-history fa-2x text-muted mb-3"></i>
                                                        <h5>No Assignment History</h5>
                                                        <p class="text-muted">No completed assignments found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Overdue Assignments Tab -->
                            <div class="tab-pane" id="overdue-assignments" role="tabpanel">
                                @if($overdueAssignments->count() > 0)
                                <div class="alert alert-danger mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-clock me-2"></i>Critical Overdue Assignments</h6>
                                    <p class="mb-0">The following {{ $overdueAssignments->count() }} assignments are overdue and require immediate action. Average overdue period: {{ $healthMetrics['average_overdue_days'] }} days.</p>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th>Assigned To</th>
                                                <th>Expected Return</th>
                                                <th>Days Overdue</th>
                                                <th>Days Assigned</th>
                                                <th>Urgency Level</th>
                                                <th>Contact Info</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($overdueAssignments as $item)
                                                <tr class="table-danger">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($item['assignment']->asset->image_path)
                                                                <img src="{{ asset('storage/' . $item['assignment']->asset->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                                                        <i class="fas fa-exclamation-triangle fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $item['assignment']->asset->id) }}" class="text-dark">
                                                                        {{ $item['assignment']->asset->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $item['assignment']->asset->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $item['assignment']->asset->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $item['assignee_name'] }}</h6>
                                                            <small class="text-muted">{{ $item['assignee_type'] }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {{ $item['assignment']->expected_return_date->format('M d, Y') }}
                                                        <br><small class="text-muted">{{ $item['assignment']->expected_return_date->diffForHumans() }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger fs-6">{{ $item['overdue_days'] }} days</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning text-dark">{{ $item['days_assigned'] }} days total</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ 
                                                            $item['urgency_level'] === 'Critical' ? 'danger' : 
                                                            ($item['urgency_level'] === 'High' ? 'warning' : 'info') 
                                                        }}">
                                                            {{ $item['urgency_level'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['assignment']->assignable && method_exists($item['assignment']->assignable, 'email'))
                                                            <small class="text-muted">
                                                                {{ $item['assignment']->assignable->email ?? 'No email' }}<br>
                                                                {{ $item['assignment']->assignable->phone ?? 'No phone' }}
                                                            </small>
                                                        @else
                                                            <span class="text-muted">Contact info N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group-vertical">
                                                            <a href="{{ route('assets.return-asset', $item['assignment']->asset->id) }}" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-undo me-1"></i> Return Now
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                                        <h5>No Overdue Assignments</h5>
                                                        <p class="text-muted">All assignments are on schedule.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- User Performance Tab -->
                            <div class="tab-pane" id="user-performance" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th class="text-center">Total Assignments</th>
                                                <th class="text-center">Active</th>
                                                <th class="text-center">Completed</th>
                                                <th class="text-center">Overdue</th>
                                                <th class="text-center">On-Time Rate</th>
                                                <th class="text-center">Avg Duration</th>
                                                <th class="text-center">Reliability Score</th>
                                                <th>Last Assignment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($userAssignmentPatterns->sortByDesc('reliability_score') as $pattern)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1">{{ $pattern['assignee_name'] }}</h6>
                                                            <small class="text-muted">{{ class_basename($pattern['assignee']) }}</small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $pattern['total_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary">{{ $pattern['active_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">{{ $pattern['completed_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($pattern['overdue_count'] > 0)
                                                            <span class="badge bg-danger">{{ $pattern['overdue_count'] }}</span>
                                                            <br><small class="text-danger">{{ $pattern['overdue_rate'] }}%</small>
                                                        @else
                                                            <span class="badge bg-success">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $pattern['on_time_rate'] >= 90 ? 'success' : ($pattern['on_time_rate'] >= 70 ? 'info' : 'warning') }}">
                                                            {{ $pattern['on_time_rate'] }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="text-muted">{{ $pattern['average_duration'] }} days</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                                <div class="progress-bar bg-{{ 
                                                                    $pattern['reliability_score'] >= 80 ? 'success' : 
                                                                    ($pattern['reliability_score'] >= 60 ? 'info' : 
                                                                    ($pattern['reliability_score'] >= 40 ? 'warning' : 'danger')) 
                                                                }}" style="width: {{ $pattern['reliability_score'] }}%"></div>
                                                            </div>
                                                            <span class="small">{{ $pattern['reliability_score'] }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($pattern['last_assignment_date'])
                                                            {{ $pattern['last_assignment_date']->format('M d, Y') }}
                                                            <br><small class="text-muted">{{ $pattern['last_assignment_date']->diffForHumans() }}</small>
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                                        <h5>No User Data</h5>
                                                        <p class="text-muted">No assignment patterns found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Asset Frequency Tab -->
                            <div class="tab-pane" id="asset-frequency" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Asset</th>
                                                <th class="text-center">Total Assignments</th>
                                                <th class="text-center">Active</th>
                                                <th class="text-center">Completed</th>
                                                <th class="text-center">Unique Assignees</th>
                                                <th class="text-center">Avg Duration</th>
                                                <th class="text-center">Condition Impact</th>
                                                <th class="text-center">Frequency Score</th>
                                                <th>Last Assignment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assetAssignmentFrequency->sortByDesc('total_assignments') as $frequency)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($frequency['asset']->image_path)
                                                                <img src="{{ asset('storage/' . $frequency['asset']->image_path) }}" 
                                                                     alt="" class="rounded me-2" height="32" width="32" style="object-fit: cover;">
                                                            @else
                                                                <div class="avatar-sm me-2">
                                                                    <span class="avatar-title bg-soft-info text-info rounded">
                                                                        <i class="fas fa-box fs-6"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    <a href="{{ route('assets.show', $frequency['asset']->id) }}" class="text-dark">
                                                                        {{ $frequency['asset']->name }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-0 small">{{ $frequency['asset']->asset_code }}</p>
                                                                <span class="badge bg-info small">{{ $frequency['asset']->category->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">{{ $frequency['total_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary">{{ $frequency['active_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">{{ $frequency['completed_assignments'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $frequency['unique_assignees'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="text-muted">{{ $frequency['average_assignment_duration'] }} days</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ 
                                                            $frequency['condition_degradation_rate'] > 0.5 ? 'danger' : 
                                                            ($frequency['condition_degradation_rate'] > 0.2 ? 'warning' : 'success') 
                                                        }}">
                                                            {{ round($frequency['condition_degradation_rate'] * 100) }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="text-muted">{{ $frequency['assignment_frequency_score'] }}/month</span>
                                                    </td>
                                                    <td>
                                                        @if($frequency['last_assignment_date'])
                                                            {{ $frequency['last_assignment_date']->format('M d, Y') }}
                                                            <br><small class="text-muted">{{ $frequency['last_assignment_date']->diffForHumans() }}</small>
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-chart-bar fa-2x text-muted mb-3"></i>
                                                        <h5>No Asset Data</h5>
                                                        <p class="text-muted">No assignment frequency data found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Trends & Analysis Tab -->
                            <div class="tab-pane" id="trends-analysis" role="tabpanel">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-chart-line me-2"></i>Monthly Assignment Trends (Last 12 Months)
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Month</th>
                                                                <th class="text-center">New Assignments</th>
                                                                <th class="text-center">Returns</th>
                                                                <th class="text-center">Net Change</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($monthlyTrends as $trend)
                                                                <tr>
                                                                    <td>{{ $trend['month'] }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-primary">{{ $trend['new_assignments'] }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-success">{{ $trend['returns'] }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @php $netChange = $trend['new_assignments'] - $trend['returns']; @endphp
                                                                        <span class="badge bg-{{ $netChange > 0 ? 'warning' : ($netChange < 0 ? 'info' : 'secondary') }}">
                                                                            {{ $netChange > 0 ? '+' : '' }}{{ $netChange }}
                                                                        </span>
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

                                <!-- Duration Analysis -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-clock me-2"></i>Assignment Duration Analysis
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h4 class="text-primary">{{ $durationAnalysis['average_duration'] }}</h4>
                                                            <p class="text-muted mb-0">Average Duration (days)</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h4 class="text-info">{{ $durationAnalysis['median_duration'] }}</h4>
                                                            <p class="text-muted mb-0">Median Duration (days)</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <h4 class="text-warning">{{ $durationAnalysis['longest_duration'] }}</h4>
                                                            <p class="text-muted mb-0">Longest Duration (days)</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <h6 class="mb-3">Duration Distribution</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted">1-7 days</span>
                                                            <span class="badge bg-success">{{ $durationAnalysis['duration_ranges']['1_7_days'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted">8-30 days</span>
                                                            <span class="badge bg-info">{{ $durationAnalysis['duration_ranges']['8_30_days'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted">31-90 days</span>
                                                            <span class="badge bg-warning">{{ $durationAnalysis['duration_ranges']['31_90_days'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted">91-365 days</span>
                                                            <span class="badge bg-danger">{{ $durationAnalysis['duration_ranges']['91_365_days'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="text-muted">Over 365 days</span>
                                                            <span class="badge bg-dark">{{ $durationAnalysis['duration_ranges']['over_365_days'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Summary -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card border shadow-none">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-chart-pie me-2"></i>Assignment System Health Metrics
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="text-success">{{ $healthMetrics['on_time_return_rate'] }}%</h5>
                                                            <p class="text-muted mb-0 small">On-Time Return Rate</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="text-info">{{ $healthMetrics['condition_preservation_rate'] }}%</h5>
                                                            <p class="text-muted mb-0 small">Condition Preservation</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="text-warning">{{ $healthMetrics['repeat_assignment_rate'] }}%</h5>
                                                            <p class="text-muted mb-0 small">Repeat Assignment Rate</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <h5 class="text-primary">{{ $healthMetrics['unique_assignees'] }}</h5>
                                                            <p class="text-muted mb-0 small">Active Assignees</p>
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
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bx bx-printer me-1 font-size-14"></i> Print
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function saveActiveTab(tabId) {
            localStorage.setItem('assetAssignmentActiveTab', tabId);
        }

        function loadActiveTab() {
            const activeTabId = localStorage.getItem('assetAssignmentActiveTab');
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
                .progress { background-color: #e9ecef !important; }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection
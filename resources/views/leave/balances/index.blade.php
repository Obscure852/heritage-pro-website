@extends('layouts.master')
@section('title')
    Leave Balances
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        .leave-balances-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .leave-balances-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .leave-balances-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .balance-positive {
            color: #059669;
            font-weight: 600;
        }

        .balance-negative {
            color: #dc2626;
            font-weight: 600;
        }

        .balance-zero {
            color: #6b7280;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Reports Dropdown Styling */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 240px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        .reports-dropdown .dropdown-divider {
            margin: 8px 0;
        }

        .reports-dropdown .dropdown-header {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            padding: 6px 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .color-indicator {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-right: 6px;
        }

        .user-name {
            font-weight: 500;
            color: #1f2937;
        }

        .leave-type-name {
            display: flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .leave-balances-header {
                padding: 20px;
            }

            .leave-balances-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="leave-balances-container">
        <div class="leave-balances-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Leave Balances</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage staff leave balances and adjustments</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalBalances }}</h4>
                                <small class="opacity-75">Total Records</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $selectedYear }}</h4>
                                <small class="opacity-75">Leave Year</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $balances->count() }}</h4>
                                <small class="opacity-75">Filtered</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="leave-balances-body">
            <div class="help-text">
                <div class="help-title">Leave Balance Management</div>
                <div class="help-content">
                    View and manage staff leave balances. Use filters to find specific staff or leave types.
                    Click on a balance to view details and make adjustments.
                </div>
            </div>

            {{-- Filters and Reports on same row --}}
            <form method="GET" action="{{ route('leave.balances.index') }}" id="filter-form">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select name="user_id" id="user_id" class="form-select">
                                        <option value="">All Staff</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                                {{ $user->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select name="leave_type_id" id="leave_type_id" class="form-select">
                                        <option value="">All Types</option>
                                        @foreach($leaveTypes as $type)
                                            <option value="{{ $type->id }}" {{ $selectedLeaveTypeId == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-4">
                                    <select name="year" id="year" class="form-select">
                                        @foreach($years as $year)
                                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-4">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                                <div class="col-lg-1 col-md-1 col-sm-4">
                                    <a href="{{ route('leave.balances.index') }}" class="btn btn-light w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                            @can('view-leave-reports')
                            <div class="btn-group reports-dropdown">
                                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <h6 class="dropdown-header">HR Reports</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.utilization') }}">
                                            <i class="fas fa-chart-pie text-primary"></i> Leave Utilization
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.outstanding') }}">
                                            <i class="fas fa-list-alt text-purple"></i> Outstanding Balances
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.carryover') }}">
                                            <i class="fas fa-exchange-alt text-info"></i> Carry-Over Report
                                        </a>
                                    </li>
                                    @can('approve-leave-requests')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <h6 class="dropdown-header">Manager Reports</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.team-summary') }}">
                                            <i class="fas fa-users text-success"></i> Team Summary
                                        </a>
                                    </li>
                                    @endcan
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <h6 class="dropdown-header">Excel Exports</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.utilization.export', ['year' => $selectedYear]) }}">
                                            <i class="fas fa-file-excel text-success"></i> Export Utilization
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.outstanding.export', ['year' => $selectedYear]) }}">
                                            <i class="fas fa-file-excel text-success"></i> Export Outstanding
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('leave.reports.carryover.export', ['from_year' => $selectedYear - 1, 'to_year' => $selectedYear]) }}">
                                            <i class="fas fa-file-excel text-success"></i> Export Carry-Over
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>

            @if($balances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Leave Type</th>
                                <th class="text-center">Entitled</th>
                                <th class="text-center">Carried</th>
                                <th class="text-center">Accrued</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Adjusted</th>
                                <th class="text-center">Available</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($balances as $index => $balance)
                                <tr>
                                    <td>{{ $balances->firstItem() + $index }}</td>
                                    <td>
                                        <span class="user-name">{{ $balance->user->full_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="leave-type-name">
                                            @if($balance->leaveType && $balance->leaveType->color)
                                                <span class="color-indicator" style="background-color: {{ $balance->leaveType->color }};"></span>
                                            @endif
                                            {{ $balance->leaveType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ number_format($balance->entitled, 1) }}</td>
                                    <td class="text-center">{{ number_format($balance->carried_over, 1) }}</td>
                                    <td class="text-center">{{ number_format($balance->accrued, 1) }}</td>
                                    <td class="text-center">{{ number_format($balance->used, 1) }}</td>
                                    <td class="text-center">{{ number_format($balance->pending, 1) }}</td>
                                    <td class="text-center">
                                        @if($balance->adjusted > 0)
                                            <span class="text-success">+{{ number_format($balance->adjusted, 1) }}</span>
                                        @elseif($balance->adjusted < 0)
                                            <span class="text-danger">{{ number_format($balance->adjusted, 1) }}</span>
                                        @else
                                            {{ number_format($balance->adjusted, 1) }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($balance->available > 0)
                                            <span class="balance-positive">{{ number_format($balance->available, 1) }}</span>
                                        @elseif($balance->available < 0)
                                            <span class="balance-negative">{{ number_format($balance->available, 1) }}</span>
                                        @else
                                            <span class="balance-zero">{{ number_format($balance->available, 1) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('leave.balances.show', $balance) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="View Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $balances->firstItem() }} to {{ $balances->lastItem() }} of {{ $balances->total() }} records
                    </div>
                    {{ $balances->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-balance-scale"></i>
                    <h4>No Leave Balances Found</h4>
                    <p>No leave balances match the selected filters. Try adjusting your filter criteria or initialize balances for the year.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js only for Staff dropdown (has many options)
            const userSelect = document.getElementById('user_id');

            if (userSelect) {
                new Choices(userSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'All Staff',
                    removeItemButton: true,
                    allowHTML: false
                });
            }
        });
    </script>
@endsection

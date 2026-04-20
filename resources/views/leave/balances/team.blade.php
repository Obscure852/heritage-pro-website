@extends('layouts.master')
@section('title')
    Team Leave Balances
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        .team-balances-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .team-balances-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .team-balances-body {
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

        .quick-nav {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .quick-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            color: #374151;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .quick-nav a:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #1f2937;
        }

        .quick-nav a.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #2563eb;
            color: white;
        }

        .filter-section {
            background: #f9fafb;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 20px;
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

        .staff-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .staff-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .staff-name {
            font-weight: 500;
            color: #1f2937;
        }

        .leave-type-name {
            display: flex;
            align-items: center;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .stats-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .stats-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
        }

        .stats-card .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-top: 4px;
        }

        .stats-card.team .stat-value {
            color: #3b82f6;
        }

        .stats-card.available .stat-value {
            color: #059669;
        }

        .stats-card.pending .stat-value {
            color: #d97706;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .team-balances-header {
                padding: 20px;
            }

            .team-balances-body {
                padding: 16px;
            }

            .quick-nav {
                flex-wrap: wrap;
            }

            .stats-cards {
                grid-template-columns: 1fr;
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

    <div class="team-balances-container">
        <div class="team-balances-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-users me-2"></i>Team Leave Balances</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View leave balances for your direct reports</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-white text-primary fs-6">
                        <i class="fas fa-user-friends me-1"></i>
                        {{ $teamStats['total_members'] }} Team {{ Str::plural('Member', $teamStats['total_members']) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="team-balances-body">
            {{-- Quick Navigation --}}
            <div class="quick-nav">
                <a href="{{ route('leave.calendar.team') }}">
                    <i class="fas fa-calendar-alt"></i> Team Calendar
                </a>
                <a href="{{ route('leave.balances.team') }}" class="active">
                    <i class="fas fa-balance-scale"></i> Team Balances
                </a>
                <a href="{{ route('leave.requests.team-history') }}">
                    <i class="fas fa-history"></i> Team History
                </a>
                <a href="{{ route('leave.requests.pending') }}">
                    <i class="fas fa-clock"></i> Pending Approvals
                </a>
            </div>

            <div class="help-text">
                <div class="help-title">Team Leave Balances</div>
                <div class="help-content">
                    View leave balances for all your direct reports. Use filters to narrow down by leave type or year.
                    The summary cards show aggregate totals across all team members.
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="stats-cards">
                <div class="stats-card team">
                    <div class="stat-value">{{ $teamStats['total_members'] }}</div>
                    <div class="stat-label">Team Members</div>
                </div>
                <div class="stats-card available">
                    <div class="stat-value">{{ number_format($teamStats['total_available'], 1) }}</div>
                    <div class="stat-label">Total Available Days</div>
                </div>
                <div class="stats-card pending">
                    <div class="stat-value">{{ number_format($teamStats['total_pending'], 1) }}</div>
                    <div class="stat-label">Total Pending Days</div>
                </div>
            </div>

            <div class="filter-section">
                <form method="GET" action="{{ route('leave.balances.team') }}" id="filter-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="leave_type_id" class="form-label">Leave Type</label>
                            <select name="leave_type_id" id="leave_type_id" class="form-select">
                                <option value="">All Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ $selectedLeaveTypeId == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Leave Year</label>
                            <select name="year" id="year" class="form-select">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('leave.balances.team') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($balances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff Member</th>
                                <th>Leave Type</th>
                                <th class="text-center">Entitled</th>
                                <th class="text-center">Carried</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($balances as $index => $balance)
                                <tr>
                                    <td>{{ $balances->firstItem() + $index }}</td>
                                    <td>
                                        <div class="staff-info">
                                            <div class="staff-avatar">
                                                {{ strtoupper(substr($balance->user->name ?? '', 0, 1)) }}{{ strtoupper(substr(explode(' ', $balance->user->name ?? '')[1] ?? '', 0, 1)) }}
                                            </div>
                                            <span class="staff-name">{{ $balance->user->name ?? 'N/A' }}</span>
                                        </div>
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
                                    <td class="text-center">{{ number_format($balance->used, 1) }}</td>
                                    <td class="text-center">
                                        @if($balance->pending > 0)
                                            <span class="text-warning fw-bold">{{ number_format($balance->pending, 1) }}</span>
                                        @else
                                            {{ number_format($balance->pending, 1) }}
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
                    @if($directReports->count() > 0)
                        <p>No leave balances found for your team members for the selected year. Balances may need to be initialized by HR.</p>
                    @else
                        <p>You don't have any direct reports assigned to you.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for leave type dropdown
            const leaveTypeSelect = document.getElementById('leave_type_id');

            if (leaveTypeSelect) {
                new Choices(leaveTypeSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select leave type...',
                    removeItemButton: true,
                    allowHTML: false
                });
            }
        });
    </script>
@endsection

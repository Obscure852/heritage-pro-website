@extends('layouts.master')
@section('title')
    Team Leave History
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        .team-history-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .team-history-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .team-history-body {
            padding: 24px;
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
            justify-content: flex-end;
        }

        .quick-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .filter-year .choices,
        .filter-year .choices__inner {
            min-width: 100px !important;
        }

        .filter-staff .choices,
        .filter-staff .choices__inner,
        .filter-type .choices,
        .filter-type .choices__inner {
            min-width: 225px !important;
        }

        .filter-section {
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #4b5563;
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

        .request-id {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
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
            gap: 8px;
        }

        .color-indicator {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .date-range {
            white-space: nowrap;
        }


        @media (max-width: 768px) {
            .team-history-header {
                padding: 20px;
            }

            .team-history-body {
                padding: 16px;
            }

            .quick-nav {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.requests.index') }}">Back</a>
        @endslot
        @slot('title')
            My Requests
        @endslot
    @endcomponent

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

    <div class="team-history-container">
        <div class="team-history-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 style="margin:0;"><i class="fas fa-history me-2"></i>Team Leave History</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View leave request history for your direct reports</p>
                </div>
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total'] }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['pending'] }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['approved'] }}</h4>
                                <small class="opacity-75">Approved</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['rejected'] }}</h4>
                                <small class="opacity-75">Rejected</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['cancelled'] }}</h4>
                                <small class="opacity-75">Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="team-history-body">
            {{-- Quick Navigation --}}
            <div class="quick-nav">
                <a href="{{ route('leave.calendar.team') }}">
                    <i class="fas fa-calendar-alt"></i> Team Calendar
                </a>
                <a href="{{ route('leave.balances.team') }}">
                    <i class="fas fa-balance-scale"></i> Team Balances
                </a>
                <a href="{{ route('leave.requests.team-history') }}" class="active">
                    <i class="fas fa-history"></i> Team History
                </a>
                <a href="{{ route('leave.requests.pending') }}">
                    <i class="fas fa-clock"></i> Pending Approvals
                </a>
            </div>

            <div class="help-text">
                <div class="help-title">Team Leave History</div>
                <div class="help-content">
                    View all leave requests submitted by your direct reports. Use filters to search by staff member, leave type, status, or year.
                    Click on a request to view details and take action if needed.
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="{{ route('leave.requests.team-history') }}">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto filter-year">
                                <select name="year" id="year" class="form-select">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto filter-staff">
                                <select name="staff_id" id="staff_id" class="form-select">
                                    <option value="">All Staff</option>
                                    @foreach($directReports as $member)
                                        <option value="{{ $member->id }}" {{ $staffId == $member->id ? 'selected' : '' }}>
                                            {{ $member->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto filter-type">
                                <select name="leave_type_id" id="leave_type_id" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" {{ $leaveTypeId == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('leave.requests.team-history') }}" class="btn btn-light">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Staff Member</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th class="text-center">Days</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>
                                        <span class="request-id">{{ substr($request->ulid, 0, 8) }}...</span>
                                    </td>
                                    <td>
                                        <div class="staff-info">
                                            <div class="staff-avatar">
                                                {{ strtoupper(substr($request->user->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($request->user->lastname ?? '', 0, 1)) }}
                                            </div>
                                            <span class="staff-name">{{ $request->user->full_name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="leave-type-name">
                                            @if($request->leaveType && $request->leaveType->color)
                                                <span class="color-indicator" style="background-color: {{ $request->leaveType->color }};"></span>
                                            @endif
                                            {{ $request->leaveType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-range">
                                            {{ $request->start_date->format('d M Y') }}
                                            @if($request->start_date->format('Y-m-d') !== $request->end_date->format('Y-m-d'))
                                                - {{ $request->end_date->format('d M Y') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">{{ number_format($request->total_days, 1) }}</td>
                                    <td>
                                        @switch($request->status)
                                            @case('pending')
                                                <span class="status-badge status-pending">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="status-badge status-approved">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="status-badge status-rejected">Rejected</span>
                                                @break
                                            @case('cancelled')
                                                <span class="status-badge status-cancelled">Cancelled</span>
                                                @break
                                            @default
                                                <span class="status-badge">{{ ucfirst($request->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        {{ $request->submitted_at ? $request->submitted_at->format('d M Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('leave.requests.show', $request) }}"
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
                        Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} requests
                    </div>
                    {{ $requests->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <h4>No Leave Requests Found</h4>
                    @if($directReports->count() > 0)
                        <p>No leave requests found matching your filters. Try adjusting your search criteria.</p>
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
            // Initialize Choices.js for dropdowns
            const yearSelect = document.getElementById('year');
            const staffSelect = document.getElementById('staff_id');
            const leaveTypeSelect = document.getElementById('leave_type_id');

            if (yearSelect) {
                new Choices(yearSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    allowHTML: false
                });
            }

            if (staffSelect) {
                new Choices(staffSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select staff...',
                    removeItemButton: true,
                    allowHTML: false
                });
            }

            if (leaveTypeSelect) {
                new Choices(leaveTypeSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select type...',
                    removeItemButton: true,
                    allowHTML: false
                });
            }

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection

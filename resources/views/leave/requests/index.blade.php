@extends('layouts.master')
@section('title')
    My Leave Requests
@endsection
@section('css')
    <style>
        .leave-requests-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .leave-requests-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .leave-requests-body {
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
            margin-bottom: 0;
        }

        .request-id {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
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
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .leave-requests-header {
                padding: 20px;
            }

            .leave-requests-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('dashboard') }}">Dashboard</a>
        @endslot
        @slot('title')
            My Leave Requests
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

    <div class="leave-requests-container">
        <div class="leave-requests-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">My Leave Requests</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View and track your leave request history</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total'] }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['pending'] }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['approved'] }}</h4>
                                <small class="opacity-75">Approved</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['rejected'] }}</h4>
                                <small class="opacity-75">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="leave-requests-body">
            <div class="help-text">
                <div class="help-title">Leave Request History</div>
                <div class="help-content">
                    View all your leave requests and their status. Click on a request to see details or take action.
                    Use the year filter to view requests from different years.
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('leave.requests.index') }}">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select name="year" id="year" class="form-select">
                                        @foreach($years as $y)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <a href="{{ route('leave.requests.index') }}" class="btn btn-light w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('leave.requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                </div>
            </div>

            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
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
                    <h4>No Leave Requests</h4>
                    <p>You haven't submitted any leave requests yet{{ $year ? " for {$year}" : '' }}.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

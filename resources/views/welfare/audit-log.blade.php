@extends('layouts.master')

@section('title')
    Welfare Audit Log
@endsection

@section('css')
    <style>
        /* Audit Container */
        .audit-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .audit-header {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .audit-body {
            padding: 24px;
        }

        /* Stats on Colored Background */
        .stat-item {
            text-align: center;
        }

        .stat-item h4 {
            font-size: 32px;
            margin: 0;
        }

        .stat-item small {
            font-size: 13px;
            opacity: 0.85;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #dee2e6;
            color: #6c757d;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f8f9fa;
            color: #495057;
        }

        /* Table Styling */
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        /* Help Text */
        .help-text {
            background: rgba(100, 116, 139, 0.05);
            padding: 16px 20px;
            border-radius: 3px;
            border-left: 3px solid #64748b;
            margin-bottom: 20px;
        }

        .help-title {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .help-content {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Button Light */
        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            Audit Log
        @endslot
    @endcomponent

    <div class="audit-container">
        <div class="audit-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">Welfare Audit Log</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track all changes and activities in the welfare system</p>
                </div>
                <div class="col-md-4">
                    @php
                        $totalLogs = $logs->total();
                        $todayLogs = $logs->where('created_at', '>=', now()->startOfDay())->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalLogs }}</h4>
                                <small class="opacity-75">Total Logs</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $todayLogs }}</h4>
                                <small class="opacity-75">Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="audit-body">
            <div class="help-text">
                <div class="help-title">Audit Trail</div>
                <div class="help-content">
                    All changes to welfare records are logged for accountability and compliance. Use filters to find specific activities.
                </div>
            </div>

            <!-- Filters -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-12 col-md-12">
                    <form method="GET" action="{{ route('welfare.audit-log') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <select name="action" class="form-select">
                                    <option value="">All Actions</option>
                                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                                    <option value="approved" {{ request('action') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('action') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="escalated" {{ request('action') === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                    <option value="assigned" {{ request('action') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="closed" {{ request('action') === 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="reopened" {{ request('action') === 'reopened' ? 'selected' : '' }}>Reopened</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <input type="date" name="date_from" class="form-control" placeholder="Date From" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <input type="date" name="date_to" class="form-control" placeholder="Date To" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-lg-1 col-md-1 col-sm-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-1 col-sm-3">
                                <a href="{{ route('welfare.audit-log') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Log Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Model</th>
                            <th>Case</th>
                            <th>Student</th>
                            <th>User</th>
                            <th>Details</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $log->action_color }}-subtle text-{{ $log->action_color }}"
                                        style="font-size: 1em;">
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td>{{ $log->model_name }}</td>
                                <td>
                                    @if ($log->welfareCase)
                                        <a href="{{ route('welfare.cases.edit', $log->welfareCase) }}">
                                            {{ $log->welfareCase->case_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->welfareCase?->student?->full_name ?? '-' }}</td>
                                <td>{{ $log->user?->full_name ?? ($log->user?->firstname . ' ' . $log->user?->lastname ?? 'System') }}
                                </td>
                                <td>
                                    @if ($log->reason)
                                        <span class="text-muted">{{ Str::limit($log->reason, 50) }}</span>
                                    @elseif(!empty($log->changed_fields))
                                        <span class="text-muted">
                                            Changed: {{ implode(', ', array_slice($log->changed_fields, 0, 3)) }}
                                            @if (count($log->changed_fields) > 3)
                                                ...
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $log->created_at->format('d M Y H:i') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No audit logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="mt-3">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif
                </div>
            </div>
        </div>
    </div>
@endsection

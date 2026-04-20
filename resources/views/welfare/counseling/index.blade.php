@extends('layouts.master')

@section('title')
    Counseling Sessions
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        /* Counseling Container */
        .counseling-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .counseling-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .counseling-body {
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

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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

        /* Action Buttons */
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px !important;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
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
            background: rgba(99, 102, 241, 0.1);
            padding: 12px 16px;
            border-radius: 3px;
            border-left: 3px solid #6366f1;
            font-size: 13px;
            color: #4f46e5;
            margin-bottom: 16px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.dashboard') }}">Welfare</a>
        @endslot
        @slot('title')
            Counseling Sessions
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="counseling-container">
        <div class="counseling-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Counseling Sessions</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage and track all counseling sessions</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $sessions->total();
                        $activeCount = $sessions->where('status', 'scheduled')->count();
                        $completedCount = $sessions->where('status', 'completed')->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activeCount }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $completedCount }}</h4>
                                <small class="opacity-75">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="counseling-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="help-text flex-grow-1 me-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Counseling Sessions:</strong> Schedule and track all student counseling sessions
                </div>
                <a href="{{ route('welfare.counseling.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Session
                </a>
            </div>

            <!-- Inline Filters -->
            <form method="GET" action="{{ route('welfare.counseling.index') }}" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size:12px;">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size:12px;">Type</label>
                        <select name="session_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="individual" {{ request('session_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="group" {{ request('session_type') === 'group' ? 'selected' : '' }}>Group</option>
                            <option value="family" {{ request('session_type') === 'family' ? 'selected' : '' }}>Family</option>
                            <option value="crisis" {{ request('session_type') === 'crisis' ? 'selected' : '' }}>Crisis</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size:12px;">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size:12px;">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size:12px;">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Student name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('welfare.counseling.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Sessions Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Counselor</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>
                                    <strong>{{ $session->session_date->format('d M Y') }}</strong>
                                    <small class="d-block text-muted">{{ $session->session_date->format('H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('welfare.counseling.edit', $session) }}">
                                        {{ $session->student->full_name ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-info-subtle text-info">{{ ucfirst($session->session_type) }}</span>
                                </td>
                                <td>{{ $session->counsellor->full_name ?? '-' }}</td>
                                <td>{{ $session->duration_minutes ?? '-' }} mins</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary',
                                            'no_show' => 'warning',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$session->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($session->status === 'scheduled')
                                        <a href="{{ route('welfare.counseling.edit', $session) }}"
                                            class="action-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times font-size-24 d-block mb-2"></i>
                                    No sessions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sessions->hasPages())
                <div class="mt-3">
                    {{ $sessions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

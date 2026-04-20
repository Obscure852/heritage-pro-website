@extends('layouts.master')
@section('title')
    Outstanding Balances
@endsection
@section('css')
    <style>
        .fee-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fee-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
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

        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
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

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            font-size: 14px;
        }

        .balance-cell {
            font-weight: 600;
            color: #dc2626;
        }

        .term-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .student-id {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .gender-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .gender-male {
            background: #dbeafe;
            color: #1e40af;
        }

        .gender-female {
            background: #fce7f3;
            color: #9d174d;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-current {
            background: #d1fae5;
            color: #065f46;
        }

        .status-left {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-suspended {
            background: #fef3c7;
            color: #92400e;
        }

        .status-other {
            background: #f3f4f6;
            color: #4b5563;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Fee Administration
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
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

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Outstanding Balances</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Students with outstanding fee balances for the selected year</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalBalance = $students->sum('balance');
                        $studentCount = $students->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $studentCount }}</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totalBalance) }}</h4>
                                <small class="opacity-75">Total Outstanding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Balance Management</div>
                <div class="help-content">
                    View students with outstanding balances for the selected year. Click "View Clearance" to see detailed clearance status and manage clearance overrides for individual students.
                </div>
            </div>

            <form method="GET" action="{{ route('fees.balance.outstanding') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search student..."
                                            id="searchInput" name="search" value="{{ $filters['search'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" id="yearFilter" name="year">
                                        <option value="">Select Year</option>
                                        @foreach ($years ?? [] as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" id="gradeFilter" name="grade_id">
                                        <option value="">All Grades</option>
                                        @foreach ($grades ?? [] as $grade)
                                            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <button type="submit" class="btn btn-light w-100">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <a href="{{ route('fees.balance.outstanding') }}" class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                            <a href="{{ route('fees.refunds.index') }}" class="btn btn-info">
                                <i class="far fa-file-alt me-1"></i> Refunds & Credit Notes
                            </a>
                            @can('approve-refunds')
                                <a href="{{ route('fees.refunds.pending') }}" class="btn btn-warning">
                                    <i class="far fa-clock me-1"></i> Pending Approvals
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="outstandingTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th class="text-end">Balance</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $item)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $item['student_name'] }}</strong>
                                        <div class="student-id">#{{ $item['student']->id }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if ($item['student']->currentGrade)
                                        <span class="grade-badge">{{ $item['student']->currentGrade->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $gender = strtolower($item['student']->gender ?? '');
                                    @endphp
                                    @if ($gender === 'male' || $gender === 'm')
                                        <span class="gender-badge gender-male">Male</span>
                                    @elseif ($gender === 'female' || $gender === 'f')
                                        <span class="gender-badge gender-female">Female</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $status = $item['student']->status ?? '';
                                        $statusLower = strtolower($status);
                                        $statusClass = match(true) {
                                            $statusLower === 'current' => 'status-current',
                                            $statusLower === 'left' || $statusLower === 'withdrawn' => 'status-left',
                                            $statusLower === 'suspended' => 'status-suspended',
                                            default => 'status-other'
                                        };
                                    @endphp
                                    @if ($status)
                                        <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end balance-cell">
                                    {{ format_currency($item['balance']) }}
                                </td>
                                <td class="text-center">
                                    {{ $item['invoice_count'] }}
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.balance.clearance', ['student' => $item['student_id'], 'year' => $filters['year'] ?? '']) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="View Clearance Status">
                                            <i class="bx bx-check-shield"></i>
                                        </a>
                                        <a href="{{ route('fees.collection.students.account', ['student' => $item['student_id'], 'year' => $filters['year'] ?? '']) }}"
                                            class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="View Fee Account">
                                            <i class="bx bx-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-students-row">
                                <td colspan="7">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-check-circle" style="font-size: 48px; opacity: 0.3; color: #10b981;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Outstanding Balances</p>
                                        <p class="text-muted" style="font-size: 13px;">All students are cleared for this year</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeTooltips();
        });

        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }
    </script>
@endsection

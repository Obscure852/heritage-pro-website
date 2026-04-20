@extends('layouts.master')
@section('title')
    Debtors List
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

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .balance-cell {
            font-weight: 600;
            color: #dc2626;
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

        .student-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .student-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .overdue-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .overdue-low { background: #fef3c7; color: #92400e; }
        .overdue-medium { background: #fed7aa; color: #9a3412; }
        .overdue-high { background: #fee2e2; color: #991b1b; }

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
            <a class="text-muted font-size-14" href="{{ route('fees.reports.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            Debtors List
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

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Debtors List</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Students with outstanding fee balances</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalBalance = collect($debtors ?? [])->sum(function($d) { return (float)$d['balance']; });
                        $debtorCount = count($debtors ?? []);
                    @endphp
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $debtorCount }}</h4>
                                <small class="opacity-75">Total Debtors</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totalBalance, 0) }}</h4>
                                <small class="opacity-75">Total Outstanding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Debtors List Report</div>
                <div class="help-content">
                    View all students with outstanding fee balances sorted by highest debt. Filter by year, grade, or minimum balance to focus on specific groups. Use the action buttons to view student accounts or generate statements.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.debtors-list') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="year">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="grade_id">
                                        <option value="">All Grades</option>
                                        @foreach ($grades ?? [] as $grade)
                                            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="number" class="form-control" name="min_balance" placeholder="Min Balance" value="{{ $filters['min_balance'] ?? '' }}">
                                </div>
                                <div class="col-lg-4 col-md-3 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.debtors-list') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.export.debtors-list', request()->query()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            @if (count($debtors ?? []) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student Number</th>
                                <th>Grade</th>
                                <th class="text-end">Total Invoiced</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Days Overdue</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($debtors as $debtor)
                                @php
                                    $daysOverdue = $debtor['oldest_overdue_days'] ?? 0;
                                    $overdueClass = match(true) {
                                        $daysOverdue <= 30 => 'overdue-low',
                                        $daysOverdue <= 60 => 'overdue-medium',
                                        default => 'overdue-high'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('fees.collection.students.account', ['student' => $debtor['student_id']]) }}" class="student-link">
                                            {{ $debtor['student_name'] }}
                                        </a>
                                    </td>
                                    <td>{{ $debtor['student_number'] ?? 'N/A' }}</td>
                                    <td><span class="grade-badge">{{ $debtor['grade_name'] ?? 'N/A' }}</span></td>
                                    <td class="text-end amount-cell">{{ format_currency($debtor['total_invoiced'] ?? 0) }}</td>
                                    <td class="text-end">{{ format_currency($debtor['total_paid'] ?? 0) }}</td>
                                    <td class="text-end balance-cell">{{ format_currency($debtor['balance']) }}</td>
                                    <td class="text-center">
                                        @if ($daysOverdue > 0)
                                            <span class="overdue-badge {{ $overdueClass }}">{{ $daysOverdue }} days</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('fees.collection.students.account', ['student' => $debtor['student_id']]) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View Account">
                                                <i class="bx bx-receipt"></i>
                                            </a>
                                            <a href="{{ route('fees.reports.student-statement', ['student_id' => $debtor['student_id']]) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="View Statement">
                                                <i class="bx bx-file"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-check-circle d-block" style="color: #10b981;"></i>
                    <p class="mt-3 mb-0">No Debtors Found</p>
                    <p class="text-muted" style="font-size: 13px;">All students have cleared their fee balances</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
        });

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

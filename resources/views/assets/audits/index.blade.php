@extends('layouts.master')
@section('title', 'Asset Audits')

@section('css')
    <style>
        .audits-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .audits-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .audits-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .audits-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .audits-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            border-left: 4px solid #4e73df;
            padding: 16px 20px;
            margin-bottom: 20px;
            border-radius: 0 3px 3px 0;
        }

        .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }

        .help-content {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        /* Action Buttons */
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
            min-width: 220px;
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

        /* Filter Controls */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 3px;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .controls .input-group-text {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-right: none;
            color: #6b7280;
        }

        .controls .input-group .form-control {
            border-left: none;
        }

        .controls .input-group .form-control:focus {
            border-left: none;
        }

        .audits-table {
            margin-bottom: 0;
        }

        .audits-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .audits-table tbody td {
            padding: 14px 10px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .audits-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .audit-code-link {
            font-weight: 600;
            color: #4e73df;
            text-decoration: none;
        }

        .audit-code-link:hover {
            text-decoration: underline;
            color: #3b5fc0;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-badge.in-progress {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .count-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .count-badge.primary {
            background: #dbeafe;
            color: #1e40af;
        }

        .count-badge.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .count-badge.warning {
            background: #fef3c7;
            color: #b45309;
        }

        .count-badge.muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .next-audit-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 6px;
        }

        .next-audit-badge.overdue {
            background: #fee2e2;
            color: #dc2626;
        }

        .next-audit-badge.soon {
            background: #fef3c7;
            color: #b45309;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
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

        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #9ca3af;
            font-size: 32px;
        }

        .empty-state h5 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .empty-state .btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }

        .empty-state .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .audits-header {
                padding: 20px;
            }

            .controls .row > div {
                margin-bottom: 12px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Audits
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="audits-container">
        <div class="audits-header">
            <h4><i class="bx bx-clipboard me-2"></i>Asset Audits</h4>
            <p>Schedule, conduct, and track asset audits across your organization</p>
        </div>

        <div class="audits-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>About Asset Audits</div>
                <p class="help-content">Asset audits help verify the presence, condition, and location of your assets. Schedule regular audits to maintain accurate inventory records and identify missing or damaged items.</p>
            </div>

            <!-- Filters and Action Buttons Row -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <form id="filterForm" method="GET" action="{{ route('audits.index') }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search audit..." name="search" value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="conducted_by">
                                        <option value="">All Users</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ request('conducted_by') == $user->id ? 'selected' : '' }}>
                                                {{ $user->lastname }}, {{ $user->firstname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From">
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <a href="{{ route('audits.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Audit
                        </a>
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.trend-analysis') }}">
                                        <i class="fas fa-chart-line text-primary"></i> Audit Trend Analysis
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.comparison-report') }}">
                                        <i class="fas fa-balance-scale text-info"></i> Multi-Audit Comparison
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('audits.performance-dashboard') }}">
                                        <i class="fas fa-tachometer-alt text-success"></i> Performance Dashboard
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audits Table -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold"><i class="bx bx-list-ul me-2"></i>Audit Records</h6>
                <span class="count-badge primary">{{ $audits->total() }} Audits</span>
            </div>

            <div class="table-responsive">
                <table class="table audits-table">
                    <thead>
                        <tr>
                            <th>Audit Code</th>
                            <th>Audit Date</th>
                            <th>Status</th>
                            <th>Assets</th>
                            <th>Missing</th>
                            <th>Maintenance</th>
                            <th>Conducted By</th>
                            <th>Next Audit</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            <tr>
                                <td>
                                    <a href="{{ route('audits.show', $audit->id) }}" class="audit-code-link">
                                        {{ $audit->audit_code }}
                                    </a>
                                </td>
                                <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                                <td>
                                    @if ($audit->status === 'Pending')
                                        <span class="status-badge pending">Pending</span>
                                    @elseif($audit->status === 'In Progress')
                                        <span class="status-badge in-progress">In Progress</span>
                                    @elseif($audit->status === 'Completed')
                                        <span class="status-badge completed">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $audit->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="count-badge primary">{{ $audit->auditItems->count() }}</span>
                                </td>
                                <td>
                                    @php $missingCount = $audit->getMissingAssetsCount(); @endphp
                                    @if($missingCount > 0)
                                        <span class="count-badge danger">{{ $missingCount }}</span>
                                    @else
                                        <span class="count-badge muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @php $maintenanceCount = $audit->getMaintenanceNeededCount(); @endphp
                                    @if($maintenanceCount > 0)
                                        <span class="count-badge warning">{{ $maintenanceCount }}</span>
                                    @else
                                        <span class="count-badge muted">0</span>
                                    @endif
                                </td>
                                <td>{{ $audit->conductedByUser->firstname ?? '' }} {{ $audit->conductedByUser->lastname ?? '' }}</td>
                                <td>
                                    @if ($audit->next_audit_date)
                                        {{ $audit->next_audit_date->format('M d, Y') }}
                                        @if ($audit->next_audit_date->isPast())
                                            <span class="next-audit-badge overdue">Overdue</span>
                                        @elseif($audit->next_audit_date->diffInDays(now()) <= 30)
                                            <span class="next-audit-badge soon">Soon</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('audits.show', $audit->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>

                                        @if ($audit->status === 'Pending')
                                            <form method="POST" action="{{ route('audits.start', $audit->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Start Audit"
                                                        onclick="return confirm('Are you sure you want to start this audit?');">
                                                    <i class="bx bx-play"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('audits.edit', $audit->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        @elseif($audit->status === 'In Progress')
                                            <a href="{{ route('audits.conduct', $audit->id) }}" class="btn btn-sm btn-outline-warning" title="Continue Audit">
                                                <i class="bx bx-check-square"></i>
                                            </a>
                                        @endif

                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if ($audit->status === 'Completed')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('audits.missing-report', $audit->id) }}">
                                                            <i class="bx bx-error-circle"></i> Missing Assets
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('audits.maintenance-report', $audit->id) }}">
                                                            <i class="bx bx-wrench"></i> Maintenance Needed
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('audits.summary', $audit->id) }}">
                                                            <i class="bx bx-chart"></i> Audit Summary
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('audits.export', $audit->id) }}">
                                                            <i class="bx bx-export"></i> Export Report
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                @endif

                                                @if ($audit->status !== 'In Progress')
                                                    <li>
                                                        <form method="POST" action="{{ route('audits.destroy', $audit->id) }}"
                                                              id="delete-form-{{ $audit->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this audit? This action cannot be undone.');">
                                                                <i class="bx bx-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-clipboard"></i>
                                        </div>
                                        <h5>No Audits Found</h5>
                                        <p>No audit records match your current search criteria.</p>
                                        <a href="{{ route('audits.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus-circle me-1"></i> Create First Audit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $audits->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelects = document.querySelectorAll('#filterForm select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Date filter change
            const dateInput = document.querySelector('#filterForm input[type="date"]');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }

            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', function() {
                window.location.href = '{{ route('audits.index') }}';
            });
        });
    </script>
@endsection

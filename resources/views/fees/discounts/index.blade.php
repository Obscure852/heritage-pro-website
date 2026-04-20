@extends('layouts.master')
@section('title')
    Student Discounts
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

        .sibling-suggestions-panel {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .sibling-suggestions-panel h5 {
            color: #92400e;
            margin: 0 0 12px 0;
        }

        .sibling-suggestions-panel p {
            color: #92400e;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .sibling-suggestions-panel .table {
            margin-bottom: 0;
            background: white;
        }

        .sibling-suggestions-panel .table thead th {
            background: #fef3c7;
            border-bottom: 1px solid #f59e0b;
            color: #92400e;
            font-size: 12px;
        }

        .sibling-suggestions-panel .table tbody td {
            vertical-align: middle;
        }

        .sibling-group-header {
            background: #fff7ed !important;
            font-weight: 600;
            color: #9a3412;
            font-size: 13px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .discount-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            background: #e0e7ff;
            color: #3730a3;
        }

        .year-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
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

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            font-weight: 500;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .notes-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notes-cell:hover {
            white-space: normal;
            overflow: visible;
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

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <form method="GET" action="{{ route('fees.discounts.index') }}">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach ($years ?? [] as $year)
                        <option value="{{ $year }}" {{ ($currentYear ?? date('Y')) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Student Discounts</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage discount assignments for students</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $studentDiscounts->total() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $discountTypes->count() }}</h4>
                                <small class="opacity-75">Types</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $siblingCandidates->count() }}</h4>
                                <small class="opacity-75">Suggestions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            {{-- Sibling Suggestions Panel --}}
            @if($siblingCandidates->isNotEmpty())
                <div class="sibling-suggestions-panel">
                    <h5>
                        <i class="fas fa-users me-2"></i>Sibling Discount Suggestions
                    </h5>
                    <p>
                        The following students share a sponsor and may qualify for sibling discounts:
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Sponsor</th>
                                    <th>Grade</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siblingCandidates->groupBy('sponsor_id') as $sponsorId => $siblings)
                                    <tr class="sibling-group-header">
                                        <td colspan="4">
                                            <i class="fas fa-home me-1"></i>
                                            {{ $siblings->first()->sponsor?->name ?? 'Unknown Sponsor' }}
                                            ({{ $siblings->count() }} students)
                                        </td>
                                    </tr>
                                    @foreach($siblings as $student)
                                    <tr>
                                        <td>{{ $student->full_name }}</td>
                                        <td><span class="text-muted">-</span></td>
                                        <td>{{ $student->currentGrade?->name ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('fees.discounts.create', ['student_id' => $student->id, 'year' => $selectedYear ?? date('Y')]) }}"
                                                class="btn btn-sm btn-warning">
                                                <i class="fas fa-plus me-1"></i> Assign
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Help Text --}}
            <div class="help-text">
                <div class="help-title">Student Discount Management</div>
                <div class="help-content">
                    Assign discounts to individual students for specific years. Discounts reduce the amount owed on fee invoices.
                    The system automatically detects students with siblings (sharing the same sponsor) who may qualify for sibling discounts.
                </div>
            </div>

            {{-- Filters --}}
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search student name..." id="searchInput" value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="discountTypeFilter">
                                    <option value="">All Discount Types</option>
                                    @foreach($discountTypes as $type)
                                        <option value="{{ $type->id }}" {{ ($filters['discount_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('fees.discounts.create', ['year' => $selectedYear ?? date('Y')]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Assign Discount
                    </a>
                </div>
            </div>

            {{-- Student Discounts Table --}}
            <div class="table-responsive">
                <table id="studentDiscountsTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Discount Type</th>
                            <th>Year</th>
                            <th>Assigned By</th>
                            <th>Date</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentDiscounts as $index => $discount)
                            <tr class="discount-row"
                                data-student="{{ strtolower($discount->student?->full_name ?? '') }}"
                                data-type="{{ $discount->discount_type_id }}">
                                <td>{{ $studentDiscounts->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $discount->student?->full_name ?? 'Unknown' }}</strong>
                                </td>
                                <td>
                                    <span class="discount-badge">{{ $discount->discountType?->code ?? 'N/A' }}</span>
                                    <span class="ms-1 text-muted" style="font-size: 12px;">{{ $discount->discountType?->name ?? '' }}</span>
                                </td>
                                <td><span class="year-badge">{{ $discount->year }}</span></td>
                                <td>{{ $discount->assignedBy?->name ?? 'System' }}</td>
                                <td>{{ $discount->created_at?->format('d M Y') }}</td>
                                <td class="notes-cell" title="{{ $discount->notes }}">
                                    {{ $discount->notes ?? '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <form action="{{ route('fees.discounts.destroy', $discount->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmDelete()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Remove Discount">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-discounts-row">
                                <td colspan="8">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-percentage" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Student Discounts</p>
                                        <p class="text-muted" style="font-size: 13px;">Assign discounts to students to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($studentDiscounts->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $studentDiscounts->appends($filters)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
            initializeAlertDismissal();
        });

        function initializeFilters() {
            const searchInput = document.getElementById('searchInput');
            const discountTypeFilter = document.getElementById('discountTypeFilter');
            const resetBtn = document.getElementById('resetFilters');

            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 500);
            });

            discountTypeFilter.addEventListener('change', applyFilters);

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                discountTypeFilter.value = '';
                applyFilters();
            });
        }

        function applyFilters() {
            const searchInput = document.getElementById('searchInput');
            const discountTypeFilter = document.getElementById('discountTypeFilter');

            const url = new URL(window.location.href);

            if (searchInput.value) {
                url.searchParams.set('search', searchInput.value);
            } else {
                url.searchParams.delete('search');
            }

            if (discountTypeFilter.value) {
                url.searchParams.set('discount_type_id', discountTypeFilter.value);
            } else {
                url.searchParams.delete('discount_type_id');
            }

            // Reset to first page when filters change
            url.searchParams.delete('page');

            window.location.href = url.toString();
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

        function confirmDelete() {
            return confirm('Are you sure you want to remove this discount from the student? This action cannot be undone.');
        }
    </script>
@endsection

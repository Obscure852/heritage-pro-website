@extends('layouts.master')
@section('title')
    Discount Types
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .applies-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .applies-all { background: #e0e7ff; color: #3730a3; }
        .applies-tuition { background: #fef3c7; color: #92400e; }

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

        .percentage-value {
            font-weight: 600;
            color: #059669;
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
                    <h3 style="margin:0;">Discount Types</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage discount type definitions</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $discountTypes->total() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $discountTypes->where('is_active', true)->count() }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Discount Types Directory</div>
                <div class="help-content">
                    Discount types define percentage-based fee reductions that can be assigned to students.
                    Common examples include sibling discounts, staff discounts, and scholarship discounts.
                    Discounts can apply to all fees or tuition fees only.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name or code..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="appliesToFilter">
                                    <option value="">All Applies To</option>
                                    @foreach ($appliesOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('fees.setup.discount-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Discount Type
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="discountTypesTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Percentage</th>
                            <th>Applies To</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($discountTypes as $index => $discountType)
                            <tr class="discount-type-row"
                                data-name="{{ strtolower($discountType->name) }}"
                                data-code="{{ strtolower($discountType->code) }}"
                                data-applies-to="{{ $discountType->applies_to }}"
                                data-status="{{ $discountType->is_active ? 'active' : 'inactive' }}">
                                <td>{{ $discountTypes->firstItem() + $index }}</td>
                                <td><code>{{ $discountType->code }}</code></td>
                                <td>{{ $discountType->name }}</td>
                                <td>
                                    <span class="percentage-value">{{ number_format($discountType->percentage, 2) }}%</span>
                                </td>
                                <td>
                                    @if ($discountType->applies_to === 'all')
                                        <span class="applies-badge applies-all">All Fees</span>
                                    @else
                                        <span class="applies-badge applies-tuition">Tuition Only</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($discountType->is_active)
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.setup.discount-types.edit', $discountType->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="Edit Discount Type">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('fees.setup.discount-types.destroy', $discountType->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmDelete()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Discount Type">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-discount-types-row">
                                <td colspan="7">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-percent" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Discount Types</p>
                                        <p class="text-muted" style="font-size: 13px;">Create your first discount type to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($discountTypes->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $discountTypes->links() }}
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
            const appliesToFilter = document.getElementById('appliesToFilter');
            const statusFilter = document.getElementById('statusFilter');
            const resetBtn = document.getElementById('resetFilters');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const appliesToValue = appliesToFilter.value.toLowerCase();
                const statusValue = statusFilter.value.toLowerCase();

                const rows = document.querySelectorAll('.discount-type-row');

                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const code = row.dataset.code || '';
                    const appliesTo = row.dataset.appliesTo || '';
                    const status = row.dataset.status || '';

                    const matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                    const matchesAppliesTo = !appliesToValue || appliesTo === appliesToValue;
                    const matchesStatus = !statusValue || status === statusValue;

                    row.style.display = (matchesSearch && matchesAppliesTo && matchesStatus) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterRows);
            appliesToFilter.addEventListener('change', filterRows);
            statusFilter.addEventListener('change', filterRows);

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                appliesToFilter.value = '';
                statusFilter.value = '';
                filterRows();
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

        function confirmDelete() {
            return confirm('Are you sure you want to delete this discount type? This action cannot be undone.');
        }
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Fee Types
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
        .status-optional { background: #dbeafe; color: #1e40af; }
        .status-required { background: #fef3c7; color: #92400e; }

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

        .category-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .category-tuition { background: #e0e7ff; color: #3730a3; }
        .category-boarding { background: #fce7f3; color: #9d174d; }
        .category-transport { background: #d1fae5; color: #065f46; }
        .category-uniform { background: #fef3c7; color: #92400e; }
        .category-books { background: #e0f2fe; color: #0369a1; }
        .category-activity { background: #f3e8ff; color: #6b21a8; }
        .category-other { background: #f3f4f6; color: #4b5563; }

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

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Fee Types</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage fee type definitions</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $feeTypes->count() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $feeTypes->where('is_active', true)->count() }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $feeTypes->where('is_optional', true)->count() }}</h4>
                                <small class="opacity-75">Optional</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Fee Types Directory</div>
                <div class="help-content">
                    Fee types define the different categories of fees that can be charged to students.
                    Each fee type has a unique code and can be marked as optional or required.
                    Active fee types can be used when creating fee structures.
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
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="tuition">Tuition</option>
                                    <option value="boarding">Boarding</option>
                                    <option value="transport">Transport</option>
                                    <option value="uniform">Uniform</option>
                                    <option value="books">Books</option>
                                    <option value="activity">Activity</option>
                                    <option value="other">Other</option>
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
                    <a href="{{ route('fees.setup.types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Fee Type
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="feeTypesTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Optional</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feeTypes as $index => $feeType)
                            <tr class="fee-type-row"
                                data-name="{{ strtolower($feeType->name) }}"
                                data-code="{{ strtolower($feeType->code) }}"
                                data-category="{{ strtolower($feeType->category) }}"
                                data-status="{{ $feeType->is_active ? 'active' : 'inactive' }}">
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $feeType->code }}</code></td>
                                <td>{{ $feeType->name }}</td>
                                <td>
                                    @php
                                        $categoryClass = 'category-' . strtolower($feeType->category ?? 'other');
                                    @endphp
                                    <span class="category-badge {{ $categoryClass }}">{{ $feeType->category ?? 'Other' }}</span>
                                </td>
                                <td>
                                    @if ($feeType->is_optional)
                                        <span class="status-badge status-optional">Optional</span>
                                    @else
                                        <span class="status-badge status-required">Required</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($feeType->is_active)
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.setup.types.edit', $feeType->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="Edit Fee Type">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('fees.setup.types.destroy', $feeType->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmDelete()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Fee Type">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-fee-types-row">
                                <td colspan="7">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-tags" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Fee Types</p>
                                        <p class="text-muted" style="font-size: 13px;">Create your first fee type to get started</p>
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
            initializeFilters();
            initializeAlertDismissal();
        });

        function initializeFilters() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const resetBtn = document.getElementById('resetFilters');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoryValue = categoryFilter.value.toLowerCase();
                const statusValue = statusFilter.value.toLowerCase();

                const rows = document.querySelectorAll('.fee-type-row');

                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const code = row.dataset.code || '';
                    const category = row.dataset.category || '';
                    const status = row.dataset.status || '';

                    const matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                    const matchesCategory = !categoryValue || category === categoryValue;
                    const matchesStatus = !statusValue || status === statusValue;

                    row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterRows);
            categoryFilter.addEventListener('change', filterRows);
            statusFilter.addEventListener('change', filterRows);

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                categoryFilter.value = '';
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
            return confirm('Are you sure you want to delete this fee type? This action cannot be undone.');
        }
    </script>
@endsection

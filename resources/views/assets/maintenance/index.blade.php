@extends('layouts.master')
@section('title', 'Asset Maintenance')

@section('css')
    <style>
        .maintenance-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .maintenance-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .maintenance-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .maintenance-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .maintenance-body {
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

        .maintenance-table {
            margin-bottom: 0;
        }

        .maintenance-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .maintenance-table tbody td {
            padding: 14px 10px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .maintenance-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .asset-link {
            font-weight: 600;
            color: #4e73df;
            text-decoration: none;
        }

        .asset-link:hover {
            text-decoration: underline;
            color: #3b5fc0;
        }

        .asset-code {
            color: #6b7280;
            font-size: 12px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.scheduled {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }

        .status-badge.in-progress {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-badge.cancelled {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .type-badge.preventive {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-badge.corrective {
            background: #fef3c7;
            color: #b45309;
        }

        .type-badge.upgrade {
            background: #ede9fe;
            color: #6d28d9;
        }

        .cost-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            background: #f3f4f6;
            color: #374151;
        }

        .cost-badge.not-specified {
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

        .count-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .maintenance-header {
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
            Maintenance Records
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

    <div class="maintenance-container">
        <div class="maintenance-header">
            <h4><i class="bx bx-wrench me-2"></i>Asset Maintenance</h4>
            <p>Manage and track maintenance records for all assets</p>
        </div>

        <div class="maintenance-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>About Maintenance Records</div>
                <p class="help-content">Track all maintenance activities including preventive, corrective, and upgrade work. Schedule future maintenance and monitor costs across your asset portfolio.</p>
            </div>

            <!-- Filters and Action Buttons Row -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <form id="filterForm" method="GET" action="{{ route('assets.maintenance.index') }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="maintenance_type">
                                        <option value="">All Types</option>
                                        <option value="Preventive" {{ request('maintenance_type') == 'Preventive' ? 'selected' : '' }}>Preventive</option>
                                        <option value="Corrective" {{ request('maintenance_type') == 'Corrective' ? 'selected' : '' }}>Corrective</option>
                                        <option value="Upgrade" {{ request('maintenance_type') == 'Upgrade' ? 'selected' : '' }}>Upgrade</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="asset_id">
                                        <option value="">All Assets</option>
                                        @foreach ($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="contact_id">
                                        <option value="">All Business Contacts</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('contact_id', request('vendor_id')) == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                        <a href="{{ route('assets.maintenance.create-with-select') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Schedule Maintenance
                        </a>
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.scheduled-maintenance-report') }}">
                                        <i class="fas fa-calendar-alt text-primary"></i> Scheduled Maintenance
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.maintenance-cost-analysis') }}">
                                        <i class="fas fa-dollar-sign text-success"></i> Cost Analysis
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold"><i class="bx bx-list-ul me-2"></i>Maintenance Records</h6>
                <span class="count-badge">{{ $maintenances->total() }} Records</span>
            </div>

            <!-- Maintenance Table -->
            <div class="table-responsive">
                <table class="table maintenance-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Business Contact</th>
                            <th>Status</th>
                            <th>Cost</th>
                            <th>Description</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                            <tr>
                                <td>
                                    <a href="{{ route('assets.show', $maintenance->asset_id) }}" class="asset-link">
                                        {{ $maintenance->asset->name ?? 'N/A' }}
                                    </a>
                                    <small class="d-block asset-code">{{ $maintenance->asset->asset_code ?? '' }}</small>
                                </td>
                                <td>
                                    @php $typeClass = strtolower($maintenance->maintenance_type); @endphp
                                    <span class="type-badge {{ $typeClass }}">{{ $maintenance->maintenance_type }}</span>
                                </td>
                                <td>{{ $maintenance->maintenance_date->format('M d, Y') }}</td>
                                <td>{{ $maintenance->vendor->name ?? 'In-house' }}</td>
                                <td>
                                    @if ($maintenance->status == 'Scheduled')
                                        <span class="status-badge scheduled">Scheduled</span>
                                    @elseif($maintenance->status == 'In Progress')
                                        <span class="status-badge in-progress">In Progress</span>
                                    @elseif($maintenance->status == 'Completed')
                                        <span class="status-badge completed">Completed</span>
                                    @elseif($maintenance->status == 'Cancelled')
                                        <span class="status-badge cancelled">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $maintenance->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($maintenance->cost)
                                        <span class="cost-badge">{{ number_format($maintenance->cost, 2) }}</span>
                                    @else
                                        <span class="cost-badge not-specified">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ $maintenance->description }}">
                                        {{ Str::limit($maintenance->description ?? '-', 30) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('assets.edit-maintenance', $maintenance->id) }}"
                                            class="btn btn-sm btn-outline-info" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        @if (in_array($maintenance->status, ['Scheduled', 'In Progress']))
                                            <a href="{{ route('assets.maintenance-complete', $maintenance->id) }}"
                                                class="btn btn-sm btn-outline-success" title="Complete">
                                                <i class="bx bx-check-circle"></i>
                                            </a>
                                            <a href="javascript:void(0)"
                                                onclick="if(confirm('Are you sure you want to cancel this maintenance?')) { window.location.href = '{{ route('assets.maintenance-cancel', $maintenance->id) }}'; }"
                                                class="btn btn-sm btn-outline-warning" title="Cancel">
                                                <i class="bx bx-x-circle"></i>
                                            </a>
                                        @endif

                                        <form method="POST" action="{{ route('assets.destroy-maintenance', $maintenance->id) }}"
                                            id="delete-form-{{ $maintenance->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="if(confirm('Are you sure you want to delete this maintenance record?')) { document.getElementById('delete-form-{{ $maintenance->id }}').submit(); }">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bx bx-wrench"></i>
                                        </div>
                                        <h5>No Maintenance Records Found</h5>
                                        <p>No maintenance records match your current search criteria.</p>
                                        <a href="{{ route('assets.maintenance.create-with-select') }}" class="btn btn-primary">
                                            <i class="fas fa-plus-circle me-1"></i> Schedule Maintenance
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $maintenances->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit on filter change
            const filterSelects = document.querySelectorAll('#filterForm select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', function() {
                window.location.href = '{{ route('assets.maintenance.index') }}';
            });
        });
    </script>
@endsection

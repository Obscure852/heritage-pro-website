@extends('layouts.master')
@section('title', 'Scheduled Maintenance Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.maintenance.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Scheduled Maintenance Report
        @endslot
    @endcomponent

    @section('css')
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                #maintenanceTable, #maintenanceTable * {
                    visibility: visible;
                }
                #maintenanceTable {
                    position: absolute;
                    left: 0;
                    top: 0;
                }
                .btn-group, .dropdown-menu {
                    display: none;
                }
            }
        </style>
    @endsection

    @if(session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <div class="d-flex gap-2">
                    <a href="#" onclick="printTable()" class="text-muted">
                        <i class="bx bx-printer me-1 font-size-18"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Records</p>
                            <h4 class="mb-2">{{ $summary['total_records'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Scheduled</p>
                            <h4 class="mb-2 text-info">{{ $summary['total_scheduled'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">In Progress</p>
                            <h4 class="mb-2 text-warning">{{ $summary['total_in_progress'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Overdue</p>
                            <h4 class="mb-2 text-danger">{{ $summary['overdue_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Completed</p>
                            <h4 class="mb-2 text-success">{{ $summary['total_completed'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Cancelled</p>
                            <h4 class="mb-2 text-secondary">{{ $summary['total_cancelled'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter Maintenance Records</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="filterDropdown">
                    <a class="dropdown-item" href="{{ route('assets.scheduled-maintenance-report') }}" id="clearFilters">Clear Filters</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('assets.scheduled-maintenance-report') }}">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="status">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Statuses</option>
                            <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="maintenance_type">Type</label>
                        <select class="form-select form-select-sm" name="maintenance_type">
                            <option value="">All Types</option>
                            <option value="Corrective" {{ request('maintenance_type') == 'Corrective' ? 'selected' : '' }}>Corrective</option>
                            <option value="Preventative" {{ request('maintenance_type') == 'Preventative' ? 'selected' : '' }}>Preventative</option>
                            <option value="Upgrade" {{ request('maintenance_type') == 'Upgrade' ? 'selected' : '' }}>Upgrade</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="contact_id">Business Contact</label>
                        <select class="form-select form-select-sm" name="contact_id">
                            <option value="">All Business Contacts</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('contact_id', request('vendor_id')) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="category_id">Category</label>
                        <select class="form-select form-select-sm" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="venue_id">Location</label>
                        <select class="form-select form-select-sm" name="venue_id">
                            <option value="">All Locations</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search"
                               placeholder="Asset name, description..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('assets.scheduled-maintenance-report') }}" class="btn btn-secondary btn-sm me-2">
                            <i class="bx bx-reset"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Records Table -->
    <div class="card shadow mb-4" id="maintenanceTable">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Maintenance Records</h6>
            <span class="badge bg-primary">{{ $maintenances->count() }} Records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Maintenance Type</th>
                            <th>Date</th>
                            <th>Business Contact</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Next Maintenance</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($maintenance->asset && $maintenance->asset->image_path)
                                            <img src="{{ asset('storage/' . $maintenance->asset->image_path) }}"
                                                 alt="" class="rounded me-2" height="32" width="32">
                                        @else
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                                    <i class="bx bx-package"></i>
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold">
                                                <a href="{{ route('assets.show', $maintenance->asset_id) }}" class="text-decoration-none">
                                                    {{ $maintenance->asset->name ?? 'N/A' }}
                                                </a>
                                            </div>
                                            <small class="text-muted">{{ $maintenance->asset->asset_code ?? 'N/A' }}</small><br>
                                            <small class="badge bg-info">{{ $maintenance->asset->category->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $maintenance->maintenance_type ?? 'N/A' }}</td>
                                <td>
                                    {{ $maintenance->maintenance_date ? $maintenance->maintenance_date->format('M d, Y') : 'N/A' }}
                                    @if($maintenance->maintenance_date < now() && $maintenance->status === 'Scheduled')
                                        <br><small class="text-danger"><i class="bx bx-error-circle"></i> Overdue</small>
                                    @endif
                                </td>
                                <td>{{ $maintenance->vendor->name ?? 'N/A' }}</td>
                                <td>
                                    @if($maintenance->cost)
                                        P {{ number_format($maintenance->cost, 2) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($maintenance->status == 'Scheduled')
                                        <span class="badge bg-info">Scheduled</span>
                                    @elseif($maintenance->status == 'In Progress')
                                        <span class="badge bg-warning">In Progress</span>
                                    @elseif($maintenance->status == 'Completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($maintenance->status == 'Cancelled')
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $maintenance->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($maintenance->next_maintenance_date)
                                        {{ $maintenance->next_maintenance_date->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    {{ Str::limit($maintenance->description ?? 'No description', 50) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-wrench fa-2x text-muted mb-3"></i>
                                        <h5>No Maintenance Found</h5>
                                        <p class="text-muted">No maintenance records match your current filter criteria.</p>
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
            const filterSelects = document.querySelectorAll('#filterForm select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            const dateInputs = document.querySelectorAll('#filterForm input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });
        });

        function printTable() {
            const printContent = document.getElementById('maintenanceTable').outerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
    </script>
@endsection

@extends('layouts.master')
@section('title', 'Asset Disposals')

@section('css')
    <style>
        .disposals-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .disposals-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .disposals-body {
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

        .method-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .method-badge.sold { background: #d1fae5; color: #065f46; }
        .method-badge.scrapped { background: #fee2e2; color: #991b1b; }
        .method-badge.donated { background: #dbeafe; color: #1e40af; }
        .method-badge.recycled { background: #fef3c7; color: #92400e; }

        .amount-badge {
            background: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 3px;
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .asset-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .asset-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fee2e2;
            color: #991b1b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
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

        /* Reports Dropdown Styling */
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            opacity: 0.5;
        }

        .empty-state h5 {
            color: #374151;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .disposals-header {
                padding: 20px;
            }

            .disposals-body {
                padding: 16px;
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
            Asset Disposals
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

    <div class="disposals-container">
        <div class="disposals-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="bx bx-trash me-2"></i>Asset Disposals</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View and manage disposed assets in the system</p>
                </div>
                <div class="col-md-6">
                    @if (!empty($disposals) && $disposals->count() > 0)
                        @php
                            $totalCount = $disposals->total();
                            $soldCount = $disposals->where('disposal_method', 'Sold')->count();
                            $otherCount = $disposals->whereIn('disposal_method', ['Scrapped', 'Donated', 'Recycled'])->count();
                        @endphp
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="stat-total">{{ $totalCount }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="stat-sold">{{ $soldCount }}</h4>
                                    <small class="opacity-75">Sold</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="stat-other">{{ $otherCount }}</h4>
                                    <small class="opacity-75">Other</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="disposals-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">About Asset Disposals</div>
                <div class="help-content">
                    Track assets that have been sold, scrapped, donated, or recycled. Disposing of an asset removes it from
                    active inventory and records the disposal details for audit purposes.
                </div>
            </div>

            <!-- Controls Row: Filters Left, Buttons Right -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by asset..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="methodFilter">
                                    <option value="">All Methods</option>
                                    <option value="sold">Sold</option>
                                    <option value="scrapped">Scrapped</option>
                                    <option value="donated">Donated</option>
                                    <option value="recycled">Recycled</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <input type="date" class="form-control" id="startDate" title="From Date">
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <input type="date" class="form-control" id="endDate" title="To Date">
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                        <a href="{{ route('disposals.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Disposal
                        </a>
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('disposals.summary-report') }}">
                                        <i class="fas fa-clipboard-list text-primary"></i> Disposal Summary Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('disposals.by-date-report') }}">
                                        <i class="fas fa-calendar-alt text-purple"></i> By Date & Status Report
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="disposals-table" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Asset</th>
                            <th>Disposal Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Authorized By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($disposals->count() > 0)
                            @foreach($disposals as $index => $disposal)
                                <tr class="disposal-row"
                                    data-asset="{{ strtolower(($disposal->asset->name ?? '') . ' ' . ($disposal->asset->asset_code ?? '')) }}"
                                    data-method="{{ strtolower($disposal->disposal_method ?? '') }}"
                                    data-date="{{ $disposal->disposal_date->format('Y-m-d') }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="asset-cell">
                                            @php
                                                $initials = strtoupper(substr($disposal->asset->name ?? 'A', 0, 2));
                                            @endphp
                                            <div class="asset-avatar-placeholder">{{ $initials }}</div>
                                            <div>
                                                <div class="fw-medium">{{ $disposal->asset->name ?? 'Unknown Asset' }}</div>
                                                <div class="text-muted" style="font-size: 12px;">{{ $disposal->asset->asset_code ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $disposal->disposal_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($disposal->disposal_method == 'Sold')
                                            <span class="method-badge sold">Sold</span>
                                        @elseif($disposal->disposal_method == 'Scrapped')
                                            <span class="method-badge scrapped">Scrapped</span>
                                        @elseif($disposal->disposal_method == 'Donated')
                                            <span class="method-badge donated">Donated</span>
                                        @elseif($disposal->disposal_method == 'Recycled')
                                            <span class="method-badge recycled">Recycled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($disposal->disposal_method == 'Sold')
                                            <span class="amount-badge">{{ number_format($disposal->disposal_amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($disposal->reason, 40) }}</td>
                                    <td>{{ $disposal->authorizedByUser->full_name ?? 'Unknown' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('disposals.edit', $disposal->id) }}"
                                                class="btn btn-sm btn-outline-info" title="Edit">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Cancel Disposal"
                                                onclick="if(confirm('Are you sure you want to cancel this disposal? This will revert the asset to Available status.')) document.getElementById('delete-form-{{ $disposal->id }}').submit();">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $disposal->id }}" action="{{ route('disposals.destroy', $disposal->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bx bx-trash"></i>
                                        <h5>No Disposal Records Found</h5>
                                        <p>No disposal records match your current search criteria.</p>
                                        <a href="{{ route('disposals.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1" style="font-size: 16px;"></i> Record New Disposal
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($disposals->count() > 0)
                <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted" id="results-info">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">{{ $disposals->total() }}</span> Disposals
                    </div>
                    <nav id="pagination-nav">
                        <!-- Pagination will be inserted here by JavaScript -->
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Client-side filtering and pagination
        let currentPage = 1;
        const itemsPerPage = 20;

        function filterAndPaginateDisposals(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const methodFilter = document.getElementById('methodFilter').value.toLowerCase();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            const allRows = document.querySelectorAll('.disposal-row');
            let filteredRows = [];
            let soldCount = 0;
            let otherCount = 0;

            // First pass: filter rows and collect stats
            allRows.forEach(row => {
                const asset = row.dataset.asset || '';
                const method = row.dataset.method || '';
                const date = row.dataset.date || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || asset.includes(searchTerm);
                const matchesMethod = !methodFilter || method === methodFilter;
                const matchesStartDate = !startDate || date >= startDate;
                const matchesEndDate = !endDate || date <= endDate;

                if (matchesSearch && matchesMethod && matchesStartDate && matchesEndDate) {
                    filteredRows.push(row);

                    // Count for stats
                    if (method === 'sold') soldCount++;
                    else otherCount++;
                }
            });

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Second pass: show/hide based on pagination
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update stats in header
            const statTotal = document.getElementById('stat-total');
            const statSold = document.getElementById('stat-sold');
            const statOther = document.getElementById('stat-other');
            if (statTotal) statTotal.textContent = totalFiltered;
            if (statSold) statSold.textContent = soldCount;
            if (statOther) statOther.textContent = otherCount;

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('total-count').textContent = totalFiltered;

            // Generate pagination controls
            generatePagination(totalPages, currentPage);
        }

        function generatePagination(totalPages, current) {
            const paginationNav = document.getElementById('pagination-nav');

            if (totalPages <= 1) {
                paginationNav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination mb-0">';

            // Previous button
            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;">Previous</a>
            </li>`;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>
                </li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>
                </li>`;
            }

            // Next button
            html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;">Next</a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            filterAndPaginateDisposals(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('methodFilter').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            filterAndPaginateDisposals(true);
        }

        // Real-time search as you type
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateDisposals(true));

        // Filter dropdowns and date inputs
        document.getElementById('methodFilter').addEventListener('change', () => filterAndPaginateDisposals(true));
        document.getElementById('startDate').addEventListener('change', () => filterAndPaginateDisposals(true));
        document.getElementById('endDate').addEventListener('change', () => filterAndPaginateDisposals(true));

        // Reset button
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateDisposals(true));
    </script>
@endsection

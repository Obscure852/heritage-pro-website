@extends('layouts.master')
@section('title')
    Grades Management
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .level-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
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

            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('setup.index') }}">Back</a>
        @endslot
        @slot('title')
            Grades Management
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

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="settings-container">
        <div class="settings-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3><i class="fas fa-layer-group me-2"></i>Grade Configuration</h3>
                    <p>Manage academic grades and promotion paths</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = count($grades ?? []);
                        $activeCount = collect($grades ?? [])->where('active', true)->count();
                        $inactiveCount = $totalCount - $activeCount;
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
                                <h4 class="mb-0 fw-bold text-white" id="stat-active">{{ $activeCount }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="stat-inactive">{{ $inactiveCount }}</h4>
                                <small class="opacity-75">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Grades</div>
                <div class="help-content">
                    Grades define the academic levels in your school. Each grade has a promotion path
                    that determines which grade students move to at the end of the year.
                </div>
            </div>

            <!-- Controls Row -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search grades..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="levelFilter">
                                    <option value="">All Levels</option>
                                    <option value="pre-primary">Pre-Primary</option>
                                    <option value="primary">Primary</option>
                                    <option value="junior">CJSS</option>
                                    <option value="senior">Senior</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <!-- Add Grade button can go here if needed -->
                </div>
            </div>

            <div class="table-responsive">
                <table id="grades-table" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Promotes To</th>
                            <th>Level</th>
                            <th>Active</th>
                            <th>Term</th>
                            @if (!session('is_past_term'))
                                <th class="text-end">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($grades) && count($grades) > 0)
                            @foreach ($grades as $grade)
                                <tr class="grade-row"
                                    data-name="{{ strtolower($grade->name) }}"
                                    data-level="{{ strtolower($grade->level ?? '') }}"
                                    data-status="{{ $grade->active ? 'active' : 'inactive' }}">
                                    <td>
                                        <span class="fw-medium">{{ $grade->name }}</span>
                                    </td>
                                    <td>{{ $grade->promotion }}</td>
                                    <td>
                                        <span class="level-badge">{{ ucfirst($grade->level) }}</span>
                                    </td>
                                    <td>
                                        @if ($grade->active)
                                            <span class="status-badge status-active">Active</span>
                                        @else
                                            <span class="status-badge status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ 'Term ' . $term->term . ', ' . $term->year }}</td>
                                    @if (!session('is_past_term'))
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="{{ route('setup.grades-view', ['gradeId' => $grade->id]) }}"
                                                    class="btn btn-sm btn-outline-info" title="View/Edit">
                                                    <i class="bx bx-edit-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="{{ session('is_past_term') ? 5 : 6 }}">
                                    <div class="empty-state">
                                        <i class="bx bx-layer"></i>
                                        <h5>No Grades Found</h5>
                                        <p>No grades have been configured for this term.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if (!empty($grades) && count($grades) > 0)
                <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted" id="results-info">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">{{ count($grades) }}</span> Grades
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

        function filterAndPaginateGrades(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.grade-row');
            let filteredRows = [];
            let activeCount = 0;
            let inactiveCount = 0;

            // First pass: filter rows and collect stats
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const level = row.dataset.level || '';
                const status = row.dataset.status || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesLevel = !levelFilter || level === levelFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesLevel && matchesStatus) {
                    filteredRows.push(row);

                    // Count for stats
                    if (status === 'active') activeCount++;
                    else inactiveCount++;
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
            const statActive = document.getElementById('stat-active');
            const statInactive = document.getElementById('stat-inactive');
            if (statTotal) statTotal.textContent = totalFiltered;
            if (statActive) statActive.textContent = activeCount;
            if (statInactive) statInactive.textContent = inactiveCount;

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
            if (!paginationNav) return;

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
            for (let i = 1; i <= totalPages; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
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
            filterAndPaginateGrades(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('levelFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterAndPaginateGrades(true);
        }

        // Real-time search as you type
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateGrades(true));

        // Filter dropdowns
        document.getElementById('levelFilter').addEventListener('change', () => filterAndPaginateGrades(true));
        document.getElementById('statusFilter').addEventListener('change', () => filterAndPaginateGrades(true));

        // Reset button
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateGrades(true));
    </script>
@endsection

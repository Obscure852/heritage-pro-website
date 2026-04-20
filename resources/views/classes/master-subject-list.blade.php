@extends('layouts.master')
@section('title')
    Master Subject List | Academic Management
@endsection

@section('css')
    <style>
        /* Main Container */
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

        .settings-body {
            padding: 24px;
        }

        /* Stats */
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

        /* Controls/Filter Row */
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

        /* Button Styling */
        .btn-add-new {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-add-new:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Table Styling */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .subjects-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .subjects-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .subjects-table tbody tr:hover {
            background: #f9fafb;
        }

        .subjects-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Action Button Styles */
        .table-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 3px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .table-action-btn.edit {
            background: #fef3c7;
            color: #d97706;
        }

        .table-action-btn.edit:hover {
            background: #d97706;
            color: white;
        }

        /* Badge */
        .badge-yes {
            background: #dcfce7;
            color: #16a34a;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-no {
            background: #fee2e2;
            color: #dc2626;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-double {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 6px;
            letter-spacing: 0.3px;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
        }

        /* Level Badge */
        .level-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .level-junior {
            background: #dbeafe;
            color: #1e40af;
        }

        .level-senior {
            background: #fce7f3;
            color: #be185d;
        }

        .level-both {
            background: #e9d5ff;
            color: #6b21a8;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .pagination .page-link {
            padding: 6px 12px;
            color: #3b82f6;
            border: 1px solid #e5e7eb;
        }

        .pagination .page-item.active .page-link {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            Master Subject List
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

    @php
        $totalSubjects = !empty($subjects) ? count($subjects) : 0;
        $withComponents = !empty($subjects) ? $subjects->where('components', true)->count() : 0;
        $withoutComponents = $totalSubjects - $withComponents;
    @endphp

    <div class="settings-container">
        <div class="settings-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-book me-2"></i>Master Subject List</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage the list of subjects available in the system</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotal">{{ $totalSubjects }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statWithComponents">{{ $withComponents }}</h4>
                                <small class="opacity-75">With Components</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statWithoutComponents">{{ $withoutComponents }}</h4>
                                <small class="opacity-75">Without</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Master Subjects</div>
                <div class="help-content">
                    This is the master list of all subjects available in the system. These subjects can be assigned to classes
                    and teachers. Subjects with components have additional grading subdivisions.
                </div>
            </div>

            <!-- Filter Row -->
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
                                <select class="form-select" id="levelFilter">
                                    <option value="">All Levels</option>
                                    <option value="junior">Junior</option>
                                    <option value="senior">Senior</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="componentsFilter">
                                    <option value="">All Components</option>
                                    <option value="yes">With Components</option>
                                    <option value="no">Without Components</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    @can('manage-academic')
                        @if (!session('is_past_term'))
                            <a href="{{ route('subjects.add-master-list') }}" class="btn-add-new">
                                <i class="bx bx-plus"></i> New Subject
                            </a>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="table-responsive">
                @if (!empty($subjects) && count($subjects) > 0)
                    <table class="subjects-table" id="subjectsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Components</th>
                                @can('manage-academic')
                                    @if (!session('is_past_term'))
                                        <th style="width: 80px;">Action</th>
                                    @endif
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $index => $subject)
                                <tr class="subject-row"
                                    data-name="{{ strtolower($subject->name) }}"
                                    data-code="{{ strtolower($subject->abbrev ?? '') }}"
                                    data-level="{{ strtolower($subject->level) }}"
                                    data-components="{{ $subject->components ? 'yes' : 'no' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $subject->abbrev ?? '' }}</strong>
                                        @if ($subject->is_double)
                                            <span class="badge-double"><i class="bx bxs-star"></i><i class="bx bxs-star"></i> Double</span>
                                        @endif
                                    </td>
                                    <td>{{ $subject->name }}</td>
                                    <td>
                                        @php
                                            $levelClass = 'level-' . strtolower($subject->level);
                                        @endphp
                                        <span class="level-badge {{ $levelClass }}">{{ $subject->level }}</span>
                                    </td>
                                    <td>
                                        @if ($subject->components)
                                            <span class="badge-yes">Yes</span>
                                        @else
                                            <span class="badge-no">No</span>
                                        @endif
                                    </td>
                                    @can('manage-academic')
                                        @if (!session('is_past_term'))
                                            <td>
                                                <a href="{{ route('subjects.edit-master-subject', $subject->id) }}"
                                                    class="table-action-btn edit" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Edit Subject">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            </td>
                                        @endif
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="text-muted" id="results-info">
                            Showing <span id="showing-from">1</span> to <span id="showing-to">{{ min(25, $totalSubjects) }}</span> of <span id="total-count">{{ $totalSubjects }}</span> subjects
                        </div>
                        <nav id="pagination-nav">
                            <!-- Pagination will be inserted here by JavaScript -->
                        </nav>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bx bx-book-open"></i>
                        <h5>No Subjects Found</h5>
                        <p>There are no subjects in the master list yet. Click "New Subject" to add one.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Client-side filtering and pagination
        let currentPage = 1;
        const itemsPerPage = 25;

        function filterAndPaginateSubjects(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value.toLowerCase();
            const componentsFilter = document.getElementById('componentsFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.subject-row');
            let filteredRows = [];

            // Stats counters
            let totalFiltered = 0;
            let withComponents = 0;
            let withoutComponents = 0;

            // First pass: filter rows
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const code = row.dataset.code || '';
                const level = row.dataset.level || '';
                const components = row.dataset.components || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                const matchesLevel = !levelFilter || level === levelFilter;
                const matchesComponents = !componentsFilter || components === componentsFilter;

                if (matchesSearch && matchesLevel && matchesComponents) {
                    filteredRows.push(row);
                    totalFiltered++;
                    if (components === 'yes') withComponents++;
                    else withoutComponents++;
                }
            });

            // Calculate pagination
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

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('total-count').textContent = totalFiltered;

            // Update stats in header
            document.getElementById('statTotal').textContent = totalFiltered;
            document.getElementById('statWithComponents').textContent = withComponents;
            document.getElementById('statWithoutComponents').textContent = withoutComponents;

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
            filterAndPaginateSubjects(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('levelFilter').value = '';
            document.getElementById('componentsFilter').value = '';
            filterAndPaginateSubjects(true);
        }

        $(document).ready(function() {
            // Real-time search as you type
            document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateSubjects(true));

            // Filter dropdowns
            document.getElementById('levelFilter').addEventListener('change', () => filterAndPaginateSubjects(true));
            document.getElementById('componentsFilter').addEventListener('change', () => filterAndPaginateSubjects(true));

            // Reset button
            document.getElementById('resetFilters').addEventListener('click', resetFilters);

            // Initialize on page load
            filterAndPaginateSubjects(true);

            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

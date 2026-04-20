@extends('layouts.master')
@section('title')
    Admissions Module
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
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

        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-current { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-enrolled { background: #dbeafe; color: #1e40af; }
        .status-left { background: #fee2e2; color: #991b1b; }
        .status-to-join { background: #e9d5ff; color: #6b21a8; }
        .status-new-online { background: #cffafe; color: #0e7490; }
        .status-offer-accepted { background: #d1fae5; color: #065f46; }
        .status-deleted { background: #f3f4f6; color: #4b5563; }

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

        .dataTables_length select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2.5rem;
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

        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .student-avatar-placeholder.male {
            background: #dbeafe;
            color: #1e40af;
        }

        .student-avatar-placeholder.female {
            background: #fce7f3;
            color: #be185d;
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

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }
        }
    </style>
    @include('layouts.partials.pagination-rounded')
@endsection
@section('content')
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

    @if (session('warning'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>{{ session('warning') }}</strong>
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

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Admissions</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage student admissions</p>
                </div>
                <div class="col-md-6">
                    @if (!empty($admissions))
                        @php
                            $totalCount = $admissions->count();
                            $maleCount = $admissions->where('gender', 'M')->count();
                            $femaleCount = $admissions->where('gender', 'F')->count();
                        @endphp
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $maleCount }}</h4>
                                    <small class="opacity-75">Male</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $femaleCount }}</h4>
                                    <small class="opacity-75">Female</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Admissions Directory</div>
                <div class="help-content">
                    Browse and manage all student admissions. Use the search and filters to find specific applications.
                    Click on an admission to view full details, or use the action buttons to view or delete entries.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    @foreach ($statuses ?? [] as $status)
                                        <option value="{{ strtolower($status) }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    @foreach ($grades ?? [] as $grade)
                                        <option value="{{ strtolower($grade) }}">{{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="genderFilter">
                                    <option value="">All Gender</option>
                                    <option value="m">Male</option>
                                    <option value="f">Female</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                        @can('manage-admissions')
                            @if (\App\Models\SchoolSetup::isSeniorSchool())
                                <a href="{{ route('admissions.placement') }}" class="btn btn-primary">
                                    <i class="fas fa-layer-group me-1"></i> Placements
                                </a>
                            @endif
                            @if (!session('is_past_term'))
                                <a href="{{ route('admissions.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> New Admission
                                </a>
                            @endif
                        @endcan
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admissions.status-report') }}">
                                        <i class="fas fa-list-ul text-primary"></i> Admission List Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admissions.status-names-report') }}">
                                        <i class="fas fa-tasks text-purple"></i> Admission Status Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admissions.analysis-by-grade') }}">
                                        <i class="fas fa-chart-pie text-success"></i> Analysis by Grade & Gender
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="admissions" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Grade</th>
                            <th>Status</th>
                            @if (!session('is_past_term'))
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admissions as $index => $admission)
                                <tr class="admission-row"
                                    data-name="{{ strtolower($admission->first_name . ' ' . $admission->last_name) }}"
                                    data-status="{{ strtolower($admission->status ?? '') }}"
                                    data-grade="{{ strtolower($admission->grade_applying_for ?? '') }}"
                                    data-gender="{{ strtolower($admission->gender ?? '') }}"
                                    style="--i: {{ $index }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="student-cell">
                                            @php
                                                $initials = strtoupper(substr($admission->first_name ?? '', 0, 1) . substr($admission->last_name ?? '', 0, 1));
                                                $genderClass = $admission->gender == 'M' ? 'male' : 'female';
                                            @endphp
                                            <div class="student-avatar-placeholder {{ $genderClass }}">{{ $initials ?: 'ST' }}</div>
                                            <div>
                                                <div>
                                                    <a href="{{ route('admissions.admissions-view', $admission->id) }}">
                                                        {{ $admission->first_name . ' ' . $admission->last_name }}
                                                    </a>
                                                </div>
                                                <div class="text-muted" style="font-size: 12px;">{{ $admission->grade_applying_for }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($admission->gender == 'M')
                                            <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                                        @else
                                            <span class="gender-female"><i class="bx bx-female-sign"></i> Female</span>
                                        @endif
                                    </td>
                                    <td>{{ $admission->formatted_date_of_birth }}</td>
                                    <td>{{ $admission->grade_applying_for }}</td>
                                    <td>
                                        @php
                                            $statusClass = 'status-pending';
                                            $statusLower = strtolower(str_replace(' ', '-', $admission->status ?? ''));
                                            if (in_array($statusLower, ['current', 'pending', 'enrolled', 'left', 'to-join', 'new-online', 'offer-accepted', 'deleted'])) {
                                                $statusClass = 'status-' . $statusLower;
                                            }
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $admission->status }}</span>
                                    </td>
                                    @if (!session('is_past_term'))
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="{{ route('admissions.admissions-view', $admission->id) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View Details">
                                                    <i class="bx bx-edit-alt"></i>
                                                </a>

                                                @can('manage-admissions')
                                                    <a href="{{ route('admissions.delete-admission-academics', $admission->id) }}"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirmDelete()"
                                                        title="Delete Admission">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                        @empty
                            <tr id="no-admissions-row">
                                <td colspan="7">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-user-graduate" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Admissions</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($admissions->count() > 0)
                <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted" id="results-info">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">{{ count($admissions) }}</span> Admissions
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

        function filterAndPaginateAdmissions(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value.toLowerCase();
            const genderFilter = document.getElementById('genderFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.admission-row');
            let filteredRows = [];
            let maleCount = 0;
            let femaleCount = 0;

            // First pass: filter rows and collect stats
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const status = row.dataset.status || '';
                const grade = row.dataset.grade || '';
                const gender = row.dataset.gender || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesGrade = !gradeFilter || grade === gradeFilter;
                const matchesGender = !genderFilter || gender === genderFilter;

                if (matchesSearch && matchesStatus && matchesGrade && matchesGender) {
                    filteredRows.push(row);

                    // Count for stats
                    if (gender === 'm') maleCount++;
                    if (gender === 'f') femaleCount++;
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
            const statElements = document.querySelectorAll('.stat-item h4');
            if (statElements.length >= 3) {
                statElements[0].textContent = totalFiltered;
                statElements[1].textContent = maleCount;
                statElements[2].textContent = femaleCount;
            }

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

            let html = '<ul class="pagination pagination-rounded mb-0">';

            // Previous button
            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;" aria-label="Previous"><i class="fas fa-chevron-left"></i></a>
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
                <a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;" aria-label="Next"><i class="fas fa-chevron-right"></i></a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            filterAndPaginateAdmissions(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('genderFilter').value = '';
            filterAndPaginateAdmissions(true);
        }

        // Real-time search as you type
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateAdmissions(true));

        // Filter dropdowns
        document.getElementById('statusFilter').addEventListener('change', () => filterAndPaginateAdmissions(true));
        document.getElementById('gradeFilter').addEventListener('change', () => filterAndPaginateAdmissions(true));
        document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginateAdmissions(true));

        // Reset button
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => filterAndPaginateAdmissions(true));

        function confirmDelete() {
            return confirm('Are you sure you want to delete this admission? This action cannot be undone.');
        }
    </script>
@endsection

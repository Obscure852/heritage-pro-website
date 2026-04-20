@extends('layouts.master')
@section('title')
    House Allocations | House Module
@endsection
@section('css')
    <style>
        /* Container Structure */
        .houses-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .houses-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .houses-body {
            padding: 24px;
        }

        /* Help Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #f59e0b;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Button Loading Animation */
        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Form Controls */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 8px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 10px 8px;
            font-size: 14px;
        }

        /* Checkbox Styling */
        .form-check-input {
            width: 18px;
            height: 18px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Gender Icons */
        .gender-male {
            color: #3b82f6;
        }

        .gender-female {
            color: #ec4899;
        }

        /* Stat Items */
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
        }

        .stat-badge i {
            font-size: 18px;
        }

        /* Pagination */
        .pagination .page-link {
            border-radius: 3px;
            margin: 0 2px;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #3b82f6;
        }

        @media (max-width: 768px) {
            .houses-header {
                padding: 20px;
            }

            .houses-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('house.index') }}">Houses</a>
        @endslot
        @slot('title')
            Allocate Students
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="houses-container">
        <div class="houses-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1 text-white"><i class="fas fa-user-plus me-2"></i>{{ $house->name ?? 'House' }}</h4>
                    <p class="mb-0 opacity-75">Allocate students to this house</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="d-flex gap-3 justify-content-md-end">
                        <div class="stat-badge">
                            <i class="fas fa-user-clock"></i>
                            <span><strong id="totalCount">{{ $students->count() ?? 0 }}</strong> Unallocated</span>
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-mars"></i>
                            <span><strong id="maleCount">0</strong> Male</span>
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-venus"></i>
                            <span><strong id="femaleCount">0</strong> Female</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="houses-body">
            <div class="help-text">
                <div class="help-title">Student Allocation</div>
                <div class="help-content">
                    Select students from the list below to allocate them to {{ $house->name ?? 'this house' }}. Only students not currently assigned to any house are shown.
                </div>
            </div>

            <!-- Filters Row -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-10 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="genderFilter">
                                    <option value="">All Gender</option>
                                    <option value="m">Male</option>
                                    <option value="f">Female</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    @php
                                        $grades = $students->map(function($s) {
                                            return $s->currentClass()?->grade?->name;
                                        })->filter()->unique()->sort();
                                    @endphp
                                    @foreach ($grades as $grade)
                                        <option value="{{ strtolower($grade) }}">{{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-2 col-sm-6">
                                <select class="form-select" id="classFilter">
                                    <option value="">All Classes</option>
                                    @php
                                        $classes = $students->map(function($s) {
                                            return $s->currentClass()?->name;
                                        })->filter()->unique()->sort();
                                    @endphp
                                    @foreach ($classes as $class)
                                        <option value="{{ strtolower($class) }}">{{ $class }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('house.move-students', $house->id) }}" id="allocateForm">
                @csrf

                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-user-plus me-1"></i> Allocate Selected</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Allocating...
                        </span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="studentsTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>ID Number</th>
                                <th>Class</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                @php($cls = $student->currentClass())
                                <tr class="student-row"
                                    data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                                    data-gender="{{ strtolower($student->gender) }}"
                                    data-grade="{{ strtolower($cls?->grade?->name ?? '') }}"
                                    data-class="{{ strtolower($cls?->name ?? '') }}">
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input student-checkbox" name="students[]"
                                                value="{{ $student->id }}">
                                        </div>
                                    </td>
                                    <td>{{ $student->first_name . ' ' . $student->last_name }}</td>
                                    <td>
                                        @if ($student->gender == 'M')
                                            <span class="gender-male"><i class="fas fa-mars"></i> {{ $student->gender }}</span>
                                        @else
                                            <span class="gender-female"><i class="fas fa-venus"></i> {{ $student->gender }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $student->id_number }}</td>
                                    <td>{{ $cls->name ?? '-' }}</td>
                                    <td>{{ $cls?->grade?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle" style="font-size: 32px; opacity: 0.3; color: #10b981;"></i>
                                        <p class="mt-2 mb-0">All students have been allocated to houses</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="pagination-info text-muted">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="filtered-count">0</span> students
                    </div>
                    <nav id="paginationContainer">
                        <ul class="pagination pagination-rounded mb-0"></ul>
                    </nav>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let currentPage = 1;
        const itemsPerPage = 20;

        function filterAndPaginate(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const genderFilter = document.getElementById('genderFilter').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value.toLowerCase();
            const classFilter = document.getElementById('classFilter').value.toLowerCase();

            const allRows = document.querySelectorAll('.student-row');
            let filteredRows = [];
            let maleCount = 0;
            let femaleCount = 0;

            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const gender = row.dataset.gender || '';
                const grade = row.dataset.grade || '';
                const studentClass = row.dataset.class || '';

                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesGender = !genderFilter || gender === genderFilter;
                const matchesGrade = !gradeFilter || grade === gradeFilter;
                const matchesClass = !classFilter || studentClass === classFilter;

                if (matchesSearch && matchesGender && matchesGrade && matchesClass) {
                    filteredRows.push(row);
                    if (gender === 'm') maleCount++;
                    if (gender === 'f') femaleCount++;
                }
            });

            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update stats
            document.getElementById('totalCount').textContent = totalFiltered;
            document.getElementById('maleCount').textContent = maleCount;
            document.getElementById('femaleCount').textContent = femaleCount;

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('filtered-count').textContent = totalFiltered;

            // Handle empty state
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) {
                emptyRow.style.display = totalFiltered === 0 ? '' : 'none';
            }

            generatePagination(totalPages, currentPage);
        }

        function generatePagination(totalPages, current) {
            const container = document.querySelector('#paginationContainer .pagination');
            container.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${current === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;"><i class="fas fa-chevron-left"></i></a>`;
            container.appendChild(prevLi);

            // Page numbers
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(totalPages, current + 2);

            if (startPage > 1) {
                container.appendChild(createPageItem(1, current));
                if (startPage > 2) {
                    const dots = document.createElement('li');
                    dots.className = 'page-item disabled';
                    dots.innerHTML = '<span class="page-link">...</span>';
                    container.appendChild(dots);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                container.appendChild(createPageItem(i, current));
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('li');
                    dots.className = 'page-item disabled';
                    dots.innerHTML = '<span class="page-link">...</span>';
                    container.appendChild(dots);
                }
                container.appendChild(createPageItem(totalPages, current));
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${current === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;"><i class="fas fa-chevron-right"></i></a>`;
            container.appendChild(nextLi);
        }

        function createPageItem(page, current) {
            const li = document.createElement('li');
            li.className = `page-item ${page === current ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${page}); return false;">${page}</a>`;
            return li;
        }

        function goToPage(page) {
            currentPage = page;
            filterAndPaginate(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('genderFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('classFilter').value = '';
            filterAndPaginate(true);
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', () => filterAndPaginate(true));
        document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginate(true));
        document.getElementById('gradeFilter').addEventListener('change', () => filterAndPaginate(true));
        document.getElementById('classFilter').addEventListener('change', () => filterAndPaginate(true));
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        // Select All (only visible rows)
        document.getElementById('selectAll').addEventListener('change', function() {
            const visibleCheckboxes = document.querySelectorAll('.student-row:not([style*="display: none"]) .student-checkbox');
            visibleCheckboxes.forEach(cb => cb.checked = this.checked);
        });

        // Form validation and loading state
        document.getElementById('allocateForm').addEventListener('submit', function(event) {
            const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            if (checkedCount === 0) {
                event.preventDefault();
                alert('Please select at least one student to allocate.');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', () => filterAndPaginate(true));
    </script>
@endsection

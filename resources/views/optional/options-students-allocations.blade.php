@extends('layouts.master')
@section('title')
    Allocate Students | Academic Management
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

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-header .class-info {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .settings-header .class-info .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 13px;
        }

        .settings-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
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

        /* Controls Row */
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

        /* Allocate Button */
        .btn-allocate {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-allocate:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-allocate.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .btn-allocate.warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-allocate.loading .btn-text {
            display: none;
        }

        .btn-allocate.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-allocate:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Table Styling */
        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            vertical-align: middle;
        }

        .students-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .students-table tbody tr:hover {
            background: #f9fafb;
        }

        .students-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Checkbox Styling */
        .form-check-input {
            width: 1.1rem;
            height: 1.1rem;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Gender Icons */
        .gender-male {
            color: #3b82f6;
        }

        .gender-female {
            color: #ec4899;
        }

        /* Student Cell */
        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
        }

        .student-avatar-placeholder.male {
            background: #dbeafe;
            color: #1e40af;
        }

        .student-avatar-placeholder.female {
            background: #fce7f3;
            color: #be185d;
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

            .settings-header .class-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Student Type Badge */
        .student-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('optional.index') }}">Optional Subjects</a>
        @endslot
        @slot('title')
            Allocate Students to {{ $class->name ?? 'Class' }}
        @endslot
    @endcomponent

    @php
        $isPastTerm = session('is_past_term', false);
        $canAllocate = auth()->user()->can('optional-teacher', $class);
        $showAllocation = $canAllocate;

        // Collect unique classes for the filter dropdown
        $uniqueClasses = $students
            ->map(function ($student) {
                $klass = $student->currentClass();
                return $klass ? ['id' => $klass->id, 'name' => $klass->name] : null;
            })
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    @endphp

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
            <h3><i class="fas fa-user-plus me-2"></i>Allocate Students to {{ $class->name ?? '' }}</h3>
            <p>Select students to add to this optional class</p>
            <div class="class-info">
                <span class="info-badge">
                    <i class="fas fa-chalkboard-teacher"></i> {{ $class->teacher->fullName ?? 'No Teacher Assigned' }}
                </span>
                <span class="info-badge">
                    <i class="fas fa-book"></i> {{ $class->gradeSubject->subject->name ?? 'N/A' }}
                </span>
                <span class="info-badge">
                    <i class="fas fa-user-graduate"></i> {{ $class->students->count() ?? 0 }} Current Students
                </span>
                <span class="info-badge">
                    <i class="fas fa-users"></i> <span id="availableStudentsCount">{{ $students->count() ?? 0 }}</span>
                    <span id="availableStudentsLabel">Available</span>
                </span>
            </div>
        </div>

        <div class="settings-body">
            @if ($isPastTerm && $canAllocate)
                <div class="help-text warning">
                    <div class="help-title"><i class="bx bx-shield-quarter me-1"></i>Past Term Allocation
                    </div>
                    <div class="help-content">
                        You are allocating students to a past term. Review selections carefully before saving changes.
                    </div>
                </div>
            @else
                <div class="help-text">
                    <div class="help-title">Student Allocation</div>
                    <div class="help-content">
                        Select students from the list below to allocate them to this optional class. Use the checkboxes to
                        select multiple students at once.
                        Only students in the same grade who are not already in this optional subject will appear in this
                        list.
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('optional.move-students', $class->id) }}" id="allocationForm">
                @csrf

                <!-- Filter Controls Row -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-5 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search by name..."
                                            id="searchInput">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" id="genderFilter">
                                        <option value="">All Gender</option>
                                        <option value="m">Male</option>
                                        <option value="f">Female</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" id="classFilter">
                                        <option value="">All Classes</option>
                                        @foreach ($uniqueClasses as $klass)
                                            <option value="{{ strtolower($klass['name']) }}">{{ $klass['name'] }}</option>
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
                        @if ($canAllocate && $showAllocation)
                            <button type="submit" class="btn-allocate {{ $isPastTerm ? 'warning' : '' }}">
                                <span class="btn-text">
                                    @if ($isPastTerm)
                                        <i class="bx bx-shield-quarter"></i> Allocate Selected Students
                                    @else
                                        <i class="bx bxs-copy-alt"></i> Allocate Selected Students
                                    @endif
                                </span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Allocating...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    @if (!empty($students) && $students->count() > 0)
                        <table id="allocation-table" class="students-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    @if ($showAllocation && $canAllocate)
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </div>
                                        </th>
                                    @endif
                                    <th>Student</th>
                                    <th>Gender</th>
                                    <th>ID/Passport No.</th>
                                    <th>Class</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    <tr class="student-row"
                                        data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                                        data-gender="{{ strtolower($student->gender ?? '') }}"
                                        data-class="{{ strtolower($student->currentClass()->name ?? '') }}"
                                        style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                        <td class="student-number" style="text-align: center;">{{ $index + 1 }}</td>
                                        @if ($showAllocation && $canAllocate)
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" name="students[]" value="{{ $student->id }}"
                                                        class="form-check-input student-checkbox"
                                                        id="student{{ $student->id }}">
                                                </div>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="student-cell">
                                                @php
                                                    $initials = strtoupper(
                                                        substr($student->first_name ?? '', 0, 1) .
                                                            substr($student->last_name ?? '', 0, 1),
                                                    );
                                                    $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                                @endphp
                                                <div class="student-avatar-placeholder {{ $genderClass }}">
                                                    {{ $initials ?: 'ST' }}</div>
                                                <div>
                                                    <strong>{{ $student->first_name . ' ' . $student->last_name }}</strong>
                                                    @if ($student->type)
                                                        <span class="student-type-badge"
                                                            style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                            <i class="fas fa-universal-access"
                                                                style="font-size: 9px;"></i>
                                                            {{ $student->type->type }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($student->gender == 'M')
                                                <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                                            @else
                                                <span class="gender-female"><i class="bx bx-female-sign"></i>
                                                    Female</span>
                                            @endif
                                        </td>
                                        <td>{{ $student->formatted_id_number ?? '' }}</td>
                                        <td>{{ $student->currentClass()?->name ?? 'No Class' }}</td>
                                        <td>{{ $student->currentClass()?->grade?->name ?? 'No Grade' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination Info -->
                        <div class="pagination-container">
                            <div class="text-muted" id="results-info">
                                Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span
                                    id="total-count">{{ $students->count() }}</span> students
                            </div>
                            <nav id="pagination-nav">
                                <!-- Pagination will be inserted here by JavaScript -->
                            </nav>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bx bx-user-check"></i>
                            <h5>No Students Available</h5>
                            <p>
                                @if ($isPastTerm)
                                    No students were available for allocation to this optional subject during this past
                                    term.
                                @else
                                    All eligible students for this grade are already allocated to optional subjects, or no
                                    students meet the criteria.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Client-side filtering and pagination
        let currentPage = 1;
        const itemsPerPage = 50;

        function filterAndPaginateStudents(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const genderFilter = document.getElementById('genderFilter').value.toLowerCase();
            const classFilter = document.getElementById('classFilter').value.toLowerCase();

            const allRows = Array.from(document.querySelectorAll('.student-row'));
            let filteredRows = [];

            // First pass: filter rows
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const gender = row.dataset.gender || '';
                const studentClass = row.dataset.class || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesGender = !genderFilter || gender === genderFilter;
                const matchesClass = !classFilter || studentClass === classFilter;

                if (matchesSearch && matchesGender && matchesClass) {
                    filteredRows.push(row);
                }
            });

            updateHeaderClassTotal(allRows, classFilter);

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Second pass: show/hide based on pagination
            filteredRows.forEach((row, index) => {
                const numberCell = row.querySelector('.student-number');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }

                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            allRows.forEach(row => {
                if (!filteredRows.includes(row)) {
                    row.style.display = 'none';
                }
            });

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('total-count').textContent = totalFiltered;

            // Generate pagination controls
            generatePagination(totalPages, currentPage);
        }

        function updateHeaderClassTotal(allRows, classFilter) {
            const availableCount = document.getElementById('availableStudentsCount');
            const availableLabel = document.getElementById('availableStudentsLabel');
            const classFilterSelect = document.getElementById('classFilter');

            if (!availableCount || !availableLabel || !classFilterSelect) {
                return;
            }

            if (!classFilter) {
                availableCount.textContent = allRows.length;
                availableLabel.textContent = 'Available';
                return;
            }

            const classTotal = allRows.filter(row => (row.dataset.class || '') === classFilter).length;
            const selectedOption = classFilterSelect.options[classFilterSelect.selectedIndex];
            const selectedClassLabel = selectedOption ? selectedOption.text.trim() : 'Selected Class';

            availableCount.textContent = classTotal;
            availableLabel.textContent = `in ${selectedClassLabel}`;
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
            filterAndPaginateStudents(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('genderFilter').value = '';
            document.getElementById('classFilter').value = '';
            filterAndPaginateStudents(true);
        }

        $(document).ready(function() {
            @php
                $isPastTerm = session('is_past_term', false);
                $canAllocate = auth()->user()->can('optional-teacher', $class);
                $showAllocation = $canAllocate;
            @endphp

            // Real-time search as you type
            document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateStudents(true));

            // Filter dropdowns
            document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginateStudents(
                true));
            document.getElementById('classFilter').addEventListener('change', () => filterAndPaginateStudents(
            true));

            // Reset button
            document.getElementById('resetFilters').addEventListener('click', resetFilters);

            // Initialize on page load
            filterAndPaginateStudents(true);

            @if ($showAllocation && $canAllocate)
                // Select All checkbox - only select visible checkboxes
                $('#checkAll').on('change', function() {
                    $('.student-row:visible .student-checkbox').prop('checked', $(this).is(':checked'));
                });

                // Form submission with loading state
                $('#allocationForm').on('submit', function(e) {
                    const checkedCount = $('.student-checkbox:checked').length;
                    if (checkedCount === 0) {
                        e.preventDefault();
                        alert('Please select at least one student to allocate.');
                        return false;
                    }

                    @if ($isPastTerm && $canAllocate)
                        const confirmMessage =
                            `Are you sure you want to allocate ${checkedCount} student(s) to the past-term class {{ $class->name ?? 'this optional subject' }}?`;
                    @else
                        const confirmMessage =
                            `Are you sure you want to allocate ${checkedCount} student(s) to {{ $class->name ?? 'this optional subject' }}?`;
                    @endif

                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                        return false;
                    }

                    // Show loading state
                    const btn = $(this).find('.btn-allocate');
                    btn.addClass('loading');
                    btn.prop('disabled', true);
                });
            @endif
        });
    </script>
@endsection

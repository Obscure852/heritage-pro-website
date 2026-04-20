@extends('layouts.master')
@section('title')
    Class Students | Academic Management
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

        /* Bulk Actions Row */
        .actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .bulk-actions {
            display: flex;
            gap: 8px;
        }

        .btn-bulk-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-bulk-action.move {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-bulk-action.move:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-bulk-action.remove {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-bulk-action.remove:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-bulk-action.loading .btn-text {
            display: none;
        }

        .btn-bulk-action.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-bulk-action:disabled {
            opacity: 0.7;
            cursor: not-allowed;
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

        /* Modal Styling */
        .modal-content {
            border: none;
            border-radius: 6px;
        }

        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 6px 6px 0 0;
            padding: 16px 20px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
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

            .actions-row {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .bulk-actions {
                justify-content: flex-end;
            }
        }

        .student-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
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
            <a class="text-muted" href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            {{ $class->name ?? 'Class' }} Students
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
            <h3><i class="fas fa-users me-2"></i>{{ $class->name ?? '' }} - Student List</h3>
            <p>View and manage students allocated to this class</p>
            <div class="class-info">
                <span class="info-badge">
                    <i class="fas fa-chalkboard-teacher"></i> {{ $class->teacher->fullName ?? 'No Teacher Assigned' }}
                </span>
                <span class="info-badge">
                    <i class="fas fa-user-graduate"></i> <span id="studentCount">{{ $class->students->count() ?? 0 }}</span>
                    Students
                </span>
            </div>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">Student Management</div>
                <div class="help-content">
                    Select students using the checkboxes to perform bulk actions like moving to another class or removing
                    from this class.
                    Use the filters below to search for specific students.
                </div>
            </div>

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
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="genderFilter">
                                    <option value="">All Gender</option>
                                    <option value="m">Male</option>
                                    <option value="f">Female</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="bulk-actions justify-content-lg-end">
                        @if (Auth::user()->can('class-allocation-teacher', $class))
                            @can('class-allocation-teacher', $class)
                                <button type="button" class="btn-bulk-action move" id="moveSelectedBtn" style="display: none;">
                                    <span class="btn-text"><i class="bx bx-copy-alt"></i> Move Students</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        Moving...
                                    </span>
                                </button>
                            @endcan
                            <button type="button" class="btn-bulk-action remove" id="deleteSelectedBtn"
                                style="display: none;">
                                <span class="btn-text"><i class="bx bx-trash"></i> Remove Students</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Removing...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <form id="deleteStudentsForm" method="POST"
                    action="{{ route('academic.remove-multiple-students', $class->id) }}">
                    @csrf
                    @method('DELETE')
                    <table id="allocated-students" class="students-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </div>
                                </th>
                                <th>Student</th>
                                <th>Gender</th>
                                <th>Class</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($class))
                                @foreach ($class->students as $index => $student)
                                    <tr class="student-row"
                                        data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                                        data-gender="{{ strtolower($student->gender ?? '') }}"
                                        style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                        <td style="text-align: center;">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input student-checkbox"
                                                    name="students[]" value="{{ $student->id }}"
                                                    data-grade-id="{{ $class->grade_id }}">
                                            </div>
                                        </td>
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
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->grade->name }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </form>
            </div>

            <!-- Pagination Info -->
            <div class="pagination-container">
                <div class="text-muted" id="results-info">
                    Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span
                        id="total-count">{{ $class->students->count() ?? 0 }}</span> students
                </div>
                <nav id="pagination-nav">
                    <!-- Pagination will be inserted here by JavaScript -->
                </nav>
            </div>
        </div>
    </div>

    <!-- Move Students Modal -->
    <div class="modal fade" id="moveStudentsModal" tabindex="-1" aria-labelledby="moveStudentsModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="moveStudentsForm" method="POST" action="{{ route('academic.move-multiple-students') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="moveStudentsModalLabel"><i class="bx bx-transfer me-2"></i>Move
                            Selected Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_class_id" class="form-label">Select the new class for the selected
                                students</label>
                            <select name="new_class_id" id="new_class_id" class="form-select" required>
                                <!-- Options will be appended dynamically via AJAX -->
                            </select>
                        </div>
                        <input type="hidden" name="source_class_id" value="{{ $class->id }}">
                        <input type="hidden" name="student_ids" id="selectedStudentIds">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Are you sure you want to move the selected student(s)? All their scores and optional subjects for this term will be lost.');">
                            <i class="bx bx-check me-1"></i> Move Students
                        </button>
                    </div>
                </form>
            </div>
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

            const allRows = document.querySelectorAll('.student-row');
            let filteredRows = [];
            let maleCount = 0;
            let femaleCount = 0;

            // First pass: filter rows and collect stats
            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const gender = row.dataset.gender || '';

                // Check if row matches all filters
                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesGender = !genderFilter || gender === genderFilter;

                if (matchesSearch && matchesGender) {
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

            // Update student count in header
            document.getElementById('studentCount').textContent = totalFiltered;

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
            filterAndPaginateStudents(false);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('genderFilter').value = '';
            filterAndPaginateStudents(true);
        }

        $(document).ready(function() {
            let baseClassesByGradeRoute = "{{ route('students.classes-by-grade', ['gradeId' => ':gradeId']) }}";

            // Real-time search as you type
            document.getElementById('searchInput').addEventListener('input', () => filterAndPaginateStudents(true));

            // Filter dropdown
            document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginateStudents(
                true));

            // Reset button
            document.getElementById('resetFilters').addEventListener('click', resetFilters);

            // Initialize on page load
            filterAndPaginateStudents(true);

            // Select All checkbox
            $('#selectAll').on('change', function() {
                // Only select visible checkboxes
                $('.student-row:visible .student-checkbox').prop('checked', $(this).is(':checked'));
                toggleDeleteButton();
            });

            $(document).on('change', '.student-checkbox', function() {
                toggleDeleteButton();
            });

            function toggleDeleteButton() {
                const checkedCount = $('.student-checkbox:checked').length;
                $('#deleteSelectedBtn').toggle(checkedCount > 0);
                $('#moveSelectedBtn').toggle(checkedCount > 0);
            }

            $('#deleteSelectedBtn').on('click', function() {
                if ($('.student-checkbox:checked').length === 0) {
                    alert('Please select at least one student to delete.');
                    return;
                }
                if (confirm(
                        'Are you sure you want to delete the selected students? All their marks and comments will be lost.'
                    )) {
                    // Show loading state
                    const btn = $(this);
                    btn.addClass('loading');
                    btn.prop('disabled', true);
                    $('#deleteStudentsForm').submit();
                }
            });

            $('#moveSelectedBtn').on('click', function() {
                const selectedCheckboxes = $('.student-checkbox:checked');
                if (selectedCheckboxes.length === 0) {
                    alert('Please select at least one student to move.');
                    return;
                }

                let studentIds = [];
                let gradeIds = new Set();

                selectedCheckboxes.each(function() {
                    studentIds.push($(this).val());
                    gradeIds.add($(this).data('grade-id'));
                });

                if (gradeIds.size > 1) {
                    alert('Selected students are from different grades and cannot be moved together.');
                    return;
                }

                // Show loading state
                const btn = $(this);
                btn.addClass('loading');
                btn.prop('disabled', true);

                let uniqueGradeId = Array.from(gradeIds)[0];
                let finalUrl = `${baseClassesByGradeRoute.replace(':gradeId', uniqueGradeId)}?for_class_allocations=1`;

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Remove loading state
                        btn.removeClass('loading');
                        btn.prop('disabled', false);

                        if (response.success) {
                            $('#new_class_id').empty();
                            $('#new_class_id').append(
                                '<option value="">-- Select Class --</option>');

                            response.data.forEach(function(klass) {
                                $('#new_class_id').append(
                                    `<option value="${klass.id}">${klass.name}</option>`
                                );
                            });

                            $('#selectedStudentIds').val(JSON.stringify(studentIds));
                            let moveModal = new bootstrap.Modal(document.getElementById(
                                'moveStudentsModal'), {
                                keyboard: false
                            });
                            moveModal.show();

                        } else {
                            alert('Error fetching classes: ' + (response.message ||
                                'Unknown error.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        // Remove loading state on error
                        btn.removeClass('loading');
                        btn.prop('disabled', false);
                        alert('AJAX Error: ' + error);
                    }
                });
            });
        });
    </script>
@endsection

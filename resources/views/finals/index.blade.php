@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Students Module')
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

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

        .placeholder-glow {
            animation: placeholder-glow 2s ease-in-out infinite;
        }

        @keyframes placeholder-glow {
            50% {
                opacity: 0.5;
            }
        }

        .placeholder-item {
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                    <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Finals Students</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} candidate results</p>
                    @include('finals.partials.context-toggle')
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotal">{{ $totalStudents ?? 0 }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statWithResults">{{ $studentsWithResults ?? 0 }}</h4>
                                <small class="opacity-75">With Results</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statNoCandidateNumber">{{ $noCandidateNumber ?? 0 }}</h4>
                                <small class="opacity-75">No Candidate #</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statPending">{{ $studentsPending ?? 0 }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statPassRate">{{ $passRate ?? 0 }}%</h4>
                                <small class="opacity-75">Pass Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Final Year Results</div>
                <div class="help-content">
                    Browse and manage final year student exam results. Use the filters to find specific students.
                    Click on a student to view their detailed transcript and subject grades.
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
                                <select class="form-select" id="graduationYear">
                                    <option value="">All Years</option>
                                    @if (!empty($availableYears))
                                        @foreach ($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select class="form-select" id="resultFilter">
                                    <option value="">All Results</option>
                                    <option value="with_results">With Results</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select class="form-select" id="genderFilter">
                                    <option value="">All Gender</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addFinalStudentModal" id="openAddFinalStudentModal">
                            <i class="bx bx-user-plus me-1"></i>Add Student
                        </button>
                        @if(($noCandidateNumber ?? 0) > 0)
                            <a href="{{ route('finals.students.no-candidate', ['finals_context' => $finalsDefinition->context]) }}" class="btn btn-danger" style="border-radius:3px; padding: 10px 16px; font-weight: 500;">
                                <i class="bx bx-user-x me-1"></i>Remove Students ({{ $noCandidateNumber }})
                            </a>
                        @endif
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @include('finals.partials.report-menu', ['items' => $reportMenu])
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="finalsStudentsList">
            </div>

            <div id="loadingPlaceholder">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Student</th>
                                <th scope="col">Candidate No.</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Class</th>
                                <th scope="col">Graduation</th>
                                <th scope="col">Exam Results</th>
                                <th scope="col">Overall Grade</th>
                                <th style="width: 80px; min-width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 10; $i++)
                                <tr class="placeholder-glow">
                                    <td><span class="placeholder-item" style="width: 120px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td>
                                        <span class="placeholder-item" style="width: 24px; height: 24px; border-radius: 50%;"></span>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addFinalStudentModal" tabindex="-1" aria-labelledby="addFinalStudentModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finals.students.add') }}" id="addFinalStudentForm">
                    @csrf
                    <input type="hidden" name="finals_context" value="{{ $finalsDefinition->context }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFinalStudentModalLabel">Add Student To {{ $finalsDefinition->examLabel ?? 'Finals' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <i class="bx bx-info-circle me-1"></i>
                            Only students who existed in <strong>Term 3 of the previous year</strong> can be added.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="addGraduationYear" class="form-label">Graduation Year</label>
                                <select class="form-select" name="graduation_year" id="addGraduationYear" required>
                                    @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="eligibleStudentSearch" class="form-label">Search Student</label>
                                <input type="text" class="form-control" id="eligibleStudentSearch"
                                    placeholder="Type name, ID number, or Candidate Number...">
                            </div>
                            <div class="col-12">
                                <label for="eligibleStudentSelect" class="form-label">Eligible Students (Name | Class | ID | Candidate Number)</label>
                                <select class="form-select" name="student_id" id="eligibleStudentSelect" size="8"
                                    required></select>
                                <small class="text-muted d-block mt-1" id="eligibleStudentHint">
                                    Loading eligible students...
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading" id="addFinalStudentSubmit" disabled>
                            <span class="btn-text"><i class="fas fa-save me-1"></i>Add To Finals</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        $(document).ready(function() {
            const finalsContext = @json($finalsDefinition->context);
            const eligibleRoute = "{{ route('finals.students.eligible') }}";
            const eligibleGradeLabel = @json($finalsDefinition->eligiblePriorYearGrade);
            let eligibleFetchTimeout = null;

            function updateBadges(badgeData) {
                document.getElementById('statTotal').textContent = badgeData.totalStudents;
                document.getElementById('statWithResults').textContent = badgeData.studentsWithResults;
                document.getElementById('statNoCandidateNumber').textContent = badgeData.noCandidateNumber;
                document.getElementById('statPending').textContent = badgeData.studentsPending;
                document.getElementById('statPassRate').textContent = badgeData.passRate + '%';
            }

            function renderEligibleStudents(students) {
                const $select = $('#eligibleStudentSelect');
                const $hint = $('#eligibleStudentHint');
                const $submit = $('#addFinalStudentSubmit');

                $select.empty();

                if (!students || students.length === 0) {
                    $hint.text(`No eligible ${eligibleGradeLabel} students found for this year.`);
                    $submit.prop('disabled', true);
                    return;
                }

                students.forEach(student => {
                    const classText = student.class_name ? `Class: ${student.class_name}` : 'Class: N/A';
                    const idText = student.id_number ? `ID: ${student.id_number}` : 'ID: N/A';
                    const candidateText = student.exam_number ? `Candidate Number: ${student.exam_number}` : 'Candidate Number: N/A';
                    const optionText = `${student.name} | ${classText} | ${idText} | ${candidateText}`;
                    $select.append(new Option(optionText, student.id));
                });

                $select.prop('selectedIndex', 0);
                $hint.text(`${students.length} eligible student(s) found.`);
                $submit.prop('disabled', false);
            }

            function loadEligibleStudents() {
                const year = $('#addGraduationYear').val();
                const search = $('#eligibleStudentSearch').val().trim();
                const $hint = $('#eligibleStudentHint');
                const $submit = $('#addFinalStudentSubmit');

                if (!year) {
                    renderEligibleStudents([]);
                    $hint.text('Please select a graduation year.');
                    $submit.prop('disabled', true);
                    return;
                }

                $hint.text('Loading eligible students...');
                $submit.prop('disabled', true);

                $.ajax({
                    url: eligibleRoute,
                    method: 'GET',
                    data: { year, search, finals_context: finalsContext },
                    success: function(response) {
                        renderEligibleStudents(response.students || []);
                        if (response.message && (!response.students || response.students.length === 0)) {
                            $hint.text(response.message);
                        }
                    },
                    error: function() {
                        renderEligibleStudents([]);
                        $hint.text('Failed to load eligible students.');
                    }
                });
            }

            function fetchBadgeData(year = null) {
                let requestData = {};

                if (year) {
                    requestData.year = year;
                }
                requestData.finals_context = finalsContext;

                $.ajax({
                    url: "{{ route('finals.students.badge-data') }}",
                    method: 'GET',
                    data: requestData,
                    success: function(badgeData) {
                        updateBadges(badgeData);
                    },
                    error: function(xhr, status, error) {
                        console.error("Badge data fetch error:", error);
                    }
                });
            }

            function fetchFinalsData() {
                $('#loadingPlaceholder').show();
                $('#finalsStudentsList').empty();

                let yearVal = $('#graduationYear').val();

                $.ajax({
                    url: "{{ route('finals.students.get-data') }}",
                    method: 'GET',
                    data: {
                        year: yearVal,
                        finals_context: finalsContext,
                    },
                    success: function(response) {
                        $('#loadingPlaceholder').hide();
                        $('#finalsStudentsList').html(response).fadeIn(200, function() {
                            if ($('#finals-table').length) {
                                // Destroy existing DataTable instance if it exists
                                if ($.fn.DataTable.isDataTable('#finals-table')) {
                                    $('#finals-table').DataTable().destroy();
                                }

                                $('#finals-table').DataTable({
                                    pageLength: 25,
                                    dom: 'rtip',
                                    columnDefs: [
                                        { orderable: false, targets: [7] }
                                    ],
                                    language: {
                                        paginate: {
                                            previous: "<i class='mdi mdi-chevron-left'></i>",
                                            next: "<i class='mdi mdi-chevron-right'></i>"
                                        }
                                    },
                                    drawCallback: function() {
                                        $('.dataTables_paginate > .pagination')
                                            .addClass('pagination-rounded');
                                    }
                                });

                                // Apply custom filters
                                applyFilters();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        $('#loadingPlaceholder').hide();
                        $('#finalsStudentsList').html(`
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bx bxs-error-alt me-2 fs-4"></i>
                            <div>
                                <strong>Oops! Something went wrong.</strong><br>
                                We couldn't load the final year students list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                            </div>
                        </div>
                    `);
                    }
                });
            }

            function applyFilters() {
                const table = $('#finals-table').DataTable();
                const searchTerm = $('#searchInput').val();
                const resultFilter = $('#resultFilter').val();
                const genderFilter = $('#genderFilter').val();

                // Apply search
                table.search(searchTerm);

                // Custom filtering
                $.fn.dataTable.ext.search.pop(); // Remove any previous custom filter
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const $row = $(row);

                    // Result filter
                    if (resultFilter) {
                        const hasResults = $row.find('.text-success').length > 0;
                        if (resultFilter === 'with_results' && !hasResults) return false;
                        if (resultFilter === 'pending' && hasResults) return false;
                    }

                    // Gender filter (column index 2)
                    if (genderFilter) {
                        const genderCell = data[2].toLowerCase();
                        if (genderFilter === 'M' && !genderCell.includes('male')) return false;
                        if (genderFilter === 'F' && !genderCell.includes('female')) return false;
                        if (genderFilter === 'M' && genderCell.includes('female')) return false;
                    }

                    return true;
                });

                table.draw();
            }

            // Event listeners for filters
            $('#searchInput').on('keyup', function() {
                if ($.fn.DataTable.isDataTable('#finals-table')) {
                    applyFilters();
                }
            });

            $('#resultFilter, #genderFilter').on('change', function() {
                if ($.fn.DataTable.isDataTable('#finals-table')) {
                    applyFilters();
                }
            });

            $('#graduationYear').change(function() {
                let year = $(this).val();
                fetchFinalsData();
                setTimeout(function() {
                    fetchBadgeData(year);
                }, 200);
            });

            $('#resetFilters').click(function() {
                $('#searchInput').val('');
                $('#resultFilter').val('');
                $('#genderFilter').val('');
                if ($.fn.DataTable.isDataTable('#finals-table')) {
                    applyFilters();
                }
            });

            $('#addFinalStudentModal').on('shown.bs.modal', function() {
                const selectedYear = $('#graduationYear').val() || "{{ $selectedYear }}";
                $('#addGraduationYear').val(selectedYear);
                $('#eligibleStudentSearch').val('');
                loadEligibleStudents();
            });

            $('#addGraduationYear').on('change', function() {
                loadEligibleStudents();
            });

            $('#eligibleStudentSearch').on('input', function() {
                if (eligibleFetchTimeout) {
                    clearTimeout(eligibleFetchTimeout);
                }
                eligibleFetchTimeout = setTimeout(loadEligibleStudents, 300);
            });

            $('#addFinalStudentForm').on('submit', function() {
                if (!this.checkValidity()) {
                    return;
                }

                const $submit = $('#addFinalStudentSubmit');
                $submit.addClass('loading');
                $submit.prop('disabled', true);
            });

            // Initial load
            fetchFinalsData();
            fetchBadgeData($('#graduationYear').val());
        });
    </script>
@endsection

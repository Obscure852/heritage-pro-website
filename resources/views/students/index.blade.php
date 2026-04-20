@extends('layouts.master')
@section('title')
    Students Module
@endsection
@section('css')
    <style>
        .students-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .students-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .students-body {
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

        .controls .input-group-text {
            background: #f8f9fa;
            border-color: #dee2e6;
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

        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
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

        .badge-class {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-grade {
            background: #f0fdf4;
            color: #166534;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
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
            border-radius: 4px;
        }

        .placeholder-button {
            width: 100px;
            height: 30px;
            background-color: #e9ecef;
            border: none;
            border-radius: 4px;
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
            min-width: 280px;
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

        .reports-dropdown .dropdown-divider {
            margin: 8px 0;
        }

        .reports-dropdown .dropdown-header {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            padding: 8px 16px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .students-header {
                padding: 20px;
            }

            .students-body {
                padding: 16px;
            }
        }

        .term-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .term-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
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
        @foreach ($errors->all() as $error)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <select name="term" id="termId" class="form-select term-select">
                @if (!empty($terms))
                    @foreach ($terms as $term)
                        <option data-year="{{ $term->year }}"
                            value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                            {{ 'Term ' . $term->term . ',' . $term->year }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="students-container">
            <div class="students-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 style="margin:0;">Students</h3>
                        <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage student records</p>
                    </div>
                    <div class="col-md-6">
                        @php
                            $totalCount = $students->count() ?? 0;
                            $maleCount = $students->where('gender', 'M')->count();
                            $femaleCount = $students->where('gender', 'F')->count();
                            $specialNeedsCount = $students->whereNotNull('student_type_id')->count();
                        @endphp
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="stat-item">
                                    <h4 id="studentsTotalCount" class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-item">
                                    <h4 id="studentsMaleCount" class="mb-0 fw-bold text-white">{{ $maleCount }}</h4>
                                    <small class="opacity-75">Male</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-item">
                                    <h4 id="studentsFemaleCount" class="mb-0 fw-bold text-white">{{ $femaleCount }}</h4>
                                    <small class="opacity-75">Female</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-item">
                                    <h4 id="studentsSpecialNeedsCount" class="mb-0 fw-bold text-white">{{ $specialNeedsCount }}</h4>
                                    <small class="opacity-75">Special Needs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="students-body">
                <div class="help-text">
                    <div class="help-title">Students Directory</div>
                    <div class="help-content">
                        Browse and manage all student records. Use the filters to find specific students by status, gender,
                        class, or grade.
                        Click on a student name to view full details, or use the action buttons to view or manage entries.
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-6 col-md-12">
                        @php
                            $duplicateCount = $duplicateStudents?->count() ?? 0;
                            $noClassCount = $studentsWithNoClasses ?? 0;
                        @endphp
                        <div id="studentIssueBadges"
                            class="d-flex flex-wrap gap-2 {{ $duplicateCount > 0 || $noClassCount > 0 ? '' : 'd-none' }}">
                            <a id="duplicateStudentsBadge" href="{{ route('students.duplicates') }}"
                                class="alert alert-danger d-flex align-items-center mb-0 py-2 px-3 text-decoration-none {{ $duplicateCount > 0 ? '' : 'd-none' }}"
                                style="border-radius: 3px; font-size: 13px;">
                                <i class="fas fa-copy me-2"></i>
                                <strong><span id="duplicateStudentsCount">{{ $duplicateCount }}</span> Duplicates</strong>
                                <i class="fas fa-chevron-right ms-2" style="font-size: 10px;"></i>
                            </a>
                            <a id="studentsWithNoClassesBadge" href="{{ route('students.unallocated') }}"
                                class="alert alert-warning d-flex align-items-center mb-0 py-2 px-3 text-decoration-none {{ $noClassCount > 0 ? '' : 'd-none' }}"
                                style="border-radius: 3px; font-size: 13px;">
                                <i class="fas fa-user-slash me-2"></i>
                                <strong><span id="studentsWithNoClassesCount">{{ $noClassCount }}</span> Students without
                                    class</strong>
                                <i class="fas fa-chevron-right ms-2" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 text-lg-end text-md-start mt-lg-0 mt-md-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                            @can('manage-students')
                                @if (!session('is_past_term'))
                                    <a href="{{ route('students.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> New Student
                                    </a>
                                @endif
                            @endcan
                            <div class="btn-group reports-dropdown">
                                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2"
                                        style="font-size: 10px;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('students.students-custom-analysis') }}">
                                            <i class="fas fa-cogs me-2" style="color: #4287f5;"></i> Students Custom
                                            Reports</a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('students.class-list-report') }}">
                                            <i class="fas fa-list-ol me-2" style="color: #10b981;"></i> Class List Report</a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('students.klasses-with-stats') }}">
                                            <i class="fas fa-chalkboard-teacher me-2" style="color: #6a5acd;"></i> Class
                                            Teacher's Report</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.students-statistics') }}">
                                            <i class="fas fa-chart-bar me-2" style="color: #4287f5;"></i> Students Class
                                            Analysis Report</a>
                                    </li>
                                    @if($school_data->boarding)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.boarding-analysis') }}">
                                            <i class="fas fa-bed me-2" style="color: #6a5acd;"></i> Boarding Analysis Report</a>
                                    </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.students-statistics-type') }}">
                                            <i class="fas fa-filter me-2" style="color: #6a5acd;"></i> Students Class
                                            Analysis
                                            By Type</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('students.students-statistics-filter') }}">
                                            <i class="fas fa-search me-2" style="color: #4287f5;"></i> Students Class
                                            Analysis
                                            By Filter</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('students.students-leaving-analysis-year') }}">
                                            <i class="fas fa-sign-out-alt me-2" style="color: #6a5acd;"></i> Students
                                            Departures Report</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('students.students-book-allocations') }}">
                                            <i class="fas fa-book me-2" style="color: #4287f5;"></i> Textbook Allocations
                                            Report</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('students.students-textbooks-status') }}">
                                            <i class="fas fa-clipboard-check me-2" style="color: #6a5acd;"></i> Textbooks
                                            By
                                            Status Report</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.house-students') }}">
                                            <i class="fas fa-home me-2" style="color: #4287f5;"></i> House Students
                                            Allocations</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.without-houses') }}">
                                            <i class="fas fa-user-slash me-2" style="color: #dc3545;"></i> Students
                                            Without
                                            Houses</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.term-import-list') }}">
                                            <i class="fas fa-file-import me-2" style="color: #6a5acd;"></i> Import
                                            Students
                                            List</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('students.id-cards') }}">
                                            <i class="fas fa-id-card me-2" style="color: #4287f5;"></i> Student ID Cards</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header">Student Management</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('students.duplicates') }}">
                                            <i class="fas fa-copy me-2" style="color: #4287f5;"></i> Duplicate
                                            Students</a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('students.unallocated') }}">
                                            <i class="fas fa-user-slash me-2" style="color: #6a5acd;"></i> Unallocated
                                            Students</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-12 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search by name..."
                                            id="searchInput">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select id="status-filter" class="form-select">
                                        <option value="Current" selected>Current</option>
                                        @if (!empty($allStatuses))
                                            @foreach ($allStatuses as $status)
                                                @if ($status->name !== 'Current')
                                                    <option value="{{ $status->name }}">{{ $status->name }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select id="class-filter" name="class" class="form-select">
                                        <option value="">All Classes</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->name }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select id="grade-filter" name="grade" class="form-select">
                                        <option value="">All Grades</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->name }}">{{ $grade->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2 col-sm-6">
                                    <button type="button" class="btn btn-light w-100" id="clear-filters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="studentTermList"></div>

                <div id="loadingPlaceholder">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Gender</th>
                                    <th scope="col">Date of Birth</th>
                                    <th scope="col">ID Number</th>
                                    <th scope="col">Class</th>
                                    <th scope="col">Grade</th>
                                    <th style="width: 80px; min-width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 10; $i++)
                                    <tr class="placeholder-glow">
                                        <td><span class="placeholder-item" style="width: 120px; height: 16px;"></span>
                                        </td>
                                        <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                        <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                        <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                        <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                        <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                        <td>
                                            <span class="placeholder-item"
                                                style="width: 24px; height: 24px; border-radius: 50%;"></span>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    @endsection
    @section('script')
        <script>
            $(document).ready(function() {
                function loadFiltersFromLocalStorage() {
                    let savedSearch = localStorage.getItem('filter_search');
                    let savedStatus = localStorage.getItem('filter_status');
                    let savedClass = localStorage.getItem('filter_class');
                    let savedGrade = localStorage.getItem('filter_grade');

                    if (savedSearch !== null) {
                        $('#searchInput').val(savedSearch);
                    }

                    if (savedStatus !== null) {
                        $('#status-filter').val(savedStatus);
                    } else {
                        $('#status-filter').val('Current');
                    }

                    if (savedClass !== null) {
                        $('#class-filter').val(savedClass);
                    }
                    if (savedGrade !== null) {
                        $('#grade-filter').val(savedGrade);
                    }
                }

                function saveFiltersToLocalStorage() {
                    localStorage.setItem('filter_status', $('#status-filter').val());
                    localStorage.setItem('filter_class', $('#class-filter').val());
                    localStorage.setItem('filter_grade', $('#grade-filter').val());
                    localStorage.setItem('filter_search', $('#searchInput').val());
                }

                function fetchTermData() {
                    $('#loadingPlaceholder').show();
                    $('#studentTermList').empty();

                    let searchVal = $('#searchInput').val();
                    let statusVal = $('#status-filter').val();
                    let classVal = $('#class-filter').val();
                    let gradeVal = $('#grade-filter').val();

                    $.ajax({
                        url: "{{ route('students.student-get-data') }}",
                        method: 'GET',
                        data: {
                            search: searchVal,
                            status: statusVal,
                            'class': classVal,
                            grade: gradeVal
                        },
                        success: function(response) {
                            $('#loadingPlaceholder').hide();
                            // Destroy existing DataTable before loading new content
                            if ($.fn.DataTable.isDataTable('#d-tables')) {
                                $('#d-tables').DataTable().destroy();
                            }
                            $('#studentTermList').html(response).fadeIn(200, function() {
                                // Safe initialization - only if element exists and not already initialized
                                if ($('#d-tables').length && !$.fn.DataTable.isDataTable('#d-tables')) {
                                    $('#d-tables').DataTable({
                                        pageLength: 10,
                                        searching: false,
                                        info: false,
                                        lengthChange: false,
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
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            $('#loadingPlaceholder').hide();
                            $('#studentTermList').html(`
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bx bxs-error-alt me-2 fs-4"></i>
                            <div>
                                <strong>Oops! Something went wrong.</strong><br>
                                We couldn't load the student term list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                            </div>
                        </div>
                    `);
                        }
                    });
                }

                function updateBadgeUi(badgeData) {
                    const totalStudents = Number(badgeData && badgeData.totalStudents) || 0;
                    const maleCount = Number(badgeData && badgeData.maleCount) || 0;
                    const femaleCount = Number(badgeData && badgeData.femaleCount) || 0;
                    const specialNeedsCount = Number(badgeData && badgeData.specialNeedsCount) || 0;
                    const duplicateCount = Number(badgeData && badgeData.duplicateStudentsCount) || 0;
                    const studentsWithNoClasses = Number(badgeData && badgeData.studentsWithNoClasses) || 0;

                    $('#studentsTotalCount').text(totalStudents);
                    $('#studentsMaleCount').text(maleCount);
                    $('#studentsFemaleCount').text(femaleCount);
                    $('#studentsSpecialNeedsCount').text(specialNeedsCount);


                    $('#duplicateStudentsCount').text(duplicateCount);
                    $('#studentsWithNoClassesCount').text(studentsWithNoClasses);

                    $('#duplicateStudentsBadge').toggleClass('d-none', duplicateCount <= 0);
                    $('#studentsWithNoClassesBadge').toggleClass('d-none', studentsWithNoClasses <= 0);
                    $('#studentIssueBadges').toggleClass('d-none', duplicateCount <= 0 && studentsWithNoClasses <= 0);
                }

                function fetchBadgeData(termId = null) {
                    const selectedTermId = (termId !== null && termId !== undefined) ? termId : $('#termId').val();
                    const statusVal = $('#status-filter').val() || 'Current';

                    $.ajax({
                        url: "{{ route('students.badge-data') }}",
                        method: 'GET',
                        data: {
                            term_id: selectedTermId,
                            status: statusVal
                        },
                        success: function(badgeData) {
                            updateBadgeUi(badgeData);
                        },
                        error: function(xhr, status, error) {
                            console.error("Badge data fetch error:", error);
                        }
                    });
                }

                $('#termId').change(function() {
                    let term = $(this).val();
                    let studentsSessionUrl = "{{ route('students.term-session') }}";

                    $.ajax({
                        url: studentsSessionUrl,
                        method: 'POST',
                        data: {
                            term_id: term,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            fetchTermData();
                            fetchBadgeData(term);
                        },
                        error: function(xhr, status, error) {
                            console.error("Term change error:", error);
                        }
                    });
                });

                $(document).on('change', '#status-filter', function() {
                    saveFiltersToLocalStorage();
                    fetchTermData();
                    fetchBadgeData();
                });

                $(document).on('change', '#class-filter, #grade-filter', function() {
                    saveFiltersToLocalStorage();
                    fetchTermData();
                    fetchBadgeData();
                });

                // Debounced search input
                let searchTimeout;
                $('#searchInput').on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        saveFiltersToLocalStorage();
                        fetchTermData();
                    }, 300);
                });

                $('#clear-filters').click(function() {
                    $('#searchInput').val('');
                    $('#status-filter').val('Current');
                    $('#class-filter').val('');
                    $('#grade-filter').val('');

                    localStorage.removeItem('filter_status');
                    localStorage.removeItem('filter_class');
                    localStorage.removeItem('filter_grade');
                    localStorage.removeItem('filter_search');

                    fetchTermData();
                    fetchBadgeData();
                });

                loadFiltersFromLocalStorage();
                $('#termId').trigger('change');
            });
        </script>
    @endsection

<style>
    /* Hide DataTables default elements */
    #studentTable_wrapper .dataTables_filter,
    #studentTable_wrapper .dataTables_length,
    #studentTable_wrapper .dataTables_info,
    #studentTable_wrapper .dataTables_paginate {
        display: none !important;
    }

    /* Hide DataTables default sort icons */
    #studentTable thead th.sorting::before,
    #studentTable thead th.sorting::after,
    #studentTable thead th.sorting_asc::before,
    #studentTable thead th.sorting_asc::after,
    #studentTable thead th.sorting_desc::before,
    #studentTable thead th.sorting_desc::after,
    #studentTable.dataTable thead .sorting::before,
    #studentTable.dataTable thead .sorting::after,
    #studentTable.dataTable thead .sorting_asc::before,
    #studentTable.dataTable thead .sorting_asc::after,
    #studentTable.dataTable thead .sorting_desc::before,
    #studentTable.dataTable thead .sorting_desc::after {
        display: none !important;
        content: none !important;
    }

    .assessment-class-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .assessment-class-header {
        background: #4e73df;
        color: white;
        padding: 16px 20px;
        border-radius: 3px 3px 0 0;
    }

    .assessment-class-header .header-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .assessment-class-header .header-icon-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .assessment-class-header .header-icon-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .assessment-class-header .header-icon-btn i {
        font-size: 18px;
    }

    .assessment-class-body {
        padding: 20px;
    }

    .stat-item {
        padding: 0 12px;
        border-left: 1px solid rgba(255, 255, 255, 0.3);
    }

    .stat-item:first-child {
        border-left: none;
    }

    .stat-item h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    .stat-item small {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
    }

    /* Reports Dropdown Styling */
    .reports-dropdown .dropdown-toggle {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: 3px;
        transition: all 0.2s ease;
        font-size: 14px;
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
        z-index: 1060;
        background: white;
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

    .reports-dropdown .dropdown-submenu {
        position: relative;
    }

    .reports-dropdown .dropdown-submenu>.dropdown-menu {
        top: 0;
        left: auto;
        right: 100%;
        margin-top: -8px;
        margin-right: 2px;
        display: none;
        z-index: 1050;
    }

    .reports-dropdown .dropdown-submenu:hover>.dropdown-menu {
        display: block;
    }

    /* Nested submenus (3rd level) - open below instead of to the side */
    .reports-dropdown .dropdown-submenu .dropdown-submenu>.dropdown-menu {
        top: 0;
        right: 100%;
        margin-right: 2px;
    }

    .reports-dropdown .dropdown-divider {
        margin: 4px 0;
    }

    /* Controls */
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

    /* Sortable header styling */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .sortable-header:hover {
        background-color: #e5e7eb !important;
    }

    .sort-icon {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-left: 4px;
        font-size: 9px;
        line-height: 0.7;
        vertical-align: middle;
        color: #9ca3af;
    }

    .sort-icon .fa-caret-up,
    .sort-icon .fa-caret-down {
        display: block;
    }

    .sort-icon.asc .fa-caret-up {
        color: #3b82f6;
    }

    .sort-icon.desc .fa-caret-down {
        color: #3b82f6;
    }

    /* Table Styling */
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
        flex-shrink: 0;
    }

    .student-avatar-placeholder.male {
        background: #dbeafe;
        color: #1e40af;
    }

    .student-avatar-placeholder.female {
        background: #fce7f3;
        color: #be185d;
    }

    .gender-male {
        color: #007bff;
    }

    .gender-female {
        color: #e83e8c;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
    }

    .action-buttons .action-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #64748b;
    }

    .action-buttons .action-icon:hover {
        background: #f3f4f6;
        color: #374151;
        transform: translateY(-1px);
    }

    .action-buttons .action-icon i {
        font-size: 24px;
    }

    .action-buttons .action-icon.pdf-icon:hover {
        color: #dc2626;
    }

    .action-buttons .action-icon.html-icon:hover {
        color: #f97316;
    }

    .action-buttons .action-icon.email-icon:hover {
        color: #3b82f6;
    }

    .remarks-icon {
        color: #64748b;
        transition: color 0.2s;
    }

    .remarks-icon:hover {
        color: #5156BE;
    }

    .remarks-icon.has-remarks {
        color: #5156BE;
    }

    /* Modal Theming */
    .modal-content {
        border: none;
        border-radius: 3px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: white;
    }

    .modal-header .modal-title {
        font-weight: 600;
        font-size: 16px;
        color: #1f2937;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-body .form-label {
        font-weight: 500;
        color: #374151;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .modal-body .form-control,
    .modal-body .form-control-sm {
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
    }

    .modal-body .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .modal-footer .btn {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .modal-footer .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .modal-footer .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
        color: white;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .modal-footer .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    /* Button Loading State */
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

<div class="assessment-class-container">
    <div class="assessment-class-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                @if (!empty($class))
                    @php
                        $totalCount = $class->students->count();
                        $maleCount = $class->students->where('gender', 'M')->count();
                        $femaleCount = $class->students->where('gender', 'F')->count();
                    @endphp
                    <h5 style="margin:0; font-weight: 600;">
                        <i class="fas fa-users me-2"></i>{{ $class->name ?? '' }}
                    </h5>
                    <p style="margin:4px 0 0 0; opacity:.9; font-size: 14px;">
                        Class Teacher: {{ $class->teacher->lastname ?? '' }}
                        <span style="margin-left: 15px; opacity: 0.8;">
                            <span id="totalStudents">{{ $totalCount }}</span> Total
                            <span class="mx-1">|</span>
                            <span id="maleCount">{{ $maleCount }}</span> Male
                            <span class="mx-1">|</span>
                            <span id="femaleCount">{{ $femaleCount }}</span> Female
                        </span>
                    </p>
                @endif
            </div>
            <div class="header-right">
                @can('email-communications')
                    @if (auth()->user()->hasAllocatedClass() && !empty($class))
                        <button type="button" class="header-icon-btn" onclick="archiveToEmailModal()"
                            data-bs-toggle="tooltip" title="Archive Report Cards">
                            <i class="bx bx-archive-in"></i>
                        </button>
                        <button type="button" class="header-icon-btn" onclick="openBulkEmailModal()"
                            data-bs-toggle="tooltip" title="Bulk Email Report Cards">
                            <i class="bx bx-mail-send"></i>
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="assessment-class-body">
        <!-- Controls Row -->
        <div class="row align-items-center mb-3">
            <div class="col-lg-6 col-md-12">
                <div class="controls">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-5 col-md-4 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search by name..."
                                    id="studentSearchInput">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <select class="form-select" id="genderFilter">
                                <option value="">All Gender</option>
                                <option value="m">Male</option>
                                <option value="f">Female</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-6">
                            <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                    <!-- Class Reports Dropdown -->
                    <div class="btn-group reports-dropdown">
                        <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-alt me-2"></i>Class Reports<i class="fas fa-chevron-down ms-2"
                                style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item"
                                    onclick="openJuniorClassReportCards($('#classId').val()); return false;"
                                    href="#">
                                    <i class="bx bxs-file-pdf text-danger"></i> Class Report Cards
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-line text-success"></i> Class Performance
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openClassAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item" onclick="openClassAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-level-up-alt text-purple"></i> Class Value Addition
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAValueAdditionAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openCAValueAdditionAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-area text-warning"></i> Overall Classes Analysis I
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAClassesAnalysis({{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item" onclick="openExamClassesAnalysis(); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-th-list text-info"></i> Overall Classes Analysis II
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAClassesAnalysisII({{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item" onclick="openExamClassesAnalysisII(); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-balance-scale text-info"></i> Tests Comparison Value Addition
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCompareValueAdditionAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openCompareValueAdditionAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-table text-primary"></i> Subjects Analysis By Class
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openSubjectGradeDistributionAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openSubjectGradeDistributionAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-user-tie text-purple"></i> Teachers Performance
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCATeachersAnalysis('CA','{{ $test->sequence }}'); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openCATeachersAnalysis('Exam',1); return false;" href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <!-- Grade Reports Dropdown -->
                    <div class="btn-group reports-dropdown">
                        <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-graduation-cap me-2"></i>Grade Reports<i class="fas fa-chevron-down ms-2"
                                style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- Subjects Performance -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-line text-primary"></i> Subjects Performance
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openExamSubjectGradeAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openExamSubjectGradeAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall Teachers Performance -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chalkboard-teacher text-success"></i> Overall Teachers
                                    Performance
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openOverallTeacherPerformanceByGrade('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openOverallTeacherPerformanceByGrade('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <!-- Grade Distribution (promoted from Grade Reports) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-pie text-purple"></i> Grade Distribution
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openGradeDistributionByGender({{ $test->sequence }},'CA'); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openGradeDistributionByGender(1,'Exam'); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall Stream Analysis -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-stream text-primary"></i> Overall Stream Analysis
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAGradeAnalysis({{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openOverallGradeAnalysisExam(); return false;" href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Value Addition -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-line text-danger"></i> Value Addition
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAValueAdditionGradeAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openCAValueAdditionGradeAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Tests Comparison Value Addition -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-balance-scale text-info"></i> Tests Comparison Value Addition
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openTestComparisonGradeAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openTestComparisonGradeAnalysis('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Region Stream Analysis -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-map-marked-alt text-success"></i> Region Stream Analysis
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openGradeStreamPSLEAnalysis({{ $test->sequence }},'CA'); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openGradeStreamPSLEAnalysis(1,'Exam'); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Special Needs Analysis -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-universal-access text-warning"></i> Special Needs Analysis
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openSpecialNeedsAnalysis({{ $test->sequence }},'CA'); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openSpecialNeedsAnalysis(1,'Exam'); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <!-- Subjects by House (promoted from House Analysis) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-book-open text-info"></i> Subjects by House
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openCAHouseAnalysis({{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item" onclick="openExamHouseAnalysis(); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall Performance (By Gender) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-area text-primary"></i> Overall Performance (By Gender)
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openOverallCAHouseAnalysis({{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openOverallExamHouseAnalysis(); return false;" href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall Performance (No Gender) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-chart-area text-primary"></i> Overall Performance (No Gender)
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openExamHousesOverallAnalysisSimple('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openExamHousesOverallAnalysisSimple('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall By Grade (By Gender) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-sort-amount-up text-purple"></i> Overall By Grade (By Gender)
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openGradeHouseAnalysis('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openGradeHouseAnalysis('Exam',1); return false;" href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Overall By Grade (No Gender) -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-sort-amount-up text-success"></i> Overall By Grade (No Gender)
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openGradeHouseAnalysisSimple('CA',{{ $test->sequence }}); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openGradeHouseAnalysisSimple('Exam',1); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <!-- Department Analysis -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-chevron-left me-2 text-muted"></i><i
                                        class="fas fa-building text-purple"></i> Department Analysis
                                </a>
                                <ul class="dropdown-menu">
                                    @if (!$tests->isEmpty())
                                        @foreach ($tests as $test)
                                            @php
                                                $endDate = \Carbon\Carbon::parse($test->end_date);
                                                $endMonth = $endDate->format('M');
                                            @endphp
                                            <li>
                                                <a class="dropdown-item"
                                                    onclick="openGradeDepartmentAnalysis({{ $test->sequence }},'CA'); return false;"
                                                    href="#">
                                                    <i class="fas fa-calendar-alt text-primary"></i> End of
                                                    {{ $endMonth }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li>
                                        <a class="dropdown-item"
                                            onclick="openGradeDepartmentAnalysis(1,'Exam'); return false;"
                                            href="#">
                                            <i class="fas fa-calendar-check text-danger"></i> End of Term (Exam)
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($class))
            <div class="table-responsive">
                <table id="studentTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th class="sortable-header" data-sort-col="1">
                                Student
                                <span class="sort-icon">
                                    <i class="fas fa-caret-up"></i>
                                    <i class="fas fa-caret-down"></i>
                                </span>
                            </th>
                            <th class="sortable-header" data-sort-col="2">
                                Date of Birth
                                <span class="sort-icon">
                                    <i class="fas fa-caret-up"></i>
                                    <i class="fas fa-caret-down"></i>
                                </span>
                            </th>
                            <th class="sortable-header" data-sort-col="3">
                                Gender
                                <span class="sort-icon">
                                    <i class="fas fa-caret-up"></i>
                                    <i class="fas fa-caret-down"></i>
                                </span>
                            </th>
                            <th class="sortable-header" data-sort-col="4">
                                Class
                                <span class="sort-icon">
                                    <i class="fas fa-caret-up"></i>
                                    <i class="fas fa-caret-down"></i>
                                </span>
                            </th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($class->students->sortBy('first_name') as $index => $student)
                            @php
                                $termId = session('selected_term_id') ?? App\Helpers\TermHelper::currentTermId();
                                $class_teacher_remarks = $student
                                    ->overallComments()
                                    ->where('term_id', $termId)
                                    ->pluck('class_teacher_remarks')
                                    ->first();

                                $school_head_remarks = $student
                                    ->overallComments()
                                    ->where('term_id', $termId)
                                    ->pluck('school_head_remarks')
                                    ->first();
                                $remarks_present = !empty($class_teacher_remarks) && !empty($school_head_remarks);

                                $fullname = $student->fullName ?? '';
                                $parentEmail = $student->sponsor->email ?? 'No Email Address';
                                $teacher = $class->teacher->fullName ?? '';
                                $initials = strtoupper(
                                    substr($student->first_name ?? '', 0, 1) . substr($student->last_name ?? '', 0, 1),
                                );
                                $genderClass = $student->gender == 'M' ? 'male' : 'female';
                            @endphp
                            <tr class="student-row"
                                data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                                data-gender="{{ strtolower($student->gender ?? '') }}"
                                style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="student-cell">
                                        <div class="student-avatar-placeholder {{ $genderClass }}">
                                            {{ $initials ?: 'ST' }}</div>
                                        <div>
                                            <div>
                                                <a href="{{ route('assessment.comments', ['id' => $student->id]) }}">
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </a>
                                                @if ($student->type)
                                                    <span class="student-type-badge"
                                                        style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                        {{ $student->type->type }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 12px;">
                                                @if ($remarks_present)
                                                    <i class="bx bxs-check-circle text-success"
                                                        style="font-size: 11px;"></i> Remarks added
                                                @else
                                                    <i class="bx bxs-x-circle text-muted"
                                                        style="font-size: 11px;"></i> No remarks
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    @if ($student->gender == 'M')
                                        <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                                    @else
                                        <span class="gender-female"><i class="bx bx-female-sign"></i> Female</span>
                                    @endif
                                </td>
                                <td>{{ $class->name }}</td>
                                <td class="text-end">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <span class="action-icon pdf-icon"
                                            onclick="pdfReportCardPopupJunior({{ $student->id }})"
                                            data-bs-toggle="tooltip" title="View PDF Report Card">
                                            <i class="bx bxs-file-pdf"></i>
                                        </span>
                                        <span class="action-icon html-icon"
                                            onclick="htmlReportCardPopup({{ $student->id }})"
                                            data-bs-toggle="tooltip" title="View HTML Report Card">
                                            <i class="bx bxs-file-html"></i>
                                        </span>
                                        @if (auth()->user()->hasAllocatedClass())
                                            <span class="action-icon email-icon"
                                                onclick="openEmailModal({{ $student->id }}, '{{ $fullname }}', '{{ $currentTerm?->term }}', '{{ $teacher }}', '{{ $parentEmail }}')"
                                                data-bs-toggle="tooltip" title="Email to Parent">
                                                <i class="bx bx-mail-send"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="bx bx-folder-open" style="font-size: 48px; opacity: 0.5;"></i>
                <p class="mt-3">No class data available.</p>
            </div>
        @endif
    </div>
</div>

@if (!empty($class))
    <input type="hidden" id="classId" name="classId" value="{{ $class->id }}">

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Report Card to Parent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        @csrf
                        <input type="hidden" id="studentId" name="studentId">
                        <div class="mb-3">
                            <label for="to" class="form-label">To:</label>
                            <input readonly type="email" class="form-control form-control-sm" id="to"
                                name="to" required>
                            <small class="text-danger">Please confirm that the email address of the parent is correct &
                                valid.</small>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control form-control-sm" id="subject" name="subject"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message:</label>
                            <textarea class="form-control form-control-sm" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachment:</label>
                            <p class="form-control-static" id="attachmentInfo">
                                <i style="color:red;font-size:14px;" class="bx bxs-file-pdf"></i> <span
                                    id="pdfFileName"></span>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-loading" id="sendEmailBtn"
                        onclick="sendEmail()">
                        <span class="btn-text"><i class="fas fa-paper-plane"></i> Send Email</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Email Modal -->
    <div class="modal fade" id="bulkEmailModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="bulkEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkEmailModalLabel">Send Bulk Report Cards</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bulkEmailForm">
                        @csrf
                        <input type="hidden" name="classId" value="{{ $class->id }}">
                        <div class="mb-3">
                            <label for="bulkSubject" class="form-label">Email Subject:</label>
                            <input type="text" class="form-control form-control-sm" id="bulkSubject"
                                name="bulkSubject" required
                                value="Report Cards for {{ $class->name }} - Term {{ $currentTerm?->term }}">
                        </div>
                        <div class="mb-3">
                            <label for="bulkMessage" class="form-label">Email Message:</label>
                            <textarea class="form-control" id="bulkMessage" name="bulkMessage" rows="4" required>Dear Parent/Guardian, Please find attached the report card for your child for Term {{ $currentTerm?->term }}.
Best regards,

{{ $class->teacher->fullName }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Students:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Include</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($class->students as $student)
                                            <tr>
                                                <td>{{ $student->fullName }}</td>
                                                <td>{{ $student->sponsor->email ?? 'N/A' }}</td>
                                                <td>
                                                    <input class="form-check-input" type="checkbox" name="students[]"
                                                        value="{{ $student->id }}"
                                                        {{ $student->sponsor && $student->sponsor->email ? 'checked' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="sendBulkEmailBtn" class="btn btn-primary btn-loading"
                        onclick="sendBulkEmail()">
                        <span class="btn-text"><i class="fas fa-paper-plane"></i> Send Emails</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive to Email Modal -->
    <div class="modal fade" id="archiveEmailModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="archiveEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveEmailModalLabel">Archive Report Cards to School Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="archiveEmailForm">
                        @csrf
                        <input type="hidden" name="classId" value="{{ $class->id }}">
                        <div class="mb-3">
                            <label for="archiveSubject" class="form-label">Email Subject:</label>
                            <input type="text" class="form-control form-control-sm" id="archiveSubject"
                                name="archiveSubject" required
                                value="Archived Report Cards for {{ $class->name }} - Term {{ $currentTerm?->term }}">
                        </div>
                        <div class="mb-3">
                            <label for="archiveMessage" class="form-label">Email Message:</label>
                            <textarea class="form-control" id="archiveMessage" name="archiveMessage" rows="4" required>Archive of report cards for {{ $class->name }} - Term {{ $currentTerm?->term }}
Created on: {{ now()->format('Y-m-d H:i:s') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachment:</label>
                            <p class="form-control-static" id="attachmentInfo">
                                <i style="color:red;font-size:14px;" class="bx bxs-file-pdf"></i>
                                <span>{{ strtolower($class->name) }}_term_{{ $currentTerm?->term }}_report_cards_list.pdf</span>
                            </p>
                            <small class="text-muted">This PDF contains a list of report cards for all students in the
                                class.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="sendArchiveEmailBtn" class="btn btn-primary btn-loading"
                        onclick="sendArchiveEmail()">
                        <span class="btn-text"><i class="fas fa-archive"></i> Send Archive</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip()
    })

    // Client-side filtering
    let sortField = null;
    let sortDirection = 1;

    function filterAndPaginateStudents() {
        const searchTerm = document.getElementById('studentSearchInput')?.value.toLowerCase() || '';
        const genderFilter = document.getElementById('genderFilter')?.value.toLowerCase() || '';

        const allRows = document.querySelectorAll('.student-row');
        let totalFiltered = 0;
        let maleCount = 0;
        let femaleCount = 0;

        allRows.forEach(row => {
            const name = row.dataset.name || '';
            const gender = row.dataset.gender || '';

            const matchesSearch = !searchTerm || name.includes(searchTerm);
            const matchesGender = !genderFilter || gender === genderFilter;

            if (matchesSearch && matchesGender) {
                row.style.display = '';
                totalFiltered++;
                if (gender === 'm') maleCount++;
                if (gender === 'f') femaleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update stats
        const totalEl = document.getElementById('totalStudents');
        const maleEl = document.getElementById('maleCount');
        const femaleEl = document.getElementById('femaleCount');
        if (totalEl) totalEl.textContent = totalFiltered;
        if (maleEl) maleEl.textContent = maleCount;
        if (femaleEl) femaleEl.textContent = femaleCount;
    }

    function resetFilters() {
        const searchInput = document.getElementById('studentSearchInput');
        const genderFilter = document.getElementById('genderFilter');
        if (searchInput) searchInput.value = '';
        if (genderFilter) genderFilter.value = '';
        filterAndPaginateStudents(true);
    }

    // Event listeners
    document.getElementById('studentSearchInput')?.addEventListener('input', () => filterAndPaginateStudents(true));
    document.getElementById('genderFilter')?.addEventListener('change', () => filterAndPaginateStudents(true));
    document.getElementById('resetFilters')?.addEventListener('click', resetFilters);

    // Initialize
    document.addEventListener('DOMContentLoaded', () => filterAndPaginateStudents(true));

    // Sortable headers with visual feedback
    $(document).ready(function() {
        $('.sortable-header').click(function() {
            const columnIndex = $(this).index();
            const $sortIcon = $(this).find('.sort-icon');

            // Reset all sort icons
            $('.sortable-header .sort-icon').removeClass('asc desc');

            if (sortField === columnIndex) {
                sortDirection *= -1;
            } else {
                sortField = columnIndex;
                sortDirection = 1;
            }

            // Update sort icon
            if (sortDirection === 1) {
                $sortIcon.addClass('asc');
            } else {
                $sortIcon.addClass('desc');
            }

            const rows = $('#studentTable tbody tr').get();
            rows.sort(function(a, b) {
                const aValue = $(a).children('td').eq(columnIndex).text().trim();
                const bValue = $(b).children('td').eq(columnIndex).text().trim();
                return aValue.localeCompare(bValue) * sortDirection;
            });
            $('#studentTable tbody').empty().append(rows);

            // Re-apply filters after sorting
            filterAndPaginateStudents(false);
        });
    });

    function openEmailModal(studentId, fullName, term, teacherName, parentEmail) {
        const [firstName, lastName] = fullName.split(' ');
        const pdfFileName = `${firstName.toLowerCase()}_${lastName.toLowerCase()}_term_${term}_report_card.pdf`;

        $('#studentId').val(studentId);
        $('#to').val(parentEmail);
        $('#subject').val(`Report Card for ${fullName} - Term ${term}`);
        $('#message').val(
            `Dear Parent/Guardian,\n\nPlease find attached the report card for ${fullName} for Term ${term}.\n\nBest regards,\n${teacherName}`
        );
        $('#pdfFileName').text(pdfFileName);
        $('#emailModal').modal('show');
    }

    if (typeof withAssessmentContext !== 'function') {
        const assessmentContext = @json($assessmentContext ?? $gradebookCurrentContext);
        window.withAssessmentContext = function(url) {
            if (!assessmentContext) {
                return url;
            }

            var separator = url.indexOf('?') === -1 ? '?' : '&';
            return url + separator + 'context=' + encodeURIComponent(assessmentContext);
        };
    }

    function pdfReportCardPopupJunior(studentId) {
        var url = "{{ route('assessment.junior-pdf-report-card', ':id') }}";
        url = withAssessmentContext(url.replace(':id', studentId));
        window.open(url, 'PDFWindow', 'width=800,height=1000');
    }

    function htmlReportCardPopup(studentId) {
        var url = "{{ route('assessment.html-report-card-junior', ':id') }}";
        url = withAssessmentContext(url.replace(':id', studentId));
        window.location.href = url;
    }

    function sendEmail() {
        var sendBtn = $('#sendEmailBtn');
        sendBtn.addClass('loading').prop('disabled', true);
        $('.modal-header .btn-close').prop('disabled', true);

        var formData = $('#emailForm').serializeArray();
        formData.push({
            name: 'to',
            value: $('#to').val()
        });

        $.ajax({
            url: "{{ route('assessment.email-report-card') }}",
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                alert(response.message);
                $('#emailModal').modal('hide');
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' - ' + xhr.responseJSON.message;
                }
                alert('Error - ' + errorMessage);
                console.log(xhr.responseText);
            },
            complete: function() {
                sendBtn.removeClass('loading').prop('disabled', false);
                $('.modal-header .btn-close').prop('disabled', false);
            }
        });
    }

    function openBulkEmailModal() {
        $('#bulkEmailModal').modal('show');
    }

    function sendBulkEmail() {
        var sendBtn = $('#sendBulkEmailBtn');
        sendBtn.addClass('loading').prop('disabled', true);
        $('.modal-header .btn-close').prop('disabled', true);

        var formData = $('#bulkEmailForm').serialize();
        $.ajax({
            url: "{{ route('assessment.bulk-email-report-cards') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response.message);
                $('#bulkEmailModal').modal('hide');
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' - ' + xhr.responseJSON.message;
                }
                alert('Error - ' + errorMessage);
                console.log(xhr.responseText);
            },
            complete: function() {
                sendBtn.removeClass('loading').prop('disabled', false);
                $('.modal-header .btn-close').prop('disabled', false);
            }
        });
    }

    function archiveToEmailModal() {
        $('#archiveEmailModal').modal('show');
    }

    function sendArchiveEmail() {
        var sendBtn = $('#sendArchiveEmailBtn');
        sendBtn.addClass('loading').prop('disabled', true);
        $('.modal-header .btn-close').prop('disabled', true);

        var formData = $('#archiveEmailForm').serialize();
        $.ajax({
            url: "{{ route('assessment.archive-email-report-cards') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response.message);
                $('#archiveEmailModal').modal('hide');
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' - ' + xhr.responseJSON.message;
                }
                alert('Error - ' + errorMessage);
                console.log(xhr.responseText);
            },
            complete: function() {
                sendBtn.removeClass('loading').prop('disabled', false);
                $('.modal-header .btn-close').prop('disabled', false);
            }
        });
    }

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

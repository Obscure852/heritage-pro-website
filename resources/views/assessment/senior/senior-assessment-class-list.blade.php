<style>
    /* ---------------------------------------------------------
       1. DATA TABLES & GENERAL UTILITIES
       --------------------------------------------------------- */
    #studentTable_wrapper .dataTables_filter,
    #studentTable_wrapper .dataTables_length,
    #studentTable_wrapper .dataTables_info,
    #studentTable_wrapper .dataTables_paginate {
        display: none !important;
    }

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

    /* ---------------------------------------------------------
       2. CLASS CONTAINER & HEADER
       --------------------------------------------------------- */
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

    .assessment-class-header .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .assessment-class-header .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .assessment-class-header .teacher-info {
        font-size: 14px;
    }

    .assessment-class-header .teacher-info strong {
        font-weight: 600;
    }

    .assessment-class-header .header-stats {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .assessment-class-header .header-stats span {
        opacity: 0.9;
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
        background: rgba(255, 255, 255, 0.15);
        border: none;
        border-radius: 3px;
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

    /* ---------------------------------------------------------
       3. REPORTS DROPDOWN STYLING
       --------------------------------------------------------- */
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

    .reports-dropdown .dropdown-menu {
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 3px;
        padding: 0.5rem 0;
        min-width: 300px;
    }

    .reports-dropdown .dropdown-item {
        padding: 10px 18px;
        font-size: 14px;
        transition: all 0.15s ease;
        color: #374151;
    }

    .reports-dropdown .dropdown-item:hover {
        background: #f3f4f6;
        color: #111827;
    }

    /* --- NESTED SUBMENU LOGIC --- */
    .dropdown-menu li {
        position: relative;
    }

    /* Flexbox on the item to separate Text and Arrow */
    .dropdown-submenu>.dropdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding-right: 1rem;
    }

    /* The Submenu Container - Opens to the LEFT of menu items */
    .dropdown-submenu>.dropdown-menu {
        display: none;
        position: absolute;
        top: 0;
        left: auto;
        right: 100%;
        margin-top: -0.5rem;
        margin-right: 0.15rem;
        min-width: 280px;
    }

    /* Show on Hover */
    .dropdown-submenu:hover>.dropdown-menu {
        display: block;
    }

    /* Arrow Icon Styling - points left */
    .submenu-arrow {
        font-size: 11px;
        opacity: 0.6;
        margin-left: 12px;
    }

    /* ---------------------------------------------------------
       4. CONTROLS & TABLE
       --------------------------------------------------------- */
    .controls-row {
        padding: 16px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .controls-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .controls-left .input-group {
        width: 280px;
    }

    .controls-left .input-group-text {
        background: white;
        border: 1px solid #d1d5db;
        border-right: none;
        border-radius: 3px 0 0 3px;
        color: #6b7280;
    }

    .controls-left .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0 3px 3px 0;
        font-size: 14px;
    }

    .controls-left .form-select {
        width: 140px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
    }

    .controls-left .btn-reset {
        background: white;
        border: 1px solid #d1d5db;
        color: #6b7280;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 3px;
    }

    .controls-left .btn-reset:hover {
        background: #f3f4f6;
        color: #374151;
    }

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

    .table td {
        vertical-align: middle;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .student-avatar-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
        color: white;
    }

    .student-avatar-placeholder.male {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .student-avatar-placeholder.female {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .student-cell a {
        color: #1f2937;
        text-decoration: none;
        font-weight: 500;
    }

    .student-cell a:hover {
        color: #3b82f6;
    }

    .gender-male {
        color: #3b82f6;
        font-size: 13px;
    }

    .gender-female {
        color: #ec4899;
        font-size: 13px;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 4px;
        justify-content: flex-start;
    }

    .action-buttons .action-icon {
        width: 32px;
        height: 32px;
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

<div class="row">
    <div class="col-12 d-flex justify-content-end gap-2 mb-3">
        <div class="btn-group reports-dropdown">
            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-alt me-1"></i> Class Reports
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                <li>
                    <a class="dropdown-item" onclick="openSeniorClassReportCards($('#classId').val()); return false;"
                        href="#">
                        <span><i class="fas fa-file-pdf me-2 text-danger"></i> Class Report Cards</span>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-chart-line me-2 text-primary"></i> Class Performance</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openCAAnalysis({{ $test->sequence }}); return false;" href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openExamAnalysis(); return false;" href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-book me-2 text-success"></i> Subjects Performance</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openSubjectsCAAnalysis({{ $test->sequence }}); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openSubjectsExamAnalysis(); return false;" href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-layer-group me-2 text-warning"></i> Grade Subjects</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openGradeSubjectsCAAnalysis({{ $test->sequence }}); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openGradeSubjectsExamAnalysis(); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="btn-group reports-dropdown">
            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-chart-bar me-1"></i> Grade Reports
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-book-open me-2 text-success"></i> Grade Subjects Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openSeniorGradeAnalysis('CA',{{ $test->sequence }}); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openSeniorGradeAnalysis('Exam',1); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-chart-area me-2 text-success"></i> Grade All Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openSeniorGradeAllAnalysis({{ $test->sequence }},'CA'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openSeniorGradeAllAnalysis(1,'Exam'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-award me-2 text-info"></i> Triple Award Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openAwardTypeAnalysis({{ $test->sequence }},'CA','triple'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openAwardTypeAnalysis(1,'Exam','triple'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-award me-2 text-warning"></i> Double Award Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openAwardTypeAnalysis({{ $test->sequence }},'CA','double'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openAwardTypeAnalysis(1,'Exam','double'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-award me-2 text-success"></i> Single Award Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openAwardTypeAnalysis({{ $test->sequence }},'CA','single'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openAwardTypeAnalysis(1,'Exam','single'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-graduation-cap me-2 text-primary"></i> Grade Credits Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openSeniorGradeCreditsAnalysis('CA',{{ $test->sequence }}); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                        {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openSeniorGradeCreditsAnalysis('Exam',1); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-home me-2 text-warning"></i> House Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-layer-group me-2 text-warning"></i> Subjects By House</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openCAHouseAnalysis({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item" onclick="openCAHouseAnalysis(1,'Exam'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-certificate me-2 text-info"></i> Credits By Class</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openClassCreditsAnalysis({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openClassCreditsAnalysis(1,'Exam'); return false;" href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-university me-2 text-danger"></i> Credits By House</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openSeniorAllGradesHouseAnalysis({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openSeniorAllGradesHouseAnalysis(1,'Exam'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-star me-2 text-warning"></i> Best 5 Performance</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openCAClassHouseAnalysisSenior({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openCAClassHouseAnalysisSenior(1,'Exam'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-star me-2 text-success"></i> Best 6 Performance</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openCAGradeHouseAnalysisSenior({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openCAGradeHouseAnalysisSenior(1,'Exam'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-trophy me-2 text-info"></i> Award Analysis By House</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openHouseAwardAnalysis({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openHouseAwardAnalysis(1,'Exam'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a class="dropdown-item" onclick="openJCEHouseGradeDistribution(); return false;" href="#">
                        <i class="fas fa-table me-2 text-info"></i> JCE House Grade Distribution
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" onclick="openValueAdditionReport(); return false;" href="#">
                        <i class="fas fa-chart-line me-2 text-success"></i> Value Addition Report
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" onclick="openHouse6CTracking(); return false;" href="#">
                        <i class="fas fa-chart-bar me-2 text-primary"></i> House 6C's Tracking Report
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-chalkboard-teacher me-2 text-info"></i> Teachers & Classes
                            Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-user-friends me-2 text-primary"></i> Teachers & Classes 1</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openTeachersAnalysis('CA',{{ $test->sequence }}); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item" onclick="openTeachersAnalysis('Exam',1); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <span><i class="fas fa-users me-2 text-secondary"></i> Teachers & Classes 2</span>
                                <i class="fas fa-chevron-left submenu-arrow"></i>
                            </a>
                            <ul class="dropdown-menu shadow">
                                @if (!$tests->isEmpty())
                                    @foreach ($tests as $test)
                                        @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                onclick="openCATeacherAnalysisSenior({{ $test->sequence }},'CA'); return false;"
                                                href="#">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                                {{ $endMonth }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openCATeacherAnalysisSenior(1,'Exam'); return false;" href="#">
                                        <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-building me-2 text-secondary"></i> Department Analysis</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openGradeDepartmentAnalysis({{ $test->sequence }},'CA'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                        {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openGradeDepartmentAnalysis(1,'Exam'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown-submenu">
                    <a class="dropdown-item" href="#">
                        <span><i class="fas fa-chart-line me-2 text-success"></i> Teacher Value Addition</span>
                        <i class="fas fa-chevron-left submenu-arrow"></i>
                    </a>
                    <ul class="dropdown-menu shadow">
                        @if (!$tests->isEmpty())
                            @foreach ($tests as $test)
                                @php $endMonth = \Carbon\Carbon::parse($test->end_date)->format('M'); @endphp
                                <li>
                                    <a class="dropdown-item"
                                        onclick="openTeacherValueAddition({{ $test->sequence }},'CA'); return false;"
                                        href="#">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> End of
                                        {{ $endMonth }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li>
                            <a class="dropdown-item" onclick="openTeacherValueAddition(1,'Exam'); return false;"
                                href="#">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> End of Term (Exam)
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

@if (!empty($class))
    @php
        $currentTerm = \App\Helpers\TermHelper::getCurrentTerm();
    @endphp
    <div class="assessment-class-container">
        <div class="assessment-class-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="teacher-info">
                        <strong>{{ $class->teacher->lastname ?? '' }}</strong>
                        <span class="header-stats">
                            | {{ $class->name }} | {{ $class->students->count() }} students
                        </span>
                    </div>
                </div>
                <div class="header-right">
                    @if (auth()->user()->hasAllocatedClass())
                        <button type="button" class="header-icon-btn" onclick="archiveToEmailModal()"
                            data-bs-toggle="tooltip" title="Archive Report Cards">
                            <i class="bx bx-archive-in"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="controls-row">
            <div class="controls-left">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="studentSearchInput"
                        placeholder="Search students...">
                </div>
                <select class="form-select" id="genderFilter">
                    <option value="">All Genders</option>
                    <option value="m">Male</option>
                    <option value="f">Female</option>
                </select>
                <button type="button" class="btn btn-reset" id="resetFilters">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>

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
                            Gender
                            <span class="sort-icon">
                                <i class="fas fa-caret-up"></i>
                                <i class="fas fa-caret-down"></i>
                            </span>
                        </th>
                        <th class="sortable-header" data-sort-col="3">
                            Date of Birth
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
                                            @if($student->type)
                                                <span class="student-type-badge" style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                    {{ $student->type->type }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 12px;">
                                            @if ($remarks_present)
                                                <i class="bx bxs-check-circle text-success"
                                                    style="font-size: 11px;"></i> Remarks added
                                            @else
                                                <i class="bx bxs-x-circle text-muted" style="font-size: 11px;"></i> No
                                                remarks
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($student->gender == 'M')
                                    <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                                @else
                                    <span class="gender-female"><i class="bx bx-female-sign"></i> Female</span>
                                @endif
                            </td>
                            <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $class->name }}</td>
                            <td class="text-end">
                                <div class="action-buttons" style="justify-content: flex-end;">
                                    <span class="action-icon pdf-icon"
                                        onclick="pdfReportCardPopupSenior({{ $student->id }})"
                                        data-bs-toggle="tooltip" title="View PDF Report Card">
                                        <i class="bx bxs-file-pdf"></i>
                                    </span>
                                    <span class="action-icon html-icon"
                                        onclick="htmlReportCardPopup({{ $student->id }})" data-bs-toggle="tooltip"
                                        title="View HTML Report Card">
                                        <i class="bx bxs-file-html"></i>
                                    </span>
                                    @if (auth()->user()->hasAllocatedClass())
                                        <span class="action-icon email-icon"
                                            onclick="openEmailModal({{ $student->id }}, '{{ $fullname }}', '{{ $currentTerm->term }}', '{{ $teacher }}', '{{ $parentEmail }}')"
                                            data-bs-toggle="tooltip" title="Email Report Card">
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
    </div>

    <input type="hidden" id="classId" value="{{ $class->id }}">

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Report Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        @csrf
                        <input type="hidden" id="studentId" name="studentId">
                        <div class="mb-3">
                            <label for="to" class="form-label">To:</label>
                            <input readonly type="email" class="form-control" id="to" name="to"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message:</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachment:</label>
                            <p class="form-control-static" id="attachmentInfo">
                                <i style="color:red;font-size:14px;" class="bx bxs-file-pdf"></i>
                                <span id="pdfFileName"></span>
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

    <!-- Archive Email Modal -->
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
                            <input type="text" class="form-control" id="archiveSubject" name="archiveSubject"
                                required
                                value="Archived Report Cards for {{ $class->name }} - Term {{ $currentTerm->term }}">
                        </div>
                        <div class="mb-3">
                            <label for="archiveMessage" class="form-label">Email Message:</label>
                            <textarea class="form-control" id="archiveMessage" name="archiveMessage" rows="4" required>Archive of report cards for {{ $class->name }} - Term {{ $currentTerm->term }}
Created on: {{ now()->format('Y-m-d H:i:s') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachment:</label>
                            <p class="form-control-static">
                                <i style="color:red;font-size:14px;" class="bx bxs-file-pdf"></i>
                                <span>{{ strtolower($class->name) }}_term_{{ $currentTerm->term }}_report_cards_list.pdf</span>
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

        const rows = document.querySelectorAll('#studentTable tbody tr.student-row');

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const gender = row.dataset.gender || '';

            const matchesSearch = !searchTerm || name.includes(searchTerm);
            const matchesGender = !genderFilter || gender === genderFilter;

            row.style.display = matchesSearch && matchesGender ? '' : 'none';
        });
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

    // Sortable headers
    $(document).ready(function() {
        $(document).on('click', '.sortable-header', function() {
            const columnIndex = parseInt($(this).attr('data-sort-col'));
            const $sortIcon = $(this).find('.sort-icon');

            $('.sortable-header .sort-icon').removeClass('asc desc');

            if (sortField === columnIndex) {
                sortDirection *= -1;
            } else {
                sortField = columnIndex;
                sortDirection = 1;
            }

            if (sortDirection === 1) {
                $sortIcon.addClass('asc');
            } else {
                $sortIcon.addClass('desc');
            }

            const rows = $('#studentTable tbody tr').get();
            rows.sort(function(a, b) {
                const aValue = $(a).children('td').eq(columnIndex).text().trim();
                const bValue = $(b).children('td').eq(columnIndex).text().trim();

                // Check if sorting Date of Birth column (index 3)
                if (columnIndex === 3) {
                    const aDate = aValue === 'N/A' ? new Date(0) : new Date(aValue);
                    const bDate = bValue === 'N/A' ? new Date(0) : new Date(bValue);
                    return (aDate - bDate) * sortDirection;
                }

                return aValue.localeCompare(bValue) * sortDirection;
            });
            $('#studentTable tbody').empty().append(rows);
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

    function pdfReportCardPopupSenior(studentId) {
        var url = "{{ route('assessment.pdf-report-card-senior', ':id') }}";
        url = url.replace(':id', studentId);
        window.open(url, 'PDFWindow', 'width=800,height=1000');
    }

    function htmlReportCardPopup(studentId) {
        var url = "{{ route('assessment.html-report-card-senior', ':id') }}";
        url = url.replace(':id', studentId);
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
            },
            complete: function() {
                sendBtn.removeClass('loading').prop('disabled', false);
                $('.modal-header .btn-close').prop('disabled', false);
            }
        });
    }
</script>

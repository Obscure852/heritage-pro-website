@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Grade Subjects Module')
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

        .controls .form-control,
        .controls .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
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

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
        }

        .table tbody td {
            font-size: 12px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .subject-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .subject-avatar-placeholder {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #dbeafe;
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 10px;
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

        .subject-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 3px;
        }

        .subject-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .grade-group-card {
            border-left: 4px solid #3b82f6;
            margin-bottom: 1.5rem;
            border-radius: 3px;
        }

        .mandatory-border {
            border-left-color: #10b981;
        }

        .optional-border {
            border-left-color: #f59e0b;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 3px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-row {
            height: 20px;
            margin: 8px 0;
        }

        .year-filter-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .year-filter-wrapper .form-select {
            width: auto;
            min-width: 150px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            padding: 8px 12px;
            transition: all 0.2s ease;
        }

        .year-filter-wrapper .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 16px;
            }

            .admissions-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Finals
        @endslot
        @slot('title')
            {{ $finalsDefinition->examLabel ?? 'Finals' }} Grade Subjects
        @endslot
    @endcomponent

    <div class="year-filter-wrapper">
        <select name="graduation_year" id="graduationYear" class="form-select">
            <option value="">Select Year...</option>
            @foreach ($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Grade Subjects</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} grade subjects</p>
                    @include('finals.partials.context-toggle')
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotal">{{ $badgeData['total'] }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statMandatory">{{ $badgeData['mandatory'] }}</h4>
                                <small class="opacity-75">Mandatory</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statOptional">{{ $badgeData['optional'] }}</h4>
                                <small class="opacity-75">Optional</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">{{ $finalsDefinition->examLabel ?? 'Finals' }} Grade Subjects Management</div>
                <div class="help-content">
                    Browse and manage grade subjects for {{ $finalsDefinition->examLabel ?? 'finals' }} students. Use the year filter to view subjects by
                    graduation year.
                    Access reports for detailed analysis of subject performance.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by subject..."
                                        id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="mandatory">Mandatory</option>
                                    <option value="optional">Optional</option>
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
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2"
                                    style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if ($finalsContext === \App\Services\SchoolModeResolver::FINALS_CONTEXT_SENIOR)
                                    @include('finals.partials.report-menu', ['items' => $reportMenu])
                                @else
                                    @can('manage-assessment')
                                        <li>
                                            <a class="dropdown-item {{ $activeReportClassId ? '' : 'disabled' }}"
                                                id="classSubjectsSummaryReportLink"
                                                href="{{ $activeReportClassId ? route('finals.class.subjects-summary-analyis', ['classId' => $activeReportClassId, 'finals_context' => $finalsDefinition->context]) : '#' }}"
                                                aria-disabled="{{ $activeReportClassId ? 'false' : 'true' }}">
                                                <i class="fas fa-chart-pie text-warning"></i> Class Subjects Summary Report
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ $activeReportClassId ? '' : 'disabled' }}"
                                                id="classOverallTeachersAnalysisReportLink"
                                                href="{{ $activeReportClassId ? route('finals.subjects.overall-teachers-analysis', ['classId' => $activeReportClassId, 'type' => 'Exam', 'sequence' => 1, 'finals_context' => $finalsDefinition->context]) : '#' }}"
                                                aria-disabled="{{ $activeReportClassId ? 'false' : 'true' }}">
                                                <i class="fas fa-users text-info"></i> Overall Teachers Analysis (Class)
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                    @endcan
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('finals.subjects.subject-gender-grades-report', ['finals_context' => $finalsDefinition->context]) }}">
                                            <i class="fas fa-chart-line text-primary"></i> Subject Performance Summary
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('finals.subjects.subject-psle-jce-comparison', ['finals_context' => $finalsDefinition->context]) }}">
                                            <i class="fas fa-balance-scale text-success"></i> Subjects Comparison Analysis
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('finals.subjects.overall-teacher-performance', ['finals_context' => $finalsDefinition->context]) }}">
                                            <i class="fas fa-users text-info"></i> Overall Teacher Performance
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="gradeSubjectsTableContainer">
            </div>

            <div id="loadingState" style="display: none;">
                <div class="row">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="loading-skeleton skeleton-row" style="width: 120px;"></div>
                                        <div class="loading-skeleton skeleton-row" style="width: 80px;"></div>
                                    </div>
                                    <div class="loading-skeleton skeleton-row" style="width: 150px;"></div>
                                    <div class="loading-skeleton skeleton-row" style="width: 100px;"></div>
                                    <div class="loading-skeleton skeleton-row" style="width: 80px;"></div>
                                    <div class="d-flex gap-2 mt-3">
                                        <div class="loading-skeleton skeleton-row" style="width: 60px;"></div>
                                        <div class="loading-skeleton skeleton-row" style="width: 60px;"></div>
                                        <div class="loading-skeleton skeleton-row" style="width: 60px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            const finalsContext = @json($finalsDefinition->context);

            const $yearSelect = $('#graduationYear');
            const $tableHolder = $('#gradeSubjectsTableContainer');
            const $loading = $('#loadingState');
            const reportClassesRoute = "{{ route('finals.subjects.report-classes') }}";
            const classSummaryBaseRoute =
                "{{ route('finals.class.subjects-summary-analyis', ['classId' => '__CLASS_ID__', 'finals_context' => $finalsDefinition->context]) }}";
            const classTeacherAnalysisBaseRoute =
                "{{ route('finals.subjects.overall-teachers-analysis', ['classId' => '__CLASS_ID__', 'type' => '__TYPE__', 'sequence' => '__SEQUENCE__', 'finals_context' => $finalsDefinition->context]) }}";
            const classTeacherAnalysisType = 'Exam';
            const classTeacherAnalysisSequence = '1';
            let activeReportClassId = @json($activeReportClassId);
            const $classSummaryLink = $('#classSubjectsSummaryReportLink');
            const $classTeacherAnalysisLink = $('#classOverallTeachersAnalysisReportLink');

            function buildClassSummaryUrl(classId) {
                return classSummaryBaseRoute.replace('__CLASS_ID__', encodeURIComponent(classId));
            }

            function buildClassTeacherAnalysisUrl(classId) {
                return classTeacherAnalysisBaseRoute
                    .replace('__CLASS_ID__', encodeURIComponent(classId))
                    .replace('__TYPE__', encodeURIComponent(classTeacherAnalysisType))
                    .replace('__SEQUENCE__', encodeURIComponent(classTeacherAnalysisSequence));
            }

            function setClassSummaryLinkState() {
                if (!$classSummaryLink.length) return;

                if (activeReportClassId) {
                    $classSummaryLink.removeClass('disabled');
                    $classSummaryLink.attr('href', buildClassSummaryUrl(activeReportClassId));
                    $classSummaryLink.attr('aria-disabled', 'false');
                    return;
                }

                $classSummaryLink.addClass('disabled');
                $classSummaryLink.attr('href', '#');
                $classSummaryLink.attr('aria-disabled', 'true');
            }

            function setClassTeacherAnalysisLinkState() {
                if (!$classTeacherAnalysisLink.length) return;

                if (activeReportClassId) {
                    $classTeacherAnalysisLink.removeClass('disabled');
                    $classTeacherAnalysisLink.attr('href', buildClassTeacherAnalysisUrl(activeReportClassId));
                    $classTeacherAnalysisLink.attr('aria-disabled', 'false');
                    return;
                }

                $classTeacherAnalysisLink.addClass('disabled');
                $classTeacherAnalysisLink.attr('href', '#');
                $classTeacherAnalysisLink.attr('aria-disabled', 'true');
            }

            function loadReportClasses(year) {
                if (!year) {
                    activeReportClassId = null;
                    setClassSummaryLinkState();
                    setClassTeacherAnalysisLinkState();
                    return;
                }

                $.ajax({
                    url: reportClassesRoute,
                    method: 'GET',
                    data: { year, finals_context: finalsContext },
                    success: function(response) {
                        const classes = (response && Array.isArray(response.classes)) ? response.classes : [];
                        activeReportClassId = classes.length > 0 ? Number(classes[0].id) : null;
                        setClassSummaryLinkState();
                        setClassTeacherAnalysisLinkState();
                    },
                    error: function() {
                        activeReportClassId = null;
                        setClassSummaryLinkState();
                        setClassTeacherAnalysisLinkState();
                    }
                });
            }

            function updateBadges(b) {
                document.getElementById('statTotal').textContent = b.total;
                document.getElementById('statMandatory').textContent = b.mandatory;
                document.getElementById('statOptional').textContent = b.optional;
            }

            function fetchBadgeData(year = null) {
                $.ajax({
                    url: "{{ route('finals.subjects.badge-data') }}",
                    method: 'GET',
                    data: year ? {
                        year,
                        finals_context: finalsContext,
                    } : {
                        finals_context: finalsContext,
                    },
                    success: updateBadges,
                    error: (xhr, status, err) => console.error('Badge fetch error:', err)
                });
            }

            function initDataTable() {
                if (!$('#grade-subjects-table').length) return;

                // Destroy existing DataTable instance if it exists
                if ($.fn.DataTable.isDataTable('#grade-subjects-table')) {
                    $('#grade-subjects-table').DataTable().destroy();
                }

                $('#grade-subjects-table').DataTable({
                    pageLength: 25,
                    dom: 'rtip', // Remove default search box and show entries dropdown
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'></i>",
                            next: "<i class='mdi mdi-chevron-right'></i>"
                        }
                    },
                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                    }
                });

                // Apply custom filters after initialization
                applyFilters();
            }

            function applyFilters() {
                if (!$.fn.DataTable.isDataTable('#grade-subjects-table')) return;

                const table = $('#grade-subjects-table').DataTable();
                const searchTerm = $('#searchInput').val();
                const typeFilter = $('#typeFilter').val();

                // Apply search
                table.search(searchTerm);

                // Custom filtering
                $.fn.dataTable.ext.search.pop(); // Remove any previous custom filter
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const $row = $(row);

                    // Type filter (mandatory/optional)
                    if (typeFilter) {
                        const typeCell = $row.find('td:eq(3)').text().toLowerCase();
                        if (typeFilter === 'mandatory' && !typeCell.includes('mandatory')) return false;
                        if (typeFilter === 'optional' && !typeCell.includes('optional')) return false;
                    }

                    return true;
                });

                table.draw();
            }

            // Event listeners for filters
            $('#searchInput').on('keyup', function() {
                applyFilters();
            });

            $('#typeFilter').on('change', function() {
                applyFilters();
            });

            $('#resetFilters').click(function() {
                $('#searchInput').val('');
                $('#typeFilter').val('');
                applyFilters();
            });

            $classSummaryLink.on('click', function(e) {
                if (!activeReportClassId) {
                    e.preventDefault();
                    return;
                }

                $(this).attr('href', buildClassSummaryUrl(activeReportClassId));
            });

            $classTeacherAnalysisLink.on('click', function(e) {
                if (!activeReportClassId) {
                    e.preventDefault();
                    return;
                }

                $(this).attr('href', buildClassTeacherAnalysisUrl(activeReportClassId));
            });

            function fetchGradeSubjectsData() {
                $loading.show();
                $tableHolder.hide().empty();

                $.ajax({
                    url: "{{ route('finals.subjects.data') }}",
                    method: 'GET',
                    data: {
                        year: $yearSelect.val(),
                        finals_context: finalsContext,
                    },
                    success: function(html) {
                        $tableHolder.html(html).fadeIn(200, initDataTable);
                    },
                    error: function(xhr, status, err) {
                        $tableHolder.html(`
                        <div class="alert alert-warning m-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-alt me-2 fs-4"></i>
                                <div>
                                    Unable to load grade-subjects data.<br>
                                    <small>Please check your connection and try again.</small>
                                </div>
                            </div>
                        </div>`).show();
                    },
                    complete: () => $loading.hide()
                });
            }

            $yearSelect.on('change', function() {
                fetchGradeSubjectsData();
                loadReportClasses(this.value);
                setTimeout(() => fetchBadgeData(this.value), 150);
            });

            fetchGradeSubjectsData();
            fetchBadgeData($yearSelect.val());
            setClassSummaryLinkState();
            setClassTeacherAnalysisLinkState();
            loadReportClasses($yearSelect.val());
        });
    </script>
@endsection

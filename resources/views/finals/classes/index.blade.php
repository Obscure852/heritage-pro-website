@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Classes Module')
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
        font-size: 13px;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .class-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .class-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
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

    .placeholder-button {
        width: 100px;
        height: 30px;
        background-color: #e9ecef;
        border: none;
        border-radius: 3px;
    }

    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .pass-rate-excellent { background: #10b981; }
    .pass-rate-good { background: #f59e0b; }
    .pass-rate-poor { background: #ef4444; }

    .class-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .teacher-name {
        color: #6c757d;
        font-size: 0.875rem;
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
        @slot('li_1') Finals @endslot
        @slot('title') {{ $finalsDefinition->examLabel ?? 'Finals' }} Classes @endslot
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
                    <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Finals Classes</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} class performance</p>
                    @include('finals.partials.context-toggle')
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statWithResults">{{ $badgeData['totalResults'] }}</h4>
                                <small class="opacity-75">With Results</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statPending">{{ $badgeData['pendingResults'] }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">{{ $finalsDefinition->examLabel ?? 'Finals' }} Class Performance</div>
                <div class="help-content">
                    Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} class performance data. Use the year filter to view classes by graduation year.
                    Click on a class to view detailed student results and performance metrics.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by class name..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="resultFilter">
                                    <option value="">All Status</option>
                                    <option value="complete">Complete</option>
                                    <option value="partial">Partial</option>
                                    <option value="pending">Pending</option>
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
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @include('finals.partials.report-menu', ['items' => $reportMenu])
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="finalsClassesList">
            </div>

            <div id="loadingPlaceholder">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Students</th>
                                <th scope="col">Results Status</th>
                                <th scope="col">Pass Rate</th>
                                <th scope="col">Avg Points</th>
                                <th style="width: 80px; min-width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 10; $i++)
                                <tr class="placeholder-glow">
                                    <td><span class="placeholder-item" style="width: 120px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 100px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
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
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            const finalsContext = @json($finalsDefinition->context);

            function updateBadges(d) {
                document.getElementById('statWithResults').textContent = d.totalResults;
                document.getElementById('statPending').textContent = d.pendingResults;
            }

            function fetchBadgeData(year = null) {
                let requestData = {};
                
                if (year) {
                    requestData.year = year;
                }
                requestData.finals_context = finalsContext;

                $.ajax({
                    url: "{{ route('finals.classes.badge-data') }}",
                    method: 'GET',
                    data: requestData,
                    success: updateBadges,
                    error: function(xhr, status, error) {
                        console.error("Badge data fetch error:", error);
                    }
                });
            }

            function fetchClassesData() {
                $('#loadingPlaceholder').show();
                $('#finalsClassesList').empty();

                let yearVal = $('#graduationYear').val();

                $.ajax({
                    url: "{{ route('finals.classes.data') }}",
                    method: 'GET',
                    data: {
                        year: yearVal,
                        finals_context: finalsContext,
                    },
                    success: function(response) {
                        $('#loadingPlaceholder').hide();
                        $('#finalsClassesList').html(response).fadeIn(200, function() {
                            if ($('#classesTable').length) {
                                // Destroy existing DataTable instance if it exists
                                if ($.fn.DataTable.isDataTable('#classesTable')) {
                                    $('#classesTable').DataTable().destroy();
                                }

                                $('#classesTable').DataTable({
                                    pageLength: 25,
                                    dom: 'rtip', // Remove default search box and show entries dropdown
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
                        $('#finalsClassesList').html(`
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bx bxs-error-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Oops! Something went wrong.</strong><br>
                                    We couldn't load the final year classes list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                                </div>
                            </div>
                        `);
                    }
                });
            }

            function applyFilters() {
                const table = $('#classesTable').DataTable();
                const searchTerm = $('#searchInput').val();
                const resultFilter = $('#resultFilter').val();

                // Apply search
                table.search(searchTerm);

                // Custom filtering
                $.fn.dataTable.ext.search.pop(); // Remove any previous custom filter
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const $row = $(row);

                    // Result status filter
                    if (resultFilter) {
                        const statusCell = $row.find('td:eq(4)').text().toLowerCase();
                        if (resultFilter === 'complete' && !statusCell.includes('complete')) return false;
                        if (resultFilter === 'partial' && !statusCell.includes('partial')) return false;
                        if (resultFilter === 'pending' && !statusCell.includes('pending')) return false;
                    }

                    return true;
                });

                table.draw();
            }

            // Event listeners for filters
            $('#searchInput').on('keyup', function() {
                if ($.fn.DataTable.isDataTable('#classesTable')) {
                    applyFilters();
                }
            });

            $('#resultFilter').on('change', function() {
                if ($.fn.DataTable.isDataTable('#classesTable')) {
                    applyFilters();
                }
            });

            $('#resetFilters').click(function() {
                $('#searchInput').val('');
                $('#resultFilter').val('');
                if ($.fn.DataTable.isDataTable('#classesTable')) {
                    applyFilters();
                }
            });

            $('#graduationYear').change(function() {
                let year = $(this).val();
                fetchClassesData();
                setTimeout(function() {
                    fetchBadgeData(year);
                }, 200);
            });

            function showAlert(type, message) {
                let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                let iconClass = type === 'success' ? 'mdi-check-all' : 'mdi-block-helper';

                let alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi ${iconClass} label-icon"></i><strong>${message}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                $('.row:first .col-12').prepend(alertHtml);

                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }

            // Initial load
            $('#graduationYear').trigger('change');
        });
    </script>
@endsection

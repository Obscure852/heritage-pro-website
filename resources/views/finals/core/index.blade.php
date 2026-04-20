@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Class Subjects Module')
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
            {{ $finalsDefinition->examLabel ?? 'Finals' }} Class Subjects
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
                    <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Class Subjects</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} class subject assignments</p>
                    @include('finals.partials.context-toggle')
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotal">{{ $badgeData['total'] ?? 0 }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statActive">{{ $badgeData['active'] ?? 0 }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statMandatory">{{ $badgeData['mandatory'] ?? 0 }}
                                </h4>
                                <small class="opacity-75">Mandatory</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">{{ $finalsDefinition->examLabel ?? 'Finals' }} Class Subject Management</div>
                <div class="help-content">
                    Browse and manage class subject assignments for {{ $finalsDefinition->examLabel ?? 'finals' }} students. Use the year filter to view
                    subjects by graduation year.
                    Access reports for detailed analysis of subject performance across classes.
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
                                @include('finals.partials.report-menu', ['items' => $reportMenu])
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="klassSubjectsList">
            </div>

            <div id="loadingPlaceholder">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Venue</th>
                                <th scope="col">Status</th>
                                <th scope="col">Students</th>
                                <th scope="col">Results</th>
                                <th scope="col">Pass Rate</th>
                                <th scope="col">Graduation</th>
                                <th style="width: 120px; min-width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 10; $i++)
                                <tr class="placeholder-glow">
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 120px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 100px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 80px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 50px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 50px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 60px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item" style="width: 70px; height: 16px;"></span></td>
                                    <td><span class="placeholder-item"
                                            style="width: 80px; height: 24px; border-radius: 3px;"></span></td>
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

            function updateBadges(badgeData) {
                document.getElementById('statTotal').textContent = badgeData.total;
                document.getElementById('statActive').textContent = badgeData.active;
                document.getElementById('statMandatory').textContent = badgeData.mandatory;
            }

            function fetchBadgeData(year = null) {
                let requestData = {};

                if (year) {
                    requestData.year = year;
                }
                requestData.finals_context = finalsContext;

                $.ajax({
                    url: "{{ route('finals.core.badge-data') }}",
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

            function fetchKlassSubjectsData() {
                $('#loadingPlaceholder').show();
                $('#klassSubjectsList').empty();

                let yearVal = $('#graduationYear').val();

                $.ajax({
                    url: "{{ route('finals.core.data') }}",
                    method: 'GET',
                    data: {
                        year: yearVal,
                        finals_context: finalsContext,
                    },
                    success: function(response) {
                        $('#loadingPlaceholder').hide();
                        $('#klassSubjectsList').html(response).fadeIn(200, function() {
                            if ($('#klass-subjects-table').length) {
                                $('#klass-subjects-table').DataTable({
                                    pageLength: 25,
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
                        $('#klassSubjectsList').html(`
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bx bx-error-alt me-2 fs-4"></i>
                            <div>
                                <strong>Oops! Something went wrong.</strong><br>
                                We couldn't load the class subjects list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                            </div>
                        </div>
                    `);
                    }
                });
            }

            $('#graduationYear').change(function() {
                let year = $(this).val();
                fetchKlassSubjectsData();
                setTimeout(function() {
                    fetchBadgeData(year);
                }, 200);
            });

            // Search functionality for accordion items
            $('#searchInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.accordion-item').each(function() {
                    const subjectName = $(this).find('.accordion-button .fw-bold').text()
                        .toLowerCase();
                    if (subjectName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#searchInput').val('');
                $('.accordion-item').show();
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

            $('#graduationYear').trigger('change');
        });
    </script>
@endsection

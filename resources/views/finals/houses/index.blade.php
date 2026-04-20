@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Houses Module')
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

        .house-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 3px;
        }

        .house-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
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
            {{ $finalsDefinition->examLabel ?? 'Finals' }} Houses
        @endslot
    @endcomponent

    @if (!empty($availableYears))
        <div class="year-filter-wrapper">
            <select name="year" id="graduationYear" class="form-select">
                <option value="">Select Year ...</option>
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
                        <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Houses</h3>
                        <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} house assignments</p>
                        @include('finals.partials.context-toggle')
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="statTotalHouses">{{ $totalHouses ?? 0 }}</h4>
                                    <small class="opacity-75">Houses</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="statTotalStudents">{{ $totalStudents ?? 0 }}
                                    </h4>
                                    <small class="opacity-75">Students</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white" id="statAvgPerHouse">{{ $avgStudentsPerHouse ?? 0 }}
                                    </h4>
                                    <small class="opacity-75">Avg/House</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admissions-body">
                <div class="help-text">
                    <div class="help-title">{{ $finalsDefinition->examLabel ?? 'Finals' }} House Management</div>
                    <div class="help-content">
                        Browse and manage house assignments for {{ $finalsDefinition->examLabel ?? 'finals' }} students. Use the year filter to view houses by
                        graduation year.
                        Access reports for house distribution and population analysis.
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search by house name..."
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
                                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false">
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

                <div id="finalsHousesList">
                </div>

                <div id="loadingPlaceholder">
                    <div class="row">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card placeholder-glow">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="placeholder-item" style="width: 120px; height: 18px;"></span>
                                                <br><span class="placeholder-item mt-1"
                                                    style="width: 80px; height: 14px;"></span>
                                            </div>
                                            <span class="placeholder-item"
                                                style="width: 40px; height: 24px; border-radius: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <span class="placeholder-item" style="width: 60px; height: 16px;"></span>
                                            <br><span class="placeholder-item mt-1"
                                                style="width: 100px; height: 14px;"></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="placeholder-item" style="width: 80px; height: 16px;"></span>
                                            <div class="row mt-2">
                                                <div class="col-4 text-center">
                                                    <span class="placeholder-item"
                                                        style="width: 20px; height: 16px;"></span>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <span class="placeholder-item"
                                                        style="width: 20px; height: 16px;"></span>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <span class="placeholder-item"
                                                        style="width: 20px; height: 16px;"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="admissions-container">
            <div class="admissions-header">
                <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Houses</h3>
                <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} house assignments</p>
            </div>
            <div class="admissions-body">
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-home display-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted">No House Data Available</h5>
                    <p class="text-muted mb-4">
                        No house information found in the Finals module. Houses will appear here after the year rollover
                        process for graduating students with house assignments.
                    </p>
                </div>
            </div>
        </div>
    @endif

@endsection
@section('script')
    <script>
        $(document).ready(function() {
            const finalsContext = @json($finalsDefinition->context);

            function updateBadges(badgeData) {
                const data = {
                    totalHouses: badgeData.totalHouses || 0,
                    totalStudents: badgeData.totalStudents || 0,
                    avgStudentsPerHouse: badgeData.avgStudentsPerHouse || 0
                };

                document.getElementById('statTotalHouses').textContent = data.totalHouses;
                document.getElementById('statTotalStudents').textContent = data.totalStudents;
                document.getElementById('statAvgPerHouse').textContent = data.avgStudentsPerHouse;
            }

            function fetchBadgeData(year = null) {
                let requestData = {};

                if (year) {
                    requestData.year = year;
                }
                requestData.finals_context = finalsContext;

                $.ajax({
                    url: "{{ route('finals.houses.badge-data') }}",
                    method: 'GET',
                    data: requestData,
                    success: function(badgeData) {
                        updateBadges(badgeData);
                    },
                    error: function(xhr, status, error) {
                        console.error("Badge data fetch error:", error);
                        // Set default values on error
                        updateBadges({
                            totalHouses: 0,
                            totalStudents: 0,
                            avgStudentsPerHouse: 0,
                            graduationTermsCount: 0
                        });
                    }
                });
            }

            function fetchFinalsHousesData() {
                $('#loadingPlaceholder').show();
                $('#finalsHousesList').empty();

                let yearVal = $('#graduationYear').val();

                $.ajax({
                    url: "{{ route('finals.houses.partial') }}",
                    method: 'GET',
                    data: {
                        year: yearVal,
                        finals_context: finalsContext,
                    },
                    success: function(response) {
                        $('#loadingPlaceholder').hide();
                        $('#finalsHousesList').html(response).fadeIn(200, function() {
                            // Initialize tooltips
                            $('[data-bs-toggle="tooltip"]').tooltip();
                        });
                    },
                    error: function(xhr, status, error) {
                        $('#loadingPlaceholder').hide();
                        $('#finalsHousesList').html(`
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bx bxs-error-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Oops! Something went wrong.</strong><br>
                                    We couldn't load the final year houses list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                                </div>
                            </div>
                        `);
                    }
                });
            }

            $('#graduationYear').change(function() {
                let year = $(this).val();
                fetchFinalsHousesData();
                setTimeout(function() {
                    fetchBadgeData(year);
                }, 200);
            });

            // Search functionality for house cards
            $('#searchInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.house-item').each(function() {
                    const houseName = $(this).find('.card-title').text().toLowerCase();
                    if (houseName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#searchInput').val('');
                $('.house-item').show();
                $('input[name="termFilter"][value="all"]').prop('checked', true).trigger('change');
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

            // Only trigger change if there are available years
            @if (!empty($availableYears))
                $('#graduationYear').trigger('change');
            @endif
        });
    </script>
@endsection

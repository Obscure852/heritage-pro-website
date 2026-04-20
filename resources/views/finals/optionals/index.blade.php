@extends('layouts.master')
@section('title', ($finalsDefinition->examLabel ?? 'Finals') . ' Finals Optional Subjects Module')
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

        .accordion-header {
            background-color: #f8f9fa;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
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
            {{ $finalsDefinition->examLabel ?? 'Finals' }} Optional Subjects
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
                    <h3 style="margin:0;">{{ $finalsDefinition->examLabel ?? 'Finals' }} Optional Subjects</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage {{ $finalsDefinition->examLabel ?? 'finals' }} optional subjects</p>
                    @include('finals.partials.context-toggle')
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statTotalOptionals">
                                    {{ $badgeData['totalOptionalSubjects'] }}</h4>
                                <small class="opacity-75">Total Optionals</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" id="statPending">{{ $badgeData['subjectsPending'] }}
                                </h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">{{ $finalsDefinition->examLabel ?? 'Finals' }} Optional Subjects Management</div>
                <div class="help-content">
                    Browse and manage optional subjects for {{ $finalsDefinition->examLabel ?? 'finals' }} students. Use the year filter to view subjects by
                    graduation year.
                    Access reports for detailed analysis of optional subject performance.
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

            <div id="subjectsTableContainer">
            </div>

            <div id="loadingState" style="display: none;">
                <div class="row">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <div class="loading-skeleton skeleton-row" style="width: 150px;"></div>
                                    <div class="loading-skeleton skeleton-row" style="width: 120px; height: 14px;"></div>
                                </div>
                                <div class="card-body">
                                    <div class="loading-skeleton skeleton-row" style="width: 100%; height: 6px;"></div>
                                    <div class="d-flex gap-2 mt-2">
                                        <div class="loading-skeleton" style="width: 50px; height: 20px;"></div>
                                        <div class="loading-skeleton" style="width: 60px; height: 20px;"></div>
                                        <div class="loading-skeleton" style="width: 40px; height: 20px;"></div>
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
        $(function() {
            const finalsContext = @json($finalsDefinition->context);

            function updateBadges(d) {
                document.getElementById('statTotalOptionals').textContent = d.totalOptionalSubjects;
                document.getElementById('statPending').textContent = d.subjectsPending;
            }

            function fetchBadgeData(year = null) {
                $.ajax({
                    url: "{{ route('finals.optionals.badge-data') }}",
                    data: year ? {
                        year,
                        finals_context: finalsContext,
                    } : {
                        finals_context: finalsContext,
                    },
                    method: 'GET',
                    success: updateBadges,
                    error: (xhr, status, err) => console.error('Badge fetch error:', err)
                });
            }

            const $loading = $('#loadingState');
            const $tableBox = $('#subjectsTableContainer');
            const $yearSel = $('#graduationYear');

            function fetchSubjectsData() {

                $loading.show();
                $tableBox.hide().empty();

                $.ajax({
                    url: "{{ route('finals.optionals.data') }}",
                    method: 'GET',
                    data: {
                        year: $yearSel.val(),
                        finals_context: finalsContext,
                    },
                    success: resp => $tableBox.html(resp).fadeIn(300),
                    error: () => $tableBox.html(`
                        <div class="alert alert-warning m-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Unable to load optional subjects data.</strong><br>
                                    Please check your connection and try again.
                                </div>
                            </div>
                        </div>`).show(),
                    complete: () => $loading.hide()
                });
            }

            $yearSel.on('change', function() {
                fetchSubjectsData();
                setTimeout(() => fetchBadgeData(this.value), 150);
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

            fetchSubjectsData();
            fetchBadgeData($yearSel.val());
        });
    </script>
@endsection

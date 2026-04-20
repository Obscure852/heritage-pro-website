@extends('layouts.master-student-portal')
@section('title')
    Student Portal - Dashboard
@endsection

@section('css')
    <style>
        .portal-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .portal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 32px;
            border-radius: 3px 3px 0 0;
        }

        .portal-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .portal-header .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .header-stats {
            display: flex;
            gap: 32px;
            margin-top: 20px;
        }

        .header-stat {
            text-align: center;
        }

        .header-stat .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .header-stat .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.85;
            margin-top: 4px;
        }

        .portal-body {
            padding: 28px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
        }

        .help-text .help-title {
            font-weight: 600;
            font-size: 1rem;
            color: #374151;
            margin-bottom: 6px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .btn-academic {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 3px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-academic:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        #gradeSelect {
            font-size: 0.9rem;
            border-radius: 3px;
            min-width: 140px;
        }

        .loading-spinner {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            gap: 16px;
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .loading-spinner p {
            color: #6b7280;
            font-size: 1rem;
        }

        @media (max-width: 991px) {
            .header-stats {
                justify-content: flex-start !important;
                margin-top: 16px;
            }
        }

        @media (max-width: 768px) {
            .portal-header {
                padding: 24px;
            }

            .portal-header h2 {
                font-size: 1.5rem;
            }

            .header-stats {
                gap: 20px;
                flex-wrap: wrap;
            }

            .header-stat .stat-value {
                font-size: 1.5rem;
            }

            .portal-body {
                padding: 20px;
            }

            .d-flex.justify-content-between.align-items-start {
                flex-direction: column;
            }

            .btn-academic {
                width: 100%;
                justify-content: center;
                margin-top: 12px;
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

    <div class="row mb-3 align-items-center">
        <div class="col">
            <span class="text-muted" style="font-size: 0.9rem;">Filter courses by grade</span>
        </div>
        <div class="col-auto">
            @if ($activeGrades->count() > 0)
                <select id="gradeSelect" class="form-select">
                    <option value="all" {{ !$currentGradeId ? 'selected' : '' }}>All Grades</option>
                    @foreach ($activeGrades as $grade)
                        <option value="{{ $grade->id }}" {{ $currentGradeId == $grade->id ? 'selected' : '' }}>
                            {{ $grade->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2>Welcome back, {{ $student->first_name ?? 'Student' }}!</h2>
                    <p class="subtitle">
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }}
                    </p>
                </div>
                <div class="col-lg-6 mt-lg-0 mt-4">
                    <div class="header-stats justify-content-lg-end" id="header-stats">
                        <div class="header-stat">
                            <div class="stat-value" id="stat-courses">-</div>
                            <div class="stat-label">Courses</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value" id="stat-progress">-</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="header-stat">
                            <div class="stat-value" id="stat-completed">-</div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div class="help-text flex-grow-1 mb-0">
                    <div class="help-title">Your Learning Dashboard</div>
                    <div class="help-content">
                        Track your LMS course progress, view recent activity, and access your academic performance.
                        Use the sidebar to navigate to different sections of your portal.
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-10"></div>
                <div class="col-2 d-flex justify-content-end">
                    <a href="{{ route('student.academic.index') }}" class="btn-academic flex-shrink-0">
                        <i class="bx bx-bar-chart-alt-2"></i> View Academic Performance
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div id="dashboard-content">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading your dashboard...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            function fetchDashboardData() {
                var termDataUrl = "{{ route('student.dashboard-term') }}";

                $('#dashboard-content').html(`
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading your dashboard...</p>
                </div>
            `);

                $.ajax({
                    url: termDataUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#dashboard-content').html(response);

                        // Update header stats from data attributes
                        var statsContainer = $('#dashboard-stats-data');
                        if (statsContainer.length) {
                            $('#stat-courses').text(statsContainer.data('total') || 0);
                            $('#stat-progress').text(statsContainer.data('progress') || 0);
                            $('#stat-completed').text(statsContainer.data('completed') || 0);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#dashboard-content').html(`
                        <div class="text-center py-5">
                            <i class="bx bx-error-circle text-danger" style="font-size: 64px;"></i>
                            <h5 class="mt-3 text-muted">Failed to load dashboard</h5>
                            <p class="text-muted">Please check your connection and try again.</p>
                            <button class="btn btn-primary" onclick="fetchDashboardData()">
                                <i class="bx bx-refresh me-1"></i> Try Again
                            </button>
                        </div>
                    `);
                        console.error("Error fetching dashboard data:", xhr.status, xhr.statusText);
                    }
                });
            }

            // Make function available globally for retry button
            window.fetchDashboardData = fetchDashboardData;

            // Grade selector change handler
            $('#gradeSelect').on('change', function() {
                var gradeId = $(this).val();

                $.ajax({
                    url: "{{ route('student.grade-session') }}",
                    method: 'POST',
                    data: {
                        grade_id: gradeId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        fetchDashboardData();
                    },
                    error: function(xhr) {
                        console.error("Error setting grade:", xhr.status);
                    }
                });
            });

            // Initial load
            fetchDashboardData();
        });
    </script>
@endsection

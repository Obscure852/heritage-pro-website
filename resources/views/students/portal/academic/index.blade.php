@extends('layouts.master-student-portal')
@section('title')
    Academic Performance
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
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .portal-body {
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

        .term-selector {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .term-selector:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: none;
            color: white;
        }

        .term-selector option {
            background: #374151;
            color: white;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .portal-header {
                padding: 20px;
            }

            .portal-body {
                padding: 16px;
            }
        }

        @media print {

            .btn,
            select,
            .help-text,
            .sidebar,
            .vertical-menu,
            .navbar,
            .topbar,
            .page-title-box,
            .breadcrumb,
            footer {
                display: none !important;
            }

            .portal-container {
                box-shadow: none;
            }

            .portal-header {
                background: #4e73df !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .portal-body {
                padding: 16px 0;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .main-content {
                margin: 0;
                padding: 0;
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

    <div class="row mb-2">
        <div class="col-9"></div>
        <div class="col-3 d-flex justify-content-end">
            <select name="term" id="termId" class="form-select" style="max-width: 200px;">
                @if (!empty($terms))
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}"
                            {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">
                        <i class="bx bx-bar-chart-alt-2 me-2"></i> My Performance
                    </h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }} -
                        Term {{ $currentTerm->term }}, {{ $currentTerm->year }}
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['totalSubjects'] }}</h4>
                                <small class="opacity-75">Subjects</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['caTestsCount'] }}</h4>
                                <small class="opacity-75">CA Tests</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['examsCount'] }}</h4>
                                <small class="opacity-75">Exams</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['avgPercentage'] }}%</h4>
                                <small class="opacity-75">Average</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <!-- Academic Performance Content -->
            <div id="academic-content">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Term change handler
            $('#termId').change(function() {
                var term = $(this).val();
                var studentTermUrl = "{{ route('student.term-session') }}";

                $.ajax({
                    url: studentTermUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("Response:", xhr.responseText);
                    },
                    success: function() {
                        fetchAcademicData();
                    }
                });
            });

            function fetchAcademicData() {
                var academicUrl = "{{ route('student.academic.performance') }}";

                $('#academic-content').html(`
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

                $.ajax({
                    url: academicUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#academic-content').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#academic-content').html(`
                        <div class="text-center py-5">
                            <i class="bx bx-error-circle text-danger display-4"></i>
                            <p class="text-muted mt-3">Failed to load academic data. Please try again.</p>
                            <button class="btn btn-sm btn-primary" onclick="fetchAcademicData()">
                                <i class="bx bx-refresh me-1"></i> Retry
                            </button>
                        </div>
                    `);
                        console.error("Error fetching academic data:", xhr.status, xhr.statusText);
                    }
                });
            }

            // Make function available globally for retry button
            window.fetchAcademicData = fetchAcademicData;

            // Initial load
            fetchAcademicData();
        });
    </script>
@endsection

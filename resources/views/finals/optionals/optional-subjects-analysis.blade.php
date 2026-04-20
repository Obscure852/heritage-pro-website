@extends('layouts.master')
@section('title')
    Optional Subjects Analysis Report (with PSLE Comparison)
@endsection

@section('css')
    <style>
        body {
            font-size: 14px;
        }

        .table {
            width: 100%;
            margin-bottom: 3mm;
            margin-top: 10px;
            page-break-inside: avoid;
            font-size: 12px;
        }

        .table th,
        .table td {
            padding: 0.2rem;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: white;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.2rem;
        }

        .subject-header {
            font-weight: bold;
            font-size: 12px;
            text-align: left;
            padding: 8px !important;
        }

        .teacher-class-col {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
            min-width: 150px;
            max-width: 200px;
            word-wrap: break-word;
            font-size: 12px;
        }

        .row-type-col {
            text-align: center !important;
            font-weight: bold;
            min-width: 60px;
            font-size: 12px;
        }

        .psle-row {
            background-color: #e3f2fd;
        }

        .output-row {
            background-color: #fff3e0;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        .subject-summary {
            background-color: #e9ecef;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 1rem;
            font-size: 16px;
            color: #6f42c1;
        }

        .grade-header {
            font-weight: bold;
            color: #000;
        }

        .grade-a {
            background-color: #f8f9fa;
        }

        .grade-b {
            background-color: #f8f9fa;
        }

        .grade-c {
            background-color: #f8f9fa;
        }

        .grade-d {
            background-color: #f8f9fa;
        }

        .grade-e {
            background-color: #f8f9fa;
        }

        .grade-u {
            background-color: #f8f9fa;
        }

        .performance-header {
            font-weight: bold;
        }

        .gender-subheader {
            background-color: white;
            font-weight: bold;
            font-size: 10px;
        }

        .male-cell {
            background-color: white;
        }

        .female-cell {
            background-color: white;
        }

        .total-cell {
            background-color: white;
            font-weight: bold;
        }

        .optional-badge {
            background-color: #6f42c1;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 0.1mm;
                font-size: 12px;
                background-color: white;
            }

            .teacher-class-col {
                max-width: 80px;
                font-size: 12px;
            }

            .subject-header {
                font-size: 12px;
            }

            .grade-header {
                font-size: 12px;
            }

            .chart-container {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#"
                onclick="event.preventDefault(); 
            if (document.referrer) {
            history.back();
            } else {
            window.location = '{{ route('finals.students.index') }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Optional Subjects Analysis Report
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="#" onclick="printContent()" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-printer me-1"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div style="font-size:14px;" class="col-md-6">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $school_data->school_name ?? 'School Name' }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address ?? 'Physical Address' }}</p>
                                <p class="mb-0">{{ $school_data->postal_address ?? 'Postal Address' }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone ?? 'Tel' }} Fax:
                                    {{ $school_data->fax ?? 'Fax' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-start mb-3">
                        Optional Subjects Analysis Report with PSLE Comparison - {{ $year }}
                    </h6>

                    <!-- Report Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-4">
                                <strong>Total Optional Subjects:</strong>
                                {{ $optional_subjects_summary['total_subjects'] }}<br>
                                <strong>Total Classes:</strong> {{ $optional_subjects_summary['total_classes'] }}
                            </div>
                            <div class="col-4">
                                <strong>Academic Year:</strong> {{ $year }}<br>
                                <strong>Report Type:</strong> Optional/Elective Subjects with PSLE Baseline
                            </div>
                            <div class="col-4">
                                <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                <strong>Subject Type:</strong> Student-Selected Electives
                            </div>
                        </div>
                    </div>

                    @if (empty($optional_subjects_analysis))
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No Data Available:</strong> No optional subjects with external exam results were found
                            for the {{ $year }} graduation year.
                        </div>
                    @else
                        <!-- Optional Subjects Analysis Tables -->
                        @foreach ($optional_subjects_analysis as $subjectName => $subjectData)
                            <div class="subject-summary">
                                <strong>{{ $subjectName }}</strong> -
                                Classes: {{ $subjectData['total_classes'] }}
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <!-- Subject Header Row -->
                                        <tr>
                                            <th rowspan="3" class="teacher-class-col subject-header">{{ $subjectName }}
                                            </th>
                                            <th rowspan="3" class="row-type-col subject-header">Type</th>
                                            <th colspan="18" class="grade-header">Grade Distribution</th>
                                            <th colspan="9" class="performance-header">Performance Categories</th>
                                        </tr>
                                        <!-- Grade Headers Row -->
                                        <tr>
                                            <th colspan="3" class="grade-header grade-a">A</th>
                                            <th colspan="3" class="grade-header grade-b">B</th>
                                            <th colspan="3" class="grade-header grade-c">C</th>
                                            <th colspan="3" class="grade-header grade-d">D</th>
                                            <th colspan="3" class="grade-header grade-e">E</th>
                                            <th colspan="3" class="grade-header grade-u">U</th>
                                            <th colspan="3" class="performance-header">AB%</th>
                                            <th colspan="3" class="performance-header">ABC%</th>
                                            <th colspan="3" class="performance-header">DEU%</th>
                                        </tr>
                                        <!-- Gender Sub Headers Row -->
                                        <tr>
                                            @for ($i = 0; $i < 9; $i++)
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subjectData['klass_subjects'] as $klassSubject)
                                            <tr
                                                class="{{ isset($klassSubject['row_type']) && $klassSubject['row_type'] === 'PSLE' ? 'psle-row' : 'output-row' }}">
                                                <td class="teacher-class-col">
                                                    @if (isset($klassSubject['row_type']) && $klassSubject['row_type'] === 'OUTPUT')
                                                        <strong>{{ $klassSubject['class_name'] }} / Teacher:
                                                            {{ $klassSubject['teacher_name'] }} /
                                                            ({{ $klassSubject['total_students'] }} students)
                                                        </strong>
                                                    @elseif(isset($klassSubject['row_type']) && $klassSubject['row_type'] === 'PSLE')
                                                        {{ $klassSubject['class_name'] }} /
                                                        {{ $klassSubject['teacher_name'] }}
                                                    @else
                                                        <strong>{{ $klassSubject['class_name'] }} / Teacher:
                                                            {{ $klassSubject['teacher_name'] }} /
                                                            ({{ $klassSubject['total_students'] }} students)</strong>
                                                    @endif
                                                </td>

                                                <td class="row-type-col">
                                                    {{ ($klassSubject['row_type'] ?? 'OUTPUT') === 'OUTPUT' ? 'JCE' : $klassSubject['row_type'] }}
                                                </td>

                                                <!-- Grade Distribution -->
                                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                    <td class="male-cell grade-{{ strtolower($grade) }}">
                                                        {{ $klassSubject['grade_analysis'][$grade]['M'] }}</td>
                                                    <td class="female-cell grade-{{ strtolower($grade) }}">
                                                        {{ $klassSubject['grade_analysis'][$grade]['F'] }}</td>
                                                    <td class="total-cell grade-{{ strtolower($grade) }}">
                                                        {{ $klassSubject['grade_analysis'][$grade]['T'] }}</td>
                                                @endforeach

                                                @php
                                                    $perfMap = [
                                                        'AB' => [40, 25, 15],
                                                        'ABC' => [80, 65, 50],
                                                        'DEU' => [15, 25, 40],
                                                    ];
                                                @endphp

                                                <!-- Performance Categories -->
                                                @foreach (['AB', 'ABC', 'DEU'] as $cat)
                                                    @foreach (['M', 'F', 'T'] as $sex)
                                                        @php
                                                            $val = $klassSubject['performance_categories'][$cat][$sex];
                                                            [$hi, $mid, $low] = $perfMap[$cat];
                                                            $class =
                                                                $cat === 'DEU'
                                                                    ? ($val <= $hi
                                                                        ? 'percent-excellent'
                                                                        : ($val <= $mid
                                                                            ? 'percent-good'
                                                                            : ($val <= $low
                                                                                ? 'percent-fair'
                                                                                : 'percent-poor')))
                                                                    : ($val >= $hi
                                                                        ? 'percent-excellent'
                                                                        : ($val >= $mid
                                                                            ? 'percent-good'
                                                                            : ($val >= $low
                                                                                ? 'percent-fair'
                                                                                : 'percent-poor')));
                                                            $cellClass =
                                                                $sex === 'M'
                                                                    ? 'male-cell'
                                                                    : ($sex === 'F'
                                                                        ? 'female-cell'
                                                                        : 'total-cell');
                                                        @endphp
                                                        <td class="{{ $cellClass }} {{ $class }}">
                                                            {{ $val }}%</td>
                                                    @endforeach
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                        <!-- Charts for Optional Subjects -->
                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="chart-container">
                                    <canvas height="500" width="800" id="optionalSubjectsBarChart"></canvas>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="chart-container">
                                    <canvas height="500" width="800" id="optionalSubjectsLineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        @if (!empty($optional_subjects_analysis))
            const optionalSubjectsChartData = @json($optional_subjects_chart_data);

            const chartColors = {
                primary: '#6f42c1',
                success: '#28a745',
                warning: '#ffc107',
                danger: '#dc3545',
                info: '#17a2b8',
                secondary: '#6c757d'
            };

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        cornerRadius: 6,
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Optional Subjects',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            };

            // Initialize Bar Chart
            if (document.getElementById('optionalSubjectsBarChart')) {
                const barCtx = document.getElementById('optionalSubjectsBarChart').getContext('2d');
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: optionalSubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'High Achievement (AB%)',
                                data: optionalSubjectsChartData?.ab_percentages || [],
                                backgroundColor: chartColors.success + '80',
                                borderColor: chartColors.success,
                                borderWidth: 2
                            },
                            {
                                label: 'Pass Rate (ABC%)',
                                data: optionalSubjectsChartData?.abc_percentages || [],
                                backgroundColor: chartColors.primary + '80',
                                borderColor: chartColors.primary,
                                borderWidth: 2
                            },
                            {
                                label: 'Failure Rate (DEU%)',
                                data: optionalSubjectsChartData?.deu_percentages || [],
                                backgroundColor: chartColors.danger + '80',
                                borderColor: chartColors.danger,
                                borderWidth: 2
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            title: {
                                display: true,
                                text: 'Optional Subjects Performance Comparison (JCE Only)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                padding: 20
                            }
                        }
                    }
                });
            }

            // Initialize Line Chart
            if (document.getElementById('optionalSubjectsLineChart')) {
                const lineCtx = document.getElementById('optionalSubjectsLineChart').getContext('2d');
                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: optionalSubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'A Grade %',
                                data: optionalSubjectsChartData?.grade_distributions?.A || [],
                                borderColor: chartColors.success,
                                backgroundColor: chartColors.success + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'B Grade %',
                                data: optionalSubjectsChartData?.grade_distributions?.B || [],
                                borderColor: chartColors.info,
                                backgroundColor: chartColors.info + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'C Grade %',
                                data: optionalSubjectsChartData?.grade_distributions?.C || [],
                                borderColor: chartColors.warning,
                                backgroundColor: chartColors.warning + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'D Grade %',
                                data: optionalSubjectsChartData?.grade_distributions?.D || [],
                                borderColor: chartColors.danger,
                                backgroundColor: chartColors.danger + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            title: {
                                display: true,
                                text: 'Optional Subjects Grade Distribution Trends (JCE Only)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                padding: 20
                            }
                        }
                    }
                });
            }
        @endif
    </script>
@endsection

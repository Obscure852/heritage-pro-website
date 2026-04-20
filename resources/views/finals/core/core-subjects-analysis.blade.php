@extends('layouts.master')
@section('title')
    Subject Analysis Report (with PSLE Comparison)
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
            background-color: #343a40;
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
        }

        .subject-summary {
            background-color: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 1rem;
            font-size: 16px;
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
            Subject Analysis Report
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
                        Subject Analysis Report with PSLE Comparison - {{ $year }}
                    </h6>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="subjectAnalysisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-subjects-tab" data-bs-toggle="tab"
                                data-bs-target="#all-subjects" type="button" role="tab" aria-controls="all-subjects"
                                aria-selected="true">
                                <i class="bx bx-list-ul me-1"></i>All Subjects
                                ({{ $all_subjects_summary['total_subjects'] }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mandatory-subjects-tab" data-bs-toggle="tab"
                                data-bs-target="#mandatory-subjects" type="button" role="tab"
                                aria-controls="mandatory-subjects" aria-selected="false">
                                <i class="bx bx-star me-1"></i>Mandatory Subjects
                                ({{ $mandatory_subjects_summary['total_subjects'] }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="subjectAnalysisTabsContent">
                        <!-- All Subjects Tab -->
                        <div class="tab-pane fade show active" id="all-subjects" role="tabpanel"
                            aria-labelledby="all-subjects-tab">
                            <div class="mt-3">
                                <!-- Report Summary for All Subjects -->
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Total Subjects:</strong>
                                            {{ $all_subjects_summary['total_subjects'] }}<br>
                                            <strong>Total Classes:</strong> {{ $all_subjects_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> Core Subjects with PSLE Comparison
                                        </div>
                                    </div>
                                </div>

                                <!-- All Subjects Analysis Tables -->
                                @foreach ($all_subjects_analysis as $subjectName => $subjectData)
                                    <div class="subject-summary">
                                        <strong>{{ $subjectName }}</strong> -
                                        Classes: {{ $subjectData['total_classes'] }}
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <!-- Subject Header Row -->
                                                <tr>
                                                    <th rowspan="3" class="teacher-class-col subject-header">
                                                        {{ $subjectName }}</th>
                                                    <th rowspan="3" class="row-type-col subject-header">Type</th>
                                                    <th colspan="18" class="grade-header">Grade Distribution</th>
                                                    <th colspan="9" class="performance-header">Performance Categories
                                                    </th>
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
                                                    <tr>
                                                        <td class="teacher-class-col">
                                                            @if ($klassSubject['row_type'] === 'OUTPUT')
                                                                <strong>{{ $klassSubject['class_name'] }} / Teacher:
                                                                    {{ $klassSubject['teacher_name'] }} /
                                                                    ({{ $klassSubject['total_students'] }}
                                                                    students)
                                                                </strong>
                                                            @else
                                                                {{ $klassSubject['class_name'] }} /
                                                                {{ $klassSubject['teacher_name'] }}
                                                            @endif
                                                        </td>
                                                        <td class="row-type-col">
                                                            {{ $klassSubject['row_type'] === 'OUTPUT' ? 'JCE' : $klassSubject['row_type'] }}
                                                        </td>

                                                        <!-- A Grade -->
                                                        <td class="male-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['M'] }}</td>
                                                        <td class="female-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['F'] }}</td>
                                                        <td class="total-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['T'] }}</td>

                                                        <!-- B Grade -->
                                                        <td class="male-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['M'] }}</td>
                                                        <td class="female-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['F'] }}</td>
                                                        <td class="total-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['T'] }}</td>

                                                        <!-- C Grade -->
                                                        <td class="male-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['M'] }}</td>
                                                        <td class="female-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['F'] }}</td>
                                                        <td class="total-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['T'] }}</td>

                                                        <!-- D Grade -->
                                                        <td class="male-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['M'] }}</td>
                                                        <td class="female-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['F'] }}</td>
                                                        <td class="total-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['T'] }}</td>

                                                        <!-- E Grade -->
                                                        <td class="male-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['M'] }}</td>
                                                        <td class="female-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['F'] }}</td>
                                                        <td class="total-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['T'] }}</td>

                                                        <!-- U Grade -->
                                                        <td class="male-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['M'] }}</td>
                                                        <td class="female-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['F'] }}</td>
                                                        <td class="total-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['T'] }}</td>

                                                        @php
                                                            $perfMap = [
                                                                'AB' => [40, 25, 15],
                                                                'ABC' => [80, 65, 50],
                                                                'DEU' => [15, 25, 40],
                                                            ];
                                                        @endphp

                                                        <!-- AB% (High Achievement) -->
                                                        @foreach (['AB', 'ABC', 'DEU'] as $cat)
                                                            @foreach (['M', 'F', 'T'] as $sex)
                                                                @php
                                                                    $val =
                                                                        $klassSubject['performance_categories'][$cat][
                                                                            $sex
                                                                        ];
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

                                <!-- Charts for All Subjects -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6 class="chart-title">All Subjects Performance Analysis (JCE Only)</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="chart-container">
                                            <canvas height="400" width="600" id="allSubjectsBarChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="chart-container">
                                            <canvas height="400" width="600" id="allSubjectsLineChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mandatory Subjects Tab -->
                        <div class="tab-pane fade" id="mandatory-subjects" role="tabpanel"
                            aria-labelledby="mandatory-subjects-tab">
                            <div class="mt-3">
                                <!-- Report Summary for Mandatory Subjects -->
                                <div class="summary-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Mandatory Subjects:</strong>
                                            {{ $mandatory_subjects_summary['total_subjects'] }}<br>
                                            <strong>Total Classes:</strong>
                                            {{ $mandatory_subjects_summary['total_classes'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Academic Year:</strong> {{ $year }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                            <strong>Report Type:</strong> Mandatory Subjects with PSLE Comparison
                                        </div>
                                    </div>
                                </div>

                                <!-- Mandatory Subjects Analysis Tables -->
                                @foreach ($mandatory_subjects_analysis as $subjectName => $subjectData)
                                    <div class="subject-summary">
                                        <strong>{{ $subjectName }}</strong> (Mandatory Subject) -
                                        Classes: {{ $subjectData['total_classes'] }}
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <!-- Subject Header Row -->
                                                <tr>
                                                    <th rowspan="3" class="teacher-class-col subject-header">
                                                        {{ $subjectName }}</th>
                                                    <th rowspan="3" class="row-type-col subject-header">Type</th>
                                                    <th colspan="18" class="grade-header">Grade Distribution</th>
                                                    <th colspan="9" class="performance-header">Performance Categories
                                                    </th>
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
                                                    <tr>
                                                        <td class="teacher-class-col">
                                                            @if ($klassSubject['row_type'] === 'OUTPUT')
                                                                <strong>{{ $klassSubject['class_name'] }} / Teacher:
                                                                    {{ $klassSubject['teacher_name'] }} /
                                                                    ({{ $klassSubject['total_students'] }}
                                                                    students)
                                                                </strong>
                                                            @else
                                                                {{ $klassSubject['class_name'] }} /
                                                                {{ $klassSubject['teacher_name'] }}
                                                            @endif
                                                        </td>
                                                        <td class="row-type-col">
                                                            {{ $klassSubject['row_type'] === 'OUTPUT' ? 'JCE' : $klassSubject['row_type'] }}
                                                        </td>

                                                        <!-- A Grade -->
                                                        <td class="male-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['M'] }}</td>
                                                        <td class="female-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['F'] }}</td>
                                                        <td class="total-cell grade-a">
                                                            {{ $klassSubject['grade_analysis']['A']['T'] }}</td>

                                                        <!-- B Grade -->
                                                        <td class="male-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['M'] }}</td>
                                                        <td class="female-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['F'] }}</td>
                                                        <td class="total-cell grade-b">
                                                            {{ $klassSubject['grade_analysis']['B']['T'] }}</td>

                                                        <!-- C Grade -->
                                                        <td class="male-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['M'] }}</td>
                                                        <td class="female-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['F'] }}</td>
                                                        <td class="total-cell grade-c">
                                                            {{ $klassSubject['grade_analysis']['C']['T'] }}</td>

                                                        <!-- D Grade -->
                                                        <td class="male-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['M'] }}</td>
                                                        <td class="female-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['F'] }}</td>
                                                        <td class="total-cell grade-d">
                                                            {{ $klassSubject['grade_analysis']['D']['T'] }}</td>

                                                        <!-- E Grade -->
                                                        <td class="male-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['M'] }}</td>
                                                        <td class="female-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['F'] }}</td>
                                                        <td class="total-cell grade-e">
                                                            {{ $klassSubject['grade_analysis']['E']['T'] }}</td>

                                                        <!-- U Grade -->
                                                        <td class="male-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['M'] }}</td>
                                                        <td class="female-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['F'] }}</td>
                                                        <td class="total-cell grade-u">
                                                            {{ $klassSubject['grade_analysis']['U']['T'] }}</td>

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
                                                                    $val =
                                                                        $klassSubject['performance_categories'][$cat][
                                                                            $sex
                                                                        ];
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

                                <!-- Charts for Mandatory Subjects -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6 class="chart-title">Mandatory Subjects Performance Analysis (JCE Only)
                                        </h6>
                                    </div>
                                    <div class="col-6">
                                        <div class="chart-container">
                                            <canvas height="400" width="600"
                                                id="mandatorySubjectsBarChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="chart-container">
                                            <canvas height="400" width="600"
                                                id="mandatorySubjectsLineChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        const allSubjectsChartData = @json($all_subjects_chart_data);
        const mandatorySubjectsChartData = @json($mandatory_subjects_chart_data);

        console.log('All Subjects Data:', allSubjectsChartData);
        console.log('Mandatory Subjects Data:', mandatorySubjectsChartData);

        const chartColors = {
            primary: '#007bff',
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
                        text: 'Subjects',
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

        let allSubjectsBarChart, allSubjectsLineChart, mandatorySubjectsBarChart, mandatorySubjectsLineChart;

        function initializeCharts() {
            if (document.getElementById('allSubjectsBarChart')) {
                const allSubjectsBarCtx = document.getElementById('allSubjectsBarChart').getContext('2d');
                allSubjectsBarChart = new Chart(allSubjectsBarCtx, {
                    type: 'bar',
                    data: {
                        labels: allSubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'High Achievement (AB%)',
                                data: allSubjectsChartData?.ab_percentages || [],
                                backgroundColor: chartColors.success + '80',
                                borderColor: chartColors.success,
                                borderWidth: 2
                            },
                            {
                                label: 'Pass Rate (ABC%)',
                                data: allSubjectsChartData?.abc_percentages || [],
                                backgroundColor: chartColors.primary + '80',
                                borderColor: chartColors.primary,
                                borderWidth: 2
                            },
                            {
                                label: 'Failure Rate (DEU%)',
                                data: allSubjectsChartData?.deu_percentages || [],
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
                                text: 'All Subjects Performance Comparison (JCE Only)',
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

            if (document.getElementById('allSubjectsLineChart')) {
                const allSubjectsLineCtx = document.getElementById('allSubjectsLineChart').getContext('2d');
                allSubjectsLineChart = new Chart(allSubjectsLineCtx, {
                    type: 'line',
                    data: {
                        labels: allSubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'A Grade %',
                                data: allSubjectsChartData?.grade_distributions?.A || [],
                                borderColor: chartColors.success,
                                backgroundColor: chartColors.success + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'B Grade %',
                                data: allSubjectsChartData?.grade_distributions?.B || [],
                                borderColor: chartColors.info,
                                backgroundColor: chartColors.info + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'C Grade %',
                                data: allSubjectsChartData?.grade_distributions?.C || [],
                                borderColor: chartColors.warning,
                                backgroundColor: chartColors.warning + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'D Grade %',
                                data: allSubjectsChartData?.grade_distributions?.D || [],
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
                                text: 'All Subjects Grade Distribution Trends (JCE Only)',
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

            if (document.getElementById('mandatorySubjectsBarChart')) {
                const mandatorySubjectsBarCtx = document.getElementById('mandatorySubjectsBarChart').getContext('2d');
                mandatorySubjectsBarChart = new Chart(mandatorySubjectsBarCtx, {
                    type: 'bar',
                    data: {
                        labels: mandatorySubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'High Achievement (AB%)',
                                data: mandatorySubjectsChartData?.ab_percentages || [],
                                backgroundColor: chartColors.success + '80',
                                borderColor: chartColors.success,
                                borderWidth: 2
                            },
                            {
                                label: 'Pass Rate (ABC%)',
                                data: mandatorySubjectsChartData?.abc_percentages || [],
                                backgroundColor: chartColors.primary + '80',
                                borderColor: chartColors.primary,
                                borderWidth: 2
                            },
                            {
                                label: 'Failure Rate (DEU%)',
                                data: mandatorySubjectsChartData?.deu_percentages || [],
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
                                text: 'Mandatory Subjects Performance Comparison (JCE Only)',
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

            if (document.getElementById('mandatorySubjectsLineChart')) {
                const mandatorySubjectsLineCtx = document.getElementById('mandatorySubjectsLineChart').getContext('2d');
                mandatorySubjectsLineChart = new Chart(mandatorySubjectsLineCtx, {
                    type: 'line',
                    data: {
                        labels: mandatorySubjectsChartData?.subjects || [],
                        datasets: [{
                                label: 'A Grade %',
                                data: mandatorySubjectsChartData?.grade_distributions?.A || [],
                                borderColor: chartColors.success,
                                backgroundColor: chartColors.success + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'B Grade %',
                                data: mandatorySubjectsChartData?.grade_distributions?.B || [],
                                borderColor: chartColors.info,
                                backgroundColor: chartColors.info + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'C Grade %',
                                data: mandatorySubjectsChartData?.grade_distributions?.C || [],
                                borderColor: chartColors.warning,
                                backgroundColor: chartColors.warning + '20',
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'D Grade %',
                                data: mandatorySubjectsChartData?.grade_distributions?.D || [],
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
                                text: 'Mandatory Subjects Grade Distribution Trends (JCE Only)',
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
        }


        function handleTabSwitch() {
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function() {
                    setTimeout(function() {
                        if (allSubjectsBarChart) allSubjectsBarChart.resize();
                        if (allSubjectsLineChart) allSubjectsLineChart.resize();
                        if (mandatorySubjectsBarChart) mandatorySubjectsBarChart.resize();
                        if (mandatorySubjectsLineChart) mandatorySubjectsLineChart.resize();
                    }, 100);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            handleTabSwitch();
        });

        window.addEventListener('resize', function() {
            if (allSubjectsBarChart) allSubjectsBarChart.resize();
            if (allSubjectsLineChart) allSubjectsLineChart.resize();
            if (mandatorySubjectsBarChart) mandatorySubjectsBarChart.resize();
            if (mandatorySubjectsLineChart) mandatorySubjectsLineChart.resize();
        });

        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.chart-container').forEach(function(container) {
                container.style.display = 'none';
            });
        });

        window.addEventListener('afterprint', function() {
            document.querySelectorAll('.chart-container').forEach(function(container) {
                container.style.display = 'block';
            });

            setTimeout(function() {
                if (allSubjectsBarChart) allSubjectsBarChart.resize();
                if (allSubjectsLineChart) allSubjectsLineChart.resize();
                if (mandatorySubjectsBarChart) mandatorySubjectsBarChart.resize();
                if (mandatorySubjectsLineChart) mandatorySubjectsLineChart.resize();
            }, 100);
        });
    </script>
@endsection

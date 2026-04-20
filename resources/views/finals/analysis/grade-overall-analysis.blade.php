@extends('layouts.master')
@section('title')
    Graduate Year External Exam Results Report - {{ $reportData['graduation_year'] }}
@endsection
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .card {
            box-shadow: none;
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
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.2rem;
        }

        .grade-a {
            background-color: #d5f5d5;
        }

        .grade-b {
            background-color: #e5f3ff;
        }

        .grade-c {
            background-color: #fff3e5;
        }

        .grade-d {
            background-color: #ffffd5;
        }

        .grade-e {
            background-color: #ffe5e5;
        }

        .grade-u {
            background-color: #ffd5d5;
        }

        .grade-merit {
            background-color: #d5f5d5;
            font-weight: bold;
        }

        .points-high {
            background-color: #d5f5d5;
        }

        .points-medium {
            background-color: #ffffd5;
        }

        .points-low {}

        .student-name {
            text-align: left !important;
            font-weight: 500;
        }

        .exam-type {
            text-align: left;
            font-weight: bold;
            color: #0066cc;
        }

        .subject-grade {
            text-align: start;
            font-weight: 500;
        }

        .total-points {
            text-align: start;
            font-weight: 600;
        }

        .overall-grade {
            text-align: start;
            font-size: 12px;
            font-weight: 600;
        }

        .section-divider {
            margin-top: 30px;
            margin-bottom: 30px;
            border-top: 1px solid #e9ecef;
        }

        .chart-container {
            height: 350px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                font-size: 7px;
            }

            .no-print {
                display: none !important;
            }

            .table td,
            .table th {
                padding: 0.3mm 0.5mm;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .graph-container {
                page-break-before: always;
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
            window.location = '{{ $gradebookBackUrl }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Graduate Year {{ $reportData['graduation_year'] }} - External Exam Results
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-download"></i>
            </a>

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
                                <h5 class="mb-0">{{ $school_data->school_name }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address }}</p>
                                <p class="mb-0">{{ $school_data->postal_address }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th class="student-name">Student Name</th>
                                    <th class="class-name">Class</th>
                                    <th>PSLE</th>
                                    <th>Exam</th>
                                    @foreach ($reportData['subjects'] as $subject)
                                        @php
                                            $subjectAbbr = strtoupper(substr($subject, 0, 3));
                                        @endphp
                                        <th title="{{ $subject }}">{{ $subjectAbbr }}</th>
                                    @endforeach
                                    <th>TP</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['students'] as $index => $student)
                                    <tr @if ($index % 2 == 0) class="table-light" @endif>
                                        <td class="student-name">{{ $student['student_name'] }}</td>
                                        <td class="class-name">{{ $student['class_name'] }}</td>
                                        <td
                                            class="
                                            @if ($student['psle_grade']) @switch($student['psle_grade'])
                                                    @case('A') grade-a @break
                                                    @case('B') grade-b @break
                                                    @case('C') grade-c @break
                                                    @case('D') grade-d @break
                                                    @case('E') grade-e @break
                                                    @case('U') grade-u @break
                                                @endswitch @endif
                                        ">
                                            @if ($student['psle_grade'])
                                                {{ $student['psle_grade'] }}
                                            @else
                                                <span class="no-results">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($student['exam_type'])
                                                {{ $student['exam_type'] }}
                                            @else
                                                <span class="no-results">N/A</span>
                                            @endif
                                        </td>
                                        @foreach ($reportData['subjects'] as $subject)
                                            <td
                                                class="subject-grade
                                                @if (isset($student['subjects'][$subject]) &&
                                                        $student['subjects'][$subject] !== '-' &&
                                                        $student['subjects'][$subject] !== '') @switch(strtolower($student['subjects'][$subject]))
                                                        @case('a') grade-a @break
                                                        @case('b') grade-b @break
                                                        @case('c') grade-c @break
                                                        @case('d') grade-d @break
                                                        @case('e') grade-e @break
                                                        @case('u') grade-u @break
                                                        @case('merit') grade-merit @break
                                                    @endswitch @endif
                                            ">
                                                {{ $student['subjects'][$subject] ?? '-' }}
                                            </td>
                                        @endforeach
                                        <td
                                            class="total-points
                                            @if (($student['has_results'] ?? false) && $student['total_points'] !== null) @if ($student['total_points'] >= 40) points-high
                                                @elseif($student['total_points'] >= 25) points-medium
                                                @else points-low @endif
                                            @endif
                                        ">
                                            @if (($student['has_results'] ?? false) && $student['total_points'] !== null)
                                                {{ number_format($student['total_points'], 1) }}
                                            @else
                                                <span class="no-results">-</span>
                                            @endif
                                        </td>
                                        <td
                                            class="overall-grade
                                            @if ($student['overall_grade'] && $student['overall_grade'] !== 'N/A') @switch(strtolower($student['overall_grade']))
                                                    @case('a') grade-a @break
                                                    @case('b') grade-b @break
                                                    @case('c') grade-c @break
                                                    @case('d') grade-d @break
                                                    @case('e') grade-e @break
                                                    @case('u') grade-u @break
                                                    @case('merit') grade-merit @break
                                                @endswitch @endif
                                        ">
                                            @if ($student['overall_grade'] && $student['overall_grade'] !== 'N/A')
                                                {{ $student['overall_grade'] }}
                                            @else
                                                <span class="no-results">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($reportData['subjects']) + 6 }}"
                                            class="text-center no-results">
                                            No graduate students found for year {{ $reportData['graduation_year'] }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                        @php
                            $studentsWithResults = collect($reportData['students'])->filter(function ($student) {
                                return $student['has_results'] ?? false;
                            });

                        $gradeDistribution = [];
                        $totalWithResults = $studentsWithResults->count();

                        if ($totalWithResults > 0) {
                            foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                                $count = $studentsWithResults->where('overall_grade', $grade)->count();
                                if ($count > 0) {
                                    $gradeDistribution[$grade] = [
                                        'count' => $count,
                                        'percentage' => round(($count / $totalWithResults) * 100, 1),
                                    ];
                                }
                            }
                        }
                    @endphp

                    <!-- Performance Chart (for screen only) -->
                    @if ($totalWithResults > 0)
                        <div class="section-divider no-print"></div>

                        <div class="row no-print">
                            <div class="col-md-6">
                                <h6 class="text-center">Overall Grade Distribution</h6>
                                <div class="chart-container">
                                    <canvas id="gradeDistributionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center">Class Performance Comparison</h6>
                                <div class="chart-container">
                                    <canvas id="classPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection

@section('script')
    @if ($totalWithResults > 0)
        <!-- Include Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const gradeData = @json($gradeDistribution ?? []);
                const classData = @json($reportData['classes_summary'] ?? []);

                // Grade Distribution Chart
                if (Object.keys(gradeData).length > 0) {
                    const ctx1 = document.getElementById('gradeDistributionChart').getContext('2d');

                    const labels = Object.keys(gradeData);
                    const data = Object.values(gradeData).map(item => item.count);
                    const colors = labels.map(grade => {
                        switch (grade.toLowerCase()) {
                            case 'a':
                            case 'merit':
                                return 'rgba(40, 167, 69, 0.8)';
                            case 'b':
                                return 'rgba(0, 123, 255, 0.8)';
                            case 'c':
                                return 'rgba(23, 162, 184, 0.8)';
                            case 'd':
                                return 'rgba(255, 193, 7, 0.8)';
                            case 'e':
                                return 'rgba(255, 133, 27, 0.8)';
                            case 'u':
                                return 'rgba(220, 53, 69, 0.8)';
                            default:
                                return 'rgba(108, 117, 125, 0.8)';
                        }
                    });

                    new Chart(ctx1, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const grade = labels[context.dataIndex];
                                            const count = data[context.dataIndex];
                                            const percentage = gradeData[grade].percentage;
                                            return `Grade ${grade}: ${count} students (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Class Performance Comparison Chart
                if (classData.length > 0) {
                    const ctx2 = document.getElementById('classPerformanceChart').getContext('2d');

                    const classLabels = classData.map(c => c.name);
                    const passRates = classData.map(c => c.pass_rate);
                    const avgPoints = classData.map(c => c.average_points);

                    new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: classLabels,
                            datasets: [{
                                label: 'Pass Rate (%)',
                                data: passRates,
                                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1,
                                yAxisID: 'y'
                            }, {
                                label: 'Average Points',
                                data: avgPoints,
                                backgroundColor: 'rgba(0, 123, 255, 0.6)',
                                borderColor: 'rgba(0, 123, 255, 1)',
                                borderWidth: 1,
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Classes'
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Pass Rate (%)'
                                    },
                                    max: 100
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Average Points'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif
@endsection

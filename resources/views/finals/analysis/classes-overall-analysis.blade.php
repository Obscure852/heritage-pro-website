@extends('layouts.master')
@section('title')
    External Exam Results Report
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

        .class-name {
            text-align: left !important;
        }

        .exam-type {
            text-align: left;
            font-weight: bold;
            color: #0066cc;
        }

        .subject-grade {
            text-align: left;
        }

        .total-points {
            text-align: center;
        }

        .overall-grade {
            text-align: left;
            font-size: 12px;
        }

        .no-results {
            color: #888;
            font-style: italic;
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
                size: potrait;
            }

            body {
                font-size: 8px;
            }

            .no-print {
                display: none !important;
            }

            .table td,
            .table th {
                font-size: 10px;
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
            External Exam Results
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
                                            $subjectAbbr = match (strtolower($subject)) {
                                                'english' => 'ENG',
                                                'mathematics' => 'MATH',
                                                'science' => 'SCI',
                                                'social studies' => 'SS',
                                                'setswana' => 'SET',
                                                'agriculture' => 'AGR',
                                                'moral education' => 'ME',
                                                'design and technology' => 'DT',
                                                'home economics' => 'HE',
                                                'art' => 'ART',
                                                'music' => 'MUS',
                                                'physical education' => 'PE',
                                                'religious education' => 'RE',
                                                'commerce and accounting' => 'CA',
                                                'commerce and office procedures' => 'COP',
                                                'creative arts' => 'CAPA',
                                                'french' => 'FR',
                                                default => strtoupper(substr($subject, 0, 3)),
                                            };
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
                                        <td @if ($student['psle_grade']) @switch($student['psle_grade'])
                                                    @case('A') grade-a @break
                                                    @case('B') grade-b @break
                                                    @case('C') grade-c @break
                                                    @case('D') grade-d @break
                                                    @case('E') grade-e @break
                                                    @case('U') grade-u @break
                                                @endswitch @endif ">
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
                                                @if (isset($student['subjects'][$subject]) && $student['subjects'][$subject] !== '-') @switch($student['subjects'][$subject])
                                                        @case('A') grade-a @break
                                                        @case('B') grade-b @break
                                                        @case('C') grade-c @break
                                                        @case('D') grade-d @break
                                                        @case('E') grade-e @break
                                                        @case('U') grade-u @break
                                                    @endswitch @endif
                                            ">
                                        {{ $student['subjects'][$subject] ?? '-' }}
                                    </td>
                                @endforeach
                                <td
                                    class="
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
                                    class="
                                            @if ($student['overall_grade'] && $student['overall_grade'] !== 'N/A') @switch($student['overall_grade'])
                                                    @case('A') grade-a @break
                                                    @case('B') grade-b @break
                                                    @case('C') grade-c @break
                                                    @case('D') grade-d @break
                                                    @case('E') grade-e @break
                                                    @case('U') grade-u @break
                                                    @case('Merit') grade-a @break
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
                                    <td colspan="{{ count($reportData['subjects']) + 6 }}" class="text-center no-results">
                                        No students found in this class
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Statistics -->
                    @if ($reportData['total_students'] > 0)
                        <div class="section-divider"></div>

                        <div class="row no-print">
                            <div class="col-md-12">
                                <h6 class="text-start mb-3">Class Performance Summary</h6>

                                @php
                                    $studentsWithResults = collect($reportData['students'])->filter(function (
                                        $student,
                                    ) {
                                        return $student['has_results'] ?? false;
                                    });

                                    $gradeDistribution = [];
                                    $totalWithResults = $studentsWithResults->count();

                                    if ($totalWithResults > 0) {
                                        foreach (['A', 'B', 'C', 'D', 'E', 'U', 'Merit'] as $grade) {
                                            $count = $studentsWithResults->where('overall_grade', $grade)->count();
                                            if ($count > 0) {
                                                $gradeDistribution[$grade] = [
                                                    'count' => $count,
                                                    'percentage' => round(($count / $totalWithResults) * 100, 1),
                                                ];
                                            }
                                        }

                                        $passCount = $studentsWithResults
                                            ->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])
                                            ->count();
                                        $passRate = round(($passCount / $totalWithResults) * 100, 1);

                                        $averagePoints = round($studentsWithResults->avg('total_points'), 1);
                                        $highestPoints = $studentsWithResults->max('total_points');
                                        $lowestPoints = $studentsWithResults
                                            ->where('total_points', '>', 0)
                                            ->min('total_points');
                                    }
                                @endphp

                                @if ($totalWithResults > 0)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h4 class="mb-1">{{ $totalWithResults }}</h4>
                                                <small>Students with Results</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-success text-white rounded">
                                                <h4 class="mb-1">{{ $passRate }}%</h4>
                                                <small>Pass Rate (A-C)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-info text-white rounded">
                                                <h4 class="mb-1">{{ $averagePoints }}</h4>
                                                <small>Average Points</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-primary text-white rounded">
                                                <h4 class="mb-1">{{ $highestPoints }}</h4>
                                                <small>Highest Points</small>
                                            </div>
                                        </div>
                                    </div>

                                    @if (!empty($gradeDistribution))
                                        <div class="mt-4">
                                            <h6>Grade Distribution</h6>
                                            <div class="row">
                                                @foreach ($gradeDistribution as $grade => $data)
                                                    <div class="col-md-2 mb-2">
                                                        <div
                                                            class="text-center p-2 border rounded 
                                                            @switch($grade)
                                                                @case('A') @case('Merit') bg-success text-white @break
                                                                @case('B') bg-primary text-white @break
                                                                @case('C') bg-info text-white @break
                                                                @case('D') bg-warning @break
                                                                @case('E') bg-orange text-white @break
                                                                @case('U') bg-danger text-white @break
                                                            @endswitch
                                                        ">
                                                            <strong>{{ $grade }}</strong><br>
                                                            <small>{{ $data['count'] }}
                                                                ({{ $data['percentage'] }}%)
                                                            </small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bx bx-warning"></i>
                                        No external exam results found for students in this class.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Performance Chart (for screen only) -->
                    @if ($totalWithResults > 0)
                        <div class="section-divider no-print"></div>

                        <div class="row no-print">
                            <div class="col-md-12">
                                <h6 class="text-center">Student Performance Distribution</h6>
                                <div class="chart-container">
                                    <canvas id="performanceChart"></canvas>
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

                if (Object.keys(gradeData).length > 0) {
                    const ctx = document.getElementById('performanceChart').getContext('2d');

                    const labels = Object.keys(gradeData);
                    const data = Object.values(gradeData).map(item => item.count);
                    const colors = labels.map(grade => {
                        switch (grade) {
                            case 'A':
                            case 'Merit':
                                return 'rgba(40, 167, 69, 0.8)';
                            case 'B':
                                return 'rgba(0, 123, 255, 0.8)';
                            case 'C':
                                return 'rgba(23, 162, 184, 0.8)';
                            case 'D':
                                return 'rgba(255, 193, 7, 0.8)';
                            case 'E':
                                return 'rgba(255, 133, 27, 0.8)';
                            case 'U':
                                return 'rgba(220, 53, 69, 0.8)';
                            default:
                                return 'rgba(108, 117, 125, 0.8)';
                        }
                    });

                    new Chart(ctx, {
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
                                        padding: 20,
                                        font: {
                                            size: 12
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
            });
        </script>
    @endif
@endsection

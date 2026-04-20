@extends('layouts.master')
@section('title')
    Optional Subjects Department Analysis Report
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

        .department-header {
            background-color: #6f42c1;
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: left;
            padding: 8px !important;
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

        /* PSLE row styling */
        .psle-row {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107 !important;
        }

        .psle-row td {
            background-color: #fff3cd !important;
            font-style: italic;
        }

        .psle-label {
            background-color: #ffc107;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
        }

        /* OUTPUT row styling */
        .output-row {
            background-color: #d1ecf1 !important;
            border-left: 4px solid #17a2b8 !important;
        }

        .output-row td {
            background-color: #d1ecf1 !important;
        }

        .output-label {
            background-color: #17a2b8;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.3px;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        .department-summary {
            background-color: #e9ecef;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 15px;
            border-radius: 0.3px;
        }

        .subject-summary {
            background-color: #f3e5f5;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 13px;
            border-left: 3px solid #9561e2;
            margin-left: 1rem;
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

        .optional-badge {
            background-color: #6f42c1;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }

        .department-badge {
            background-color: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
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

            .department-header {
                font-size: 12px;
            }

            .subject-header {
                font-size: 12px;
            }

            .grade-header {
                font-size: 12px;
            }

            .chart-container {
                page-break-inside: avoid;
                display: none;
            }

            .psle-row td {
                background-color: #fff3cd !important;
            }

            .output-row td {
                background-color: #d1ecf1 !important;
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
            Optional Subjects Department Analysis
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
                        Optional Subjects Department Analysis Report - {{ $year }} (with PSLE Comparison)
                    </h6>
                    <!-- Report Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-4">
                                <strong>Total Departments:</strong> {{ $departments_summary['total_departments'] ?? 0 }}<br>
                                <strong>Total Subjects:</strong> {{ $departments_summary['total_subjects'] ?? 0 }}
                            </div>
                            <div class="col-4">
                                <strong>Academic Year:</strong> {{ $year }}<br>
                                <strong>Report Type:</strong> Optional Subjects by Department (with PSLE Comparison)
                            </div>
                            <div class="col-4">
                                <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                <strong>Analysis Type:</strong> Department-based Grouping with PSLE Input Analysis
                            </div>
                        </div>
                    </div>

                    @if (empty($departments_analysis))
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No Data Available:</strong> No optional subjects with external exam results were found
                            for the {{ $year }} graduation year.
                        </div>
                    @else
                        <!-- Department Analysis Tables -->
                        @foreach ($departments_analysis as $departmentName => $departmentData)
                            <div class="department-summary">
                                <strong>{{ $departmentName }} Department</strong> -
                                Subjects:
                                {{ $departmentData['total_subjects'] ?? count($departmentData['subjects'] ?? []) }},
                                Classes: {{ $departmentData['total_classes'] ?? 0 }}
                            </div>

                            @foreach ($departmentData['subjects'] ?? [] as $subjectName => $subjectData)
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <!-- Subject Header Row -->
                                            <tr>
                                                <th rowspan="3" class="teacher-class-col subject-header">
                                                    {{ $subjectName }}</th>
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
                                                <!-- A Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- B Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- C Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- D Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- E Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- U Grade -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- AB% -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- ABC% -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                                <!-- DEU% -->
                                                <th class="gender-subheader">M</th>
                                                <th class="gender-subheader">F</th>
                                                <th class="gender-subheader">T</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subjectData['klass_subjects'] ?? [] as $klassSubject)
                                                @php
                                                    $rowClass = '';
                                                    $labelClass = '';
                                                    $labelText = '';

                                                    if (isset($klassSubject['row_type'])) {
                                                        if ($klassSubject['row_type'] === 'PSLE') {
                                                            $rowClass = 'psle-row';
                                                            $labelClass = 'psle-label';
                                                            $labelText = 'PSLE';
                                                        } elseif ($klassSubject['row_type'] === 'OUTPUT') {
                                                            $rowClass = 'output-row';
                                                            $labelClass = 'output-label';
                                                            $labelText = 'JCE';
                                                        }
                                                    } else {
                                                        $rowClass = 'output-row';
                                                        $labelClass = 'output-label';
                                                        $labelText = 'JCE';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="teacher-class-col">
                                                        <strong>{{ $klassSubject['class_name'] ?? 'Unknown Class' }} /
                                                            {{ $klassSubject['teacher_name'] ?? 'Unknown Teacher' }} /
                                                            ({{ $klassSubject['total_students'] ?? 0 }} students)
                                                        </strong>
                                                    </td>
                                                    <td class="row-type-col">
                                                        <span><strong>{{ $labelText }}</strong></span>
                                                    </td>

                                                    <!-- A Grade -->
                                                    <td class="male-cell grade-a">
                                                        {{ $klassSubject['grade_analysis']['A']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-a">
                                                        {{ $klassSubject['grade_analysis']['A']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-a">
                                                        {{ $klassSubject['grade_analysis']['A']['T'] ?? 0 }}</td>

                                                    <!-- B Grade -->
                                                    <td class="male-cell grade-b">
                                                        {{ $klassSubject['grade_analysis']['B']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-b">
                                                        {{ $klassSubject['grade_analysis']['B']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-b">
                                                        {{ $klassSubject['grade_analysis']['B']['T'] ?? 0 }}</td>

                                                    <!-- C Grade -->
                                                    <td class="male-cell grade-c">
                                                        {{ $klassSubject['grade_analysis']['C']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-c">
                                                        {{ $klassSubject['grade_analysis']['C']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-c">
                                                        {{ $klassSubject['grade_analysis']['C']['T'] ?? 0 }}</td>

                                                    <!-- D Grade -->
                                                    <td class="male-cell grade-d">
                                                        {{ $klassSubject['grade_analysis']['D']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-d">
                                                        {{ $klassSubject['grade_analysis']['D']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-d">
                                                        {{ $klassSubject['grade_analysis']['D']['T'] ?? 0 }}</td>

                                                    <!-- E Grade -->
                                                    <td class="male-cell grade-e">
                                                        {{ $klassSubject['grade_analysis']['E']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-e">
                                                        {{ $klassSubject['grade_analysis']['E']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-e">
                                                        {{ $klassSubject['grade_analysis']['E']['T'] ?? 0 }}</td>

                                                    <!-- U Grade -->
                                                    <td class="male-cell grade-u">
                                                        {{ $klassSubject['grade_analysis']['U']['M'] ?? 0 }}</td>
                                                    <td class="female-cell grade-u">
                                                        {{ $klassSubject['grade_analysis']['U']['F'] ?? 0 }}</td>
                                                    <td class="total-cell grade-u">
                                                        {{ $klassSubject['grade_analysis']['U']['T'] ?? 0 }}</td>

                                                    <!-- AB% (High Achievement) -->
                                                    <td class="male-cell">
                                                        {{ $klassSubject['performance_categories']['AB']['M'] ?? 0 }}%</td>
                                                    <td class="female-cell">
                                                        {{ $klassSubject['performance_categories']['AB']['F'] ?? 0 }}%</td>
                                                    <td class="total-cell">
                                                        {{ $klassSubject['performance_categories']['AB']['T'] ?? 0 }}%</td>

                                                    <!-- ABC% (Pass Rate) -->
                                                    <td class="male-cell">
                                                        {{ $klassSubject['performance_categories']['ABC']['M'] ?? 0 }}%
                                                    </td>
                                                    <td class="female-cell">
                                                        {{ $klassSubject['performance_categories']['ABC']['F'] ?? 0 }}%
                                                    </td>
                                                    <td class="total-cell">
                                                        {{ $klassSubject['performance_categories']['ABC']['T'] ?? 0 }}%
                                                    </td>

                                                    <!-- DEU% (Below Pass Rate) -->
                                                    <td class="male-cell">
                                                        {{ $klassSubject['performance_categories']['DEU']['M'] ?? 0 }}%
                                                    </td>
                                                    <td class="female-cell">
                                                        {{ $klassSubject['performance_categories']['DEU']['F'] ?? 0 }}%
                                                    </td>
                                                    <td class="total-cell">
                                                        {{ $klassSubject['performance_categories']['DEU']['T'] ?? 0 }}%
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @endforeach

                        <!-- Charts for Department Analysis (Only showing OUTPUT rows for chart data) -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div id="departmentBarChart" style="height: 400px;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div id="departmentLineChart" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof echarts === 'undefined') {
                console.error('ECharts library not loaded');
                return;
            }

            const chartData = @json($chart_data);
            const barChartContainer = document.getElementById('departmentBarChart');
            const lineChartContainer = document.getElementById('departmentLineChart');

            if (!barChartContainer || !lineChartContainer) {
                console.error('Chart containers not found');
                return;
            }

            try {
                const departmentBarChart = echarts.init(barChartContainer);
                const departmentBarOption = {
                    title: {
                        text: 'Department Performance Comparison (Optional Subjects - JCE Results)',
                        left: 'center',
                        textStyle: {
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            let result = params[0].name + ' Department<br/>';
                            params.forEach(function(item) {
                                result += item.marker + ' ' + item.seriesName + ': ' + item.value +
                                    '%<br/>';
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: ['High Achievement (AB%)', 'Pass Rate (ABC%)', 'Below Pass Rate (DEU%)'],
                        top: 30
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '15%',
                        top: '25%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: chartData.departments,
                        axisLabel: {
                            rotate: 45,
                            interval: 0
                        }
                    },
                    yAxis: {
                        type: 'value',
                        min: 0,
                        max: 100,
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    },
                    series: [{
                            name: 'High Achievement (AB%)',
                            type: 'bar',
                            data: chartData.ab_percentages,
                            itemStyle: {
                                color: '#28a745'
                            }
                        },
                        {
                            name: 'Pass Rate (ABC%)',
                            type: 'bar',
                            data: chartData.abc_percentages,
                            itemStyle: {
                                color: '#6f42c1'
                            }
                        },
                        {
                            name: 'Below Pass Rate (DEU%)',
                            type: 'bar',
                            data: chartData.deu_percentages,
                            itemStyle: {
                                color: '#dc3545'
                            }
                        }
                    ]
                };

                departmentBarChart.setOption(departmentBarOption);
                const departmentLineChart = echarts.init(lineChartContainer);
                const departmentLineOption = {
                    title: {
                        text: 'Department Grade Distribution Trends (Optional Subjects - JCE Results)',
                        left: 'center',
                        textStyle: {
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(params) {
                            let result = params[0].name + ' Department<br/>';
                            params.forEach(function(item) {
                                result += item.marker + ' ' + item.seriesName + ': ' + item.value +
                                    '%<br/>';
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: ['A Grade %', 'B Grade %', 'C Grade %', 'D Grade %'],
                        top: 30
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '15%',
                        top: '25%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: chartData.departments,
                        axisLabel: {
                            rotate: 45,
                            interval: 0
                        }
                    },
                    yAxis: {
                        type: 'value',
                        min: 0,
                        max: 100,
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    },
                    series: [{
                            name: 'A Grade %',
                            type: 'line',
                            data: chartData.grade_distributions.A,
                            smooth: true,
                            lineStyle: {
                                color: '#28a745',
                                width: 3
                            },
                            itemStyle: {
                                color: '#28a745'
                            },
                            areaStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0,
                                        color: 'rgba(40, 167, 69, 0.3)'
                                    }, {
                                        offset: 1,
                                        color: 'rgba(40, 167, 69, 0.1)'
                                    }]
                                }
                            }
                        },
                        {
                            name: 'B Grade %',
                            type: 'line',
                            data: chartData.grade_distributions.B,
                            smooth: true,
                            lineStyle: {
                                color: '#17a2b8',
                                width: 3
                            },
                            itemStyle: {
                                color: '#17a2b8'
                            },
                            areaStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0,
                                        color: 'rgba(23, 162, 184, 0.3)'
                                    }, {
                                        offset: 1,
                                        color: 'rgba(23, 162, 184, 0.1)'
                                    }]
                                }
                            }
                        },
                        {
                            name: 'C Grade %',
                            type: 'line',
                            data: chartData.grade_distributions.C,
                            smooth: true,
                            lineStyle: {
                                color: '#ffc107',
                                width: 3
                            },
                            itemStyle: {
                                color: '#ffc107'
                            },
                            areaStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0,
                                        color: 'rgba(255, 193, 7, 0.3)'
                                    }, {
                                        offset: 1,
                                        color: 'rgba(255, 193, 7, 0.1)'
                                    }]
                                }
                            }
                        },
                        {
                            name: 'D Grade %',
                            type: 'line',
                            data: chartData.grade_distributions.D,
                            smooth: true,
                            lineStyle: {
                                color: '#dc3545',
                                width: 3
                            },
                            itemStyle: {
                                color: '#dc3545'
                            },
                            areaStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0,
                                        color: 'rgba(220, 53, 69, 0.3)'
                                    }, {
                                        offset: 1,
                                        color: 'rgba(220, 53, 69, 0.1)'
                                    }]
                                }
                            }
                        }
                    ]
                };

                departmentLineChart.setOption(departmentLineOption);
                window.addEventListener('resize', function() {
                    departmentBarChart.resize();
                    departmentLineChart.resize();
                });

            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        });
    </script>
@endsection

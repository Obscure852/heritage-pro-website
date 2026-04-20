@extends('layouts.master')
@section('title')
    Optional Subjects Teacher Analysis Report
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

        .teacher-header {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: left;
            padding: 8px !important;
        }

        .subject-header {
            background-color: #6f42c1;
            color: white;
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

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        .teacher-summary {
            background-color: #e3e4e6;
            font-size: 14px;
            padding: 2px;
            border-radius: 0.3px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 2rem;
        }

        .optional-badge {
            background-color: #6f42c1;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }

        .teacher-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
        }

        .department-tags {
            margin-top: 0.25rem;
        }

        .department-tag {
            background-color: #17a2b8;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 4px;
            display: inline-block;
        }

        @media screen {
            .chart-container {
                display: block !important;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .chart-container {
                display: none !important;
            }

            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 0.1mm;
                font-size: 10px;
            }

            .teacher-class-col {
                max-width: 80px;
                font-size: 10px;
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
            Optional Subjects Teacher Analysis
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
                        Optional Subjects Teacher Analysis Report - {{ $year }}
                    </h6>

                    <!-- Report Summary -->
                    <div class="summary-box">
                        <div class="row">
                            <div class="col-4">
                                <strong>Total Teachers:</strong> {{ $teachers_summary['total_teachers'] ?? 0 }}<br>
                                <strong>Total Classes:</strong> {{ $teachers_summary['total_classes'] ?? 0 }}
                            </div>
                            <div class="col-4">
                                <strong>Academic Year:</strong> {{ $year }}<br>
                                <strong>Report Type:</strong> Optional Subjects by Teacher
                            </div>
                            <div class="col-4">
                                <strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}<br>
                                <strong>Analysis Type:</strong> Teacher-based Grouping
                            </div>
                        </div>
                    </div>

                    @if (empty($teachers_analysis))
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No Data Available:</strong> No optional subjects with external exam results were found
                            for the {{ $year }} graduation year.
                        </div>
                    @else
                        @foreach ($teachers_analysis as $teacherName => $teacherData)
                            <div class="teacher-summary">
                                <strong>{{ $teacherName }}</strong> -
                                Classes: {{ $teacherData['total_classes'] ?? 0 }},
                                Students: {{ $teacherData['total_students'] ?? 0 }}
                            </div>
                            @if (!empty($teacherData['teacher_subjects']))
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <!-- Teacher Header Row -->
                                            <tr>
                                                <th rowspan="3" class="teacher-class-col teacher-header">
                                                    {{ $teacherName }}</th>
                                                <th rowspan="3" class="type-col"><strong>Type</strong></th>
                                                <th colspan="18" class="grade-header">Grade Distribution</th>
                                                <th colspan="9" class="performance-header">Performance Categories</th>
                                            </tr>
                                            <!-- Grade Headers Row -->
                                            <tr>
                                                <th colspan="3" class="grade-header">A</th>
                                                <th colspan="3" class="grade-header">B</th>
                                                <th colspan="3" class="grade-header">C</th>
                                                <th colspan="3" class="grade-header">D</th>
                                                <th colspan="3" class="grade-header">E</th>
                                                <th colspan="3" class="grade-header">U</th>
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
                                            @foreach ($teacherData['teacher_subjects'] as $teacherSubject)
                                                <tr>
                                                    <td class="teacher-class-col">
                                                        @if (($teacherSubject['row_type'] ?? 'OUTPUT') === 'PSLE')
                                                            <strong>{{ $teacherSubject['class_name'] ?? 'Unknown Class' }}
                                                                /PSLE Results /
                                                                {{ $teacherSubject['subject_name'] ?? 'Unknown Subject' }}
                                                                ({{ $teacherSubject['total_students'] ?? 0 }}
                                                                students)
                                                            </strong>
                                                        @else
                                                            <strong>{{ $teacherSubject['class_name'] ?? 'Unknown Class' }}
                                                                -
                                                                {{ $teacherSubject['subject_name'] ?? 'Unknown Subject' }}
                                                                ({{ $teacherSubject['total_students'] ?? 0 }}
                                                                students)</strong>
                                                        @endif
                                                    </td>

                                                    <!-- Type Column -->
                                                    <td class="type-cell">
                                                        @if (($teacherSubject['row_type'] ?? 'OUTPUT') === 'PSLE')
                                                            <span class="type-badge psle-type"><strong>PSLE</strong></span>
                                                        @else
                                                            <span
                                                                class="type-badge output-type"><strong>JCE</strong></span>
                                                        @endif
                                                    </td>

                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                        <td class="male-cell">
                                                            {{ $teacherSubject['grade_analysis'][$grade]['M'] ?? 0 }}</td>
                                                        <td class="female-cell">
                                                            {{ $teacherSubject['grade_analysis'][$grade]['F'] ?? 0 }}</td>
                                                        <td class="total-cell">
                                                            {{ $teacherSubject['grade_analysis'][$grade]['T'] ?? 0 }}</td>
                                                    @endforeach

                                                    @foreach (['AB', 'ABC', 'DEU'] as $category)
                                                        <td class="male-cell">
                                                            {{ $teacherSubject['performance_categories'][$category]['M'] ?? 0 }}%
                                                        </td>
                                                        <td class="female-cell">
                                                            {{ $teacherSubject['performance_categories'][$category]['F'] ?? 0 }}%
                                                        </td>
                                                        <td class="total-cell">
                                                            {{ $teacherSubject['performance_categories'][$category]['T'] ?? 0 }}%
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                        <!-- Charts Section -->
                        @if (isset($chart_data) && !empty($chart_data['teachers']))
                            <div class="row mt-4 no-print">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <div id="teacherBarChart" style="height: 400px; width: 100%;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <div id="teacherLineChart" style="height: 400px; width: 100%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');

            @if (isset($chart_data) && !empty($chart_data['teachers']))
                try {
                    const chartData = @json($chart_data);
                    console.log('Chart Data:', chartData);

                    // Check if containers exist
                    const barChartContainer = document.getElementById('teacherBarChart');
                    const lineChartContainer = document.getElementById('teacherLineChart');

                    if (!barChartContainer || !lineChartContainer) {
                        console.error('Chart containers not found');
                        return;
                    }

                    // Initialize Bar Chart
                    const teacherBarChart = echarts.init(barChartContainer);
                    const teacherBarOption = {
                        title: {
                            text: 'Teacher Performance Comparison (Optional Subjects)',
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
                                let result = params[0].name + '<br/>';
                                params.forEach(function(item) {
                                    result += item.marker + ' ' + item.seriesName + ': ' + item
                                        .value + '%<br/>';
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
                            top: '20%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: chartData.teachers,
                            axisLabel: {
                                rotate: 45,
                                interval: 0,
                                fontSize: 10
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

                    const teacherLineChart = echarts.init(lineChartContainer);
                    const teacherLineOption = {
                        title: {
                            text: 'Teacher Grade Distribution Trends (Optional Subjects)',
                            left: 'center',
                            textStyle: {
                                fontSize: 16,
                                fontWeight: 'bold'
                            }
                        },
                        tooltip: {
                            trigger: 'axis',
                            formatter: function(params) {
                                let result = params[0].name + '<br/>';
                                params.forEach(function(item) {
                                    result += item.marker + ' ' + item.seriesName + ': ' + item
                                        .value + '%<br/>';
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
                            top: '20%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: chartData.teachers,
                            axisLabel: {
                                rotate: 45,
                                interval: 0,
                                fontSize: 10
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
                                }
                            }
                        ]
                    };

                    teacherBarChart.setOption(teacherBarOption, true);
                    teacherLineChart.setOption(teacherLineOption, true);

                    window.addEventListener('resize', function() {
                        teacherBarChart.resize();
                        teacherLineChart.resize();
                    });

                    console.log('Charts initialized successfully');
                } catch (error) {
                    console.error('Error initializing charts:', error);
                }
            @else
                console.log('No chart data available - charts will not be displayed');
            @endif
        });
    </script>
@endsection

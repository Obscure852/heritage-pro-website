@extends('layouts.master')
@section('title')
    Top Performing Classes Analysis
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
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
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.2rem;
        }

        .class-name {
            text-align: left !important;
            font-weight: 500;
            background-color: #f8f9fa;
        }

        .grade-header {
            font-weight: bold;
            font-size: 10px;
        }

        .gender-header {
            font-size: 9px;
            font-weight: normal;
        }

        .percentage-header {
            font-weight: bold;
            font-size: 10px;
        }

        .grade-merit {
            background-color: #e8f5e8;
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

        .percent-high {
            background-color: #d5f5d5;
            font-weight: bold;
        }

        .percent-medium {
            background-color: #ffffd5;
            font-weight: bold;
        }

        .percent-low {
            background-color: #ffd5d5;
        }

        .male-cell {
            background-color: #e3f2fd;
        }

        .female-cell {
            background-color: #fce4ec;
        }

        .total-cell {
            background-color: #f3e5f5;
            font-weight: bold;
        }

        .no-results {
            color: #888;
            font-style: italic;
        }

        .chart-container {
            height: 400px;
            width: 100%;
            margin: 20px 0;
        }

        @media print {
            .printable-charts {
                display: block !important;
                page-break-inside: avoid;
            }

            .chart-container {
                height: 350px;
                /* Slightly smaller for printing */
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                font-size: 8px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .table {
                font-size: 7px;
            }

            .table th,
            .table td {
                padding: 0.2mm;
                font-size: 7px;
            }

            .class-name {
                max-width: 20mm;
                font-size: 6px;
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
            window.location = '{{ route('finals.classes.index') }}';
            }
     ">Back</a>
        @endslot
        @slot('title')
            Top Performing Classes Analysis - {{ $year }}
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="#" onclick="printContent()">
                <i class="bx bx-printer font-size-18 me-1 text-muted"></i>
            </a>
        </div>
    </div>

    <!-- Error/Success Messages -->
    @if (session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Export Failed:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success:</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-start mb-3">
                        Top Performing Classes Analysis - Graduation Year {{ $year }}
                        <br><small class="text-muted">Generated on {{ $generated_at->format('F d, Y \a\t g:i A') }}</small>
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <!-- Main Header Row -->
                                <tr>
                                    <th rowspan="2" class="class-name">Class Name</th>
                                    <th rowspan="2" class="class-name">Teacher</th>
                                    <th colspan="3" class="grade-header">Merit</th>
                                    <th colspan="3" class="grade-header">A</th>
                                    <th colspan="3" class="grade-header">B</th>
                                    <th colspan="3" class="grade-header">C</th>
                                    <th colspan="3" class="grade-header">D</th>
                                    <th colspan="3" class="grade-header">E</th>
                                    <th colspan="3" class="grade-header">U</th>
                                    <th colspan="3" class="percentage-header">MAB%</th>
                                    <th colspan="3" class="percentage-header">ABC%</th>
                                    <th colspan="3" class="percentage-header">DEU%</th>
                                </tr>
                                <!-- Sub Header Row -->
                                <tr>
                                    <!-- Merit -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade A -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade B -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade C -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade D -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade E -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- Grade U -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- AB% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- ABC% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                    <!-- DEU% -->
                                    <th class="gender-header">M</th>
                                    <th class="gender-header">F</th>
                                    <th class="gender-header">T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classes as $index => $class)
                                    <tr @if ($index % 2 == 0) class="table-light" @endif>
                                        <td class="class-name" title="{{ $class['name'] }}">{{ $class['name'] }}</td>
                                        <td class="class-name" title="{{ $class['teacher'] }}">{{ $class['teacher'] }}</td>

                                        <!-- Merit -->
                                        <td class="male-cell grade-merit">{{ $class['grade_analysis']['Merit']['M'] }}</td>
                                        <td class="female-cell grade-merit">{{ $class['grade_analysis']['Merit']['F'] }}
                                        </td>
                                        <td class="total-cell grade-merit">{{ $class['grade_analysis']['Merit']['T'] }}
                                        </td>

                                        <!-- Grade A -->
                                        <td class="male-cell grade-a">{{ $class['grade_analysis']['A']['M'] }}</td>
                                        <td class="female-cell grade-a">{{ $class['grade_analysis']['A']['F'] }}</td>
                                        <td class="total-cell grade-a">{{ $class['grade_analysis']['A']['T'] }}</td>

                                        <!-- Grade B -->
                                        <td class="male-cell grade-b">{{ $class['grade_analysis']['B']['M'] }}</td>
                                        <td class="female-cell grade-b">{{ $class['grade_analysis']['B']['F'] }}</td>
                                        <td class="total-cell grade-b">{{ $class['grade_analysis']['B']['T'] }}</td>

                                        <!-- Grade C -->
                                        <td class="male-cell grade-c">{{ $class['grade_analysis']['C']['M'] }}</td>
                                        <td class="female-cell grade-c">{{ $class['grade_analysis']['C']['F'] }}</td>
                                        <td class="total-cell grade-c">{{ $class['grade_analysis']['C']['T'] }}</td>

                                        <!-- Grade D -->
                                        <td class="male-cell grade-d">{{ $class['grade_analysis']['D']['M'] }}</td>
                                        <td class="female-cell grade-d">{{ $class['grade_analysis']['D']['F'] }}</td>
                                        <td class="total-cell grade-d">{{ $class['grade_analysis']['D']['T'] }}</td>

                                        <!-- Grade E -->
                                        <td class="male-cell grade-e">{{ $class['grade_analysis']['E']['M'] }}</td>
                                        <td class="female-cell grade-e">{{ $class['grade_analysis']['E']['F'] }}</td>
                                        <td class="total-cell grade-e">{{ $class['grade_analysis']['E']['T'] }}</td>

                                        <!-- Grade U -->
                                        <td class="male-cell grade-u">{{ $class['grade_analysis']['U']['M'] }}</td>
                                        <td class="female-cell grade-u">{{ $class['grade_analysis']['U']['F'] }}</td>
                                        <td class="total-cell grade-u">{{ $class['grade_analysis']['U']['T'] }}</td>

                                        <!-- MAB% -->
                                        <td
                                            class="male-cell 
                                            @if ($class['percentage_analysis']['MAB']['M'] >= 50) percent-high
                                            @elseif($class['percentage_analysis']['MAB']['M'] >= 30) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['MAB']['M'] }}%</td>
                                        <td
                                            class="female-cell
                                            @if ($class['percentage_analysis']['MAB']['F'] >= 50) percent-high
                                            @elseif($class['percentage_analysis']['MAB']['F'] >= 30) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['MAB']['F'] }}%</td>
                                        <td
                                            class="total-cell
                                            @if ($class['percentage_analysis']['MAB']['T'] >= 50) percent-high
                                            @elseif($class['percentage_analysis']['MAB']['T'] >= 30) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['MAB']['T'] }}%</td>

                                        <!-- ABC% -->
                                        <td
                                            class="male-cell
                                            @if ($class['percentage_analysis']['ABC']['M'] >= 70) percent-high
                                            @elseif($class['percentage_analysis']['ABC']['M'] >= 50) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['ABC']['M'] }}%</td>
                                        <td
                                            class="female-cell
                                            @if ($class['percentage_analysis']['ABC']['F'] >= 70) percent-high
                                            @elseif($class['percentage_analysis']['ABC']['F'] >= 50) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['ABC']['F'] }}%</td>
                                        <td
                                            class="total-cell
                                            @if ($class['percentage_analysis']['ABC']['T'] >= 70) percent-high
                                            @elseif($class['percentage_analysis']['ABC']['T'] >= 50) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['ABC']['T'] }}%</td>

                                        <!-- DEU% -->
                                        <td
                                            class="male-cell
                                            @if ($class['percentage_analysis']['DEU']['M'] <= 20) percent-high
                                            @elseif($class['percentage_analysis']['DEU']['M'] <= 40) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['DEU']['M'] }}%</td>
                                        <td
                                            class="female-cell
                                            @if ($class['percentage_analysis']['DEU']['F'] <= 20) percent-high
                                            @elseif($class['percentage_analysis']['DEU']['F'] <= 40) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['DEU']['F'] }}%</td>
                                        <td
                                            class="total-cell
                                            @if ($class['percentage_analysis']['DEU']['T'] <= 20) percent-high
                                            @elseif($class['percentage_analysis']['DEU']['T'] <= 40) percent-medium
                                            @else percent-low @endif
                                        ">
                                            {{ $class['percentage_analysis']['DEU']['T'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="26" class="text-center no-results">
                                            No classes with exam results found for the selected year
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mt-4 printable-charts">
                        <div class="col-lg-8">
                            <h6 class="text-start mb-3">Class Performance Analysis</h6>
                            <div id="mixedChart" class="chart-container"></div>
                        </div>
                        <div class="col-lg-4">
                            <h6 class="text-start mb-3">Overall Grade Distribution</h6>
                            <div id="pieChart" class="chart-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function printContent() {
            setTimeout(() => {
                window.print();
            }, 500);
        }

        const classData = @json($classes);

        function initMixedChart() {
            const mixedChart = echarts.init(document.getElementById('mixedChart'));

            const classNames = classData.map(c => c.name.length > 10 ? c.name.substring(0, 10) + '...' : c.name);
            const abcPercentages = classData.map(c => c.percentage_analysis.ABC.T);
            const mabPercentages = classData.map(c => c.percentage_analysis.MAB.T);
            const passStudents = classData.map(c =>
                c.grade_analysis.Merit.T + c.grade_analysis.A.T + c.grade_analysis.B.T + c.grade_analysis.C.T
            );
            const totalStudents = classData.map(c => c.total_with_results);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        crossStyle: {
                            color: '#999'
                        }
                    }
                },
                legend: {
                    data: ['Pass Rate (ABC%)', 'High Performance (MAB%)', 'Pass Students', 'Total Students'],
                    top: 10
                },
                xAxis: [{
                    type: 'category',
                    data: classNames,
                    axisPointer: {
                        type: 'shadow'
                    },
                    axisLabel: {
                        rotate: 45,
                        fontSize: 10
                    }
                }],
                yAxis: [{
                        type: 'value',
                        name: 'Students',
                        position: 'left',
                        axisLabel: {
                            formatter: '{value}'
                        }
                    },
                    {
                        type: 'value',
                        name: 'Percentage',
                        position: 'right',
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    }
                ],
                series: [{
                        name: 'Pass Students',
                        type: 'bar',
                        yAxisIndex: 0,
                        data: passStudents,
                        itemStyle: {
                            color: '#5470c6'
                        }
                    },
                    {
                        name: 'Total Students',
                        type: 'bar',
                        yAxisIndex: 0,
                        data: totalStudents,
                        itemStyle: {
                            color: '#91cc75'
                        }
                    },
                    {
                        name: 'Pass Rate (ABC%)',
                        type: 'line',
                        yAxisIndex: 1,
                        data: abcPercentages,
                        itemStyle: {
                            color: '#fac858'
                        },
                        lineStyle: {
                            width: 3
                        }
                    },
                    {
                        name: 'High Performance (MAB%)',
                        type: 'line',
                        yAxisIndex: 1,
                        data: mabPercentages,
                        itemStyle: {
                            color: '#ee6666'
                        },
                        lineStyle: {
                            width: 3
                        }
                    }
                ]
            };

            mixedChart.setOption(option);

            // Responsive resize
            window.addEventListener('resize', function() {
                mixedChart.resize();
            });
        }

        // Pie Chart - Overall Grade Distribution
        function initPieChart() {
            const pieChart = echarts.init(document.getElementById('pieChart'));

            // Calculate total grades across all classes
            const gradeDistribution = {
                'Merit': 0,
                'A': 0,
                'B': 0,
                'C': 0,
                'D': 0,
                'E': 0,
                'U': 0
            };

            classData.forEach(classItem => {
                Object.keys(gradeDistribution).forEach(grade => {
                    gradeDistribution[grade] += classItem.grade_analysis[grade].T;
                });
            });

            const pieData = Object.entries(gradeDistribution).map(([grade, count]) => ({
                name: grade,
                value: count
            })).filter(item => item.value > 0);

            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'horizontal',
                    bottom: 10,
                    data: pieData.map(item => item.name)
                },
                series: [{
                    name: 'Grade Distribution',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['50%', '45%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: pieData,
                    color: ['#73c0de', '#5470c6', '#91cc75', '#fac858', '#ee6666', '#ff9f7f', '#fc7d02']
                }]
            };

            pieChart.setOption(option);
            window.addEventListener('resize', function() {
                pieChart.resize();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (classData && classData.length > 0) {
                initMixedChart();
                initPieChart();
            }
        });
    </script>
@endsection

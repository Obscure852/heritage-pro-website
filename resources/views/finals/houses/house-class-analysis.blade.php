@extends('layouts.master')
@section('title', 'Overall Grade Analysis Report')
@section('css')
    <style>
        .house-section {
            page-break-inside: avoid;
        }

        .table td,
        .table th {
            font-size: 12px;
            padding: 0.2rem;
        }

        .table thead th {
            font-weight: 600;
            font-size: 12px;
        }

        .bg-success {
            background-color: #28a745 !important;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            color: black;
        }

        .bg-danger {
            background-color: #dc3545 !important;
            color: white;
        }

        .chart-container {
            height: 400px;
            margin-bottom: 30px;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .card-header {
            color: #212529;
            border: none;
        }


        @media print {

            .card-tools,
            .btn-group,
            .charts-section {
                display: none;
            }

            .house-section {
                page-break-after: always;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
            }

            .table th,
            .table td {
                border: 1px solid #dee2e6;
                padding: 8px;
                text-align: left;
            }

            .table th {
                background-color: #f8f9fa;
            }

            .table td {
                background-color: white;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('finals.houses.index') }}">Back</a>
        @endslot
        @slot('title')
            House Class Analysis Report
        @endslot
    @endcomponent
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-end">
                <a href="javascript:void(0)" onclick="printReport()">
                    <i class="bx bx-printer font-size-18 text-muted me-2"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border-1">
                    <div class="card-body">
                        <!-- House Reports -->
                        @foreach ($reportData['houses'] as $house)
                            <div class="house-section mb-5">
                                <div class="mb-3">
                                    <h4 class="mb-0">
                                        {{ $house['house_name'] }} House
                                    </h4>
                                    <small>
                                        <strong>House Head:</strong> {{ $house['house_head'] }} |
                                        <strong>Assistant:</strong> {{ $house['house_assistant'] }}
                                    </small>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle">Class</th>
                                                <th rowspan="2" class="align-middle">Class Teacher</th>

                                                <!-- Grade Headers -->
                                                <th colspan="3" class="text-center">Merit</th>
                                                <th colspan="3" class="text-center">A</th>
                                                <th colspan="3" class="text-center">B</th>
                                                <th colspan="3" class="text-center">C</th>
                                                <th colspan="3" class="text-center">D</th>
                                                <th colspan="3" class="text-center">E</th>
                                                <th colspan="3" class="text-center">U</th>

                                                <!-- Percentage Headers -->
                                                <th colspan="3" class="text-center">MAB%</th>
                                                <th colspan="3" class="text-center">MAC%</th>
                                                <th colspan="3" class="text-center">DEU%</th>
                                            </tr>
                                            <tr>
                                                <!-- Sub-headers for grades -->
                                                @for ($i = 0; $i < 7; $i++)
                                                    <th class="text-center">M</th>
                                                    <th class="text-center">F</th>
                                                    <th class="text-center">T</th>
                                                @endfor

                                                <!-- Sub-headers for percentages -->
                                                @for ($i = 0; $i < 3; $i++)
                                                    <th class="text-center">M</th>
                                                    <th class="text-center">F</th>
                                                    <th class="text-center">T</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($house['classes'] as $class)
                                                <tr>
                                                    <td><strong>{{ $class['class_name'] }}</strong></td>
                                                    <td>{{ $class['class_teacher'] }}</td>

                                                    <!-- Grade Data -->
                                                    @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                        <td class="text-center">{{ $class['grade_analysis'][$grade]['M'] }}
                                                        </td>
                                                        <td class="text-center">{{ $class['grade_analysis'][$grade]['F'] }}
                                                        </td>
                                                        <td class="text-center font-weight-bold">
                                                            {{ $class['grade_analysis'][$grade]['T'] }}</td>
                                                    @endforeach

                                                    <!-- Percentage Data -->
                                                    @foreach (['MAB', 'MAC', 'DEU'] as $category)
                                                        <td class="text-center">{{ $class['percentages'][$category]['M'] }}%
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $class['percentages'][$category]['F'] }}%</td>
                                                        <td class="text-center font-weight-bold">
                                                            {{ $class['percentages'][$category]['T'] }}%</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach

                                            <!-- House Summary Row -->
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="2" class="text-center"><strong>{{ $house['house_name'] }}
                                                        House Total</strong></td>

                                                <!-- Grade Totals -->
                                                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                    <td class="text-center">{{ $house['house_summary'][$grade]['M'] }}</td>
                                                    <td class="text-center">{{ $house['house_summary'][$grade]['F'] }}</td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ $house['house_summary'][$grade]['T'] }}</td>
                                                @endforeach

                                                <!-- Percentage Totals -->
                                                @foreach (['MAB', 'MAC', 'DEU'] as $category)
                                                    <td class="text-center">
                                                        {{ $house['house_summary']['percentages'][$category]['M'] }}%</td>
                                                    <td class="text-center">
                                                        {{ $house['house_summary']['percentages'][$category]['F'] }}%</td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ $house['house_summary']['percentages'][$category]['T'] }}%</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        <!-- Overall Summary -->
                        <div class="mb-3">
                            <h4 class="mb-0">
                                Overall Summary - All Houses
                            </h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">Summary</th>

                                        <!-- Grade Headers -->
                                        <th colspan="3" class="text-center">Merit</th>
                                        <th colspan="3" class="text-center">A</th>
                                        <th colspan="3" class="text-center">B</th>
                                        <th colspan="3" class="text-center">C</th>
                                        <th colspan="3" class="text-center">D</th>
                                        <th colspan="3" class="text-center">E</th>
                                        <th colspan="3" class="text-center">U</th>

                                        <!-- Percentage Headers -->
                                        <th colspan="3" class="text-center">MAB%</th>
                                        <th colspan="3" class="text-center">MAC%</th>
                                        <th colspan="3" class="text-center">DEU%</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                        <!-- Sub-headers -->
                                        @for ($i = 0; $i < 10; $i++)
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-weight-bold">
                                        <td colspan="2" class="text-center"><strong>GRAND TOTAL</strong></td>

                                        <!-- Grade Totals -->
                                        @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                            <td class="text-center">{{ $reportData['overall_summary'][$grade]['M'] }}</td>
                                            <td class="text-center">{{ $reportData['overall_summary'][$grade]['F'] }}</td>
                                            <td class="text-center font-weight-bold">
                                                {{ $reportData['overall_summary'][$grade]['T'] }}</td>
                                        @endforeach

                                        <!-- Percentage Totals -->
                                        @foreach (['MAB', 'MAC', 'DEU'] as $category)
                                            <td class="text-center">
                                                {{ $reportData['overall_summary']['percentages'][$category]['M'] }}%</td>
                                            <td class="text-center">
                                                {{ $reportData['overall_summary']['percentages'][$category]['F'] }}%</td>
                                            <td class="text-center font-weight-bold">
                                                {{ $reportData['overall_summary']['percentages'][$category]['T'] }}%</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="fas fa-chart-line"></i> Visual Analytics
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Overall Grade Distribution -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-title">Overall Grade Distribution</div>
                                    <div id="gradeDistributionChart" class="chart-container"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-title">Gender Distribution by Grade</div>
                                    <div id="genderDistributionChart" class="chart-container"></div>
                                </div>
                            </div>

                            <!-- House Performance Comparison -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="chart-title">House Performance Comparison</div>
                                    <div id="houseComparisonChart" class="chart-container"></div>
                                </div>
                            </div>

                            <!-- Pass Rate Analysis -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-title">Pass Rate by House (MAC%)</div>
                                    <div id="passRateChart" class="chart-container"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-title">Performance Categories Distribution</div>
                                    <div id="performanceCategoriesChart" class="chart-container"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        function printReport() {
            window.print();
        }

        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
            $('.dropdown-item').on('click', function(e) {
                $(this).append(' <i class="fas fa-spinner fa-spin"></i>');
            });

            initializeCharts();
        });

        function initializeCharts() {
            const reportData = @json($reportData);
            initGradeDistributionChart(reportData);
            initGenderDistributionChart(reportData);
            initHouseComparisonChart(reportData);
            initPassRateChart(reportData);
            initPerformanceCategoriesChart(reportData);
        }

        function initGradeDistributionChart(data) {
            const chart = echarts.init(document.getElementById('gradeDistributionChart'));

            const gradeData = [];
            const grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
            const colors = ['#8B5CF6', '#10B981', '#059669', '#34D399', '#F59E0B', '#EF4444', '#DC2626'];

            grades.forEach((grade, index) => {
                gradeData.push({
                    name: grade,
                    value: data.overall_summary[grade].T,
                    itemStyle: {
                        color: colors[index]
                    }
                });
            });

            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Grade Distribution',
                    type: 'pie',
                    radius: '60%',
                    center: ['50%', '50%'],
                    data: gradeData,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };

            chart.setOption(option);
        }

        function initGenderDistributionChart(data) {
            const chart = echarts.init(document.getElementById('genderDistributionChart'));
            const grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
            const maleData = [];
            const femaleData = [];

            grades.forEach(grade => {
                maleData.push(data.overall_summary[grade].M);
                femaleData.push(data.overall_summary[grade].F);
            });

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['Male', 'Female']
                },
                xAxis: {
                    type: 'category',
                    data: grades
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                        name: 'Male',
                        type: 'bar',
                        stack: 'total',
                        data: maleData,
                        itemStyle: {
                            color: '#3B82F6'
                        }
                    },
                    {
                        name: 'Female',
                        type: 'bar',
                        stack: 'total',
                        data: femaleData,
                        itemStyle: {
                            color: '#EC4899'
                        }
                    }
                ]
            };

            chart.setOption(option);
        }

        function initHouseComparisonChart(data) {
            const chart = echarts.init(document.getElementById('houseComparisonChart'));
            const houseNames = [];
            const mabData = [];
            const macData = [];
            const deuData = [];

            data.houses.forEach(house => {
                houseNames.push(house.house_name);
                mabData.push(house.house_summary.percentages.MAB.T);
                macData.push(house.house_summary.percentages.MAC.T);
                deuData.push(house.house_summary.percentages.DEU.T);
            });

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['MAB%', 'MAC%', 'DEU%']
                },
                xAxis: {
                    type: 'category',
                    data: houseNames,
                    axisLabel: {
                        interval: 0,
                        rotate: 45
                    }
                },
                yAxis: {
                    type: 'value',
                    max: 100
                },
                series: [{
                        name: 'MAB%',
                        type: 'bar',
                        data: mabData,
                        itemStyle: {
                            color: '#10B981'
                        }
                    },
                    {
                        name: 'MAC%',
                        type: 'bar',
                        data: macData,
                        itemStyle: {
                            color: '#3B82F6'
                        }
                    },
                    {
                        name: 'DEU%',
                        type: 'bar',
                        data: deuData,
                        itemStyle: {
                            color: '#EF4444'
                        }
                    }
                ]
            };

            chart.setOption(option);
        }

        function initPassRateChart(data) {
            const chart = echarts.init(document.getElementById('passRateChart'));
            const passRateData = [];
            data.houses.forEach(house => {
                passRateData.push({
                    name: house.house_name,
                    value: house.house_summary.percentages.MAC.T
                });
            });

            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c}%'
                },
                series: [{
                    name: 'Pass Rate',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
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
                    data: passRateData
                }]
            };

            chart.setOption(option);
        }

        function initPerformanceCategoriesChart(data) {
            const chart = echarts.init(document.getElementById('performanceCategoriesChart'));

            const categoryData = [{
                    name: 'MAB (Excellent)',
                    value: data.overall_summary.percentages.MAB.counts.T,
                    itemStyle: {
                        color: '#10B981'
                    }
                },
                {
                    name: 'C (Pass)',
                    value: data.overall_summary.C.T,
                    itemStyle: {
                        color: '#34D399'
                    }
                },
                {
                    name: 'DEU (Fail)',
                    value: data.overall_summary.percentages.DEU.counts.T,
                    itemStyle: {
                        color: '#EF4444'
                    }
                }
            ];

            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Performance Categories',
                    type: 'pie',
                    radius: '60%',
                    center: ['50%', '50%'],
                    data: categoryData,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };

            chart.setOption(option);
        }

        window.addEventListener('resize', function() {
            const charts = ['gradeDistributionChart', 'genderDistributionChart', 'houseComparisonChart',
                'passRateChart', 'performanceCategoriesChart'
            ];
            charts.forEach(chartId => {
                const chartElement = document.getElementById(chartId);
                if (chartElement) {
                    const chart = echarts.getInstanceByDom(chartElement);
                    if (chart) {
                        chart.resize();
                    }
                }
            });
        });
    </script>
@endsection

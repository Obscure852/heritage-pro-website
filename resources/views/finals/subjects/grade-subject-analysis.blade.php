@extends('layouts.master')
@section('title')
    Subject Gender Grades Report
@endsection
@section('css')
    <style>
        @media print {
            .card-tools {
                display: none !important;
            }

            table {
                font-size: 7px !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .sticky-col {
                position: static !important;
            }

            .charts-section {
                display: none !important;
            }
        }

        .table th,
        .table td {
            vertical-align: middle;
            white-space: nowrap;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.2rem;
            font-size: 12px;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #f8f9fa !important;
        }

        .table-responsive {
            max-height: 80vh;
            overflow-y: auto;
        }

        .font-weight-bold {
            font-weight: bold !important;
        }


        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-box {
            height: 400px;
            width: 100%;
            min-height: 300px;
            max-width: 100%;
        }

        .full-width-chart {
            height: 500px;
            width: 100%;
            min-height: 400px;
            max-width: 100%;
        }

        .chart-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .control-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .control-btn:hover {
            background: #f0f0f0;
        }

        .control-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 3px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {

            .table-sm th,
            .table-sm td {
                padding: 0.1rem;
                font-size: 0.75rem;
            }

            .card-body {
                padding: 0.5rem;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-box {
                height: 300px;
                min-height: 250px;
            }

            .full-width-chart {
                height: 400px;
                min-height: 300px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('finals.subjects.index') }}">Back</a>
        @endslot
        @slot('title')
            Subject Gender Grades Report
        @endslot
    @endcomponent
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                        <a href="javascript:void(0)" onclick="printReport()">
                            <i class="bx bx-printer font-size-18 text-muted me-2"></i>
                        </a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6 align-items-start">
                                <div class="form-group">
                                    <strong>{{ $school_data->school_name }}</strong><br>
                                    <span>{{ $school_data->physical_address }}</span><br>
                                    <span>{{ $school_data->postal_address }}</span><br>
                                    <span>Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (count($subjects) > 0)
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered table-sm" id="subjectGenderGradesTable"
                                    style="min-width: 1500px; background-color: #f8f9fa;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th rowspan="2" class="align-middle text-center sticky-col"
                                                style="min-width: 150px;">Subject</th>
                                            <th rowspan="2" class="align-middle text-center" style="min-width: 120px;">
                                                Department</th>

                                            <!-- Individual Grades -->
                                            <th colspan="3" class="text-center">A</th>
                                            <th colspan="3" class="text-center">B</th>
                                            <th colspan="3" class="text-center">C</th>
                                            <th colspan="3" class="text-center">D</th>
                                            <th colspan="3" class="text-center">E</th>
                                            <th colspan="3" class="text-center">U</th>

                                            <!-- Performance Categories -->
                                            <th colspan="3" class="text-center">AB%</th>
                                            <th colspan="3" class="text-center">ABC%</th>
                                            <th colspan="3" class="text-center">DEU%</th>

                                            <th rowspan="2" class="align-middle text-center text-white">Total</th>
                                        </tr>
                                        <tr>
                                            <!-- A Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- B Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- C Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- D Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- E Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- U Grade -->
                                            <th class="text-center" style="width: 40px;">M</th>
                                            <th class="text-center" style="width: 40px;">F</th>
                                            <th class="text-center" style="width: 40px;">T</th>

                                            <!-- AB% -->
                                            <th class="text-center" style="width: 50px;">M</th>
                                            <th class="text-center" style="width: 50px;">F</th>
                                            <th class="text-center" style="width: 50px;">T</th>

                                            <!-- ABC% -->
                                            <th class="text-center" style="width: 50px;">M</th>
                                            <th class="text-center" style="width: 50px;">F</th>
                                            <th class="text-center" style="width: 50px;">T</th>

                                            <!-- DEU% -->
                                            <th class="text-center" style="width: 50px;">M</th>
                                            <th class="text-center" style="width: 50px;">F</th>
                                            <th class="text-center" style="width: 50px;">T</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subjects as $index => $subject)
                                            <tr>
                                                <td class="font-weight-bold sticky-col bg-light">
                                                    {{ $subject['subject_name'] }}</td>
                                                <td>{{ $subject['department_name'] }}</td>

                                                <!-- A Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['A']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['A']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['A']['T'] }}</td>

                                                <!-- B Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['B']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['B']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['B']['T'] }}</td>

                                                <!-- C Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['C']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['C']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['C']['T'] }}</td>

                                                <!-- D Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['D']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['D']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['D']['T'] }}</td>

                                                <!-- E Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['E']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['E']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['E']['T'] }}</td>

                                                <!-- U Grade -->
                                                <td class="text-center">{{ $subject['grade_analysis']['U']['M'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['U']['F'] }}</td>
                                                <td class="text-center">{{ $subject['grade_analysis']['U']['T'] }}</td>

                                                <!-- AB% -->
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['AB']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['AB']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['AB']['T'] }}%</td>

                                                <!-- ABC% -->
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['ABC']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['ABC']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['ABC']['T'] }}%</td>

                                                <!-- DEU% -->
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['DEU']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['DEU']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $subject['percentage_categories']['DEU']['T'] }}%</td>

                                                <td class="text-center text-dark">{{ $subject['total_students'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Updated Charts Section -->
                            <div style="padding: 10px;" class="card border-1">
                                <h4 class="text-center mb-4">
                                    <i class="bx bx-bar-chart-alt-2 me-2"></i>
                                    Grade Analysis Visualization
                                </h4>

                                <div class="summary-stats">
                                    <div class="stat-card">
                                        <div class="stat-value">{{ count($subjects) }}</div>
                                        <div class="stat-label">Total Subjects</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-value">{{ $unique_students ?? '-' }}</div>
                                        <div class="stat-label">Total Students</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-value">
                                            @php
                                                $abPercentages = array_map(function ($subject) {
                                                    return $subject['percentage_categories']['AB']['T'];
                                                }, $subjects);
                                                $abAvg =
                                                    count($subjects) > 0
                                                        ? array_sum($abPercentages) / count($subjects)
                                                        : 0;
                                            @endphp
                                            {{ number_format($abAvg, 1) }}%
                                        </div>
                                        <div class="stat-label">Average AB%</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-value">
                                            @php
                                                $abcPercentages = array_map(function ($subject) {
                                                    return $subject['percentage_categories']['ABC']['T'];
                                                }, $subjects);
                                                $abcAvg =
                                                    count($subjects) > 0
                                                        ? array_sum($abcPercentages) / count($subjects)
                                                        : 0;
                                            @endphp
                                            {{ number_format($abcAvg, 1) }}%
                                        </div>
                                        <div class="stat-label">Average ABC%</div>
                                    </div>
                                </div>

                                <div class="chart-controls">
                                    <button class="control-btn active"
                                        onclick="showChart('performance-chart-wrapper', this)">
                                        <i class="bx bx-bar-chart-alt-2 me-1"></i>Performance Overview
                                    </button>
                                    <button class="control-btn" onclick="showChart('grades-chart-wrapper', this)">
                                        <i class="bx bx-pie-chart-alt-2 me-1"></i>Grade Distribution
                                    </button>
                                    <button class="control-btn" onclick="showChart('gender-chart-wrapper', this)">
                                        <i class="bx bx-male-female me-1"></i>Gender Analysis
                                    </button>
                                    <button class="control-btn" onclick="showChart('subjects-chart-wrapper', this)">
                                        <i class="bx bx-trending-up me-1"></i>Subject Comparison
                                    </button>
                                </div>

                                <div class="charts-grid">
                                    <div id="performance-chart-wrapper" class="chart-container chart-wrapper">
                                        <div class="chart-title">AB% and ABC% Performance</div>
                                        <div id="performanceChart" class="chart-box"></div>
                                    </div>

                                    <div id="grades-chart-wrapper" class="chart-container chart-wrapper"
                                        style="display: none;">
                                        <div class="chart-title">Grade Distribution Overview</div>
                                        <div id="gradeDistributionChart" class="chart-box"></div>
                                    </div>
                                </div>

                                <div id="subjects-chart-wrapper" class="chart-container chart-wrapper"
                                    style="display: none;">
                                    <div class="chart-title">Subject Performance Comparison</div>
                                    <div id="subjectComparisonChart" class="full-width-chart"></div>
                                </div>

                                <div id="gender-chart-wrapper" class="chart-container chart-wrapper"
                                    style="display: none;">
                                    <div class="chart-title">Gender Performance Analysis</div>
                                    <div id="genderAnalysisChart" class="full-width-chart"></div>
                                </div>

                                <div id="chart-error" class="alert alert-warning" style="display: none;">
                                    Unable to load charts. Please try refreshing the page or contact support.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No subjects with external exam results found for the academic year {{ $year }}.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const charts = {};

        function printReport() {
            window.print();
        }

        function showChart(chartWrapperId, element) {
            console.log('Showing chart wrapper:', chartWrapperId);
            document.querySelectorAll('.chart-wrapper').forEach(wrapper => {
                wrapper.style.display = 'none';
            });
            const activeWrapper = document.getElementById(chartWrapperId);
            if (activeWrapper) {
                activeWrapper.style.display = 'block';
                Object.values(charts).forEach(chart => chart.resize());
            } else {
                console.error('Chart wrapper not found:', chartWrapperId);
                document.getElementById('chart-error').style.display = 'block';
            }
            document.querySelectorAll('.control-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof echarts === 'undefined') {
                console.error('ECharts library not found. Charts cannot be initialized.');
                document.getElementById('chart-error').style.display = 'block';
                return;
            } else {
                console.log('ECharts is loaded successfully.');
            }

            const subjectDataObj = @json($subjects);
            const subjectData = Object.values(subjectDataObj);

            if (!subjectData || subjectData.length === 0) {
                console.log('No subject data available to generate charts.');
                const chartsSection = document.querySelector('.charts-section');
                if (chartsSection) chartsSection.style.display = 'none';
                document.getElementById('chart-error').style.display = 'block';
                return;
            }

            try {
                setupPerformanceChart(subjectData);
                setupGradeDistributionChart(subjectData);
                setupSubjectComparisonChart(subjectData);
                setupGenderAnalysisChart(subjectData);
            } catch (error) {
                document.getElementById('chart-error').style.display = 'block';
            }
        });

        function setupPerformanceChart(data) {
            try {
                const chartDom = document.getElementById('performanceChart');
                if (!chartDom) {
                    return;
                }

                const labels = data.map(s => s.subject_name || 'Unknown');
                const abData = data.map(s => {
                    const value = parseFloat(s.percentage_categories?.AB?.T);
                    return isNaN(value) ? 0 : value;
                });
                const abcData = data.map(s => {
                    const value = parseFloat(s.percentage_categories?.ABC?.T);
                    return isNaN(value) ? 0 : value;
                });

                charts.performance = echarts.init(chartDom);
                charts.performance.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['AB%', 'ABC%']
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            rotate: 45
                        }
                    },
                    yAxis: {
                        type: 'value',
                        max: 100,
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    },
                    series: [{
                            name: 'AB%',
                            type: 'bar',
                            data: abData,
                            itemStyle: {
                                color: 'rgba(54, 162, 235, 0.6)'
                            }
                        },
                        {
                            name: 'ABC%',
                            type: 'bar',
                            data: abcData,
                            itemStyle: {
                                color: 'rgba(75, 192, 192, 0.6)'
                            }
                        }
                    ]
                });
            } catch (error) {
                console.error('Error in setupPerformanceChart:', error);
            }
        }

        function setupGradeDistributionChart(data) {
            try {
                const chartDom = document.getElementById('gradeDistributionChart');
                if (!chartDom) {
                    return;
                }

                const grades = ['A', 'B', 'C', 'D', 'E', 'U'];
                const gradeData = grades.map(grade => ({
                    name: `Grade ${grade}`,
                    value: data.reduce((sum, subject) => sum + (subject.grade_analysis?.[grade]?.T || 0), 0)
                })).filter(item => item.value > 0);

                charts.grades = echarts.init(chartDom);
                charts.grades.setOption({
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: {c} ({d}%)'
                    },
                    legend: {
                        orient: 'vertical',
                        left: 'left'
                    },
                    series: [{
                        type: 'pie',
                        radius: ['40%', '70%'],
                        data: gradeData,
                        label: {
                            show: true,
                            formatter: '{b}: {d}%'
                        },
                        itemStyle: {
                            color: params => ['#36a2eb', '#4bc0c0', '#ffcd56', '#ff9f40', '#ff6384',
                                '#9966ff'
                            ][params.dataIndex]
                        }
                    }]
                });
                console.log('Grade distribution chart initialized.');
            } catch (error) {
                console.error('Error in setupGradeDistributionChart:', error);
            }
        }

        function setupSubjectComparisonChart(data) {
            try {
                const chartDom = document.getElementById('subjectComparisonChart');
                if (!chartDom) {
                    return;
                }

                const labels = data.map(s => s.subject_name || 'Unknown');
                const grades = ['A', 'B', 'C', 'D', 'E', 'U'];
                const colors = ['#36a2eb', '#4bc0c0', '#ffcd56', '#ff9f40', '#ff6384', '#9966ff'];

                const series = grades.map((grade, index) => ({
                    name: `Grade ${grade}`,
                    type: 'bar',
                    stack: 'total',
                    data: data.map(s => s.grade_analysis?.[grade]?.T || 0),
                    itemStyle: {
                        color: colors[index]
                    }
                }));

                charts.subjects = echarts.init(chartDom);
                charts.subjects.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: grades.map(g => `Grade ${g}`)
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            rotate: 45
                        }
                    },
                    yAxis: {
                        type: 'value',
                        min: 0
                    },
                    series: series
                });
            } catch (error) {
                console.error('Error in setupSubjectComparisonChart:', error);
            }
        }

        function setupGenderAnalysisChart(data) {
            try {
                const chartDom = document.getElementById('genderAnalysisChart');
                if (!chartDom) {
                    return;
                }

                const labels = data.map(s => s.subject_name || 'Unknown');
                const series = [{
                        name: 'Male AB%',
                        type: 'bar',
                        data: data.map(s => {
                            const value = parseFloat(s.percentage_categories?.AB?.M);
                            return isNaN(value) ? 0 : value;
                        }),
                        itemStyle: {
                            color: 'rgba(54, 162, 235, 0.6)'
                        }
                    },
                    {
                        name: 'Female AB%',
                        type: 'bar',
                        data: data.map(s => {
                            const value = parseFloat(s.percentage_categories?.AB?.F);
                            return isNaN(value) ? 0 : value;
                        }),
                        itemStyle: {
                            color: 'rgba(255, 99, 132, 0.6)'
                        }
                    },
                    {
                        name: 'Male ABC%',
                        type: 'line',
                        data: data.map(s => {
                            const value = parseFloat(s.percentage_categories?.ABC?.M);
                            return isNaN(value) ? 0 : value;
                        }),
                        lineStyle: {
                            color: '#36a2eb'
                        },
                        smooth: true
                    },
                    {
                        name: 'Female ABC%',
                        type: 'line',
                        data: data.map(s => {
                            const value = parseFloat(s.percentage_categories?.ABC?.F);
                            return isNaN(value) ? 0 : value;
                        }),
                        lineStyle: {
                            color: '#ff6384'
                        },
                        smooth: true
                    }
                ];
                charts.gender = echarts.init(chartDom);
                charts.gender.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['Male AB%', 'Female AB%', 'Male ABC%', 'Female ABC%']
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            rotate: 45
                        }
                    },
                    yAxis: {
                        type: 'value',
                        max: 100,
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    },
                    series: series
                });
            } catch (error) {
                console.error('Error in setupGenderAnalysisChart:', error);
            }
        }
    </script>
@endsection

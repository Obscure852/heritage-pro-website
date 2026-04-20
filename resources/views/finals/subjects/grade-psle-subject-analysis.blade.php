@extends('layouts.master')
@section('css')
    <style>
        @media print {
            .card-tools {
                display: none !important;
            }

            table {
                font-size: 6px !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .sticky-col {
                position: static !important;
            }

            .chart-section {
                display: none !important;
            }
        }

        .table th,
        .table td {
            vertical-align: middle;
            white-space: nowrap;
            border: 1px solid #dee2e6;
            padding: 0.2rem;
            font-size: 12px;
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

        .font-weight-bold {
            font-weight: bold !important;
        }

        .chart-section {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .chart-box {
            height: 500px;
            width: 100%;
            min-height: 400px;
            max-width: 100%;
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

            .table-responsive {
                max-height: 100vh;
            }

            .chart-box {
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
            Subjects Comparison Report
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
                        @if (count($report_data) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th rowspan="2" class="align-middle text-start sticky-col">Subject</th>
                                            <th rowspan="2" class="align-middle text-center">Type</th>

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

                                            <th rowspan="2" class="align-middle text-center">Total</th>
                                        </tr>
                                        <tr>
                                            <!-- A Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

                                            <!-- B Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

                                            <!-- C Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

                                            <!-- D Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

                                            <!-- E Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

                                            <!-- U Grade -->
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>

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
                                        @foreach ($report_data as $row)
                                            <tr>
                                                @if ($row['is_first_row'])
                                                    <td class="sticky-col bg-light align-middle font-weight-bold"
                                                        rowspan="{{ $row['rowspan'] }}">
                                                        {{ $row['subject_name'] }}
                                                    </td>
                                                @endif

                                                <td class="text-center font-weight-bold">
                                                    {{ $row['level'] }}
                                                </td>

                                                <!-- A Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['A']['M'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['A']['F'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['A']['T'] }}</td>

                                                <!-- B Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['B']['M'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['B']['F'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['B']['T'] }}</td>

                                                <!-- C Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['C']['M'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['C']['F'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['C']['T'] }}</td>

                                                <!-- D Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['D']['M'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['D']['F'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['D']['T'] }}</td>

                                                <!-- E Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['E']['M'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['E']['F'] }}</td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['E']['T'] }}
                                                </td>

                                                <!-- U Grade -->
                                                <td class="text-center">{{ $row['data']['grade_analysis']['U']['M'] }}
                                                </td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['U']['F'] }}
                                                </td>
                                                <td class="text-center">{{ $row['data']['grade_analysis']['U']['T'] }}
                                                </td>

                                                <!-- AB% -->
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['AB']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['AB']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['AB']['T'] }}%</td>

                                                <!-- ABC% -->
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['ABC']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['ABC']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['ABC']['T'] }}%</td>

                                                <!-- DEU% -->
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['DEU']['M'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['DEU']['F'] }}%</td>
                                                <td class="text-center">
                                                    {{ $row['data']['percentage_categories']['DEU']['T'] }}%</td>

                                                <td class="text-center">{{ $row['data']['total_students'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!-- Chart Section -->
                                <div class="chart-section">
                                    <h4 class="text-center mb-4">
                                        <i class="bx bx-bar-chart-alt-2 me-2"></i>
                                        Subject Performance Comparison
                                    </h4>
                                    <div class="chart-container">
                                        <div class="chart-title">AB%, ABC%, and Total Students by Subject</div>
                                        <div id="subjectComparisonChart" class="chart-box"></div>
                                    </div>
                                    <div id="chart-error" class="alert alert-warning" style="display: none;">
                                        Unable to load chart. Please try refreshing the page or contact support.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No subjects with both PSLE and JCE results found for the academic year {{ $year }}.
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
        function printReport() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof echarts === 'undefined') {
                console.error('ECharts library not found. Chart cannot be initialized.');
                document.getElementById('chart-error').style.display = 'block';
                return;
            } else {
                console.log('ECharts is loaded successfully.');
            }

            const reportData = @json($report_data);
            console.log('Raw Report Data:', reportData);

            if (!reportData || reportData.length === 0) {
                console.log('No report data available to generate chart.');
                const chartSection = document.querySelector('.chart-section');
                if (chartSection) chartSection.style.display = 'none';
                document.getElementById('chart-error').style.display = 'block';
                return;
            }

            try {
                setupSubjectComparisonChart(reportData);
                console.log('Subject comparison chart initialized successfully with ECharts.');
            } catch (error) {
                console.error('An error occurred during chart initialization:', error);
                document.getElementById('chart-error').style.display = 'block';
            }
        });

        function setupSubjectComparisonChart(data) {
            try {
                const chartDom = document.getElementById('subjectComparisonChart');
                if (!chartDom) {
                    console.error('Element for subjectComparisonChart not found.');
                    return;
                }

                // Group data by subject_name, combining levels (e.g., PSLE, JCE)
                const subjectMap = {};
                data.forEach(row => {
                    const subjectName = row.subject_name || 'Unknown';
                    if (!subjectMap[subjectName]) {
                        subjectMap[subjectName] = {
                            ab: 0,
                            abc: 0,
                            total_students: 0,
                            count: 0
                        };
                    }
                    subjectMap[subjectName].ab += parseFloat(row.data?.percentage_categories?.AB?.T || 0);
                    subjectMap[subjectName].abc += parseFloat(row.data?.percentage_categories?.ABC?.T || 0);
                    subjectMap[subjectName].total_students += parseFloat(row.data?.total_students || 0);
                    subjectMap[subjectName].count += 1;
                });

                // Convert to array and average where multiple levels exist
                const subjects = Object.keys(subjectMap).map(subjectName => ({
                    subject_name: subjectName,
                    ab: subjectMap[subjectName].ab / subjectMap[subjectName].count,
                    abc: subjectMap[subjectName].abc / subjectMap[subjectName].count,
                    total_students: subjectMap[subjectName].total_students
                }));

                console.log('Processed Subject Data:', subjects);

                const labels = subjects.map(s => s.subject_name);
                const abData = subjects.map(s => isNaN(s.ab) ? 0 : s.ab);
                const abcData = subjects.map(s => isNaN(s.abc) ? 0 : s.abc);
                const totalStudentsData = subjects.map(s => isNaN(s.total_students) ? 0 : s.total_students);

                const chart = echarts.init(chartDom);
                chart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['AB%', 'ABC%', 'Total Students']
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            rotate: 45
                        }
                    },
                    yAxis: [{
                            type: 'value',
                            name: 'Percentage',
                            max: 100,
                            axisLabel: {
                                formatter: '{value}%'
                            }
                        },
                        {
                            type: 'value',
                            name: 'Total Students',
                            position: 'right'
                        }
                    ],
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
                        },
                        {
                            name: 'Total Students',
                            type: 'line',
                            yAxisIndex: 1,
                            data: totalStudentsData,
                            lineStyle: {
                                color: '#ff6384'
                            },
                            smooth: true
                        }
                    ]
                });

                // Resize chart on window resize
                window.addEventListener('resize', () => chart.resize());
                console.log('Subject comparison chart initialized.');
            } catch (error) {
                console.error('Error in setupSubjectComparisonChart:', error);
            }
        }
    </script>
@endsection

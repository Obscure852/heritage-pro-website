@extends('layouts.master')
@section('title')
    Subjects Analysis
@endsection
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table td:first-child,
        .table th:first-child {
            text-align: left;
        }

        @media print {
            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
            }

            .table {
                font-size: 10px;
            }

            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Subjects Analysis
        @endslot
    @endcomponent
    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size:20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if ($test->type == 'CA')
                            <h5>{{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Subjects
                                Analysis
                            </h5>
                        @else
                            <h5>{{ $test->grade->name ?? 'Grade' }} - End of Term Subjects Analysis</h5>
                        @endif
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="text-align:left;vertical-align:middle;">
                                                <strong>Subject</strong>
                                            </th>
                                            <th colspan="3"><strong>A*</strong></th>
                                            <th colspan="3"><strong>A</strong></th>
                                            <th colspan="3"><strong>B</strong></th>
                                            <th colspan="3"><strong>C</strong></th>
                                            <th colspan="3"><strong>ABC%</strong></th>
                                            <th colspan="3"><strong>ABC%</strong></th>
                                            <th colspan="3"><strong>Students</strong></th>
                                        </tr>
                                        <tr>
                                            {{-- A* --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- A --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- B --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- C --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- ABC --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- ABC% --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                            {{-- Students --}}
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subjectPerformance as $subjectName => $counts)
                                            <tr>
                                                <td style="text-align:left;">{{ $subjectName }}</td>
                                                {{-- A* --}}
                                                <td>{{ $counts['A*']['M'] }}</td>
                                                <td>{{ $counts['A*']['F'] }}</td>
                                                <td>{{ $counts['A*']['M'] + $counts['A*']['F'] }}</td>
                                                {{-- A --}}
                                                <td>{{ $counts['A']['M'] }}</td>
                                                <td>{{ $counts['A']['F'] }}</td>
                                                <td>{{ $counts['A']['M'] + $counts['A']['F'] }}</td>
                                                {{-- B --}}
                                                <td>{{ $counts['B']['M'] }}</td>
                                                <td>{{ $counts['B']['F'] }}</td>
                                                <td>{{ $counts['B']['M'] + $counts['B']['F'] }}</td>
                                                {{-- C --}}
                                                <td>{{ $counts['C']['M'] }}</td>
                                                <td>{{ $counts['C']['F'] }}</td>
                                                <td>{{ $counts['C']['M'] + $counts['C']['F'] }}</td>
                                                {{-- ABC --}}
                                                <td>{{ $counts['ABC']['M'] }}</td>
                                                <td>{{ $counts['ABC']['F'] }}</td>
                                                <td>{{ $counts['ABC']['M'] + $counts['ABC']['F'] }}</td>
                                                {{-- ABC% --}}
                                                <td>{{ $counts['ABC%']['M'] }}%</td>
                                                <td>{{ $counts['ABC%']['F'] }}%</td>
                                                <td>{{ number_format((($counts['ABC']['M'] + $counts['ABC']['F']) / max($counts['total']['M'] + $counts['total']['F'], 1)) * 100, 1) }}%
                                                </td>
                                                {{-- Students --}}
                                                <td>{{ $counts['total']['M'] }}</td>
                                                <td>{{ $counts['total']['F'] }}</td>
                                                <td>{{ $counts['total']['M'] + $counts['total']['F'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:bold;">
                                            <td style="text-align:left;">TOTAL</td>
                                            {{-- Calculate totals for each column --}}
                                            @php
                                                $totals = [
                                                    'A*' => ['M' => 0, 'F' => 0],
                                                    'A' => ['M' => 0, 'F' => 0],
                                                    'B' => ['M' => 0, 'F' => 0],
                                                    'C' => ['M' => 0, 'F' => 0],
                                                    'ABC' => ['M' => 0, 'F' => 0],
                                                    'students' => ['M' => 0, 'F' => 0],
                                                ];

                                                foreach ($subjectPerformance as $counts) {
                                                    $totals['A*']['M'] += $counts['A*']['M'];
                                                    $totals['A*']['F'] += $counts['A*']['F'];
                                                    $totals['A']['M'] += $counts['A']['M'];
                                                    $totals['A']['F'] += $counts['A']['F'];
                                                    $totals['B']['M'] += $counts['B']['M'];
                                                    $totals['B']['F'] += $counts['B']['F'];
                                                    $totals['C']['M'] += $counts['C']['M'];
                                                    $totals['C']['F'] += $counts['C']['F'];
                                                    $totals['ABC']['M'] += $counts['ABC']['M'];
                                                    $totals['ABC']['F'] += $counts['ABC']['F'];
                                                    $totals['students']['M'] += $counts['total']['M'];
                                                    $totals['students']['F'] += $counts['total']['F'];
                                                }
                                            @endphp
                                            {{-- A* --}}
                                            <td>{{ $totals['A*']['M'] }}</td>
                                            <td>{{ $totals['A*']['F'] }}</td>
                                            <td>{{ $totals['A*']['M'] + $totals['A*']['F'] }}</td>
                                            {{-- A --}}
                                            <td>{{ $totals['A']['M'] }}</td>
                                            <td>{{ $totals['A']['F'] }}</td>
                                            <td>{{ $totals['A']['M'] + $totals['A']['F'] }}</td>
                                            {{-- B --}}
                                            <td>{{ $totals['B']['M'] }}</td>
                                            <td>{{ $totals['B']['F'] }}</td>
                                            <td>{{ $totals['B']['M'] + $totals['B']['F'] }}</td>
                                            {{-- C --}}
                                            <td>{{ $totals['C']['M'] }}</td>
                                            <td>{{ $totals['C']['F'] }}</td>
                                            <td>{{ $totals['C']['M'] + $totals['C']['F'] }}</td>
                                            {{-- ABC --}}
                                            <td>{{ $totals['ABC']['M'] }}</td>
                                            <td>{{ $totals['ABC']['F'] }}</td>
                                            <td>{{ $totals['ABC']['M'] + $totals['ABC']['F'] }}</td>
                                            {{-- ABC% --}}
                                            <td>{{ $totals['students']['M'] > 0 ? number_format(($totals['ABC']['M'] / $totals['students']['M']) * 100, 1) : 0 }}%
                                            </td>
                                            <td>{{ $totals['students']['F'] > 0 ? number_format(($totals['ABC']['F'] / $totals['students']['F']) * 100, 1) : 0 }}%
                                            </td>
                                            <td>{{ $totals['students']['M'] + $totals['students']['F'] > 0 ? number_format((($totals['ABC']['M'] + $totals['ABC']['F']) / ($totals['students']['M'] + $totals['students']['F'])) * 100, 1) : 0 }}%
                                            </td>
                                            {{-- Students --}}
                                            <td>{{ $totals['students']['M'] }}</td>
                                            <td>{{ $totals['students']['F'] }}</td>
                                            <td>{{ $totals['students']['M'] + $totals['students']['F'] }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row no-print">
                        <div class="col-12">
                            <div id="subjectPerformanceChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        var subjectPerformance = @json($subjectPerformance);
        var subjects = Object.keys(subjectPerformance);
        var grades = ['A*', 'A', 'B', 'C'];

        var gradeData = grades.map(function(grade) {
            return subjects.map(function(subject) {
                return subjectPerformance[subject][grade]['M'] + subjectPerformance[subject][grade]['F'];
            });
        });

        var abcPercentages = subjects.map(subject => {
            var totalStudents = subjectPerformance[subject]['total']['M'] + subjectPerformance[subject]['total'][
                'F'
            ];
            var abcTotal = subjectPerformance[subject]['ABC']['M'] + subjectPerformance[subject]['ABC']['F'];
            return totalStudents > 0 ? (abcTotal / totalStudents * 100).toFixed(1) : 0;
        });

        var chartDom = document.getElementById('subjectPerformanceChart');
        var myChart = echarts.init(chartDom);
        var option;

        option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {},
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: subjects
            },
            yAxis: [{
                    type: 'value',
                    name: 'Count'
                },
                {
                    type: 'value',
                    name: 'Percentage',
                    axisLabel: {
                        formatter: '{value} %'
                    }
                }
            ],
            series: [{
                    name: 'A*',
                    type: 'bar',
                    stack: 'grades',
                    data: gradeData[0]
                },
                {
                    name: 'A',
                    type: 'bar',
                    stack: 'grades',
                    data: gradeData[1]
                },
                {
                    name: 'B',
                    type: 'bar',
                    stack: 'grades',
                    data: gradeData[2]
                },
                {
                    name: 'C',
                    type: 'bar',
                    stack: 'grades',
                    data: gradeData[3]
                },
                {
                    name: 'ABC%',
                    type: 'line',
                    yAxisIndex: 1,
                    data: abcPercentages
                }
            ]
        };

        option && myChart.setOption(option);

        function printContent() {
            window.print();
        }
    </script>
@endsection

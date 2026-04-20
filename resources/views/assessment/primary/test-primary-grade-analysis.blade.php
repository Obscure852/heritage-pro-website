@extends('layouts.master')
@section('title')
    Exam Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Class Term Analysis
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: none;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .card {
                box-shadow: none;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: relative;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                /* Allow horizontal scrolling for wide content */
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-body {
                margin: 0 auto;
                padding: 0;
            }

            .printable .table {
                width: 100%;
                overflow-x: auto;
                /* Allow horizontal scrolling for wide tables */
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }
        }

    </style>
    <div class="row">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a href="{{ route('assessment.export-grade-analysis',['classId' => $klass->id,'type' => $type,'sequenceId' => $sequenceId]) }}">
                <i style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                class="bx bx-export text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="center">Grade Distribution</h5>
                    <table class="table table-bordered table-striped grade-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Grade</th>
                                <th colspan="2">A</th>
                                <th colspan="2">B</th>
                                <th colspan="2">C</th>
                                <th rowspan="2">ABC(%)</th>
                                <th rowspan="2">ABCD(%)</th>
                            </tr>
                            <tr>
                                <th>M</th>
                                <th>F</th>
                                <th>M</th>
                                <th>F</th>
                                <th>M</th>
                                <th>F</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalStudents = count($allStudentData);
                                $abcTotal = $gradeCombinationsCounts['ABC']['M'] + $gradeCombinationsCounts['ABC']['F'];
                                $abcdTotal =
                                    $gradeCombinationsCounts['ABCD']['M'] + $gradeCombinationsCounts['ABCD']['F'];
                            @endphp
                            <tr>
                                <td>Total</td>
                                <td>{{ $gradeCounts['A']['M'] }}</td>
                                <td>{{ $gradeCounts['A']['F'] }}</td>
                                <td>{{ $gradeCounts['B']['M'] }}</td>
                                <td>{{ $gradeCounts['B']['F'] }}</td>
                                <td>{{ $gradeCounts['C']['M'] }}</td>
                                <td>{{ $gradeCounts['C']['F'] }}</td>
                                <td>{{ number_format(($abcTotal / max($totalStudents, 1)) * 100, 2) }}%</td>
                                <td>{{ number_format(($abcdTotal / max($totalStudents, 1)) * 100, 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <h5>{{ $klass->grade->name . ' Class Monthly Analysis' }}</h5>
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Gender</th>
                                @foreach ($subjects as $subject)
                                    <th style="text-align: center;" colspan="2">{{ $subject ?? '' }}
                                    </th>
                                @endforeach
                                <th>Total Score</th>
                                <th>Average</th>
                                <th>Grade</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allStudentData as $data)
                                <tr>
                                    <td>{{ $data['studentName'] }}</td>
                                    <td>{{ $data['gender'] }}</td>
                                    @foreach ($data['scores'] as $subjectId => $score)
                                        <td>{{ $score['score'] }}</td>
                                        <td>{{ $score['grade'] }}</td>
                                    @endforeach
                                    <td>{{ $data['totalScore'] }}</td>
                                    <td>{{ number_format($data['averageScore'], 2) }}</td>
                                    <td>{{ $data['overallGrade'] }}</td>
                                    <td>{{ $data['position'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <br>
                    <div style="width:75%;">
                        <div id="gradeDistributionChart" style="width: 100%;height:600px;"></div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <!-- pristine js -->
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/echarts/echarts.min.js') }}"></script>

    <script>
        function printContent() {
            window.print();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const dom = document.getElementById('gradeDistributionChart');
            const myChart = echarts.init(dom);
            const gradeCounts = @json($gradeCounts);

            const labels = Object.keys(gradeCounts);
            const maleData = labels.map(label => gradeCounts[label]['M']);
            const femaleData = labels.map(label => gradeCounts[label]['F']);

            const option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['Male', 'Female']
                },
                toolbox: {
                    show: true,
                    feature: {
                        dataZoom: {
                            yAxisIndex: 'none'
                        },
                        dataView: {
                            readOnly: false
                        },
                        magicType: {
                            type: ['line', 'bar']
                        },
                        restore: {},
                        saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: labels
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}'
                    }
                },
                series: [{
                        name: 'Male',
                        type: 'line',
                        data: maleData,
                        emphasis: {
                            focus: 'series'
                        },
                        smooth: true
                    },
                    {
                        name: 'Female',
                        type: 'line',
                        data: femaleData,
                        emphasis: {
                            focus: 'series'
                        },
                        smooth: true
                    }
                ]
            };

            myChart.setOption(option);
        });
    </script>
@endsection

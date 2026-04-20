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
                width: 1000px;
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
            <a href="{{ route('assessment.export-class-region-analysis',['classId' => $klass->id]) }}">
                <i style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                    class="bx bx-export text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
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
                    <h5>{{ $school_data->region ?? '' }} Result Analysis Report - For Year ( {{ $klass->year ?? '' }} )</h5>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Subjects</th>
                                <th colspan="3">Candidates Numbers</th>
                                <th colspan="3">A</th>
                                <th colspan="3">B</th>
                                <th colspan="3">C</th>
                                <th colspan="3">D</th>
                                <th colspan="3">E</th>
                                <th colspan="3">U</th>
                                <th colspan="3">AB%</th>
                                <th colspan="3">ABC%</th>
                                <th colspan="3">DEU%</th>
                            </tr>
                            <tr>
                                <!-- Sub-headers for M, F, T under each main column -->
                                @for ($i = 0; $i < 10; $i++)
                                    <th>M</th>
                                    <th>F</th>
                                    <th>T</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjectPerformance as $subject => $data)
                                <tr>
                                    <td>{{ $subject }}</td>
                                    <td>{{ $data['Candidates']['M'] }}</td>
                                    <td>{{ $data['Candidates']['F'] }}</td>
                                    <td>{{ $data['Candidates']['T'] }}</td>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                        <td>{{ $data[$grade]['M'] }}</td>
                                        <td>{{ $data[$grade]['F'] }}</td>
                                        <td>{{ $data[$grade]['T'] }}</td>
                                    @endforeach
                                    @foreach (['AB%', 'ABC%', 'DEU%'] as $percentage)
                                        <td>{{ $data[$percentage]['M'] }}</td>
                                        <td>{{ $data[$percentage]['F'] }}</td>
                                        <td>{{ $data[$percentage]['T'] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <div style="width: 100%;margin: auto;">
                        <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
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
            var chartDom = document.getElementById('gradeDistributionChart');
            var myChart = echarts.init(chartDom);

            // Get data from Laravel Blade
            var subjectData = @json($subjectPerformance);

            var labels = Object.keys(subjectData);
            var gradeTotals = [];
            var abcPercents = [];
            var deuPercents = [];

            labels.forEach(function(subject) {
                gradeTotals.push(subjectData[subject]['Candidates']['T']);
                abcPercents.push(subjectData[subject]['ABC%']['T']);
                deuPercents.push(subjectData[subject]['DEU%']['T']);
            });

            var option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['Total Candidates', 'ABC%', 'DEU%']
                },
                xAxis: [{
                    type: 'category',
                    data: labels,
                    axisTick: {
                        alignWithLabel: true
                    }
                }],
                yAxis: [{
                        type: 'value',
                        name: 'Total Count',
                        position: 'left',
                        axisLine: {
                            show: true
                        }
                    },
                    {
                        type: 'value',
                        name: 'Percentage',
                        position: 'right',
                        axisLine: {
                            show: true
                        },
                        min: 0,
                        max: 100
                    }
                ],
                series: [{
                        name: 'Total Candidates',
                        type: 'bar',
                        data: gradeTotals
                    },
                    {
                        name: 'ABC%',
                        type: 'line',
                        yAxisIndex: 1,
                        data: abcPercents
                    },
                    {
                        name: 'DEU%',
                        type: 'line',
                        yAxisIndex: 1,
                        data: deuPercents
                    }
                ]
            };

            myChart.setOption(option);
        });
    </script>
@endsection

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
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
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
                    <h5 class="text-start">{{ $klass->grade->name ?? '' }} Grade Performance Analysis</h5>
                    <table class="table table-sm table-striped table-bordered mt-3">
                        <thead>
                            <tr style="text-align: center">
                                @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                    <th colspan="3" class="header-cell">{{ $grade }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                    <th class="header-cell">M</th>
                                    <th class="header-cell">F</th>
                                    <th class="header-cell">{{ $grade }}%</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                    <td>{{ $gradeDistributions[$grade]['M'] }}</td>
                                    <td>{{ $gradeDistributions[$grade]['F'] }}</td>
                                    <td>{{ round($gradeDistributions[$grade . '%']['M'], 1) }}% /
                                        {{ round($gradeDistributions[$grade . '%']['F'], 1) }}%</td>
                                @endforeach
                            </tr>
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
            var gradeDistributions = @json($gradeDistributions);

            var labels = ['A%', 'B%', 'C%', 'D%', 'E%', 'AB%', 'ABC%', 'DE%'];
            var dataMale = [];
            var dataFemale = [];

            labels.forEach(function(label) {
                dataMale.push(gradeDistributions[label]['M']);
                dataFemale.push(gradeDistributions[label]['F']);
            });

            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['Male', 'Female']
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: labels
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value} %'
                    },
                    min: 0,
                    max: 100 // As percentages
                },
                series: [{
                        name: 'Male',
                        type: 'line',
                        data: dataMale,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 8,
                        lineStyle: {
                            width: 3
                        }
                    },
                    {
                        name: 'Female',
                        type: 'line',
                        data: dataFemale,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 8,
                        lineStyle: {
                            width: 3
                        }
                    }
                ]
            };

            myChart.setOption(option);
        });
    </script>
@endsection

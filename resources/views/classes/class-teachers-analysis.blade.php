@extends('layouts.master')
@section('title')
    Academic Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}"> Back </a>
        @endslot
        @slot('title')
            Class List Analysis
        @endslot
    @endcomponent
    <style>
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
                width: 750px;
                overflow-x: auto;
            }

            .printable #main {
                display: none;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }
        }
    </style>
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer font-size-18 text-muted"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-m12">
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
                    <div class="report-card">
                        <div class="row">
                            <h5 class="text-muted">Class Teacher Allocations</h5>
                            <div class="col-md-12 col-lg-12">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Classes</th>
                                            <th rowspan="2">Class Teacher</th>
                                            <th class="text-center" colspan="3">No. of Students</th>
                                            <!-- Use colspan to group the student counts -->
                                        </tr>
                                        <tr>
                                            <!-- These are sub-headers for the No. of Students column -->
                                            <th>M</th>
                                            <th>F</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($classes as $class)
                                            <tr>
                                                <td>{{ $class->name }}</td>
                                                <td>{{ $class->teacher->fullName ?? 'N/A' }}</td>
                                                <td>{{ $class->male_count }}</td>
                                                <td>{{ $class->female_count }}</td>
                                                <td>{{ $class->total_students }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="main" style="width: 100%;height:400px;"></div>
                            </div>
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
            window.print();
        }

        var chartDom = document.getElementById('main');
        var myChart = echarts.init(chartDom);
        var option;

        option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: ['Males', 'Females', 'Total']
            },
            xAxis: {
                type: 'category',
                data: @json($classes->pluck('name'))
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                    name: 'Males',
                    data: @json($classes->pluck('male_count')),
                    type: 'bar'
                },
                {
                    name: 'Females',
                    data: @json($classes->pluck('female_count')),
                    type: 'bar'
                },
                {
                    name: 'Total',
                    data: @json($classes->pluck('total_students')),
                    type: 'line',
                    smooth: true
                }
            ]
        };
        myChart.setOption(option);
    </script>
@endsection

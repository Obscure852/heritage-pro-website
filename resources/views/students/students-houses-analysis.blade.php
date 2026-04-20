@extends('layouts.master')
@section('title')
    Houses Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}">Back</a>
        @endslot
        @slot('title')
            House List Analysis
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
        }

        #main {
            width: 100%;
            height: auto;
            min-height: 300px;
        }

        @media (max-width: 768px) {
            #main {
                min-height: 250px;
            }
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
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-12">
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
                            <h5>Houses List</h5>
                            <div class="col-12">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <td rowspan="2">#</td>
                                            <th rowspan="2">Name</th>
                                            <th rowspan="2">Head</th>
                                            <th rowspan="2">Assistant</th>
                                            <th colspan="3">No. Of Students</th>
                                        </tr>
                                        <tr>
                                            <!-- Sub-headers for the student count columns -->
                                            <th>M</th> <!-- Male students column -->
                                            <th>F</th> <!-- Female students column -->
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($houses as $index => $house)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $house->name ?? 'N/A' }}</td>
                                                <td>{{ $house->houseHead->fullName ?? 'N/A' }}</td>
                                                <td>{{ $house->houseAssistant->fullName ?? 'N/A' }}</td>
                                                <td>{{ $house->males_count ?? '0' }}</td> <!-- Male students count -->
                                                <td>{{ $house->females_count ?? '0' }}</td> <!-- Female students count -->
                                                <td>{{ $house->students_count ?? '0' }}</td> <!-- Total students count -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-12">
                                <div id="main"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        function printContent() {
            window.print();
        }

        var myChart = echarts.init(document.getElementById('main'));

        var option = {
            title: {
                text: 'Students Per House',
                left: 'center'
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b} : {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
            },
            series: [{
                    name: 'Total Students',
                    type: 'pie',
                    radius: ['50%', '70%'],
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '30',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: @json($chartData),
                },
                {
                    name: 'Gender Distribution',
                    type: 'pie',
                    radius: ['30%', '45%'],
                    label: {
                        show: true,
                        position: 'inner'
                    },
                    data: @json($genderData),
                }
            ]
        };

        myChart.setOption(option);
    </script>
@endsection

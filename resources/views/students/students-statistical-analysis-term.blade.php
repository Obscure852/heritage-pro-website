@extends('layouts.master')
@section('title')
    Students Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Students Statistical Analysis List
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
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
                width: 750px;
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
            <a href="{{ route('students.class-statistical-analysis') }}">
                <i style="font-size: 18px;margin-bottom:10px;margin-right:5px;cursor:pointer;"
                    class="bx bx-export text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    @if ($klasses->isNotEmpty())
                        @php
                            $boys = 0;
                            $girls = 0;
                        @endphp
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Class</th>
                                    <th rowspan="2">Class Teacher</th>
                                    <th colspan="3">Totals</th>
                                </tr>
                                <tr>
                                    <th>B</th>
                                    <th>G</th>
                                    <th>T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($klasses as $index => $klass)
                                    <tr>
                                        <td>{{ $index }}</td>
                                        <td>{{ $klass->name }}</td>
                                        <td>{{ $klass->teacher->full_name ?? '' }}</td>

                                        @php
                                            $boys += $klass->boys_count;
                                            $girls += $klass->girls_count;
                                        @endphp

                                        <td>{{ $klass->boys_count }}</td>
                                        <td>{{ $klass->girls_count }}</td>
                                        <td>{{ intval($klass->boys_count) + intval($klass->girls_count) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="3" style="text-align: end"><strong>Totals: </strong></td>
                                    <td>{{ intval($boys) }}</td>
                                    <td>{{ intval($girls) }}</td>
                                    <td>{{ intval($boys) + intval($girls) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <div id="combinedChart" style="width: 100%; height: 400px;"></div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const classNames = @json($classNames);
            const boysCounts = @json($boysCounts);
            const girlsCounts = @json($girlsCounts);

            const chart = echarts.init(document.getElementById('combinedChart'));

            const option = {
                title: {
                    text: 'Students by Class',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['Boys', 'Girls'],
                    bottom: 0
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: classNames,
                    axisLabel: {
                        rotate: 45,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value',
                    minInterval: 1
                },
                series: [
                    {
                        name: 'Boys',
                        type: 'bar',
                        data: boysCounts,
                        itemStyle: {
                            color: 'rgba(54, 162, 235, 0.8)'
                        }
                    },
                    {
                        name: 'Girls',
                        type: 'bar',
                        data: girlsCounts,
                        itemStyle: {
                            color: 'rgba(255, 99, 132, 0.8)'
                        }
                    }
                ]
            };

            chart.setOption(option);

            window.addEventListener('resize', function() {
                chart.resize();
            });
        });
    </script>
@endsection

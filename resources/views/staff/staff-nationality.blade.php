@extends('layouts.master')
@section('title')
    Staff Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.index') }}"> Back </a>
        @endslot
        @slot('title')
            Staff List By Nationality
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
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
                    @if (!empty($usersByNationality))
                        <h5 class="text-muted">Staff By Gender & Nationality</h5>
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Nationality</th>
                                    <th colspan="2">Number of Staff</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th>Male</th>
                                    <th>Female</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usersByNationality as $user)
                                    <tr>
                                        <td>{{ $user['nationality'] }}</td>
                                        <td>{{ $user['male'] }}</td>
                                        <td>{{ $user['female'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    <br>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-center">
                            <div id="mainMale" style="width: 600px;height:400px;"></div>
                            <div id="mainFemale" style="width: 600px;height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.0.0/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        var chartDomMale = document.getElementById('mainMale');
        var myChartMale = echarts.init(chartDomMale);
        var chartDomFemale = document.getElementById('mainFemale');
        var myChartFemale = echarts.init(chartDomFemale);
        var optionMale, optionFemale;

        optionMale = {
            title: {
                text: 'Male Users by Nationality',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            series: [{
                name: 'Nationality',
                type: 'pie',
                radius: '50%',
                data: [
                    @foreach ($usersByNationality as $user)
                        {
                            value: {{ $user['male'] }},
                            name: '{{ $user['nationality'] }}'
                        },
                    @endforeach
                ]
            }]
        };

        optionFemale = {
            title: {
                text: 'Female Users by Nationality',
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            series: [{
                name: 'Nationality',
                type: 'pie',
                radius: '50%',
                data: [
                    @foreach ($usersByNationality as $user)
                        {
                            value: {{ $user['female'] }},
                            name: '{{ $user['nationality'] }}'
                        },
                    @endforeach
                ]
            }]
        };

        myChartMale.setOption(optionMale);
        myChartFemale.setOption(optionFemale);
    </script>
@endsection

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
            <a
                href="{{ route('assessment.export-grade-subjects-analysis', ['classId' => $klass->id, 'type' => $type, 'sequenceId' => $sequenceId]) }}">
                <i style="font-size: 18px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                    class="bx bx-export text-muted"></i>
            </a>
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
                    <h5 class="text-start">Grade Subjects Performance Analysis</h5>
                    <table class="table table-sm table-striped table-bordered mt-3">
                        <thead>
                            <tr>
                                <th rowspan="2" class="header-cell">Subject</th>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                    <th colspan="3" style="text-align: center;" class="header-cell">{{ $grade }}
                                    </th>
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
                            @foreach ($subjectPerformance as $subjectName => $data)
                                <tr>
                                    <td>{{ $subjectName }}</td>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade)
                                        <td>{{ $data[$grade]['M'] }}</td>
                                        <td>{{ $data[$grade]['F'] }}</td>
                                        <td>{{ round($data[$grade . '%']['M'], 1) }}% /
                                            {{ round($data[$grade . '%']['F'], 1) }}%</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <div style="width: 100%;margin: auto;">
                        <div id="gradeDistributionLineChart" style="width: 100%; height: 460px;"></div>
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
            const subjectPerformance = @json($subjectPerformance);
            const subjects = Object.keys(subjectPerformance);
            const chart = echarts.init(document.getElementById('gradeDistributionLineChart'));
            const colors = {
                'A Male': '#0f766e',
                'A Female': '#14b8a6',
                'B Male': '#2563eb',
                'B Female': '#60a5fa',
                'C Male': '#7c3aed',
                'C Female': '#a78bfa',
                'D Male': '#ea580c',
                'D Female': '#fb923c',
                'E Male': '#dc2626',
                'E Female': '#f87171',
                'AB Male': '#1d4ed8',
                'AB Female': '#38bdf8',
                'ABC Male': '#15803d',
                'ABC Female': '#4ade80',
                'DE Male': '#b91c1c',
                'DE Female': '#fca5a5'
            };

            const series = [];
            ['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'].forEach(grade => {
                ['M', 'F'].forEach(gender => {
                    const label = grade + ' ' + (gender === 'M' ? 'Male' : 'Female');
                    series.push({
                        name: label,
                        type: 'line',
                        smooth: true,
                        data: subjects.map(subject => subjectPerformance[subject][grade][gender]),
                        lineStyle: {
                            width: 2,
                            color: colors[label]
                        },
                        itemStyle: {
                            color: colors[label]
                        }
                    });
                });
            });

            chart.setOption({
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    type: 'scroll'
                },
                toolbox: {
                    show: true,
                    feature: {
                        restore: {},
                        saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    data: subjects
                },
                yAxis: {
                    type: 'value',
                    minInterval: 1
                },
                series: series
            });

            window.addEventListener('resize', function() {
                chart.resize();
            });
        });
    </script>
@endsection

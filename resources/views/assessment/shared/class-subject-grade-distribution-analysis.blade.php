@extends('layouts.master')
@section('title')
    Class Exam Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#" onclick="event.preventDefault(); 
                if (document.referrer) {
                history.back();
                } else {
                window.location = '{{ $gradebookBackUrl }}';
                }   
            ">Back</a>
        @endslot
        @slot('title')
            {{ $klass->name ?? '' }} Subject Grade Distribution Analysis
        @endslot
    @endcomponent
@section('css')
    <style>

        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 12px;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #333;
            padding: 5px;
            margin: 10px 0;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 12px;
                line-height: 1.1;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            canvas,
            .graph-container {
                display: none !important;
            }

            .printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 5mm;
            }

            .card {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .card-header {
                padding: 5mm;
                margin-bottom: 3mm;
            }

            .card-header .row {
                display: flex;
                align-items: center;
            }

            .card-header .col-md-6,
            .card-header .col-lg-6 {
                display: flex;
                align-items: center;
            }

            .card-header img {
                height: 30px;
                width: auto;
                visibility: hidden;
                margin-top: -40px;
            }

            .table {
                width: 100%;
                margin-bottom: 3mm;
                margin-top: 10px;
                page-break-inside: avoid;
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 1mm;
                white-space: nowrap;
            }

            .table-sm td,
            .table-sm th {
                padding: 0.5mm 1mm;
            }

            h5 {
                margin: 2mm 0;
                font-size: 9px;
            }

            .form-group {
                font-size: 12px !important;
                line-height: 1.2;
            }

            .table-responsive {
                margin-bottom: 3mm;
            }

            .report-card {
                margin: 0;
                padding: 0;
                page-break-before: avoid;
                page-break-after: avoid;
            }

            .row {
                page-break-inside: avoid;
            }

            .card-body {
                page-break-before: avoid;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
@endsection

<div class="row">
    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
        <i onclick="window.location.href='{{ request()->fullUrlWithQuery(['export' => 'true']) }}'"
            style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-download text-muted me-2"></i>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
    </div>
</div>

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
                <div class="row">
                    <h5>
                        {{ $klass->name ?? '' }}
                        @if(isset($test->type) && strtolower($test->type) === 'exam')
                            End Of Term Exam
                        @elseif(isset($test->type) && strtolower($test->type) === 'ca')
                            End Of {{ $test->name ?? '' }}
                        @else
                            End Of {{ $test->name ?? '' }}
                        @endif
                        Term {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }} Subject Grade Distribution Analysis
                    </h5>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Subject</th>
                                        <th rowspan="2">Teacher</th>
                                        @foreach(['A', 'B', 'C', 'D', 'E', 'U', 'Total Enrolled', 'No Scores', 'AB%', 'ABC%', 'DEU%'] as $column)
                                            <th colspan="3" class="text-center">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach(['A', 'B', 'C', 'D', 'E', 'U', 'Total Enrolled', 'No Scores', 'AB%', 'ABC%', 'DEU%'] as $column)
                                            <th class="text-center">M</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">T</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectsData as $data)
                                        <tr>
                                            <td>{{ $data['subject'] }}</td>
                                            <td>{{ $data['teacher'] }}</td>
                                            
                                            @foreach(['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <td class="text-center">{{ $data['grades'][$grade]['M'] }}</td>
                                                <td class="text-center">{{ $data['grades'][$grade]['F'] }}</td>
                                                <td class="text-center">{{ $data['grades'][$grade]['T'] }}</td>
                                            @endforeach
                                            
                                            <td class="text-center">{{ $data['total_enrolled']['M'] }}</td>
                                            <td class="text-center">{{ $data['total_enrolled']['F'] }}</td>
                                            <td class="text-center">{{ $data['total_enrolled']['T'] }}</td>
                                            
                                            <td class="text-center">{{ $data['no_scores']['M'] }}</td>
                                            <td class="text-center">{{ $data['no_scores']['F'] }}</td>
                                            <td class="text-center">{{ $data['no_scores']['T'] }}</td>
                                            
                                            @foreach(['AB', 'ABC', 'DEU'] as $percent)
                                                <td class="text-center">{{ $data['percentages'][$percent]['M'] }}%</td>
                                                <td class="text-center">{{ $data['percentages'][$percent]['F'] }}%</td>
                                                <td class="text-center">{{ $data['percentages'][$percent]['T'] }}%</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div id="gradeDistributionChart" style="height: 500px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.2/dist/echarts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('gradeDistributionChart');
            var myChart = echarts.init(chartDom);
            
            var subjects = @json($chartData['subjects']);
            var series = @json($chartData['series']);
            
            var option = {
                title: {
                    text: 'Subject Grade Distribution',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: series.map(item => item.name),
                    top: 30
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: subjects,
                    axisLabel: {
                        rotate: 45,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Percentage (%)',
                    min: 0,
                    max: 100
                },
                series: series,
                color: ['#5470c6', '#ff69b4', '#91cc75', '#73c0de', '#ee6666']
            };
            
            myChart.setOption(option);
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        });
    </script>
@endsection
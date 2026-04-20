@extends('layouts.master')
@section('title')
    Boarding Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Boarding Analysis Report
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
        }

        .summary-card {
            background: white;
            border-radius: 3px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-card .label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

            #boardingChart {
                display: none !important;
            }
        }
    </style>
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('students.boarding-analysis-export') }}">
                <i style="font-size: 18px;margin-bottom:10px;margin-right:5px;cursor:pointer;"
                    class="bx bx-export text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
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
                    {{-- Summary Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div class="value" style="color: #1f2937;">{{ $grandTotal['total'] }}</div>
                                <div class="label">Total Students</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div class="value" style="color: #2563eb;">{{ $grandTotal['boarding_total'] }}</div>
                                <div class="label">Boarding</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div class="value" style="color: #059669;">{{ $grandTotal['day_total'] }}</div>
                                <div class="label">Day</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div class="value" style="color: #7c3aed;">{{ $grandTotal['boarding_percentage'] }}%</div>
                                <div class="label">Boarding %</div>
                            </div>
                        </div>
                    </div>

                    {{-- Grade Summary Table --}}
                    <h5 class="section-title">Grade Summary</h5>
                    @if($gradeData->isNotEmpty())
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Grade</th>
                                    <th colspan="3" class="text-center" style="background-color: rgba(37, 99, 235, 0.1);">Boarding</th>
                                    <th colspan="3" class="text-center" style="background-color: rgba(5, 150, 105, 0.1);">Day</th>
                                    <th rowspan="2">Total</th>
                                </tr>
                                <tr>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">B</th>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">G</th>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">T</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">B</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">G</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeData as $index => $grade)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $grade['grade_name'] }}</td>
                                        <td>{{ $grade['boarding_boys'] }}</td>
                                        <td>{{ $grade['boarding_girls'] }}</td>
                                        <td><strong>{{ $grade['boarding_total'] }}</strong></td>
                                        <td>{{ $grade['day_boys'] }}</td>
                                        <td>{{ $grade['day_girls'] }}</td>
                                        <td><strong>{{ $grade['day_total'] }}</strong></td>
                                        <td><strong>{{ $grade['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                                <tr style="font-weight: bold; background-color: #f3f4f6;">
                                    <td colspan="2" style="text-align: end;">Totals:</td>
                                    <td>{{ $grandTotal['boarding_boys'] }}</td>
                                    <td>{{ $grandTotal['boarding_girls'] }}</td>
                                    <td>{{ $grandTotal['boarding_total'] }}</td>
                                    <td>{{ $grandTotal['day_boys'] }}</td>
                                    <td>{{ $grandTotal['day_girls'] }}</td>
                                    <td>{{ $grandTotal['day_total'] }}</td>
                                    <td>{{ $grandTotal['total'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    {{-- Class Detail Table --}}
                    <h5 class="section-title">Class Detail</h5>
                    @if($classData->isNotEmpty())
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Class</th>
                                    <th rowspan="2">Teacher</th>
                                    <th colspan="3" class="text-center" style="background-color: rgba(37, 99, 235, 0.1);">Boarding</th>
                                    <th colspan="3" class="text-center" style="background-color: rgba(5, 150, 105, 0.1);">Day</th>
                                    <th rowspan="2">Total</th>
                                </tr>
                                <tr>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">B</th>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">G</th>
                                    <th style="background-color: rgba(37, 99, 235, 0.1);">T</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">B</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">G</th>
                                    <th style="background-color: rgba(5, 150, 105, 0.1);">T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classData as $index => $class)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $class['name'] }}</td>
                                        <td>{{ $class['teacher'] }}</td>
                                        <td>{{ $class['boarding_boys'] }}</td>
                                        <td>{{ $class['boarding_girls'] }}</td>
                                        <td><strong>{{ $class['boarding_total'] }}</strong></td>
                                        <td>{{ $class['day_boys'] }}</td>
                                        <td>{{ $class['day_girls'] }}</td>
                                        <td><strong>{{ $class['day_total'] }}</strong></td>
                                        <td><strong>{{ $class['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                                <tr style="font-weight: bold; background-color: #f3f4f6;">
                                    <td colspan="3" style="text-align: end;">Totals:</td>
                                    <td>{{ $grandTotal['boarding_boys'] }}</td>
                                    <td>{{ $grandTotal['boarding_girls'] }}</td>
                                    <td>{{ $grandTotal['boarding_total'] }}</td>
                                    <td>{{ $grandTotal['day_boys'] }}</td>
                                    <td>{{ $grandTotal['day_girls'] }}</td>
                                    <td>{{ $grandTotal['day_total'] }}</td>
                                    <td>{{ $grandTotal['total'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    {{-- ECharts Stacked Bar Chart --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div id="boardingChart" style="width: 100%; height: 400px;"></div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const classData = @json($classData->values());
            const classNames = classData.map(c => c.name);
            const boardingBoys = classData.map(c => c.boarding_boys);
            const boardingGirls = classData.map(c => c.boarding_girls);
            const dayBoys = classData.map(c => c.day_boys);
            const dayGirls = classData.map(c => c.day_girls);

            const chart = echarts.init(document.getElementById('boardingChart'));

            const option = {
                title: {
                    text: 'Boarding vs Day Students by Class',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['Boarding Boys', 'Boarding Girls', 'Day Boys', 'Day Girls'],
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
                        name: 'Boarding Boys',
                        type: 'bar',
                        stack: 'Boarding',
                        data: boardingBoys,
                        itemStyle: {
                            color: 'rgba(37, 99, 235, 0.85)'
                        }
                    },
                    {
                        name: 'Boarding Girls',
                        type: 'bar',
                        stack: 'Boarding',
                        data: boardingGirls,
                        itemStyle: {
                            color: 'rgba(96, 165, 250, 0.85)'
                        }
                    },
                    {
                        name: 'Day Boys',
                        type: 'bar',
                        stack: 'Day',
                        data: dayBoys,
                        itemStyle: {
                            color: 'rgba(5, 150, 105, 0.85)'
                        }
                    },
                    {
                        name: 'Day Girls',
                        type: 'bar',
                        stack: 'Day',
                        data: dayGirls,
                        itemStyle: {
                            color: 'rgba(52, 211, 153, 0.85)'
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

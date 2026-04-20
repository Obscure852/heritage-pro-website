@extends('layouts.master')
@section('title')
    Student Types Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Student Types Statistical Analysis
        @endslot
    @endcomponent

@section('css')
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
@endsection
<div class="row">
    <div class="col-12 d-flex justify-content-end">
        <i onclick="printContent()" class="bx bx-printer text-muted"
            style="font-size: 18px;margin-bottom:10px;cursor:pointer;"></i>
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
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">Class</th>
                                <th rowspan="2">Class Teacher</th>
                                @foreach ($studentTypes as $type)
                                    <th colspan="3">{{ $type }}</th>
                                @endforeach
                                <th colspan="3">Totals</th>
                            </tr>
                            <tr>
                                @foreach ($studentTypes as $type)
                                    <th>B</th>
                                    <th>G</th>
                                    <th>T</th>
                                @endforeach
                                <th>B</th>
                                <th>G</th>
                                <th>T</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($klasses as $index => $klass)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $klass['name'] }}</td>
                                    <td>{{ $klass['teacher'] }}</td>
                                    @foreach ($studentTypes as $type)
                                        <td>{{ $klass['type_counts'][$type]['boys'] }}</td>
                                        <td>{{ $klass['type_counts'][$type]['girls'] }}</td>
                                        <td>{{ $klass['type_counts'][$type]['total'] }}</td>
                                    @endforeach
                                    <td>{{ $klass['total_boys'] }}</td>
                                    <td>{{ $klass['total_girls'] }}</td>
                                    <td>{{ $klass['class_total'] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3" class="text-end"><strong>Totals:</strong></td>
                                @foreach ($studentTypes as $type)
                                    <td>{{ $totalsByType[$type]['boys'] }}</td>
                                    <td>{{ $totalsByType[$type]['girls'] }}</td>
                                    <td>{{ $totalsByType[$type]['total'] }}</td>
                                @endforeach
                                <td>{{ $grandTotal['boys'] }}</td>
                                <td>{{ $grandTotal['girls'] }}</td>
                                <td>{{ $grandTotal['total'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div id="studentTypesChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                @else
                    <p>No data available</p>
                @endif
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
        const studentTypes = @json($studentTypes);
        const klasses = @json($klasses);

        const chart = echarts.init(document.getElementById('studentTypesChart'));

        const colors = [
            ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 0.5)'],
            ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 0.5)'],
            ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 0.5)'],
            ['rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 0.5)'],
            ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 0.5)']
        ];

        const series = [];
        const legendData = [];

        studentTypes.forEach((type, index) => {
            const colorPair = colors[index % colors.length];

            legendData.push(type + ' Boys', type + ' Girls');

            series.push({
                name: type + ' Boys',
                type: 'bar',
                stack: type,
                data: klasses.map(klass => klass.type_counts[type].boys),
                itemStyle: {
                    color: colorPair[0]
                }
            });

            series.push({
                name: type + ' Girls',
                type: 'bar',
                stack: type,
                data: klasses.map(klass => klass.type_counts[type].girls),
                itemStyle: {
                    color: colorPair[1]
                }
            });
        });

        const option = {
            title: {
                text: 'Students by Type and Class',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: legendData,
                bottom: 0,
                type: 'scroll'
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '15%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: klasses.map(klass => klass.name),
                axisLabel: {
                    rotate: 45,
                    interval: 0
                }
            },
            yAxis: {
                type: 'value',
                minInterval: 1
            },
            series: series
        };

        chart.setOption(option);

        window.addEventListener('resize', function() {
            chart.resize();
        });
    });
</script>
@endsection

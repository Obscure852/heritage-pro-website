@extends('layouts.master')
@section('title')
    Houses Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Houses Analysis
        @endslot
    @endcomponent
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
        }

        @media screen {
            body {
                font-size: 12px;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
            }

            .table {
                font-size: 9pt;
            }

            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
@endsection
<div class="row no-print">
    <div class="col-md-12 d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" title="Export to Excel">
            <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
        </a>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
            class="bx bx-printer text-muted"></i>
    </div>
</div>

<div class="row printable">
    <div class="col-md-12">
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
                        <div class="col-md-12">
                            <h6>All Grades Houses Performance Analysis -
                                {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                                - Term
                                {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">House</th>
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                <th colspan="3">{{ $g }}</th>
                                            @endforeach
                                            <th colspan="3">Total</th>
                                            <th colspan="3">AB%</th>
                                            <th colspan="3">ABC%</th>
                                            <th colspan="3">ABCD%</th>
                                            <th colspan="3">DEU%</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 0; $i < 11; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($housePerformance as $house => $h)
                                            @php
                                                $totM = $h['grades']['total']['M'];
                                                $totF = $h['grades']['total']['F'];
                                                $totT = $totM + $totF;
                                                $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                                // Calculate M/F percentages
                                                $abM = $h['grades']['A']['M'] + $h['grades']['B']['M'];
                                                $abF = $h['grades']['A']['F'] + $h['grades']['B']['F'];
                                                $abcM = $abM + $h['grades']['C']['M'];
                                                $abcF = $abF + $h['grades']['C']['F'];
                                                $abcdM = $abcM + $h['grades']['D']['M'];
                                                $abcdF = $abcF + $h['grades']['D']['F'];
                                                $deuM = $h['grades']['D']['M'] + $h['grades']['E']['M'] + $h['grades']['U']['M'];
                                                $deuF = $h['grades']['D']['F'] + $h['grades']['E']['F'] + $h['grades']['U']['F'];
                                            @endphp
                                            <tr>
                                                <td>{{ $house }}</td>
                                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                    <td>{{ $h['grades'][$g]['M'] }}</td>
                                                    <td>{{ $h['grades'][$g]['F'] }}</td>
                                                    <td>{{ $h['grades'][$g]['M'] + $h['grades'][$g]['F'] }}</td>
                                                @endforeach
                                                <td>{{ $totM }}</td>
                                                <td>{{ $totF }}</td>
                                                <td>{{ $totT }}</td>
                                                <td>{{ $pct($abM, $totM) }}%</td>
                                                <td>{{ $pct($abF, $totF) }}%</td>
                                                <td>{{ $h['AB%'] }}%</td>
                                                <td>{{ $pct($abcM, $totM) }}%</td>
                                                <td>{{ $pct($abcF, $totF) }}%</td>
                                                <td>{{ $h['ABC%'] }}%</td>
                                                <td>{{ $pct($abcdM, $totM) }}%</td>
                                                <td>{{ $pct($abcdF, $totF) }}%</td>
                                                <td>{{ $h['ABCD%'] }}%</td>
                                                <td>{{ $pct($deuM, $totM) }}%</td>
                                                <td>{{ $pct($deuF, $totF) }}%</td>
                                                <td>{{ $h['DEU%'] }}%</td>
                                            </tr>
                                        @endforeach

                                        @php
                                            $oTotM = $overallTotals['grades']['total']['M'];
                                            $oTotF = $overallTotals['grades']['total']['F'];
                                            $oTotT = $oTotM + $oTotF;
                                            $pctO = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                            $oAbM = $overallTotals['grades']['A']['M'] + $overallTotals['grades']['B']['M'];
                                            $oAbF = $overallTotals['grades']['A']['F'] + $overallTotals['grades']['B']['F'];
                                            $oAbcM = $oAbM + $overallTotals['grades']['C']['M'];
                                            $oAbcF = $oAbF + $overallTotals['grades']['C']['F'];
                                            $oAbcdM = $oAbcM + $overallTotals['grades']['D']['M'];
                                            $oAbcdF = $oAbcF + $overallTotals['grades']['D']['F'];
                                            $oDeuM = $overallTotals['grades']['D']['M'] + $overallTotals['grades']['E']['M'] + $overallTotals['grades']['U']['M'];
                                            $oDeuF = $overallTotals['grades']['D']['F'] + $overallTotals['grades']['E']['F'] + $overallTotals['grades']['U']['F'];
                                        @endphp
                                        <tr style="font-weight:600;background:#f3f3f3">
                                            <td>Totals</td>
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                <td>{{ $overallTotals['grades'][$g]['M'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['F'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['M'] + $overallTotals['grades'][$g]['F'] }}</td>
                                            @endforeach
                                            <td>{{ $oTotM }}</td>
                                            <td>{{ $oTotF }}</td>
                                            <td>{{ $oTotT }}</td>
                                            <td>{{ $pctO($oAbM, $oTotM) }}%</td>
                                            <td>{{ $pctO($oAbF, $oTotF) }}%</td>
                                            <td>{{ $overallTotals['AB%'] }}%</td>
                                            <td>{{ $pctO($oAbcM, $oTotM) }}%</td>
                                            <td>{{ $pctO($oAbcF, $oTotF) }}%</td>
                                            <td>{{ $overallTotals['ABC%'] }}%</td>
                                            <td>{{ $pctO($oAbcdM, $oTotM) }}%</td>
                                            <td>{{ $pctO($oAbcdF, $oTotF) }}%</td>
                                            <td>{{ $overallTotals['ABCD%'] }}%</td>
                                            <td>{{ $pctO($oDeuM, $oTotM) }}%</td>
                                            <td>{{ $pctO($oDeuF, $oTotF) }}%</td>
                                            <td>{{ $overallTotals['DEU%'] }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row no-print">
                        <div class="col-md-12">
                            <div id="main" style="width: 100%; height: 500px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
    var housePerformance = @json($housePerformance);
    var houseNames = Object.keys(housePerformance);
    var grades = ['A', 'B', 'C', 'D', 'E', 'U'];
    var gradeColors = ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#9a60b4'];
    var series = [];

    // Bar series for grade counts by gender
    grades.forEach(function(grade, index) {
        series.push({
            name: grade + ' (M)',
            type: 'bar',
            stack: 'Male',
            data: houseNames.map(function(name) {
                return housePerformance[name]['grades'][grade]['M'];
            }),
            itemStyle: { color: gradeColors[index] }
        });
        series.push({
            name: grade + ' (F)',
            type: 'bar',
            stack: 'Female',
            data: houseNames.map(function(name) {
                return housePerformance[name]['grades'][grade]['F'];
            }),
            itemStyle: { color: gradeColors[index], borderColor: '#333', borderWidth: 1 }
        });
    });

    // Line series for percentages
    series.push({
        name: 'AB%',
        type: 'line',
        yAxisIndex: 1,
        data: houseNames.map(function(name) { return housePerformance[name]['AB%']; }),
        itemStyle: { color: '#3ba272' },
        lineStyle: { width: 3 },
        symbol: 'circle',
        symbolSize: 10
    });

    series.push({
        name: 'ABC%',
        type: 'line',
        yAxisIndex: 1,
        data: houseNames.map(function(name) { return housePerformance[name]['ABC%']; }),
        itemStyle: { color: '#91CC75' },
        lineStyle: { width: 3 },
        symbol: 'diamond',
        symbolSize: 10
    });

    series.push({
        name: 'ABCD%',
        type: 'line',
        yAxisIndex: 1,
        data: houseNames.map(function(name) { return housePerformance[name]['ABCD%']; }),
        itemStyle: { color: '#FAC858' },
        lineStyle: { width: 3 },
        symbol: 'triangle',
        symbolSize: 10
    });

    series.push({
        name: 'DEU%',
        type: 'line',
        yAxisIndex: 1,
        data: houseNames.map(function(name) { return housePerformance[name]['DEU%']; }),
        itemStyle: { color: '#EE6666' },
        lineStyle: { width: 3, type: 'dashed' },
        symbol: 'rect',
        symbolSize: 10
    });

    var myChart = echarts.init(document.getElementById('main'));
    var option = {
        title: {
            text: 'House Performance Distribution by Gender',
            left: 'center'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross' },
            formatter: function(params) {
                var result = params[0].axisValue + '<br/>';
                params.forEach(function(p) {
                    var value = p.seriesName.includes('%') ? p.value + '%' : p.value;
                    result += p.marker + ' ' + p.seriesName + ': ' + value + '<br/>';
                });
                return result;
            }
        },
        legend: {
            top: 30,
            type: 'scroll'
        },
        grid: {
            left: '3%',
            right: '5%',
            bottom: '3%',
            top: 100,
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: houseNames
        },
        yAxis: [{
            type: 'value',
            name: 'Grade Count',
            position: 'left',
            minInterval: 1
        }, {
            type: 'value',
            name: 'Percentage',
            position: 'right',
            min: 0,
            max: 100,
            axisLabel: { formatter: '{value}%' }
        }],
        series: series
    };

    myChart.setOption(option);

    window.addEventListener('resize', function() {
        myChart.resize();
    });

    function printContent() {
        window.print();
    }
</script>
@endsection

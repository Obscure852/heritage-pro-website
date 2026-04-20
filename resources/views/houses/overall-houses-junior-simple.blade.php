@extends('layouts.master')
@section('title')
    Houses Overall Performance Analysis (Simple)
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Houses Overall Performance Analysis
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
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        @media screen {
            body {
                font-size: 14px;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            body {
                font-size: 11pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
            }

            .table {
                font-size: 10pt;
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
                            <span>{{ $school_data->physical_address }}</span>
                            <br>
                            <span>{{ $school_data->postal_address }}</span>
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
                            <h5 class="mb-3">All Grades House Performance Analysis -
                                {{ strtolower($test->type ?? '') === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                                - Term {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">House</th>
                                            <th colspan="7">Grade Counts</th>
                                            <th colspan="4">Percentages</th>
                                            <th rowspan="2">Total</th>
                                        </tr>
                                        <tr>
                                            <th>Merit</th>
                                            <th>A</th>
                                            <th>B</th>
                                            <th>C</th>
                                            <th>D</th>
                                            <th>E</th>
                                            <th>U</th>
                                            <th>MAB%</th>
                                            <th>MABC%</th>
                                            <th>MABCD%</th>
                                            <th>DEU%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($housePerformance as $houseName => $hp)
                                            <tr>
                                                <td>{{ $houseName }}</td>
                                                <td>{{ $hp['gradeCounts']['Merit'] }}</td>
                                                <td>{{ $hp['gradeCounts']['A'] }}</td>
                                                <td>{{ $hp['gradeCounts']['B'] }}</td>
                                                <td>{{ $hp['gradeCounts']['C'] }}</td>
                                                <td>{{ $hp['gradeCounts']['D'] }}</td>
                                                <td>{{ $hp['gradeCounts']['E'] }}</td>
                                                <td>{{ $hp['gradeCounts']['U'] }}</td>
                                                <td>{{ $hp['mabPercentage'] }}%</td>
                                                <td>{{ $hp['mabcPercentage'] }}%</td>
                                                <td>{{ $hp['mabcdPercentage'] }}%</td>
                                                <td>{{ $hp['deuPercentage'] }}%</td>
                                                <td>{{ $hp['total'] }}</td>
                                            </tr>
                                        @endforeach

                                        <tr style="font-weight:600; background:#f3f3f3;">
                                            <td>Totals</td>
                                            <td>{{ $overallTotals['grades']['Merit'] }}</td>
                                            <td>{{ $overallTotals['grades']['A'] }}</td>
                                            <td>{{ $overallTotals['grades']['B'] }}</td>
                                            <td>{{ $overallTotals['grades']['C'] }}</td>
                                            <td>{{ $overallTotals['grades']['D'] }}</td>
                                            <td>{{ $overallTotals['grades']['E'] }}</td>
                                            <td>{{ $overallTotals['grades']['U'] }}</td>
                                            <td>{{ $overallTotals['MAB%'] }}%</td>
                                            <td>{{ $overallTotals['MABC%'] }}%</td>
                                            <td>{{ $overallTotals['MABCD%'] }}%</td>
                                            <td>{{ $overallTotals['DEU%'] }}%</td>
                                            <td>{{ $overallTotals['total'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row no-print mt-4">
                        <div class="col-md-6">
                            <h5 class="text-center">Grade Distribution by House</h5>
                            <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center">Performance Percentages by House</h5>
                            <div id="percentageChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">Total Students per House</h5>
                            <div id="totalStudentsChart" style="width: 100%; height: 400px;"></div>
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
    var grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
    var gradeColors = ['#9a60b4', '#91cc75', '#5470c6', '#fac858', '#fc8452', '#ee6666', '#909399'];

    // 1. Grade Distribution Chart (Stacked Bar)
    var gradeDistChart = echarts.init(document.getElementById('gradeDistributionChart'));
    var gradeSeries = grades.map(function(grade, index) {
        return {
            name: grade,
            type: 'bar',
            stack: 'total',
            data: houseNames.map(function(house) {
                return housePerformance[house]['gradeCounts'][grade];
            }),
            itemStyle: { color: gradeColors[index] }
        };
    });

    gradeDistChart.setOption({
        title: { text: 'Grade Distribution by House', left: 'center' },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { top: 30, data: grades },
        grid: { top: 80, containLabel: true },
        xAxis: { type: 'category', data: houseNames },
        yAxis: { type: 'value', name: 'Students', minInterval: 1 },
        series: gradeSeries
    });

    // 2. Percentage Chart (Line)
    var percentageChart = echarts.init(document.getElementById('percentageChart'));
    percentageChart.setOption({
        title: { text: 'Performance Percentages by House', left: 'center' },
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                var result = params[0].axisValue + '<br/>';
                params.forEach(function(p) {
                    result += p.marker + ' ' + p.seriesName + ': ' + p.value + '%<br/>';
                });
                return result;
            }
        },
        legend: { top: 30, data: ['MAB%', 'MABC%', 'MABCD%', 'DEU%'] },
        grid: { top: 80, containLabel: true },
        xAxis: { type: 'category', data: houseNames },
        yAxis: { type: 'value', name: 'Percentage', min: 0, max: 100, axisLabel: { formatter: '{value}%' } },
        series: [
            {
                name: 'MAB%',
                type: 'line',
                data: houseNames.map(function(house) { return housePerformance[house]['mabPercentage']; }),
                itemStyle: { color: '#91cc75' },
                lineStyle: { width: 3 },
                symbol: 'circle',
                symbolSize: 10
            },
            {
                name: 'MABC%',
                type: 'line',
                data: houseNames.map(function(house) { return housePerformance[house]['mabcPercentage']; }),
                itemStyle: { color: '#5470c6' },
                lineStyle: { width: 3 },
                symbol: 'diamond',
                symbolSize: 10
            },
            {
                name: 'MABCD%',
                type: 'line',
                data: houseNames.map(function(house) { return housePerformance[house]['mabcdPercentage']; }),
                itemStyle: { color: '#fac858' },
                lineStyle: { width: 3 },
                symbol: 'triangle',
                symbolSize: 10
            },
            {
                name: 'DEU%',
                type: 'line',
                data: houseNames.map(function(house) { return housePerformance[house]['deuPercentage']; }),
                itemStyle: { color: '#ee6666' },
                lineStyle: { width: 3, type: 'dashed' },
                symbol: 'rect',
                symbolSize: 10
            }
        ]
    });

    // 3. Total Students Chart (Bar)
    var totalStudentsChart = echarts.init(document.getElementById('totalStudentsChart'));
    totalStudentsChart.setOption({
        title: { text: 'Total Students per House', left: 'center' },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { top: 60, containLabel: true },
        xAxis: { type: 'category', data: houseNames },
        yAxis: { type: 'value', name: 'Students', minInterval: 1 },
        series: [{
            name: 'Total Students',
            type: 'bar',
            data: houseNames.map(function(house) { return housePerformance[house]['total']; }),
            itemStyle: {
                color: function(params) {
                    var colors = ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272'];
                    return colors[params.dataIndex % colors.length];
                }
            },
            label: { show: true, position: 'top' }
        }]
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        gradeDistChart.resize();
        percentageChart.resize();
        totalStudentsChart.resize();
    });

    function printContent() {
        window.print();
    }
</script>
@endsection

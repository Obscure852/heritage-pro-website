@extends('layouts.master')
@section('title')
    Exam House Performance Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            All Grades House Performance Analysis
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

        .card {
            width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 10px;
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
                        <div class="col-12">
                            <h6>All Grades House Performance Analysis -
                                {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                                - Term
                                {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3">House</th>
                                            <th colspan="21">Grade Counts</th>
                                            <th colspan="12">Percentages</th>
                                            <th colspan="3" rowspan="2">Total&nbsp;Students</th>
                                        </tr>
                                        <tr>
                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                <th colspan="3">{{ $g }}</th>
                                            @endforeach
                                            @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $p)
                                                <th colspan="3">{{ $p }}</th>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            @for ($i = 0; $i < 7; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor
                                            @for ($i = 0; $i < 4; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($housePerformance as $houseName => $hp)
                                            @php
                                                $totalStudents = $hp['totalMale'] + $hp['totalFemale'];
                                                $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                                // Calculate combined percentages for this house
                                                $mabTotal = $hp['gradeCounts']['Merit']['M'] + $hp['gradeCounts']['Merit']['F']
                                                          + $hp['gradeCounts']['A']['M'] + $hp['gradeCounts']['A']['F']
                                                          + $hp['gradeCounts']['B']['M'] + $hp['gradeCounts']['B']['F'];
                                                $mabcTotal = $mabTotal + $hp['gradeCounts']['C']['M'] + $hp['gradeCounts']['C']['F'];
                                                $mabcdTotal = $mabcTotal + $hp['gradeCounts']['D']['M'] + $hp['gradeCounts']['D']['F'];
                                                $deuTotal = $hp['gradeCounts']['D']['M'] + $hp['gradeCounts']['D']['F']
                                                          + $hp['gradeCounts']['E']['M'] + $hp['gradeCounts']['E']['F']
                                                          + $hp['gradeCounts']['U']['M'] + $hp['gradeCounts']['U']['F'];

                                                $mabPercentageT = $pct($mabTotal, $totalStudents);
                                                $mabcPercentageT = $pct($mabcTotal, $totalStudents);
                                                $mabcdPercentageT = $pct($mabcdTotal, $totalStudents);
                                                $deuPercentageT = $pct($deuTotal, $totalStudents);
                                            @endphp
                                            <tr>
                                                <td>{{ $houseName }}</td>

                                                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                    <td>{{ $hp['gradeCounts'][$g]['M'] }}</td>
                                                    <td>{{ $hp['gradeCounts'][$g]['F'] }}</td>
                                                    <td>{{ $hp['gradeCounts'][$g]['M'] + $hp['gradeCounts'][$g]['F'] }}</td>
                                                @endforeach

                                                <td>{{ $hp['mabPercentageM'] }}%</td>
                                                <td>{{ $hp['mabPercentageF'] }}%</td>
                                                <td>{{ $mabPercentageT }}%</td>
                                                <td>{{ $hp['mabcPercentageM'] }}%</td>
                                                <td>{{ $hp['mabcPercentageF'] }}%</td>
                                                <td>{{ $mabcPercentageT }}%</td>
                                                <td>{{ $hp['mabcdPercentageM'] }}%</td>
                                                <td>{{ $hp['mabcdPercentageF'] }}%</td>
                                                <td>{{ $mabcdPercentageT }}%</td>
                                                <td>{{ $hp['deuPercentageM'] }}%</td>
                                                <td>{{ $hp['deuPercentageF'] }}%</td>
                                                <td>{{ $deuPercentageT }}%</td>

                                                <td>{{ $hp['totalMale'] }}</td>
                                                <td>{{ $hp['totalFemale'] }}</td>
                                                <td>{{ $totalStudents }}</td>
                                            </tr>
                                        @endforeach

                                        @php
                                            $grandTotal = $overallTotals['totalMale'] + $overallTotals['totalFemale'];
                                            $pctG = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                                            // Calculate combined percentages for totals row
                                            $mabTotalAll = $overallTotals['grades']['Merit']['M'] + $overallTotals['grades']['Merit']['F']
                                                         + $overallTotals['grades']['A']['M'] + $overallTotals['grades']['A']['F']
                                                         + $overallTotals['grades']['B']['M'] + $overallTotals['grades']['B']['F'];
                                            $mabcTotalAll = $mabTotalAll + $overallTotals['grades']['C']['M'] + $overallTotals['grades']['C']['F'];
                                            $mabcdTotalAll = $mabcTotalAll + $overallTotals['grades']['D']['M'] + $overallTotals['grades']['D']['F'];
                                            $deuTotalAll = $overallTotals['grades']['D']['M'] + $overallTotals['grades']['D']['F']
                                                         + $overallTotals['grades']['E']['M'] + $overallTotals['grades']['E']['F']
                                                         + $overallTotals['grades']['U']['M'] + $overallTotals['grades']['U']['F'];

                                            $mabPercentageTotals = $pctG($mabTotalAll, $grandTotal);
                                            $mabcPercentageTotals = $pctG($mabcTotalAll, $grandTotal);
                                            $mabcdPercentageTotals = $pctG($mabcdTotalAll, $grandTotal);
                                            $deuPercentageTotals = $pctG($deuTotalAll, $grandTotal);
                                        @endphp
                                        <tr style="font-weight:600; background:#f3f3f3;">
                                            <td>Totals</td>

                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                <td>{{ $overallTotals['grades'][$g]['M'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['F'] }}</td>
                                                <td>{{ $overallTotals['grades'][$g]['M'] + $overallTotals['grades'][$g]['F'] }}</td>
                                            @endforeach

                                            <td>{{ $overallTotals['MAB%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MAB%']['F'] }}%</td>
                                            <td>{{ $mabPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['MABC%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MABC%']['F'] }}%</td>
                                            <td>{{ $mabcPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['MABCD%']['M'] }}%</td>
                                            <td>{{ $overallTotals['MABCD%']['F'] }}%</td>
                                            <td>{{ $mabcdPercentageTotals }}%</td>
                                            <td>{{ $overallTotals['DEU%']['M'] }}%</td>
                                            <td>{{ $overallTotals['DEU%']['F'] }}%</td>
                                            <td>{{ $deuPercentageTotals }}%</td>

                                            <td>{{ $overallTotals['totalMale'] }}</td>
                                            <td>{{ $overallTotals['totalFemale'] }}</td>
                                            <td>{{ $grandTotal }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Graphs Section -->
                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">Overall Grade Distribution by Gender</h5>
                            <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">Subject Grade Distribution by Gender</h5>
                            <select id="subjectSelect" class="form-select form-select-sm mb-3">
                                @foreach ($allSubjects as $subject)
                                    <option value="{{ $subject }}">{{ $subject }}</option>
                                @endforeach
                            </select>
                            <div id="subjectGradeChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">Total Students per House by Gender</h5>
                            <div id="totalStudentsChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <!-- Mixed Graph Section -->
                    <div class="row no-print mt-4">
                        <div class="col-md-12">
                            <h5 class="text-center">MAB% vs DEU% by House and Gender</h5>
                            <div id="mixedChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <!-- Additional Graphs or Data as needed -->

                </div>
            </div>
        </div>
    </div> <!-- end col -->
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
    var housePerformance = @json($housePerformance);
    var houseNames = Object.keys(housePerformance);
    var subjects = @json($allSubjects);
    var grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
    var colors = ['#4caf50', '#2196f3', '#ffc107', '#ff5722', '#9c27b0', '#e91e63', '#795548'];

    // 1. Overall Grade Distribution Chart (Line)
    var gradeDistChart = echarts.init(document.getElementById('gradeDistributionChart'));
    var gradeDistSeries = [];

    grades.forEach(function(grade, index) {
        gradeDistSeries.push({
            name: grade + ' (M)',
            type: 'line',
            data: houseNames.map(function(house) {
                return housePerformance[house]['gradeCounts'][grade]['M'];
            }),
            lineStyle: { color: colors[index] },
            itemStyle: { color: colors[index] }
        });
        gradeDistSeries.push({
            name: grade + ' (F)',
            type: 'line',
            data: houseNames.map(function(house) {
                return housePerformance[house]['gradeCounts'][grade]['F'];
            }),
            lineStyle: { color: colors[index], type: 'dashed' },
            itemStyle: { color: colors[index] }
        });
    });

    gradeDistChart.setOption({
        title: { text: 'Overall Grade Distribution by House and Gender', left: 'center' },
        tooltip: { trigger: 'axis' },
        legend: { top: 30, type: 'scroll' },
        grid: { top: 100, containLabel: true },
        xAxis: { type: 'category', data: houseNames, name: 'House' },
        yAxis: { type: 'value', name: 'Students', minInterval: 1 },
        series: gradeDistSeries
    });

    // 2. Subject Grade Distribution Chart (Bar)
    var subjectChart = echarts.init(document.getElementById('subjectGradeChart'));
    var subjectSelect = document.getElementById('subjectSelect');

    function updateSubjectChart(subjectName) {
        var subjectGrades = ['A', 'B', 'C', 'D', 'E', 'U'];
        var gradeColors = ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40'];
        var series = [];

        subjectGrades.forEach(function(grade, index) {
            series.push({
                name: grade + ' (M)',
                type: 'bar',
                stack: 'Male',
                data: houseNames.map(function(house) {
                    return housePerformance[house]['subjectGradeCounts'][subjectName][grade]['M'];
                }),
                itemStyle: { color: gradeColors[index] }
            });
            series.push({
                name: grade + ' (F)',
                type: 'bar',
                stack: 'Female',
                data: houseNames.map(function(house) {
                    return housePerformance[house]['subjectGradeCounts'][subjectName][grade]['F'];
                }),
                itemStyle: { color: gradeColors[index], borderColor: '#000', borderWidth: 1 }
            });
        });

        subjectChart.setOption({
            title: { text: 'Grade Distribution for ' + subjectName + ' by Gender', left: 'center' },
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            legend: { top: 30, type: 'scroll' },
            grid: { top: 100, containLabel: true },
            xAxis: { type: 'category', data: houseNames },
            yAxis: { type: 'value', name: 'Students', minInterval: 1 },
            series: series
        }, true);
    }

    updateSubjectChart(subjects[0]);
    subjectSelect.addEventListener('change', function() {
        updateSubjectChart(this.value);
    });

    // 3. Total Students per House Chart (Bar)
    var totalStudentsChart = echarts.init(document.getElementById('totalStudentsChart'));
    totalStudentsChart.setOption({
        title: { text: 'Total Students per House by Gender', left: 'center' },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { top: 30, data: ['Male', 'Female'] },
        grid: { top: 80, containLabel: true },
        xAxis: { type: 'category', data: houseNames },
        yAxis: { type: 'value', name: 'Students', minInterval: 1 },
        series: [
            {
                name: 'Male',
                type: 'bar',
                data: houseNames.map(function(house) { return housePerformance[house]['totalMale']; }),
                itemStyle: { color: '#42a5f5' }
            },
            {
                name: 'Female',
                type: 'bar',
                data: houseNames.map(function(house) { return housePerformance[house]['totalFemale']; }),
                itemStyle: { color: '#ef5350' }
            }
        ]
    });

    // 4. Mixed Chart (MAB% vs DEU%)
    var mixedChart = echarts.init(document.getElementById('mixedChart'));
    mixedChart.setOption({
        title: { text: 'MAB% vs DEU% by House and Gender', left: 'center' },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross' },
            formatter: function(params) {
                var result = params[0].axisValue + '<br/>';
                params.forEach(function(p) {
                    result += p.marker + ' ' + p.seriesName + ': ' + p.value + '%<br/>';
                });
                return result;
            }
        },
        legend: { top: 30, data: ['MAB% (M)', 'MAB% (F)', 'DEU% (M)', 'DEU% (F)'] },
        grid: { top: 80, containLabel: true },
        xAxis: { type: 'category', data: houseNames },
        yAxis: [
            { type: 'value', name: 'MAB%', min: 0, max: 100, axisLabel: { formatter: '{value}%' } },
            { type: 'value', name: 'DEU%', min: 0, max: 100, axisLabel: { formatter: '{value}%' } }
        ],
        series: [
            {
                name: 'MAB% (M)',
                type: 'bar',
                data: houseNames.map(function(house) { return housePerformance[house]['mabPercentageM']; }),
                itemStyle: { color: '#42a5f5' }
            },
            {
                name: 'MAB% (F)',
                type: 'bar',
                data: houseNames.map(function(house) { return housePerformance[house]['mabPercentageF']; }),
                itemStyle: { color: '#ef5350' }
            },
            {
                name: 'DEU% (M)',
                type: 'line',
                yAxisIndex: 1,
                data: houseNames.map(function(house) { return housePerformance[house]['deuPercentageM']; }),
                lineStyle: { color: '#1e88e5' },
                itemStyle: { color: '#1e88e5' }
            },
            {
                name: 'DEU% (F)',
                type: 'line',
                yAxisIndex: 1,
                data: houseNames.map(function(house) { return housePerformance[house]['deuPercentageF']; }),
                lineStyle: { color: '#d81b60' },
                itemStyle: { color: '#d81b60' }
            }
        ]
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        gradeDistChart.resize();
        subjectChart.resize();
        totalStudentsChart.resize();
        mixedChart.resize();
    });

    function printContent() {
        window.print();
    }
</script>
@endsection

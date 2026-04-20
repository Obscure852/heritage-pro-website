@extends('layouts.master')
@section('title')
    Teachers Subjects Analysis
@endsection
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
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
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

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Teachers Subjects Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
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
                    <div class="report-card">
                        @if (isset($isGrouped) && $isGrouped)
                            @if ($test->type == 'CA')
                                <h5>{{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Subjects
                                    Analysis</h5>
                            @else
                                <h5>{{ $test->grade->name ?? 'Grade' }} - End of Term Subjects Analysis</h5>
                            @endif
                            @foreach ($subjectList as $subject)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6>{{ $subject }} - Performance Analysis</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left" rowspan="2">Teacher</th>
                                                        <th style="text-align:left" rowspan="2">Class</th>
                                                        <th style="text-align:left" rowspan="2">Subject</th>

                                                        {{-- grade groups --}}
                                                        @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $grade)
                                                            <th colspan="3">{{ $grade }}</th>
                                                        @endforeach
                                                        {{-- percentage groups --}}
                                                        @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $percentage)
                                                            <th colspan="2">{{ $percentage }}</th>
                                                        @endforeach
                                                        <th colspan="3">Total</th>
                                                    </tr>
                                                    <tr>
                                                        {{-- 10 grades with M/F/T --}}
                                                        @for ($i = 0; $i < 10; $i++)
                                                            <th>M</th>
                                                            <th>F</th>
                                                            <th>T</th>
                                                        @endfor
                                                        {{-- 4 percentage groups with M/F --}}
                                                        @for ($i = 0; $i < 4; $i++)
                                                            <th>M</th>
                                                            <th>F</th>
                                                        @endfor
                                                        {{-- Total with M/F/T --}}
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @if (!empty($teacherPerformance[$subject] ?? []))
                                                        {{-- per‑teacher rows --}}
                                                        @foreach ($teacherPerformance[$subject] as $data)
                                                            <tr>
                                                                <td style="text-align:left">{{ $data['teacher_name'] }}
                                                                </td>
                                                                <td style="text-align:left">{{ $data['class_name'] }}</td>
                                                                <td style="text-align:left">{{ $data['subject_name'] }}
                                                                </td>

                                                                @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $g)
                                                                    <td>{{ $data['grades'][$g]['M'] }}</td>
                                                                    <td>{{ $data['grades'][$g]['F'] }}</td>
                                                                    <td>{{ $data['grades'][$g]['T'] }}</td>
                                                                @endforeach

                                                                @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $p)
                                                                    <td>{{ $data[$p]['M'] }}%</td>
                                                                    <td>{{ $data[$p]['F'] }}%</td>
                                                                @endforeach

                                                                <td>{{ $data['totalMale'] }}</td>
                                                                <td>{{ $data['totalFemale'] }}</td>
                                                                <td>{{ $data['totalStudents'] }}</td>
                                                            </tr>
                                                        @endforeach

                                                        {{-- grand‑totals row --}}
                                                        @php $tot = $teacherTotals[$subject]; @endphp
                                                        <tr style="font-weight:600;background:#f3f3f3;">
                                                            <td colspan="3" class="text-start">Totals</td>

                                                            {{-- raw grade totals --}}
                                                            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $g)
                                                                <td>{{ $tot['grades'][$g]['M'] }}</td>
                                                                <td>{{ $tot['grades'][$g]['F'] }}</td>
                                                                <td>{{ $tot['grades'][$g]['T'] }}</td>
                                                            @endforeach

                                                            {{-- averaged % totals --}}
                                                            @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $p)
                                                                <td>{{ $tot[$p]['M'] }}%</td>
                                                                <td>{{ $tot[$p]['F'] }}%</td>
                                                            @endforeach

                                                            <td>{{ $tot['totalMale'] }}</td>
                                                            <td>{{ $tot['totalFemale'] }}</td>
                                                            <td>{{ $tot['totalMale'] + $tot['totalFemale'] }}</td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="44" class="text-center">
                                                                No data available for {{ $subject }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($teacherPerformance[$subject]) && !empty($teacherPerformance[$subject]))
                                    <!-- Subject-specific Graph Section -->
                                    <div class="row no-print mt-2 mb-5">
                                        <div class="col-md-12">
                                            <div id="mixChart_{{ str_replace(' ', '_', $subject) }}"
                                                style="width:100%;height:420px;"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="row">
                                <div class="col-12">
                                    <h5>Senior Teacher Performance Analysis - {{ ucfirst($type) }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align:left" rowspan="2">Teacher</th>
                                                    <th style="text-align:left" rowspan="2">Class</th>
                                                    <th style="text-align:left" rowspan="2">Subject</th>

                                                    {{-- grade groups --}}
                                                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $grade)
                                                        <th colspan="3">{{ $grade }}</th>
                                                    @endforeach
                                                    {{-- percentage groups --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $percentage)
                                                        <th colspan="2">{{ $percentage }}</th>
                                                    @endforeach
                                                    <th colspan="3">Total</th>
                                                </tr>
                                                <tr>
                                                    {{-- 10 grades with M/F/T --}}
                                                    @for ($i = 0; $i < 10; $i++)
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    @endfor
                                                    {{-- 4 percentage groups with M/F --}}
                                                    @for ($i = 0; $i < 4; $i++)
                                                        <th>M</th>
                                                        <th>F</th>
                                                    @endfor
                                                    {{-- Total with M/F/T --}}
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {{-- ───────── per‑teacher rows ───────── --}}
                                                @foreach ($teacherPerformance as $data)
                                                    <tr>
                                                        <td style="text-align:left">{{ $data['teacher_name'] }}</td>
                                                        <td style="text-align:left">{{ $data['class_name'] }}</td>
                                                        <td style="text-align:left">{{ $data['subject_name'] }}</td>

                                                        {{-- raw grades --}}
                                                        @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $g)
                                                            <td>{{ $data['grades'][$g]['M'] }}</td>
                                                            <td>{{ $data['grades'][$g]['F'] }}</td>
                                                            <td>{{ $data['grades'][$g]['T'] }}</td>
                                                        @endforeach

                                                        {{-- percentages --}}
                                                        @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $p)
                                                            <td>{{ $data[$p]['M'] }}%</td>
                                                            <td>{{ $data[$p]['F'] }}%</td>
                                                        @endforeach

                                                        {{-- totals --}}
                                                        <td>{{ $data['totalMale'] }}</td>
                                                        <td>{{ $data['totalFemale'] }}</td>
                                                        <td>{{ $data['totalStudents'] }}</td>
                                                    </tr>
                                                @endforeach

                                                {{-- ───────── grand‑totals row ───────── --}}
                                                @php $tot = $teacherTotals['__overall__']; @endphp
                                                <tr style="font-weight:600;background:#f3f3f3;">
                                                    <td colspan="3" class="text-start">Totals</td>

                                                    {{-- raw grade totals --}}
                                                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'] as $g)
                                                        <td>{{ $tot['grades'][$g]['M'] }}</td>
                                                        <td>{{ $tot['grades'][$g]['F'] }}</td>
                                                        <td>{{ $tot['grades'][$g]['T'] }}</td>
                                                    @endforeach

                                                    {{-- averaged % totals --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEFGU%'] as $p)
                                                        <td>{{ $tot[$p]['M'] }}%</td>
                                                        <td>{{ $tot[$p]['F'] }}%</td>
                                                    @endforeach

                                                    {{-- student totals --}}
                                                    <td>{{ $tot['totalMale'] }}</td>
                                                    <td>{{ $tot['totalFemale'] }}</td>
                                                    <td>{{ $tot['totalMale'] + $tot['totalFemale'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Graphs Section for non-grouped view -->
                            <div class="row no-print mt-4 mb-5">
                                <div class="col-md-12">
                                    <div id="mixChartOverall" style="width:100%;height:480px;"></div>
                                </div>
                            </div>
                        @endif
                    </div> <!-- report-card -->
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col -->
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        function initChart(domId, option) {
            var chartDom = document.getElementById(domId);
            if (!chartDom || typeof echarts === 'undefined') return null;
            var existing = echarts.getInstanceByDom(chartDom);
            if (existing) existing.dispose();
            var chart = echarts.init(chartDom);
            chart.setOption(option);
            var ro = new ResizeObserver(function() {
                if (chart && !chart.isDisposed()) chart.resize();
            });
            ro.observe(chartDom);
            return chart;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var grades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'NS'];
            var barColors = {
                'A*': '#6366f1', 'A': '#3b82f6', 'B': '#06b6d4', 'C': '#10b981',
                'D': '#eab308', 'E': '#f97316', 'F': '#ef4444', 'G': '#be185d',
                'U': '#78716c', 'NS': '#d4d4d4'
            };
            var lineColors = { 'AB%': '#2563eb', 'ABC%': '#059669', 'ABCD%': '#d97706', 'DEFGU%': '#dc2626' };
            var percentages = ['AB%', 'ABC%', 'ABCD%', 'DEFGU%'];

            function buildMixOption(title, labels, rows) {
                var barSeries = grades.map(function(g) {
                    return {
                        name: g,
                        type: 'bar',
                        stack: 'grades',
                        itemStyle: { color: barColors[g], borderRadius: g === grades[grades.length - 1] ? [2, 2, 0, 0] : 0 },
                        emphasis: { focus: 'series' },
                        data: rows.map(function(r) { return r.grades[g]['T']; })
                    };
                });

                var lineSeries = percentages.map(function(p) {
                    return {
                        name: p,
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 6,
                        lineStyle: { width: 2, color: lineColors[p] },
                        itemStyle: { color: lineColors[p] },
                        data: rows.map(function(r) {
                            var tot = r.grades.total ? r.grades.total['T'] : r.totalStudents;
                            if (tot === 0) return 0;
                            if (p === 'AB%') return +((( r.grades['A*']['T'] + r.grades['A']['T'] + r.grades['B']['T']) / tot * 100).toFixed(1));
                            if (p === 'ABC%') return +(((r.grades['A*']['T'] + r.grades['A']['T'] + r.grades['B']['T'] + r.grades['C']['T']) / tot * 100).toFixed(1));
                            if (p === 'ABCD%') return +(((r.grades['A*']['T'] + r.grades['A']['T'] + r.grades['B']['T'] + r.grades['C']['T'] + r.grades['D']['T']) / tot * 100).toFixed(1));
                            if (p === 'DEFGU%') return +(((r.grades['D']['T'] + r.grades['E']['T'] + r.grades['F']['T'] + r.grades['G']['T'] + r.grades['U']['T']) / tot * 100).toFixed(1));
                            return 0;
                        })
                    };
                });

                return {
                    title: { text: title, left: 'center', textStyle: { fontSize: 14, fontWeight: 600 } },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'cross' },
                        formatter: function(params) {
                            var tip = '<strong>' + params[0].axisValue + '</strong><br/>';
                            params.forEach(function(p) {
                                if (p.value === 0 && p.seriesType === 'bar') return;
                                var marker = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + p.color + ';margin-right:5px;"></span>';
                                tip += marker + p.seriesName + ': ' + p.value + (p.seriesType === 'line' ? '%' : '') + '<br/>';
                            });
                            return tip;
                        }
                    },
                    legend: {
                        data: grades.concat(percentages),
                        top: 30,
                        type: 'scroll',
                        textStyle: { fontSize: 11 }
                    },
                    grid: { top: 80, bottom: 10, left: '3%', right: '5%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: { interval: 0, fontSize: 10, rotate: labels.length > 4 ? 20 : 0 }
                    },
                    yAxis: [
                        { type: 'value', name: 'Students', position: 'left', splitLine: { lineStyle: { type: 'dashed' } } },
                        { type: 'value', name: '%', position: 'right', min: 0, max: 100, splitLine: { show: false }, axisLabel: { formatter: '{value}%' } }
                    ],
                    series: barSeries.concat(lineSeries),
                    toolbox: {
                        right: 20,
                        feature: {
                            saveAsImage: { title: 'Save' },
                            magicType: { type: ['line', 'bar'], title: { line: 'Line', bar: 'Bar' } },
                            restore: { title: 'Restore' }
                        }
                    }
                };
            }

            @if (isset($isGrouped) && $isGrouped)
                var teacherPerformance = @json($teacherPerformance);
                var subjectList = @json($subjectList);

                subjectList.forEach(function(subject) {
                    if (!teacherPerformance[subject] || teacherPerformance[subject].length === 0) return;

                    var rows = teacherPerformance[subject];
                    var labels = rows.map(function(r) { return r.teacher_name + ' — ' + r.class_name; });
                    var domId = 'mixChart_' + subject.replace(/ /g, '_');
                    initChart(domId, buildMixOption(subject + ' — Performance Analysis', labels, rows));
                });
            @else
                var teacherPerformance = @json($teacherPerformance);
                var labels = teacherPerformance.map(function(r) {
                    return r.teacher_name + ' — ' + r.class_name + ' — ' + r.subject_name;
                });
                initChart('mixChartOverall', buildMixOption('Teacher Performance — Grade Distribution & Percentages', labels, teacherPerformance));
            @endif
        });
    </script>
@endsection

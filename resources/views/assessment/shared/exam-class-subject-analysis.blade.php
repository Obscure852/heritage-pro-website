@extends('layouts.master')
@section('title')
    Grade Subjects Exam Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
        <a href="#" onclick="event.preventDefault(); 
            if (document.referrer) {    
                history.back();
            } else {
                window.location = '{{ $gradebookBackUrl }}';
            }">Back</a>
        @endslot
        @slot('title')
            Grade Subjects Analysis
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

        @page {
            size: A4 landscape;
            margin: 0;
        }

        @media print {
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                font-size: 10px;
                line-height: normal;
                overflow: visible;
            }

            body * {
                visibility: hidden;
            }

            .printable, .printable * {
                visibility: visible;
            }

            .printable {
                position: static;
                width: 100% !important;
                margin: 0;
                padding: 10mm;
                page-break-after: avoid;
            }

            .card-header {
                padding: 5mm 0;
            }

            .table-responsive {
                margin-top: 5mm;
            }

            .table {
                width: 100% !important;
                table-layout: fixed;
                border-collapse: collapse;
                font-size: 8pt;
            }

            .table th,
            .table td {
                padding: 2mm;
                border: 0.5pt solid black;
            }

            .chart-container {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                width: 100% !important;
            }

            .card-body {
                padding: 0;
                width: 100% !important;
            }

            h5 {
                margin: 3mm 0;
                font-size: 11pt;
            }

            tr {
                page-break-inside: avoid;
            }

            br {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    @endsection
    <div class="row">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <i onclick="window.location.href = window.location.pathname + '?export=true'"
                style="font-size: 20px; margin-bottom: 10px; cursor: pointer;" class="bx bx-download text-muted me-2"
                  title="Export to Excel"></i>

            <i onclick="printContent()" style="font-size: 20px; margin-bottom: 10px; cursor: pointer;" class="bx bx-printer text-muted me-2"
                title="Print"></i>
        </div>
    </div>
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span> {{ $school_data->physical_address }}</span>
                                <br>
                                <span> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5>
                        @if(isset($test1->type) && strtolower($test1->type) === 'exam')
                            {{ $klass->grade->name ?? '' }} - End Of Term Exam Grade Subjects 
                        @elseif(isset($test1->type) && strtolower($test1->type) === 'ca')
                        {{ $klass->grade->name ?? '' }} - End Of {{ $test1->name ?? '' }} Grade Subjects
                        @else
                            {{ $klass->grade->name ?? '' }} - End Of {{ $test1->name ?? '' }} Grade Subjects 
                        @endif
                        Analysis - Term {{ $test1->term->term ?? '' }}, {{ $test1->term->year ?? '' }}
                    </h5>
                    <div class="col-md-12 col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2"><strong>Subject</strong></th>
                                        @foreach (['A','B','C','D','E','U'] as $g)
                                            <th class="text-center" colspan="3"><strong>{{ $g }}</strong></th>
                                        @endforeach
                                        <th class="text-center" colspan="3"><strong>No Scores</strong></th>
                                        <th class="text-center" colspan="3"><strong>Total w/ Scores</strong></th>
                                        <th class="text-center" colspan="3"><strong>Total Enrolled</strong></th>
                                        @foreach (['AB%','ABC%','ABCD%','DEU%'] as $pct)
                                            <th class="text-center" colspan="3"><strong>{{ $pct }}</strong></th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach (array_fill(0, 6+3+2, 0) as $i)   {{-- 6 grades + NS + 2 totals --}}
                                            <th>M</th><th>F</th><th>T</th>
                                        @endforeach
                                        @foreach (array_fill(0, 2, 0) as $i)   {{-- 2 percentage groups --}}
                                            <th>M</th><th>F</th><th>T</th>
                                        @endforeach
                                    </tr>
                                </thead>
                            
                                <tbody>
                                    @foreach ($subjectPerformance as $subjectName => $c)
                                        <tr>
                                            <td>{{ $subjectName }}</td>
                                            {{-- Grade counts --}}
                                            @foreach (['A','B','C','D','E','U'] as $g)
                                                <td>{{ $c[$g]['M'] }}</td>
                                                <td>{{ $c[$g]['F'] }}</td>
                                                <td>{{ $c[$g]['M'] + $c[$g]['F'] }}</td>
                                            @endforeach
                                            
                                            {{-- No Score count --}}
                                            <td>{{ $c['NS']['M'] }}</td>
                                            <td>{{ $c['NS']['F'] }}</td>
                                            <td>{{ $c['NS']['M'] + $c['NS']['F'] }}</td>
                                            
                                            {{-- Total with Scores --}}
                                            <td>{{ $c['totalWithScores']['M'] }}</td>
                                            <td>{{ $c['totalWithScores']['F'] }}</td>
                                            <td>{{ $c['totalWithScores']['M'] + $c['totalWithScores']['F'] }}</td>
                                            
                                            {{-- Total Enrolled --}}
                                            <td>{{ $c['totalEnrolled']['M'] }}</td>
                                            <td>{{ $c['totalEnrolled']['F'] }}</td>
                                            <td>{{ $c['totalEnrolled']['M'] + $c['totalEnrolled']['F'] }}</td>
                                            
                                            {{-- Percentages --}}
                                            @php
                                                $totalWithScores = $c['totalWithScores']['M'] + $c['totalWithScores']['F'];
                                            @endphp
                                            @foreach (['AB%','ABC%','ABCD%','DEU%'] as $pct)
                                                <td>{{ $c[$pct]['M'] }}%</td>
                                                <td>{{ $c[$pct]['F'] }}%</td>
                                                <td>
                                                    @if($pct == 'AB%')
                                                        {{ $totalWithScores > 0 ? round(($c['A']['M'] + $c['A']['F'] + $c['B']['M'] + $c['B']['F']) / $totalWithScores * 100, 1) : 0 }}%
                                                    @elseif($pct == 'ABC%')
                                                        {{ $totalWithScores > 0 ? round(($c['A']['M'] + $c['A']['F'] + $c['B']['M'] + $c['B']['F'] + $c['C']['M'] + $c['C']['F']) / $totalWithScores * 100, 1) : 0 }}%
                                                    @elseif($pct == 'ABCD%')
                                                        {{ $totalWithScores > 0 ? round(($c['A']['M'] + $c['A']['F'] + $c['B']['M'] + $c['B']['F'] + $c['C']['M'] + $c['C']['F'] + $c['D']['M'] + $c['D']['F']) / $totalWithScores * 100, 1) : 0 }}%
                                                    @elseif($pct == 'DEU%')
                                                        {{ $totalWithScores > 0 ? round(($c['D']['M'] + $c['D']['F'] + $c['E']['M'] + $c['E']['F'] + $c['U']['M'] + $c['U']['F']) / $totalWithScores * 100, 1) : 0 }}%
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                            
                                    {{-- ▼▼  GRAND TOTALS  ▼▼ --}}
                                    <tr style="font-weight:600;background:#f3f3f3;">
                                        <td>Totals</td>
                                        {{-- Grade totals --}}
                                        @foreach (['A','B','C','D','E','U'] as $g)
                                            <td>{{ $subjectTotals[$g]['M'] }}</td>
                                            <td>{{ $subjectTotals[$g]['F'] }}</td>
                                            <td>{{ $subjectTotals[$g]['M'] + $subjectTotals[$g]['F'] }}</td>
                                        @endforeach
                                        
                                        {{-- No Score totals --}}
                                        <td>{{ $subjectTotals['NS']['M'] }}</td>
                                        <td>{{ $subjectTotals['NS']['F'] }}</td>
                                        <td>{{ $subjectTotals['NS']['M'] + $subjectTotals['NS']['F'] }}</td>
                                        
                                        {{-- Total with Scores --}}
                                        <td>{{ $subjectTotals['totalWithScores']['M'] }}</td>
                                        <td>{{ $subjectTotals['totalWithScores']['F'] }}</td>
                                        <td>{{ $subjectTotals['totalWithScores']['M'] + $subjectTotals['totalWithScores']['F'] }}</td>
                                        
                                        {{-- Total Enrolled --}}
                                        <td>{{ $subjectTotals['totalEnrolled']['M'] }}</td>
                                        <td>{{ $subjectTotals['totalEnrolled']['F'] }}</td>
                                        <td>{{ $subjectTotals['totalEnrolled']['M'] + $subjectTotals['totalEnrolled']['F'] }}</td>
                                        
                                        {{-- Percentage totals --}}
                                        @php
                                            $grandTotalWithScores = $subjectTotals['totalWithScores']['M'] + $subjectTotals['totalWithScores']['F'];
                                        @endphp
                                        @foreach (['AB%','ABC%','ABCD%','DEU%'] as $pct)
                                            <td>{{ $subjectTotals[$pct]['M'] }}%</td>
                                            <td>{{ $subjectTotals[$pct]['F'] }}%</td>
                                            <td>
                                                @if($pct == 'AB%')
                                                    {{ $grandTotalWithScores > 0 ? round(($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F']) / $grandTotalWithScores * 100, 1) : 0 }}%
                                                @elseif($pct == 'ABC%')
                                                    {{ $grandTotalWithScores > 0 ? round(($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F'] + $subjectTotals['C']['M'] + $subjectTotals['C']['F']) / $grandTotalWithScores * 100, 1) : 0 }}%
                                                @elseif($pct == 'ABCD%')
                                                    {{ $grandTotalWithScores > 0 ? round(($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F'] + $subjectTotals['C']['M'] + $subjectTotals['C']['F'] + $subjectTotals['D']['M'] + $subjectTotals['D']['F']) / $grandTotalWithScores * 100, 1) : 0 }}%
                                                @elseif($pct == 'DEU%')
                                                    {{ $grandTotalWithScores > 0 ? round(($subjectTotals['D']['M'] + $subjectTotals['D']['F'] + $subjectTotals['E']['M'] + $subjectTotals['E']['F'] + $subjectTotals['U']['M'] + $subjectTotals['U']['F']) / $grandTotalWithScores * 100, 1) : 0 }}%
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <br>
                    <div class="chart-container">
                        <div id="subjectPerformanceChart" style="width: 100%; height: 600px;"></div>
                    </div>
                </div>
            </div>
            <br>
            <br>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>  
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var subjectPerformance = @json($subjectPerformance);
            var subjects = Object.keys(subjectPerformance);
            
            if (subjects.length === 0) {
                document.getElementById('subjectPerformanceChart').innerHTML = 
                    '<p style="text-align:center; padding-top:50px;">No data available to display the chart.</p>';
                return;
            }
            
            var chartDom = document.getElementById('subjectPerformanceChart');
            var myChart = echarts.init(chartDom);
            
            const colors = {
                gradeA: '#91cc75',  // Green
                gradeB: '#5470c6',  // Blue
                gradeC: '#fac858',  // Yellow
                gradeD: '#fc8452',  // Orange
                gradeE: '#ee6666',  // Red
                gradeU: '#909399',  // Gray
                gradeNS: '#d3d3d3', // Light Gray for No Score
                
                lineAB: '#5470c6',   // Blue
                lineABC: '#91cc75',  // Green
                lineABCD: '#9a60b4', // Purple
                lineDEU: '#ee6666'   // Red
            };
            
            var grades = ['A', 'B', 'C', 'D', 'E', 'U', 'NS'];
            var percentageLabels = ['AB%', 'ABC%', 'ABCD%', 'DEU%'];
            
            function getSafeValue(obj, key1, key2, defaultValue = 0) {
                if (obj && obj[key1] && typeof obj[key1][key2] !== 'undefined') {
                    const val = parseFloat(obj[key1][key2]);
                    return isNaN(val) ? defaultValue : val;
                }
                return defaultValue;
            }
            
            var gradeData = grades.map(function(grade) {
                return subjects.map(function(subject) {
                    const maleCount = getSafeValue(subjectPerformance[subject], grade, 'M');
                    const femaleCount = getSafeValue(subjectPerformance[subject], grade, 'F');
                    return maleCount + femaleCount;
                });
            });
            
            var percentageData = percentageLabels.map(function(pct) {
                return subjects.map(function(subject) {
                    const malePercent = getSafeValue(subjectPerformance[subject], pct, 'M');
                    const femalePercent = getSafeValue(subjectPerformance[subject], pct, 'F');
                    
                    // Calculate total percentage based on actual counts
                    const totalWithScores = getSafeValue(subjectPerformance[subject], 'totalWithScores', 'M') + 
                                          getSafeValue(subjectPerformance[subject], 'totalWithScores', 'F');
                    
                    if (totalWithScores === 0) return 0;
                    
                    let count = 0;
                    if (pct === 'AB%') {
                        count = getSafeValue(subjectPerformance[subject], 'A', 'M') + getSafeValue(subjectPerformance[subject], 'A', 'F') +
                               getSafeValue(subjectPerformance[subject], 'B', 'M') + getSafeValue(subjectPerformance[subject], 'B', 'F');
                    } else if (pct === 'ABC%') {
                        count = getSafeValue(subjectPerformance[subject], 'A', 'M') + getSafeValue(subjectPerformance[subject], 'A', 'F') +
                               getSafeValue(subjectPerformance[subject], 'B', 'M') + getSafeValue(subjectPerformance[subject], 'B', 'F') +
                               getSafeValue(subjectPerformance[subject], 'C', 'M') + getSafeValue(subjectPerformance[subject], 'C', 'F');
                    } else if (pct === 'ABCD%') {
                        count = getSafeValue(subjectPerformance[subject], 'A', 'M') + getSafeValue(subjectPerformance[subject], 'A', 'F') +
                               getSafeValue(subjectPerformance[subject], 'B', 'M') + getSafeValue(subjectPerformance[subject], 'B', 'F') +
                               getSafeValue(subjectPerformance[subject], 'C', 'M') + getSafeValue(subjectPerformance[subject], 'C', 'F') +
                               getSafeValue(subjectPerformance[subject], 'D', 'M') + getSafeValue(subjectPerformance[subject], 'D', 'F');
                    } else if (pct === 'DEU%') {
                        count = getSafeValue(subjectPerformance[subject], 'D', 'M') + getSafeValue(subjectPerformance[subject], 'D', 'F') +
                               getSafeValue(subjectPerformance[subject], 'E', 'M') + getSafeValue(subjectPerformance[subject], 'E', 'F') +
                               getSafeValue(subjectPerformance[subject], 'U', 'M') + getSafeValue(subjectPerformance[subject], 'U', 'F');
                    }
                    
                    return parseFloat((count / totalWithScores * 100).toFixed(1));
                });
            });
            
            var gradeSeries = grades.map(function(grade, index) {
                return {
                    name: grade,
                    type: 'bar',
                    stack: 'grades',
                    emphasis: { focus: 'series' },
                    data: gradeData[index],
                    color: colors['grade' + grade],
                    barWidth: '60%',
                    z: 10 - index
                };
            });
            
            var percentageSeries = [
                {
                    name: 'AB%',
                    type: 'line',
                    yAxisIndex: 1,
                    data: percentageData[0],
                    symbol: 'circle',
                    symbolSize: 8,
                    color: colors.lineAB,
                    smooth: true,
                    z: 15
                },
                {
                    name: 'ABC%',
                    type: 'line',
                    yAxisIndex: 1,
                    data: percentageData[1],
                    symbol: 'triangle',
                    symbolSize: 8,
                    color: colors.lineABC,
                    smooth: true,
                    z: 16
                }
            ];
            
            // Chart options
            var option = {
                title: {
                    text: 'Subject Performance Analysis',
                    left: 'center',
                    top: 10
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                legend: {
                    data: [...grades, 'AB%', 'ABC%'],
                    top: 40
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '10%',
                    top: '20%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {},
                        restore: {},
                        dataView: { readOnly: true },
                        magicType: { type: ['line', 'bar', 'stack'] }
                    },
                    right: 20,
                    top: 20
                },
                xAxis: {
                    type: 'category',
                    data: subjects,
                    axisLabel: {
                        interval: 0,
                        rotate: subjects.length > 8 ? 45 : 0,
                        formatter: function(value) {
                            return value.length > 15 ? value.substring(0, 12) + '...' : value;
                        }
                    }
                },
                yAxis: [
                    {
                        type: 'value',
                        name: 'Students',
                        position: 'left'
                    },
                    {
                        type: 'value',
                        name: 'Percentage',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLabel: { formatter: '{value}%' }
                    }
                ],
                series: [...gradeSeries, ...percentageSeries],
                dataZoom: [
                    {
                        type: 'slider',
                        show: subjects.length > 10,
                        bottom: 10
                    },
                    {
                        type: 'inside'
                    }
                ]
            };
        
            myChart.setOption(option);
            window.addEventListener('resize', function() {
                if (myChart && !myChart.isDisposed()) {
                    myChart.resize();
                }
            });
        });
    </script>
@endsection
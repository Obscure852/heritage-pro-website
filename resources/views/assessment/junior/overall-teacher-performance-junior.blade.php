@extends('layouts.master')
@section('title')
    Overall Teacher Performance - {{ $grade->name }}
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
            Grade Overall Teachers Performance
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted me-2"></i>
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
                        <div class="row">
                            <div class="col-12">
                                <h5>
                                    {{ $grade->name }} -
                                    @if(isset($test->type) && strtolower($test->type) === 'exam')
                                        End Of Term Exam
                                    @elseif(isset($test->type) && strtolower($test->type) === 'ca')
                                        End Of {{ $test->name ?? '' }}
                                    @else
                                        End Of {{ $test->name ?? '' }}
                                    @endif
                                    Overall Teacher Performance Analysis Ranked - Term {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }}
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="text-align:left" rowspan="2">Teacher</th>
                                                {{-- grade groups --}}
                                                @foreach (['A','B','C','D','E','U'] as $grade)
                                                    <th colspan="3">{{ $grade }}</th>
                                                @endforeach
                                                <th colspan="3">Total</th>
                                                {{-- percentage groups --}}
                                                @foreach (['AB%','ABC%','ABCD%'] as $percentage)
                                                    <th colspan="3">{{ $percentage }}</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @for ($i = 0; $i < 6 + 1 + 3; $i++)  {{-- 6 grades + TOT + 3 % groups --}}
                                                    <th>M</th><th>F</th><th>T</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                    
                                        <tbody>
                                            @if (!empty($teacherPerformance))
                                                {{-- per‑teacher rows --}}
                                                @foreach ($teacherPerformance as $data)
                                                    <tr>
                                                        <td style="text-align:left">{{ $data['teacher_name'] }}</td>
                                    
                                                        {{-- Grade counts --}}
                                                        @foreach (['A','B','C','D','E','U'] as $g)
                                                            <td>{{ $data['grades'][$g]['M'] }}</td>
                                                            <td>{{ $data['grades'][$g]['F'] }}</td>
                                                            <td>{{ $data['grades'][$g]['M'] + $data['grades'][$g]['F'] }}</td>
                                                        @endforeach
                                                        
                                                        {{-- Total --}}
                                                        <td>{{ $data['totalMale'] }}</td>
                                                        <td>{{ $data['totalFemale'] }}</td>
                                                        <td>{{ $data['totalMale'] + $data['totalFemale'] }}</td>
                                    
                                                        {{-- Percentages --}}
                                                        @php
                                                            $totalStudents = $data['totalMale'] + $data['totalFemale'];
                                                        @endphp
                                                        
                                                        <!-- AB% -->
                                                        <td>{{ $data['AB%']['M'] }}%</td>
                                                        <td>{{ $data['AB%']['F'] }}%</td>
                                                        <td>{{ $totalStudents > 0 ? round(($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F']) / $totalStudents * 100) : 0 }}%</td>
                                                        
                                                        <!-- ABC% -->
                                                        <td>{{ $data['ABC%']['M'] }}%</td>
                                                        <td>{{ $data['ABC%']['F'] }}%</td>
                                                        <td>{{ $totalStudents > 0 ? round(($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F']) / $totalStudents * 100) : 0 }}%</td>
                                                        
                                                        <!-- ABCD% -->
                                                        <td>{{ $data['ABCD%']['M'] }}%</td>
                                                        <td>{{ $data['ABCD%']['F'] }}%</td>
                                                        <td>{{ $totalStudents > 0 ? round(($data['grades']['A']['M'] + $data['grades']['A']['F'] + $data['grades']['B']['M'] + $data['grades']['B']['F'] + $data['grades']['C']['M'] + $data['grades']['C']['F'] + $data['grades']['D']['M'] + $data['grades']['D']['F']) / $totalStudents * 100) : 0 }}%</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="31" class="text-center">
                                                        No teacher performance data available
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="row no-print mt-4">
                            <div class="col-md-12">
                                <h5 class="text-center">Teacher Performance Comparison - Grade Distribution</h5>
                                <div id="gradeDistributionChart" style="width: 100%; height: 500px;"></div>
                            </div>
                        </div>
                        
                        <div class="row no-print mt-4">
                            <div class="col-md-12">
                                <h5 class="text-center">Teacher Performance Comparison - Percentage Analysis</h5>
                                <div id="percentageChart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const teacherPerformance = @json($teacherPerformance ?? []);
            console.log('Teacher Performance Data:', teacherPerformance);
            
            if (!Array.isArray(teacherPerformance) || teacherPerformance.length === 0) {
                const gradeChart = document.getElementById('gradeDistributionChart');
                const percChart = document.getElementById('percentageChart');
                
                if (gradeChart) gradeChart.innerHTML = '<p style="text-align:center; padding:50px;">No data available for charts.</p>';
                if (percChart) percChart.innerHTML = '<p style="text-align:center; padding:50px;">No data available for charts.</p>';
                return;
            }

            const colors = {
                gradeA: '#91cc75',  // Green
                gradeB: '#5470c6',  // Blue
                gradeC: '#fac858',  // Yellow
                gradeD: '#fc8452',  // Orange
                gradeE: '#ee6666',  // Red
                gradeU: '#909399',  // Gray
                
                lineAB: '#5470c6',   // Blue
                lineABC: '#91cc75',  // Green
                lineABCD: '#9a60b4'  // Purple
            };
            
            const grades = ['A', 'B', 'C', 'D', 'E', 'U'];
            const percentageLabels = ['AB%', 'ABC%', 'ABCD%'];
            
            const teacherNames = teacherPerformance.map(item => item.teacher_name || 'Unknown');
            
            // Grade Distribution Chart
            const gradeChartDom = document.getElementById('gradeDistributionChart');
            const gradeChart = echarts.init(gradeChartDom);
            
            const gradeData = grades.map(grade => {
                return teacherPerformance.map(item => {
                    const maleCount = item?.grades?.[grade]?.['M'] ?? 0;
                    const femaleCount = item?.grades?.[grade]?.['F'] ?? 0;
                    return maleCount + femaleCount;
                });
            });
            
            const gradeSeries = grades.map((grade, index) => ({
                name: grade,
                type: 'bar',
                stack: 'grades',
                emphasis: { focus: 'series' },
                data: gradeData[index],
                color: colors['grade' + grade],
                barWidth: '60%'
            }));
            
            const gradeOption = {
                title: {
                    text: 'Grade Distribution by Teacher',
                    left: 'center',
                    textStyle: { fontSize: 16 }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                legend: {
                    data: grades,
                    top: 40
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
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
                    data: teacherNames,
                    axisLabel: {
                        interval: 0,
                        rotate: teacherNames.length > 8 ? 45 : 0,
                        formatter: function(value) {
                            return value.length > 20 ? value.substring(0, 17) + '...' : value;
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Number of Students'
                },
                series: gradeSeries,
                dataZoom: [
                    {
                        type: 'slider',
                        show: teacherNames.length > 10,
                        bottom: 10
                    },
                    {
                        type: 'inside'
                    }
                ]
            };
            
            gradeChart.setOption(gradeOption);
            
            // Percentage Chart
            const percentageChartDom = document.getElementById('percentageChart');
            const percentageChart = echarts.init(percentageChartDom);
            
            const percentageData = percentageLabels.map(pct => {
                return teacherPerformance.map(item => {
                    const totalStudents = (item?.totalMale ?? 0) + (item?.totalFemale ?? 0);
                    
                    if (totalStudents === 0) return 0;
                    
                    let count = 0;
                    if (pct === 'AB%') {
                        count = (item?.grades?.['A']?.['M'] ?? 0) + (item?.grades?.['A']?.['F'] ?? 0) +
                               (item?.grades?.['B']?.['M'] ?? 0) + (item?.grades?.['B']?.['F'] ?? 0);
                    } else if (pct === 'ABC%') {
                        count = (item?.grades?.['A']?.['M'] ?? 0) + (item?.grades?.['A']?.['F'] ?? 0) +
                               (item?.grades?.['B']?.['M'] ?? 0) + (item?.grades?.['B']?.['F'] ?? 0) +
                               (item?.grades?.['C']?.['M'] ?? 0) + (item?.grades?.['C']?.['F'] ?? 0);
                    } else if (pct === 'ABCD%') {
                        count = (item?.grades?.['A']?.['M'] ?? 0) + (item?.grades?.['A']?.['F'] ?? 0) +
                               (item?.grades?.['B']?.['M'] ?? 0) + (item?.grades?.['B']?.['F'] ?? 0) +
                               (item?.grades?.['C']?.['M'] ?? 0) + (item?.grades?.['C']?.['F'] ?? 0) +
                               (item?.grades?.['D']?.['M'] ?? 0) + (item?.grades?.['D']?.['F'] ?? 0);
                    }
                    
                    return parseFloat((count / totalStudents * 100).toFixed(1));
                });
            });
            
            const percentageSeries = percentageLabels.map((pct, index) => ({
                name: pct,
                type: 'line',
                data: percentageData[index],
                symbol: 'circle',
                symbolSize: 8,
                color: colors['line' + pct.replace('%', '')],
                smooth: true,
                lineStyle: { width: 3 }
            }));
            
            const percentageOption = {
                title: {
                    text: 'Percentage Performance by Teacher',
                    left: 'center',
                    textStyle: { fontSize: 16 }
                },
                tooltip: {
                    trigger: 'axis',
                    valueFormatter: val => val + '%'
                },
                legend: {
                    data: percentageLabels,
                    top: 40
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top: '20%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {},
                        restore: {},
                        dataView: { readOnly: true },
                        magicType: { type: ['line', 'bar'] }
                    },
                    right: 20,
                    top: 20
                },
                xAxis: {
                    type: 'category',
                    data: teacherNames,
                    axisLabel: {
                        interval: 0,
                        rotate: teacherNames.length > 8 ? 45 : 0,
                        formatter: function(value) {
                            return value.length > 20 ? value.substring(0, 17) + '...' : value;
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Percentage',
                    min: 0,
                    max: 100,
                    axisLabel: { formatter: '{value}%' }
                },
                series: percentageSeries,
                dataZoom: [
                    {
                        type: 'slider',
                        show: teacherNames.length > 10,
                        bottom: 10
                    },
                    {
                        type: 'inside'
                    }
                ]
            };
            
            percentageChart.setOption(percentageOption);
            window.addEventListener('resize', function() {
                if (gradeChart && !gradeChart.isDisposed()) {
                    gradeChart.resize();
                }
                if (percentageChart && !percentageChart.isDisposed()) {
                    percentageChart.resize();
                }
            });
        });
    </script>
@endsection
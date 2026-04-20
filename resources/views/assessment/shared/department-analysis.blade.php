@extends('layouts.master')
@section('css')
    <style>
        .report-card+.report-card {
            margin-top: 2rem;
        }

        .dept-header {
            background: #f8f9fa;
        }

        .report-table thead th {
            vertical-align: middle;
        }

        .report-table thead td {
            font-size: 10px;
        }

        .chart-container {
            position: relative;
            height: 380px;
            margin-top: .75rem;
        }

        .total-col {
            font-weight: 500;
        }

        .totals-row {
            font-weight: 600;
        }

        .department-section {
            margin-bottom: 2rem;
        }

        @media print {
            body {
                font-size: 9pt;
                margin: 0;
            }

            .report-card {
                box-shadow: none !important;
                page-break-inside: avoid;
            }

            .chart-container {
                height: 300px;
            }

            .no-print {
                display: none !important;
            }

            .total-col {
                background-color: #f3f3f3 !important;
                print-color-adjust: exact;
            }

            .totals-row {
                background-color: #e6e6e6 !important;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="#"
                onclick="event.preventDefault(); 
        if (document.referrer) {
        history.back();
        } else {
        window.location = '{{ $gradebookBackUrl }}';
        }   
    ">Back</a>
        @endslot
        @slot('title')
            Department Analysis
        @endslot
    @endcomponent
    <div class="container-fluid">
        <div class="row mb-2 no-print">
            <div class="col-12 text-end">
                <i class="bx bx-download text-muted me-2" style="font-size:20px;cursor:pointer;" title="Export to Excel"
                    onclick="location.href='{{ request()->fullUrlWithQuery(['export' => true]) }}'"></i>
                <i class="bx bx-printer text-muted" style="font-size:20px;cursor:pointer;" title="Print"
                    onclick="printContent()"></i>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6 col-lg-6 align-items-start">
                        <div style="font-size:14px;" class="form-group">
                            <strong>{{ $school_data->school_name }}</strong>
                            <br>
                            <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                            <br>
                            <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
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
                @if ($test->type == 'CA')
                    <h5>{{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Departments Analysis</h5>
                @else
                    <h5>{{ $test->grade->name ?? 'Grade' }} - End of Term Departments Analysis</h5>
                @endif
                @forelse($performanceData as $deptName => $dept)
                    <div class="department-section">
                        <h6>{{ $deptName }} Analysis</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered report-table" style="font-size: 12px;">
                                <thead class="text-center align-middle">
                                    <tr>
                                        <th rowspan="2" class="text-start">Subject</th>
                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                            <th colspan="3">{{ $g }}</th>
                                        @endforeach
                                        <th rowspan="2">AB %</th>
                                        <th rowspan="2">ABC %</th>
                                        <th rowspan="2">ABCD %</th>
                                        <th rowspan="2">Total</th>
                                    </tr>
                                    <tr>
                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                            <th>M</th>
                                            <th>F</th>
                                            <th class="total-col">T</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $deptTotals = array_fill_keys(
                                            ['A', 'B', 'C', 'D', 'E', 'U'],
                                            ['M' => 0, 'F' => 0, 'T' => 0],
                                        );
                                        $sum_ab = 0;
                                        $sum_abc = 0;
                                        $sum_abcd = 0;
                                        $subjectCount = count($dept['subjects']);
                                        $grandTotal = 0;
                                    @endphp
                                    @foreach ($dept['subjects'] as $subject => $row)
                                        <tr>
                                            <td class="text-start">{{ $subject }}</td>
                                            @php
                                                $subjectTotal = 0;
                                            @endphp
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                @php
                                                    $maleCount = $row['grades'][$g]['M'] ?? 0;
                                                    $femaleCount = $row['grades'][$g]['F'] ?? 0;
                                                    $total = $maleCount + $femaleCount;
                                                    $subjectTotal += $total;
                                                    $deptTotals[$g]['M'] += $maleCount;
                                                    $deptTotals[$g]['F'] += $femaleCount;
                                                    $deptTotals[$g]['T'] += $total;
                                                @endphp
                                                <td class="text-center">{{ $maleCount }}</td>
                                                <td class="text-center">{{ $femaleCount }}</td>
                                                <td class="text-center total-col">{{ $total }}</td>
                                            @endforeach
                                            <td class="text-center">{{ $row['ab_percent'] }} %</td>
                                            <td class="text-center">{{ $row['abc_percent'] }} %</td>
                                            <td class="text-center">{{ $row['abcd_percent'] }} %</td>
                                            <td class="text-center fw-semibold">{{ $subjectTotal }}</td>
                                            @php
                                                $sum_ab += $row['ab_percent'];
                                                $sum_abc += $row['abc_percent'];
                                                $sum_abcd += $row['abcd_percent'];
                                                $grandTotal += $subjectTotal;
                                            @endphp
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td class="text-start">Subtotal</td>
                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                            <td class="text-center">{{ $deptTotals[$g]['M'] }}</td>
                                            <td class="text-center">{{ $deptTotals[$g]['F'] }}</td>
                                            <td class="text-center total-col">{{ $deptTotals[$g]['T'] }}</td>
                                        @endforeach
                                        <td class="text-center">
                                            {{ $subjectCount > 0 ? round($sum_ab / $subjectCount) : 0 }} %</td>
                                        <td class="text-center">
                                            {{ $subjectCount > 0 ? round($sum_abc / $subjectCount) : 0 }} %</td>
                                        <td class="text-center">
                                            {{ $subjectCount > 0 ? round($sum_abcd / $subjectCount) : 0 }} %</td>
                                        <td class="text-center fw-semibold">{{ $grandTotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="chart-{{ Str::slug($deptName) }}" class="chart-container"></div>
                    </div>
                @empty
                    <div class="alert alert-info">No data available for this grade in this term.</div>
                @endforelse
                <div class="department-section">
                    <h5>Overall Totals (All Departments)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered report-table">
                            <thead class="text-center">
                                <tr>
                                    <th class="text-start"></th>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                        <th colspan="3">{{ $g }}</th>
                                    @endforeach
                                    <th>AB %</th>
                                    <th>ABC %</th>
                                    <th>ABCD %</th>
                                    <th>Total</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                        <th>M</th>
                                        <th>F</th>
                                        <th class="total-col">T</th>
                                    @endforeach
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center fw-semibold">
                                    <td class="text-start">Total</td>
                                    @php
                                        $overallTotal = 0;
                                    @endphp
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                        @php
                                            $maleCount = $totals['gender'][$g]['M'] ?? 0;
                                            $femaleCount = $totals['gender'][$g]['F'] ?? 0;
                                            $gradeTotal = $maleCount + $femaleCount;
                                            $overallTotal += $gradeTotal;
                                        @endphp
                                        <td>{{ $maleCount }}</td>
                                        <td>{{ $femaleCount }}</td>
                                        <td class="total-col">{{ $gradeTotal }}</td>
                                    @endforeach
                                    <td>{{ $totals['ab_percent'] }} %</td>
                                    <td>{{ $totals['abc_percent'] }} %</td>
                                    <td>{{ $totals['abcd_percent'] }} %</td>
                                    <td>{{ $overallTotal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
            console.log("DOM Loaded. Initializing Department Analysis charts...");

            window.echartsInstances = {};

            const performanceData = @json($performanceData ?? []);

            const echartsColors = {
                gradeA: '#91cc75', // Green
                gradeB: '#5470c6', // Blue
                gradeC: '#fac858', // Yellow
                gradeD: '#fc8452', // Orange
                gradeE: '#ee6666', // Red
                gradeU: '#9e9e9e', // Grey
                lineAB: '#2e7d32', // Dark Green for AB%
                lineABC: '#3ba272', // Darker Green/Teal
                lineABCD: '#73c0de' // Light Blue/Cyan
            };

            function initChart(domId, option) {
                const chartDom = document.getElementById(domId);
                if (chartDom) {
                    try {
                        const existingInstance = echarts.getInstanceByDom(chartDom);
                        if (existingInstance) {
                            existingInstance.dispose();
                        }
                        const chart = echarts.init(chartDom);
                        chart.setOption(option);
                        window.echartsInstances[domId] = chart;
                        const resizeObserver = new ResizeObserver(() => {
                            if (chart && !chart.isDisposed()) {
                                chart.resize();
                            }
                        });
                        resizeObserver.observe(chartDom);
                        window.addEventListener('resize', () => {
                            if (chart && !chart.isDisposed()) {
                                chart.resize();
                            }
                        });
                        return chart;
                    } catch (e) {
                        chartDom.innerHTML =
                            `<p style="text-align:center; padding:20px; color: red;">Error loading chart #${domId}.</p>`;
                    }
                } else {
                    console.warn(`Chart container #${domId} not found.`);
                }
                return null;
            }

            if (performanceData && Object.keys(performanceData).length > 0) {
                Object.entries(performanceData).forEach(([deptName, dept]) => {
                    const chartId = `chart-${deptName.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;
                    const subjects = Object.keys(dept.subjects);
                    const grades = ['A', 'B', 'C', 'D', 'E', 'U'];
                    const barSeries = grades.map(grade => ({
                        name: grade,
                        type: 'bar',
                        stack: 'total',
                        emphasis: {
                            focus: 'series'
                        },
                        color: echartsColors['grade' + grade] || '#ccc',
                        data: subjects.map(subject => {
                            const male = dept.subjects[subject].grades[grade]?.M ?? 0;
                            const female = dept.subjects[subject].grades[grade]?.F ?? 0;
                            return male + female;
                        })
                    }));
                    const lineSeries = [{
                            name: 'AB %',
                            type: 'line',
                            yAxisIndex: 1,
                            smooth: true,
                            color: echartsColors.lineAB,
                            data: subjects.map(subject => dept.subjects[subject].ab_percent)
                        },
                        {
                            name: 'ABC %',
                            type: 'line',
                            yAxisIndex: 1,
                            smooth: true,
                            color: echartsColors.lineABC,
                            data: subjects.map(subject => dept.subjects[subject].abc_percent)
                        },
                        {
                            name: 'ABCD %',
                            type: 'line',
                            yAxisIndex: 1,
                            smooth: true,
                            color: echartsColors.lineABCD,
                            data: subjects.map(subject => dept.subjects[subject].abcd_percent)
                        }
                    ];
                    const deptChartOption = {
                        title: {
                            text: `${deptName} Department`,
                            left: 'center',
                            textStyle: {
                                fontSize: 16
                            }
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            },
                            valueFormatter: (value) => typeof value === 'number' ? value.toFixed(1) :
                                value
                        },
                        legend: {
                            data: grades.concat(['AB %', 'ABC %', 'ABCD %']),
                            top: 30,
                            type: 'scroll'
                        },
                        grid: {
                            top: 70,
                            bottom: 30,
                            left: '3%',
                            right: '4%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: subjects,
                            axisLabel: {
                                interval: 0,
                                rotate: 30
                            }
                        },
                        yAxis: [{
                                type: 'value',
                                name: 'Student Count',
                                position: 'left',
                                splitLine: {
                                    lineStyle: {
                                        type: 'dashed'
                                    }
                                }
                            },
                            {
                                type: 'value',
                                name: 'Percentage (%)',
                                position: 'right',
                                min: 0,
                                max: 100,
                                splitLine: {
                                    show: false
                                },
                                axisLabel: {
                                    formatter: '{value}%'
                                }
                            }
                        ],
                        series: [...barSeries, ...lineSeries],
                        toolbox: {
                            right: 20,
                            feature: {
                                saveAsImage: {
                                    title: 'Save Image'
                                },
                                dataView: {
                                    readOnly: true,
                                    title: 'View Data'
                                },
                                magicType: {
                                    type: ['line', 'bar', 'stack'],
                                    title: {
                                        line: 'Line',
                                        bar: 'Bar',
                                        stack: 'Stack'
                                    }
                                },
                                restore: {
                                    title: 'Restore'
                                }
                            }
                        }
                    };
                    initChart(chartId, deptChartOption);
                });
            } else {
                console.warn("Performance data is empty or invalid. No charts will be rendered.");
            }
        });
    </script>
@endsection

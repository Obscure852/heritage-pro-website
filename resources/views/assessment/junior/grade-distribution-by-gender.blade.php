@extends('layouts.master')
@section('title')
    Grade Level Distribution Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#"
                onclick="event.preventDefault(); 
            if (document.referrer) {    
                history.back();
            } else {
                window.location = '{{ $gradebookBackUrl }}';
            }
        ">Back</a>
        @endslot
        @slot('title')
            Overall Grade Distribution Analysis by Gender
        @endslot
    @endcomponent

@section('css')
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                font-size: 12pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .chart-container {
                break-inside: avoid;
            }
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .chart-container {
            height: 350px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .chart-section {
            margin-top: 30px;
        }

        .section-divider {
            margin-top: 30px;
            margin-bottom: 30px;
            border-top: 1px solid #e9ecef;
        }
    </style>
@endsection

<div class="row no-print mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="me-2 text-muted">
            <i style="font-size: 20px;" class="bx bx-download"></i>
        </a>

        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
            class="bx bx-printer text-muted"></i>
    </div>
</div>

<div class="row printable">
    <div class="col-12">
        <div class="card">
            <!-- School Header -->
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex flex-column">
                            <h5 class="mb-0">{{ $school_data->school_name }}</h5>
                            <p class="mb-0">{{ $school_data->physical_address }}</p>
                            <p class="mb-0">{{ $school_data->postal_address }}</p>
                            <p class="mb-0">Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                    </div>
                </div>
            </div>

            <!-- Card Body with Table and Charts -->
            <div class="card-body">
                <!-- Report Title -->
                <div class="text-start mb-4">
                    <h6 class="mb-1"> {{ $grade->name ?? '' }}
                        {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                        Overall Grade
                        Distribution By Gender Report Term
                        {{ $currentTerm->term }}, {{ $currentTerm->year }}</h6>
                </div>
                <!-- Main Distribution Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered text-center">
                        <thead>
                            <tr>
                                <th>Gender</th>
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
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Male Row -->
                            <tr>
                                <td>Male</td>
                                <td>{{ $m_M }}</td>
                                <td>{{ $a_M }}</td>
                                <td>{{ $b_M }}</td>
                                <td>{{ $c_M }}</td>
                                <td>{{ $d_M }}</td>
                                <td>{{ $e_M }}</td>
                                <td>{{ $u_M }}</td>
                                <td>{{ $mab_M_Percentage }}%</td>
                                <td>{{ $mabc_M_Percentage }}%</td>
                                <td>{{ $mabcd_M_Percentage }}%</td>
                                <td>{{ $maleCount }}</td>
                            </tr>
                            <!-- Male Percentage Row -->
                            <tr class="percent-row">
                                <td>%</td>
                                <td>{{ $validMaleCount > 0 ? number_format(($m_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($a_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($b_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($c_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($d_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($e_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validMaleCount > 0 ? number_format(($u_M / $validMaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <!-- Female Row -->
                            <tr>
                                <td>Female</td>
                                <td>{{ $m_F }}</td>
                                <td>{{ $a_F }}</td>
                                <td>{{ $b_F }}</td>
                                <td>{{ $c_F }}</td>
                                <td>{{ $d_F }}</td>
                                <td>{{ $e_F }}</td>
                                <td>{{ $u_F }}</td>
                                <td>{{ $mab_F_Percentage }}%</td>
                                <td>{{ $mabc_F_Percentage }}%</td>
                                <td>{{ $mabcd_F_Percentage }}%</td>
                                <td>{{ $femaleCount }}</td>
                            </tr>
                            <!-- Female Percentage Row -->
                            <tr class="percent-row">
                                <td>%</td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($m_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($a_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($b_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($c_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($d_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($e_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validFemaleCount > 0 ? number_format(($u_F / $validFemaleCount) * 100, 1) : 0 }}%
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <!-- Totals Row (highlighted) -->
                            <tr class="total-row">
                                <td>Total ({{ $validTotalStudents }})</td>
                                <td>{{ $sumM }}</td>
                                <td>{{ $sumA }}</td>
                                <td>{{ $sumB }}</td>
                                <td>{{ $sumC }}</td>
                                <td>{{ $sumD }}</td>
                                <td>{{ $sumE }}</td>
                                <td>{{ $sumU }}</td>
                                <td>{{ $mab_T_percentage }}%</td>
                                <td>{{ $mabc_T_percentage }}%</td>
                                <td>{{ $mabcd_T_percentage }}%</td>
                                <td>{{ $totalStudents }}</td>
                            </tr>
                            <!-- Total Percentage Row -->
                            <tr class="percent-row">
                                <td>%</td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumM / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumA / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumB / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumC / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumD / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumE / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td>{{ $validTotalStudents > 0 ? number_format(($sumU / $validTotalStudents) * 100, 1) : 0 }}%
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- First Chart Section: Grade Distribution by Gender and Overall Distribution -->
                <div class="section-divider"></div>
                <div class="row chart-section">
                    <div class="col-md-6">
                        <h6 class="text-center mb-3">Grade Distribution by Gender</h6>
                        <div class="chart-container">
                            <div id="gradeDistributionChart" class="chart-container"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-center mb-3">Overall Grade Distribution</h6>
                        <div class="chart-container">
                            <div id="overallDistributionChart" class="chart-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Subject Details Table -->
                <div class="section-divider"></div>
                <div class="row chart-section">
                    <div class="col-md-12">
                        <h6 class="text-center mb-3">Subject Performance Details</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th class="text-start">Subject</th>
                                        <th>Total Students</th>
                                        <th>Pass Rate</th>
                                        <th>Male Pass Rate</th>
                                        <th>Female Pass Rate</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>U</th>
                                        <th>X</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subjectNames as $subjectName)
                                        <tr>
                                            <td class="text-start">{{ $subjectName }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['total'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['passRate'] }}%</td>
                                            <td>{{ $subjectPerformance[$subjectName]['malePassRate'] }}%</td>
                                            <td>{{ $subjectPerformance[$subjectName]['femalePassRate'] }}%</td>
                                            <td>{{ $subjectPerformance[$subjectName]['A'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['B'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['C'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['D'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['E'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['U'] }}</td>
                                            <td>{{ $subjectPerformance[$subjectName]['X'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="section-divider"></div>
                <div class="row chart-section">
                    <div class="col-md-6">
                        <h6 class="text-center mb-3">Performance Comparison (%)</h6>
                        <div id="performanceComparisonChart" style="width: 100%; height: 400px;"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-center mb-3">Subject Pass Rates (%)</h6>
                        <div id="subjectPerformanceChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
                <div class="row chart-section">
                    <div class="col-md-12">
                        <h6 class="text-center mb-3">Subject Grade Distribution (Counts)</h6>
                        <div id="subjectGradeDistribution" style="width: 100%; height: 500px;"></div>
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
    function printContent() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {

        window.echartsInstances = {};

        const grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
        const maleGradeCounts = [
            {{ $m_M ?? 0 }}, {{ $a_M ?? 0 }}, {{ $b_M ?? 0 }}, {{ $c_M ?? 0 }},
            {{ $d_M ?? 0 }}, {{ $e_M ?? 0 }}, {{ $u_M ?? 0 }}
        ];
        const femaleGradeCounts = [
            {{ $m_F ?? 0 }}, {{ $a_F ?? 0 }}, {{ $b_F ?? 0 }}, {{ $c_F ?? 0 }},
            {{ $d_F ?? 0 }}, {{ $e_F ?? 0 }}, {{ $u_F ?? 0 }}
        ];
        const totalGradeCounts = [
            {{ $sumM ?? 0 }}, {{ $sumA ?? 0 }}, {{ $sumB ?? 0 }}, {{ $sumC ?? 0 }},
            {{ $sumD ?? 0 }}, {{ $sumE ?? 0 }}, {{ $sumU ?? 0 }}
        ];

        const performanceData = {
            mabc: [{{ $mabc_M_Percentage ?? 0 }}, {{ $mabc_F_Percentage ?? 0 }},
                {{ $mabc_T_percentage ?? 0 }}
            ],
            mabcd: [{{ $mabcd_M_Percentage ?? 0 }}, {{ $mabcd_F_Percentage ?? 0 }},
                {{ $mabcd_T_percentage ?? 0 }}
            ]
        };

        const subjectPerformance = @json($subjectPerformance ?? []);
        const subjectNames = @json($subjectNames ?? []);

        const echartsColors = {
            male: '#5470c6',
            female: '#ee6666',
            total: '#73c0de',
            merit: '#9a60b4',
            gradeA: '#91cc75',
            gradeB: '#5470c6',
            gradeC: '#fac858',
            gradeD: '#fc8452',
            gradeE: '#ee6666',
            gradeU: '#909399',
            gradeX: '#777777',
            passRate: '#73c0de',
            malePassRate: '#5470c6',
            femalePassRate: '#ee6666',
            mabc: '#3ba272',
            mabcd: '#ea7ccc'
        };

        function initChart(domId, option) {
            const chartDom = document.getElementById(domId);
            if (chartDom) {
                try {
                    const chart = echarts.init(chartDom);
                    chart.setOption(option);
                    window.echartsInstances[domId] = chart;
                    window.addEventListener('resize', () => {
                        if (chart && !chart.isDisposed()) {
                            chart.resize();
                        }
                    });
                    return chart;
                } catch (e) {
                    console.error(`Error initializing chart #${domId}:`, e);
                    chartDom.innerHTML =
                        `<p style="text-align:center; padding:20px; color: red;">Error loading chart.</p>`;
                }
            } else {
                console.warn(`Chart container #${domId} not found.`);
            }
            return null;
        }

        const gradeDistOption = {
            title: {
                text: 'Grade Distribution by Gender',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: ['Male', 'Female'],
                top: 30
            },
            grid: {
                top: 70,
                bottom: 50,
                left: '5%',
                right: '5%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: grades
            },
            yAxis: {
                type: 'value',
                name: 'Number of Students'
            },
            series: [{
                    name: 'Male',
                    type: 'bar',
                    data: maleGradeCounts,
                    color: echartsColors.male,
                    emphasis: {
                        focus: 'series'
                    }
                },
                {
                    name: 'Female',
                    type: 'bar',
                    data: femaleGradeCounts,
                    color: echartsColors.female,
                    emphasis: {
                        focus: 'series'
                    }
                }
            ],
            toolbox: {
                feature: {
                    saveAsImage: {},
                    dataView: {},
                    magicType: {
                        type: ['line', 'bar']
                    },
                    restore: {}
                }
            }
        };
        initChart('gradeDistributionChart', gradeDistOption);

        const overallDistData = grades.map((grade, index) => ({
            name: grade,
            value: totalGradeCounts[index]
        }));

        const overallDistOption = {
            title: {
                text: 'Overall Grade Distribution',
                left: 'center'
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                top: 'middle'
            },
            series: [{
                name: 'Grade',
                type: 'pie',
                radius: ['40%', '70%'],
                center: ['65%', '50%'],
                avoidLabelOverlap: true,
                label: {
                    show: true,
                    formatter: '{b}: {d}%'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '16',
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: true
                },
                data: overallDistData,
                itemStyle: {
                    color: function(params) {
                        const gradeColors = [
                            echartsColors.merit, echartsColors.gradeA, echartsColors.gradeB,
                            echartsColors.gradeC, echartsColors.gradeD, echartsColors
                            .gradeE, echartsColors.gradeU
                        ];
                        return gradeColors[params.dataIndex] || '#ccc';
                    }
                }
            }],
            toolbox: {
                feature: {
                    saveAsImage: {},
                    dataView: {}
                }
            }
        };
        initChart('overallDistributionChart', overallDistOption);


        const performanceOption = {
            title: {
                text: 'Performance Comparison (%)',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                },
                valueFormatter: val => val + '%'
            },
            legend: {
                data: ['MABC %', 'MABCD %'],
                top: 30
            },
            grid: {
                top: 70,
                bottom: 30,
                left: '5%',
                right: '5%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: ['Male', 'Female', 'Overall']
            },
            yAxis: {
                type: 'value',
                name: 'Percentage (%)',
                min: 0,
                max: 100,
                axisLabel: {
                    formatter: '{value}%'
                }
            },
            series: [{
                    name: 'MABC %',
                    type: 'bar',
                    data: performanceData.mabc,
                    color: echartsColors.mabc,
                    label: {
                        show: true,
                        position: 'top',
                        formatter: '{c}%'
                    }
                },
                {
                    name: 'MABCD %',
                    type: 'bar',
                    data: performanceData.mabcd,
                    color: echartsColors.mabcd,
                    label: {
                        show: true,
                        position: 'top',
                        formatter: '{c}%'
                    }
                }
            ],
            toolbox: {
                feature: {
                    saveAsImage: {},
                    dataView: {},
                    magicType: {
                        type: ['line', 'bar']
                    },
                    restore: {}
                }
            }
        };
        initChart('performanceComparisonChart', performanceOption);

        if (subjectNames.length > 0 && Object.keys(subjectPerformance).length > 0) {
            const passRates = subjectNames.map(name => subjectPerformance[name]?.passRate ?? 0);
            const malePassRates = subjectNames.map(name => subjectPerformance[name]?.malePassRate ?? 0);
            const femalePassRates = subjectNames.map(name => subjectPerformance[name]?.femalePassRate ?? 0);

            const subjectPerfOption = {
                title: {
                    text: 'Subject Pass Rates (%)',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                    valueFormatter: val => val + '%'
                },
                legend: {
                    data: ['Overall Pass Rate', 'Male Pass Rate', 'Female Pass Rate'],
                    top: 30
                },
                grid: {
                    top: 70,
                    bottom: 60,
                    left: '5%',
                    right: '5%',
                    containLabel: true
                }, // Increased bottom margin
                xAxis: {
                    type: 'category',
                    data: subjectNames,
                    axisLabel: {
                        interval: 0,
                        rotate: 45
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Pass Rate (%)',
                    min: 0,
                    max: 100,
                    axisLabel: {
                        formatter: '{value}%'
                    }
                },
                series: [{
                        name: 'Overall Pass Rate',
                        type: 'bar',
                        data: passRates,
                        color: echartsColors.passRate,
                        emphasis: {
                            focus: 'series'
                        }
                    },
                    {
                        name: 'Male Pass Rate',
                        type: 'bar',
                        data: malePassRates,
                        color: echartsColors.malePassRate,
                        emphasis: {
                            focus: 'series'
                        }
                    },
                    {
                        name: 'Female Pass Rate',
                        type: 'bar',
                        data: femalePassRates,
                        color: echartsColors.femalePassRate,
                        emphasis: {
                            focus: 'series'
                        }
                    }
                ],
                dataZoom: [{
                    type: 'slider',
                    show: subjectNames.length > 10,
                    bottom: 10
                }, {
                    type: 'inside'
                }],
                toolbox: {
                    feature: {
                        saveAsImage: {},
                        dataView: {},
                        magicType: {
                            type: ['line', 'bar']
                        },
                        restore: {}
                    }
                }
            };
            initChart('subjectPerformanceChart', subjectPerfOption);


            const subjectGrades = ['A', 'B', 'C', 'D', 'E', 'U', 'X'];
            const subjectGradeSeries = subjectGrades.map(grade => ({
                name: grade,
                type: 'bar',
                stack: 'total',
                emphasis: {
                    focus: 'series'
                },
                color: echartsColors['grade' + grade] || '#ccc',
                data: subjectNames.map(name => subjectPerformance[name]?.[grade] ?? 0)
            }));

            const subjectGradeDistOption = {
                title: {
                    text: 'Subject Grade Distribution (Counts)',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: subjectGrades,
                    top: 30,
                    type: 'scroll'
                },
                grid: {
                    top: 70,
                    bottom: 60,
                    left: '5%',
                    right: '5%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: subjectNames,
                    axisLabel: {
                        interval: 0,
                        rotate: 45
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Number of Students'
                },
                series: subjectGradeSeries,
                dataZoom: [{
                    type: 'slider',
                    show: subjectNames.length > 10,
                    bottom: 10
                }, {
                    type: 'inside'
                }],
                toolbox: {
                    feature: {
                        saveAsImage: {},
                        dataView: {},
                        magicType: {
                            type: ['line', 'bar', 'stack']
                        },
                        restore: {}
                    }
                }
            };
            initChart('subjectGradeDistribution', subjectGradeDistOption);

        } else {
            const subjPerfChartDom = document.getElementById('subjectPerformanceChart');
            if (subjPerfChartDom) subjPerfChartDom.innerHTML =
                '<p style="text-align:center; padding:20px;">No subject data available for charts.</p>';
            const subjGradeChartDom = document.getElementById('subjectGradeDistribution');
            if (subjGradeChartDom) subjGradeChartDom.innerHTML =
                '<p style="text-align:center; padding:20px;">No subject data available for charts.</p>';
        }

    });
</script>
@endsection

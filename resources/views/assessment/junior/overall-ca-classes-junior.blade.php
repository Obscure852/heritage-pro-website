@extends('layouts.master')

@section('title')
    Classes Analysis
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
            Classes Analysis
        @endslot
    @endcomponent

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
    <div class="col-12 d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="me-3">
            <i style="font-size: 20px;" class="bx bx-download text-muted"></i>
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
                    <div class="col-6 align-items-start">
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
                    <div class="col-6 d-flex justify-content-end">
                        <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="report-card">
                    <div class="row">
                        <div class="col-12">
                            <h5>
                                {{ $grade->name ?? 'Grade' }} -
                                @if (isset($test->type) && strtolower($test->type) === 'exam')
                                    End Of Term Exam
                                @elseif(isset($test->type) && strtolower($test->type) === 'ca')
                                    End Of {{ $test->name ?? '' }}
                                @else
                                    End Of {{ $test->name ?? '' }}
                                @endif
                                Term {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }} Classes Performance
                                Analysis
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Class</th>
                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <th colspan="3">{{ $grade }}</th>
                                            @endforeach
                                            @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $percentage)
                                                <th colspan="3">{{ $percentage }}</th>
                                            @endforeach
                                            <th colspan="3">Total</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 0; $i < 7 + 4; $i++)
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
                                        @foreach ($classPerformance as $className => $data)
                                            <tr>
                                                <td>{{ $className }}</td>
                                                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                    <td>{{ $data['grades'][$grade]['M'] }}</td>
                                                    <td>{{ $data['grades'][$grade]['F'] }}</td>
                                                    <td>{{ $data['grades'][$grade]['M'] + $data['grades'][$grade]['F'] }}
                                                    </td>
                                                @endforeach
                                                @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $percentage)
                                                    <td>{{ $data[$percentage]['M'] }}%</td>
                                                    <td>{{ $data[$percentage]['F'] }}%</td>
                                                    <td>
                                                        {{ round(
                                                            ($data[$percentage]['M'] * $data['totalMale'] + $data[$percentage]['F'] * $data['totalFemale']) /
                                                                max($data['totalMale'] + $data['totalFemale'], 1),
                                                            2,
                                                        ) }}%
                                                    </td>
                                                @endforeach
                                                <td>{{ $data['totalMale'] }}</td>
                                                <td>{{ $data['totalFemale'] }}</td>
                                                <td>{{ $data['totalMale'] + $data['totalFemale'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="font-weight:600; background:#f3f3f3;">
                                            <td>Totals</td>
                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <td>{{ $overallTotals['grades'][$grade]['M'] }}</td>
                                                <td>{{ $overallTotals['grades'][$grade]['F'] }}</td>
                                                <td>{{ $overallTotals['grades'][$grade]['M'] + $overallTotals['grades'][$grade]['F'] }}
                                                </td>
                                            @endforeach
                                            @foreach (['MAB%', 'MABC%', 'MABCD%', 'DEU%'] as $percentage)
                                                <td>{{ $overallTotals[$percentage]['M'] }}%</td>
                                                <td>{{ $overallTotals[$percentage]['F'] }}%</td>
                                                <td>
                                                    {{ round(
                                                        ($overallTotals[$percentage]['M'] * $overallTotals['totalMale'] +
                                                            $overallTotals[$percentage]['F'] * $overallTotals['totalFemale']) /
                                                            max($overallTotals['totalMale'] + $overallTotals['totalFemale'], 1),
                                                        2,
                                                    ) }}%
                                                </td>
                                            @endforeach
                                            <td>{{ $overallTotals['totalMale'] }}</td>
                                            <td>{{ $overallTotals['totalFemale'] }}</td>
                                            <td>{{ $overallTotals['totalMale'] + $overallTotals['totalFemale'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row no-print mt-4">
                        <div class="col-12">
                            <div id="percentageChart" style="width: 100%; height: 400px;"></div>
                        </div>
                        <div class="col-12">
                            <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end col -->
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
        const classPerformance = @json($classPerformance ?? []);

        const classNames = Object.keys(classPerformance);
        const gradesForChart = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
        const percentagesForChart = ['MAB%', 'MABC%', 'MABCD%', 'DEU%'];

        let dataIsValid = false;

        const maleGradeCounts = {};
        const femaleGradeCounts = {};
        const malePercentageData = {};
        const femalePercentageData = {};

        gradesForChart.forEach(g => {
            maleGradeCounts[g] = [];
            femaleGradeCounts[g] = [];
        });
        percentagesForChart.forEach(p => {
            malePercentageData[p] = [];
            femalePercentageData[p] = [];
        });

        if (classPerformance && classNames.length > 0) {

            classNames.forEach(cname => {
                const data = classPerformance[cname];
                if (!data || typeof data !== 'object') {
                    console.warn(`Skipping invalid data for class: ${cname}`);
                    return;
                }

                gradesForChart.forEach(grade => {
                    maleGradeCounts[grade].push(data.grades?.[grade]?.['M'] ?? 0);
                    femaleGradeCounts[grade].push(data.grades?.[grade]?.['F'] ?? 0);
                });

                percentagesForChart.forEach(percentage => {
                    malePercentageData[percentage].push(data[percentage]?.['M'] ?? 0);
                    femalePercentageData[percentage].push(data[percentage]?.['F'] ?? 0);
                });
            });

            dataIsValid = true;

        } else {
            console.warn("Initial class performance data is empty or invalid.");
        }

        if (!dataIsValid) {
            const gradeChartDom = document.getElementById('gradeDistributionChart');
            if (gradeChartDom) gradeChartDom.innerHTML =
                '<p style="text-align:center; padding:20px;">No data available for Grade Distribution chart.</p>';

            const percChartDom = document.getElementById('percentageChart');
            if (percChartDom) percChartDom.innerHTML =
                '<p style="text-align:center; padding:20px;">No data available for Percentage Performance chart.</p>';

            return;
        }

        const echartsColors = {
            male: '#5470c6',
            female: '#ee6666',
            gradeMerit: '#9a60b4',
            gradeA: '#91cc75',
            gradeB: '#5470c6',
            gradeC: '#fac858',
            gradeD: '#fc8452',
            gradeE: '#ee6666',
            gradeU: '#9e9e9e',
            lineMAB: '#5470c6',
            lineMABC: '#91cc75',
            lineMABCD: '#fac858',
            lineDEU: '#ee6666'
        };

        function initChart(domId, option) {
            const chartDom = document.getElementById(domId);
            if (chartDom) {
                try {
                    const existingInstance = echarts.getInstanceByDom(chartDom);
                    if (existingInstance) {
                        console.log(`Disposing existing ECharts instance for: ${domId}`);
                        existingInstance.dispose();
                    }
                    console.log(`Initializing ECharts on: ${domId}`);
                    const chart = echarts.init(chartDom);
                    console.log(`Setting options for: ${domId}`);
                    chart.setOption(option);
                    console.log(`Options set successfully for: ${domId}`);
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
                    console.error(`Error initializing or setting options for chart #${domId}:`, e);
                    chartDom.innerHTML =
                        `<p style="text-align:center; padding:20px; color: red;">Error loading chart #${domId}.</p>`;
                }
            } else {
                console.warn(`Chart container #${domId} not found.`);
            }
            return null;
        }

        const gradeDistSeries = gradesForChart.flatMap(grade => ([{
                name: `${grade} (M)`,
                type: 'bar',
                stack: 'Male',
                emphasis: {
                    focus: 'series'
                },
                color: echartsColors['grade' + grade] || '#ccc',
                data: maleGradeCounts[grade]
            },
            {
                name: `${grade} (F)`,
                type: 'bar',
                stack: 'Female',
                emphasis: {
                    focus: 'series'
                },
                color: echartsColors['grade' + grade] || '#ccc',
                itemStyle: {
                    // Example: borderColor: '#555', borderWidth: 1
                    // Example using pattern (more advanced):
                    // decal: { symbol: 'rect', symbolSize: 0.5, color: 'rgba(0,0,0,0.2)' } 
                },
                data: femaleGradeCounts[grade]
            }
        ]));

        const gradeDistOption = {
            title: {
                text: 'Grade Distribution by Class and Gender',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                top: 30,
                type: 'scroll'
            }, // Legend might get long
            grid: {
                top: 80,
                bottom: 30,
                left: '3%',
                right: '4%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: classNames
            },
            yAxis: {
                type: 'value',
                name: 'Number of Students'
            },
            series: gradeDistSeries,
            toolbox: {
                right: 20,
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
        initChart('gradeDistributionChart', gradeDistOption);

        const percentageSeries = percentagesForChart.flatMap(percentage => ([{
                name: `${percentage} (M)`,
                type: 'line',
                smooth: true,
                color: echartsColors['line' + percentage.replace('%', '')] || '#333',
                data: malePercentageData[percentage]
            },
            {
                name: `${percentage} (F)`,
                type: 'line',
                smooth: true,
                lineStyle: {
                    type: 'dashed'
                },
                color: echartsColors['line' + percentage.replace('%', '')] || '#333',
                data: femalePercentageData[percentage]
            }
        ]));

        const percentageOption = {
            title: {
                text: 'Percentage Performance by Class and Gender',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                valueFormatter: val => val + '%'
            },
            legend: {
                top: 30,
                type: 'scroll'
            },
            grid: {
                top: 80,
                bottom: 30,
                left: '3%',
                right: '4%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: classNames
            },
            yAxis: {
                type: 'value',
                name: 'Percentage',
                min: 0,
                max: 100,
                axisLabel: {
                    formatter: '{value}%'
                }
            },
            series: percentageSeries,
            toolbox: {
                right: 20,
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
        initChart('percentageChart', percentageOption);

        console.log("Class Analysis chart initialization complete.");

    });
</script>
@endsection

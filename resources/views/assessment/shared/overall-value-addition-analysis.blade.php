@extends('layouts.master')
@section('title')
    Value Addition Analysis
@endsection

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    <style>
        .card {
            margin-bottom: 10px;
        }

        body {
            font-size: 12px;
        }

        .table-responsive {
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
            padding: 0.4rem;
        }

        .chart-container {
            width: 100%;
            max-width: 850px;
            height: 450px;
            margin: 30px auto;
        }

        .matrix-table th,
        .matrix-table td {
            min-width: 40px;
        }

        .text-center {
            text-align: center;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .align-middle {
            vertical-align: middle !important;
        }

        @page {
            size: A4 landscape;
            margin: 0.5in;
        }

        @media print {

            html,
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                font-size: 9pt;
                line-height: 1.2;
                overflow: visible;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: static;
                width: 100% !important;
                margin: 0;
                padding: 0;
                page-break-after: avoid;
            }

            .no-print {
                display: none !important;
            }

            .card-header img {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }

            .card-body {
                width: 100% !important;
                margin: 0;
                padding: 0;
            }

            h5,
            h6 {
                margin-top: 15px;
                margin-bottom: 8px;
                font-size: 11pt;
                text-align: center;
            }

            p.text-center.text-muted {
                font-size: 9pt;
                margin-bottom: 10px;
            }

            .table-responsive {
                width: 100% !important;
                margin: 0 0 15px 0;
                padding: 0;
                page-break-inside: avoid;
            }

            .table {
                width: 100% !important;
                table-layout: auto;
                border-collapse: collapse;
                margin: 0;
                padding: 0;
            }

            .table th,
            .table td {
                overflow: visible;
                word-wrap: break-word;
                padding: 3px;
                font-size: 8pt;
            }

            .table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .matrix-table th[style*="background-color"],
            .table td[style*="background-color"] {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000 !important;
            }

            .chart-container {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#"
                onclick="event.preventDefault(); 
                if (document.referrer && document.referrer !== window.location.href) {
                    history.back();
                } else {
                    window.location = '{{ $gradebookBackUrl }}';
                }   
            ">Back</a>
        @endslot
        @slot('title')
            Value Addition Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a title="Export to Excel" href="{{ request()->fullUrlWithQuery(['export' => 'true']) }}"
                style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-download me-2 text-muted"></a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted" title="Print Report"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div style="font-size:14px;" class="col-md-6">
                            <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                            {{ $school_data->physical_address ?? 'Address Line 1' }}<br>
                            {{ $school_data->postal_address ?? 'Address Line 2' }}<br>
                            Tel: {{ $school_data->telephone ?? 'N/A' }} | Fax: {{ $school_data->fax ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 text-end">
                            @if (isset($school_data->logo_path) && $school_data->logo_path)
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @else
                                <span class="text-muted">Logo Not Available</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Updated Title for Grade Level --}}
                    <h6 class="text-start mb-1">{{ $grade->name }}
                        {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . $test->name }} Value Addition
                        Analysis Grade Distribution (PSLE vs JC) - Term {{ $term->term ?? '' }},
                        {{ $term->year ?? 'N/A' }}
                    </h6>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">Grade</th>
                                    @foreach ($jcSubjects as $subject)
                                        <th colspan="2" class="text-center">{{ $subject }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($jcSubjects as $subject)
                                        <th class="text-center" style="background-color: #f2f4f6;">PSLE</th>
                                        <th class="text-center" style="background-color: #e9ecef;">OUTPUT</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($psleGradeCategories as $gradeCat)
                                    {{-- Changed variable name to avoid conflict --}}
                                    <tr>
                                        <td>{{ $gradeCat }}</td>
                                        @foreach ($jcSubjects as $subject)
                                            <td class="text-center">{{ $gradeCounts[$subject]['PSLE'][$gradeCat] ?? 0 }}
                                            </td>
                                            <td class="text-center">
                                                {{ $gradeCounts[$subject]['JC'][$gradeCat] ?? 0 }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>M <small>(JC Only)</small></td>
                                    @foreach ($jcSubjects as $subject)
                                        <td class="text-center" style="background-color: #f8f9fa;">-</td>
                                        <td class="text-center">{{ $gradeCounts[$subject]['JC']['M'] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                                <!-- New Total Row -->
                                <tr style="font-weight: bold; background-color: #e6f7ff;">
                                    <td>Total</td>
                                    @foreach ($jcSubjects as $subject)
                                        <td class="text-center">{{ $gradeCounts[$subject]['totalPSLE'] }}</td>
                                        <td class="text-center">{{ $gradeCounts[$subject]['totalJC'] }}</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight: bold;">
                                    <td>Quality % <small>(PSLE: A-C / JC: M-C)</small></td>
                                    @foreach ($jcSubjects as $subject)
                                        <td class="text-center">{{ $gradeCounts[$subject]['qualityPSLE'] }}%</td>
                                        <td class="text-center">{{ $gradeCounts[$subject]['qualityJC'] }}%</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight: bold; background-color: #f2f2f2;">
                                    <td>Value Add (Qual. %)</td>
                                    @foreach ($jcSubjects as $subject)
                                        <td colspan="2" class="text-center"
                                            style="background-color: {{ ($gradeCounts[$subject]['valueAddition'] ?? 0) >= 0 ? '#d4edda' : '#f8d7da' }}; color: {{ ($gradeCounts[$subject]['valueAddition'] ?? 0) >= 0 ? '#155724' : '#721c24' }};">
                                            {{ $gradeCounts[$subject]['valueAddition'] }}%
                                        </td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight: bold;">
                                    <td>Rank</td>
                                    @foreach ($jcSubjects as $subject)
                                        <td colspan="2" class="text-center">
                                            {{ array_search($subject, $rankedSubjects) !== false ? array_search($subject, $rankedSubjects) + 1 : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6>Overall Grade Shift Matrix (PSLE Overall vs JC Overall) - Grade {{ $grade->name }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm matrix-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" colspan="2" class="text-center align-middle"
                                        style="background-color: #f8f9fa;">
                                        <div style="line-height: 1;">PSLE Overall →</div>
                                        <div style="line-height: 1;">JC Overall ↓</div>
                                    </th>
                                    <th colspan="{{ count($psleGradeCategories) }}" class="text-center"
                                        style="background-color: #e9ecef;">PSLE Overall Grade</th>
                                    <th rowspan="2" class="text-center align-middle" style="background-color: #f8f9fa;">
                                        Total from JC</th>
                                </tr>
                                <tr>
                                    @foreach ($psleGradeCategories as $psleGradeCat)
                                        {{-- Changed variable --}}
                                        <th class="text-center" style="background-color: #f2f4f6;">{{ $psleGradeCat }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php $jcRowTotals = array_fill_keys($gradeCategories, 0); @endphp
                                @foreach ($gradeCategories as $jcGradeCat)
                                    {{-- Changed variable --}}
                                    <tr>
                                        @if ($loop->first)
                                            <th rowspan=" {{ count($gradeCategories) }}" class="text-center align-middle"
                                                style="writing-mode: vertical-rl; transform: rotate(180deg); background-color: #e9ecef; padding: 5px 2px;">
                                                JC Overall Grade</th>
                                        @endif
                                        <td class="text-center font-weight-bold" style="background-color: #f2f4f6;">
                                            {{ $jcGradeCat }}</td>
                                        @foreach ($psleGradeCategories as $psleGradeCat2)
                                            {{-- Changed variable --}}
                                            <td class="text-center">
                                                {{ $gradeShiftMatrix[$psleGradeCat2][$jcGradeCat] ?? 0 }}</td>
                                            @php $jcRowTotals[$jcGradeCat] += ($gradeShiftMatrix[$psleGradeCat2][$jcGradeCat] ?? 0); @endphp
                                        @endforeach
                                        <td class="text-center font-weight-bold" style="background-color: #f8f9fa;">
                                            {{ $jcRowTotals[$jcGradeCat] }}</td>
                                    </tr>
                                @endforeach
                                <tr style="font-weight: bold; background-color: #f8f9fa;">
                                    <th colspan="2" class="text-center">Total from PSLE</th>
                                    @foreach ($psleGradeCategories as $psleGradeCat)
                                        {{-- Changed variable --}}
                                        <td class="text-center">{{ $psleOverallGradeCounts[$psleGradeCat] ?? 0 }}</td>
                                    @endforeach
                                    <td class="text-center">{{ array_sum($psleOverallGradeCounts) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6>Progression of High PSLE Achievers (Overall Grades A, B, or C in PSLE) - Grade {{ $grade->name }}
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Student Name</th>
                                    <th class="text-center">PSLE Overall Grade</th>
                                    <th class="text-center">JC Overall Grade ({{ $type }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($highPsleAchievers as $index => $achiever)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $achiever['name'] }}</td>
                                        <td class="text-center">
                                            {{ $achiever['psle_grade'] }}
                                        </td>
                                        <td class="text-center">
                                            {{ $achiever['jc_grade'] }} ({{ $achiever['jc_points'] }} Points)
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No students in Grade
                                            {{ $grade->name }} found with PSLE overall grades A, B, or C.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6>PSLE Overall Grade Distribution (Grade {{ $grade->name }} Cohort)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    @foreach ($psleGradeCategories as $gradeCat)
                                        {{-- Changed variable --}}
                                        <th class="text-center">{{ $gradeCat }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                    <th class="text-center">A-C%</th>
                                    <th class="text-center">DEU%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($psleGradeCategories as $gradeCat)
                                        {{-- Changed variable --}}
                                        <td class="text-center">{{ $psleOverallGradeCounts[$gradeCat] ?? 0 }}</td>
                                    @endforeach
                                    @php
                                        $totalPSLEOverall = max(array_sum($psleOverallGradeCounts), 1);
                                        $psleQualityPercent =
                                            ((($psleOverallGradeCounts['A'] ?? 0) +
                                                ($psleOverallGradeCounts['B'] ?? 0) +
                                                ($psleOverallGradeCounts['C'] ?? 0)) /
                                                $totalPSLEOverall) *
                                            100;
                                        $psleDEU =
                                            ((($psleOverallGradeCounts['D'] ?? 0) +
                                                ($psleOverallGradeCounts['E'] ?? 0) +
                                                ($psleOverallGradeCounts['U'] ?? 0)) /
                                                $totalPSLEOverall) *
                                            100;
                                    @endphp
                                    <td class="text-center">{{ array_sum($psleOverallGradeCounts) }}</td>
                                    <td class="text-center">{{ round($psleQualityPercent, 0) }}%</td>
                                    <td class="text-center">{{ round($psleDEU, 0) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6>JC Overall Grade Distribution (Grade {{ $grade->name }} Cohort for {{ $type }})</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    @foreach ($gradeCategories as $gradeCat)
                                        {{-- Changed variable --}}
                                        <th class="text-center">{{ $gradeCat }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                    <th class="text-center">M-C%</th>
                                    <th class="text-center">DEU%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($gradeCategories as $gradeCat)
                                        {{-- Changed variable --}}
                                        <td class="text-center">{{ $jcOverallGradeCounts[$gradeCat] ?? 0 }}</td>
                                    @endforeach
                                    @php
                                        $totalJCOverall = max(array_sum($jcOverallGradeCounts), 1);
                                        $jcQualityPercent =
                                            ((($jcOverallGradeCounts['M'] ?? 0) +
                                                ($jcOverallGradeCounts['A'] ?? 0) +
                                                ($jcOverallGradeCounts['B'] ?? 0) +
                                                ($jcOverallGradeCounts['C'] ?? 0)) /
                                                $totalJCOverall) *
                                            100;
                                        $jcDEU =
                                            ((($jcOverallGradeCounts['D'] ?? 0) +
                                                ($jcOverallGradeCounts['E'] ?? 0) +
                                                ($jcOverallGradeCounts['U'] ?? 0)) /
                                                $totalJCOverall) *
                                            100;
                                    @endphp
                                    <td class="text-center">{{ array_sum($jcOverallGradeCounts) }}</td>
                                    <td class="text-center">{{ round($jcQualityPercent, 0) }}%</td>
                                    <td class="text-center">{{ round($jcDEU, 0) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6>Value Addition Summary - Grade {{ $grade->name }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <td style="font-weight: bold;">Overall Value Addition (Based on Quality % M+A+B+C for JC vs
                                    A+B+C for PSLE):</td>
                                <td class="text-center"
                                    style="font-weight: bold; background-color: {{ ($valueAdditions['overall'] ?? 0) >= 0 ? '#d4edda' : '#f8d7da' }}; color: {{ ($valueAdditions['overall'] ?? 0) >= 0 ? '#155724' : '#721c24' }};">
                                    {{ number_format($valueAdditions['overall'] ?? 0, 0) }}%
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="chart-container no-print" style="height: 450px;">
                        <h6>Value Addition % per Subject (Quality: JC M+A+B+C vs PSLE A+B+C) - Grade {{ $grade->name }}
                        </h6>
                        <div id="valueAdditionEchart" style="width: 100%; height: 100%;"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- ECharts CDN moved here --}}
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM Content Loaded. Attempting to initialize charts for Grade Value Addition.");
            console.log("Is ECharts library loaded?", typeof echarts);

            // ECharts: Value Addition Bar Chart
            const vaChartDom = document.getElementById('valueAdditionEchart');
            if (vaChartDom) {
                if (typeof echarts !== 'undefined') {
                    try {
                        const vaChart = echarts.init(vaChartDom);
                        const valueAdditionChartData = @json($valueAdditionChart ?? ['labels' => [], 'data' => []]);
                        console.log("Grade Level - Value Addition Chart Data:", valueAdditionChartData);

                        if (valueAdditionChartData.labels && valueAdditionChartData.labels.length > 0) {
                            const option = {
                                title: {
                                    // text: 'Value Addition per Subject', // Title is an h6 above
                                    subtext: 'Quality % (JC M-C vs PSLE A-C)',
                                    left: 'center',
                                    top: 5
                                },
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: {
                                        type: 'shadow'
                                    },
                                    formatter: function(params) {
                                        const param = params[0];
                                        return param.name + '<br/>' + param.seriesName + ': ' + param
                                            .value + '%';
                                    }
                                },
                                legend: {
                                    data: ['Value Addition %'],
                                    bottom: 5
                                },
                                grid: {
                                    left: '3%',
                                    right: '4%',
                                    bottom: '12%',
                                    top: '15%',
                                    containLabel: true
                                },
                                xAxis: {
                                    type: 'value',
                                    name: 'Value Addition (%)',
                                    nameLocation: 'middle',
                                    nameGap: 30,
                                    axisLabel: {
                                        formatter: '{value}%'
                                    }
                                },
                                yAxis: {
                                    type: 'category',
                                    data: valueAdditionChartData.labels.slice().reverse(),
                                    name: 'Subjects (Ranked)',
                                    axisLabel: {
                                        interval: 0,
                                        rotate: 0
                                    }
                                },
                                series: [{
                                    name: 'Value Addition %',
                                    type: 'bar',
                                    data: valueAdditionChartData.data.slice().reverse().map(value => ({
                                        value: value,
                                        itemStyle: {
                                            color: value >= 0 ? '#5470C6' : '#EE6666'
                                        }
                                    })),
                                    barWidth: '60%',
                                    label: {
                                        show: true,
                                        position: 'right',
                                        formatter: '{c}%',
                                        color: '#333',
                                        distance: 5
                                    }
                                }]
                            };
                            vaChart.setOption(option);
                            window.addEventListener('resize', function() {
                                vaChart.resize();
                            });
                            console.log("Grade Level - Value Addition Bar Chart initialized.");
                        } else {
                            console.warn("Grade Level - Value Addition Chart: No labels or data to display.");
                            vaChartDom.innerHTML =
                                '<p class="text-center text-muted mt-3">No data available for subject value addition chart.</p>';
                        }
                    } catch (error) {
                        console.error('Error rendering Grade Level Value Addition Bar Chart:', error);
                        if (vaChartDom) vaChartDom.innerHTML =
                            '<p class="text-center text-danger mt-3">Error rendering bar chart. Check console.</p>';
                    }
                } else {
                    console.error('ECharts library not loaded when trying to init bar chart.');
                    if (vaChartDom) vaChartDom.innerHTML =
                        '<p class="text-center text-warning mt-3">Could not initialize chart (ECharts library missing).</p>';
                }
            } else {
                console.warn('ECharts DOM element "valueAdditionEchart" not found.');
            }

            // ECharts: Overall Value Addition Gauge Chart
            const overallGaugeChartDom = document.getElementById('overallValueAdditionGaugeChart');
            if (overallGaugeChartDom) {
                if (typeof echarts !== 'undefined') {
                    try {
                        const overallGaugeChart = echarts.init(overallGaugeChartDom);
                        const overallValue = @json($valueAdditions['overall'] ?? 0);
                        console.log("Grade Level - Overall Value Addition for Gauge:", overallValue);

                        const gaugeOption = {
                            title: {
                                text: 'Overall Grade Value Addition',
                                left: 'center',
                                top: 0,
                                subtext: 'Quality % (JC M-C vs PSLE A-C)',
                            },
                            series: [{
                                type: 'gauge',
                                center: ['50%', '60%'],
                                radius: '90%',
                                startAngle: 180,
                                endAngle: 0,
                                min: -50,
                                max: 50,
                                splitNumber: 10,
                                axisLine: {
                                    lineStyle: {
                                        width: 20,
                                        color: [
                                            [0.2, '#EE6666'],
                                            [0.4, '#FAC858'],
                                            [0.6, '#91CC75'],
                                            [0.8, '#5470C6'],
                                            [1, '#3BA272']
                                        ]
                                    }
                                },
                                pointer: {
                                    itemStyle: {
                                        color: 'auto'
                                    },
                                    width: 5,
                                    length: '70%'
                                },
                                axisTick: {
                                    length: 12,
                                    lineStyle: {
                                        color: 'auto',
                                        width: 2
                                    }
                                },
                                splitLine: {
                                    length: 20,
                                    lineStyle: {
                                        color: 'auto',
                                        width: 3
                                    }
                                },
                                axisLabel: {
                                    color: '#464646',
                                    fontSize: 12,
                                    distance: -50,
                                    formatter: '{value}%'
                                },
                                title: {
                                    offsetCenter: [0, '70%'],
                                    fontSize: 16,
                                    fontWeight: 'bold'
                                },
                                detail: {
                                    fontSize: 28,
                                    fontWeight: 'bold',
                                    offsetCenter: [0, '40%'],
                                    valueAnimation: true,
                                    formatter: '{value}%',
                                    color: 'auto'
                                },
                                data: [{
                                    value: overallValue,
                                    name: 'Overall Value Add.'
                                }]
                            }],
                            tooltip: {
                                formatter: 'Overall Value Addition: {c}%'
                            }
                        };
                        overallGaugeChart.setOption(gaugeOption);
                        window.addEventListener('resize', function() {
                            overallGaugeChart.resize();
                        });
                        console.log("Grade Level - Overall Value Addition Gauge Chart initialized.");
                    } catch (error) {
                        console.error('Error rendering Grade Level Overall Value Addition Gauge Chart:', error);
                        if (overallGaugeChartDom) overallGaugeChartDom.innerHTML =
                            '<p class="text-center text-danger mt-3">Error rendering gauge chart. Check console.</p>';
                    }
                } else {
                    console.error('ECharts library not loaded when trying to init gauge chart.');
                    if (overallGaugeChartDom) overallGaugeChartDom.innerHTML =
                        '<p class="text-center text-warning mt-3">Could not initialize chart (ECharts library missing).</p>';
                }
            } else {
                console.warn('ECharts DOM element "overallValueAdditionGaugeChart" not found.');
            }
        });
    </script>
@endsection

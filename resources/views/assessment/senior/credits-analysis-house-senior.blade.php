@extends('layouts.master')
@section('title')
    House Credits Analysis
@endsection

@section('css')
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: center;
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
            font-size: 11px;
        }

        .house-card {
            margin-bottom: 32px;
        }

        .house-card .card-header {
            background-color: #e6e6e6;
            color: #1f2937;
            padding: 12px 20px;
        }

        .house-card .card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .house-card .card-header .house-meta {
            font-size: 13px;
            color: #4b5563;
            margin-top: 2px;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .overall-row {
            font-weight: bold;
            background-color: #d1e7dd;
        }

        .header-group-obtained {
            background-color: #dbeafe !important;
            color: #1e40af;
            font-weight: 700;
        }

        .header-group-cumulative {
            background-color: #fce7f3 !important;
            color: #9d174d;
            font-weight: 700;
        }

        .chart-container {
            width: 100%;
            height: 350px;
            margin-top: 8px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 0.5cm;
            }

            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                break-inside: avoid;
            }

            .house-card {
                break-inside: avoid;
            }

            .table {
                font-size: 9pt;
            }

            .total-row {
                background-color: #e6e6e6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .overall-row {
                background-color: #d1e7dd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header-group-obtained {
                background-color: #dbeafe !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header-group-cumulative {
                background-color: #fce7f3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .chart-container {
                height: 300px;
                break-inside: avoid;
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
            House Credits Analysis
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

    {{-- School header --}}
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                                <span>{{ $school_data->physical_address ?? '' }}</span><br>
                                <span>{{ $school_data->postal_address ?? '' }}</span><br>
                                <span>Tel: {{ $school_data->telephone ?? 'N/A' }} Fax: {{ $school_data->fax ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @else
                                <span>Logo Not Available</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pb-2">
                    @if ($test && $test->type == 'CA')
                        <h5 class="mb-0">End of {{ $test->name ?? 'Month' }} House Credits Performance Analysis</h5>
                    @else
                        <h5 class="mb-0">End of Term House Credits Performance Analysis</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Per-house tables + charts --}}
    @php $houseIndex = 0; @endphp
    @foreach ($houseData as $houseName => $data)
        <div class="row">
            <div class="col-12">
                <div class="card house-card">
                    <div class="card-header">
                        <h5>{{ $houseName }} House</h5>
                        <div class="house-meta">
                            House Head: {{ $data['houseHead'] ?? 'Not Assigned' }} &bull;
                            Students: {{ $data['stats']['classSize'] }} &bull;
                            Wrote: {{ $data['stats']['total'] }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="vertical-align:middle;">Name of<br>House</th>
                                        <th rowspan="2" style="vertical-align:middle;">Class</th>
                                        <th rowspan="2" style="vertical-align:middle;">Class<br>Size</th>
                                        <th rowspan="2" style="vertical-align:middle;">No.<br>Wrote</th>
                                        <th colspan="6" class="header-group-obtained">Number[No.] and Percentage[%] Obtained</th>
                                        <th colspan="8" class="header-group-cumulative">CUMULATIVE PERCENTAGES</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="header-group-obtained">9+ Credits</th>
                                        <th colspan="2" class="header-group-obtained">8-9 Credits</th>
                                        <th colspan="2" class="header-group-obtained">7-9 Credits</th>
                                        <th colspan="2" class="header-group-cumulative">6-9 Credits</th>
                                        <th colspan="2" class="header-group-cumulative">5-9 Credits</th>
                                        <th colspan="2" class="header-group-cumulative">4-9 Credits</th>
                                        <th colspan="2" class="header-group-cumulative">3-9+ Credits</th>
                                    </tr>
                                    <tr>
                                        <th></th><th></th><th></th><th></th>
                                        @for ($i = 0; $i < 7; $i++)
                                            <th>No.</th><th>%</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['classes'] as $className => $classStats)
                                        <tr>
                                            <td class="text-start fw-bold">{{ $loop->first ? strtoupper($houseName) : '' }}</td>
                                            <td>{{ $className }}</td>
                                            <td>{{ $classStats['classSize'] }}</td>
                                            <td>{{ $classStats['total'] }}</td>
                                            @foreach ($cumulativeThresholds as $th)
                                                <td>{{ $classStats['cumulative']['no'][$th] ?? 0 }}</td>
                                                <td>{{ number_format($classStats['cumulative']['pct'][$th] ?? 0, 2) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td class="text-start">Total</td>
                                        <td>{{ count($data['classes']) }}</td>
                                        <td>{{ $data['stats']['classSize'] }}</td>
                                        <td>{{ $data['stats']['total'] }}</td>
                                        @foreach ($cumulativeThresholds as $th)
                                            <td>{{ $data['stats']['cumulative']['no'][$th] ?? 0 }}</td>
                                            <td>{{ number_format($data['stats']['cumulative']['pct'][$th] ?? 0, 2) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="houseChart-{{ $houseIndex }}" class="chart-container no-print"></div>
                    </div>
                </div>
            </div>
        </div>
        @php $houseIndex++; @endphp
    @endforeach

    {{-- School Overall --}}
    <div class="row">
        <div class="col-12">
            <div class="card house-card">
                <div class="card-header">
                    <h5>School Overall</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align:middle;">Name of<br>House</th>
                                    <th rowspan="2" style="vertical-align:middle;">Class</th>
                                    <th rowspan="2" style="vertical-align:middle;">Class<br>Size</th>
                                    <th rowspan="2" style="vertical-align:middle;">No.<br>Wrote</th>
                                    <th colspan="6" class="header-group-obtained">Number[No.] and Percentage[%] Obtained</th>
                                    <th colspan="8" class="header-group-cumulative">CUMULATIVE PERCENTAGES</th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="header-group-obtained">9+ Credits</th>
                                    <th colspan="2" class="header-group-obtained">8-9 Credits</th>
                                    <th colspan="2" class="header-group-obtained">7-9 Credits</th>
                                    <th colspan="2" class="header-group-cumulative">6-9 Credits</th>
                                    <th colspan="2" class="header-group-cumulative">5-9 Credits</th>
                                    <th colspan="2" class="header-group-cumulative">4-9 Credits</th>
                                    <th colspan="2" class="header-group-cumulative">3-9+ Credits</th>
                                </tr>
                                <tr>
                                    <th></th><th></th><th></th><th></th>
                                    @for ($i = 0; $i < 7; $i++)
                                        <th>No.</th><th>%</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($houseData as $houseName => $data)
                                    <tr>
                                        <td class="text-start fw-bold">{{ strtoupper($houseName) }}</td>
                                        <td>{{ count($data['classes']) }}</td>
                                        <td>{{ $data['stats']['classSize'] }}</td>
                                        <td>{{ $data['stats']['total'] }}</td>
                                        @foreach ($cumulativeThresholds as $th)
                                            <td>{{ $data['stats']['cumulative']['no'][$th] ?? 0 }}</td>
                                            <td>{{ number_format($data['stats']['cumulative']['pct'][$th] ?? 0, 2) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="overall-row">
                                    <td class="text-start">{{ $school_data->school_name ?? 'School' }}</td>
                                    <td>Overall</td>
                                    <td>{{ $totalStats['classSize'] }}</td>
                                    <td>{{ $totalStats['total'] }}</td>
                                    @foreach ($cumulativeThresholds as $th)
                                        <td>{{ $totalStats['cumulative']['no'][$th] ?? 0 }}</td>
                                        <td>{{ number_format($totalStats['cumulative']['pct'][$th] ?? 0, 2) }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="schoolOverallChart" class="chart-container no-print"></div>
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
            const houseData = @json($houseData);
            const cumulativeThresholds = @json($cumulativeThresholds);
            const totalStats = @json($totalStats);

            const bandLabels = {
                9: '9+ Credits', 8: '8-9 Credits', 7: '7-9 Credits',
                6: '6-9 Credits', 5: '5-9 Credits', 4: '4-9 Credits', 3: '3-9+ Credits'
            };

            const barColors = {
                9: '#1e3a5f', 8: '#2563eb', 7: '#3b82f6',
                6: '#f59e0b', 5: '#f97316', 4: '#ef4444', 3: '#991b1b'
            };

            const lineColors = {
                6: '#059669', 5: '#7c3aed', 4: '#dc2626', 3: '#0891b2'
            };

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
                window.addEventListener('resize', function() {
                    if (chart && !chart.isDisposed()) chart.resize();
                });
                return chart;
            }

            // Per-house charts
            var houseIndex = 0;
            Object.keys(houseData).forEach(function(houseName) {
                var data = houseData[houseName];
                var classes = data.classes;
                var classNames = Object.keys(classes);

                if (classNames.length === 0) {
                    houseIndex++;
                    return;
                }

                // Bar series: cumulative counts for 9+, 8+, 7+ (obtained)
                var barSeries = [9, 8, 7].map(function(th) {
                    return {
                        name: bandLabels[th],
                        type: 'bar',
                        barGap: '10%',
                        itemStyle: { color: barColors[th], borderRadius: [2, 2, 0, 0] },
                        data: classNames.map(function(cn) {
                            return classes[cn].cumulative.no[th] || 0;
                        })
                    };
                });

                // Line series: cumulative % for 6+, 5+, 4+, 3+ (cumulative percentages)
                var lineSeries = [6, 5, 4, 3].map(function(th) {
                    return {
                        name: bandLabels[th] + ' %',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 6,
                        lineStyle: { width: 2, color: lineColors[th] },
                        itemStyle: { color: lineColors[th] },
                        data: classNames.map(function(cn) {
                            return classes[cn].cumulative.pct[th] || 0;
                        })
                    };
                });

                var option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'cross' },
                        valueFormatter: function(v) { return typeof v === 'number' ? v.toFixed(1) : v; }
                    },
                    legend: {
                        data: [9, 8, 7].map(function(t) { return bandLabels[t]; })
                            .concat([6, 5, 4, 3].map(function(t) { return bandLabels[t] + ' %'; })),
                        top: 0,
                        type: 'scroll',
                        textStyle: { fontSize: 11 }
                    },
                    grid: { top: 50, bottom: 10, left: '3%', right: '4%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: classNames,
                        axisLabel: { interval: 0, fontSize: 11 }
                    },
                    yAxis: [
                        {
                            type: 'value',
                            name: 'Students',
                            position: 'left',
                            splitLine: { lineStyle: { type: 'dashed' } }
                        },
                        {
                            type: 'value',
                            name: '%',
                            position: 'right',
                            min: 0,
                            max: 100,
                            splitLine: { show: false },
                            axisLabel: { formatter: '{value}%' }
                        }
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

                initChart('houseChart-' + houseIndex, option);
                houseIndex++;
            });

            // School overall chart: houses on x-axis
            var houseNames = Object.keys(houseData);
            if (houseNames.length > 0) {
                var overallBarSeries = [9, 7, 6, 3].map(function(th) {
                    return {
                        name: bandLabels[th],
                        type: 'bar',
                        barGap: '10%',
                        itemStyle: { color: barColors[th], borderRadius: [2, 2, 0, 0] },
                        data: houseNames.map(function(h) {
                            return houseData[h].stats.cumulative.no[th] || 0;
                        })
                    };
                });

                var overallLineSeries = [6, 5, 4, 3].map(function(th) {
                    return {
                        name: bandLabels[th] + ' %',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 6,
                        lineStyle: { width: 2, color: lineColors[th] },
                        itemStyle: { color: lineColors[th] },
                        data: houseNames.map(function(h) {
                            return houseData[h].stats.cumulative.pct[th] || 0;
                        })
                    };
                });

                initChart('schoolOverallChart', {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'cross' },
                        valueFormatter: function(v) { return typeof v === 'number' ? v.toFixed(1) : v; }
                    },
                    legend: {
                        data: [9, 7, 6, 3].map(function(t) { return bandLabels[t]; })
                            .concat([6, 5, 4, 3].map(function(t) { return bandLabels[t] + ' %'; })),
                        top: 0,
                        type: 'scroll',
                        textStyle: { fontSize: 11 }
                    },
                    grid: { top: 50, bottom: 10, left: '3%', right: '4%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: houseNames.map(function(h) { return h + ' House'; }),
                        axisLabel: { interval: 0, fontSize: 11 }
                    },
                    yAxis: [
                        { type: 'value', name: 'Students', position: 'left', splitLine: { lineStyle: { type: 'dashed' } } },
                        { type: 'value', name: '%', position: 'right', min: 0, max: 100, splitLine: { show: false }, axisLabel: { formatter: '{value}%' } }
                    ],
                    series: overallBarSeries.concat(overallLineSeries),
                    toolbox: {
                        right: 20,
                        feature: {
                            saveAsImage: { title: 'Save' },
                            magicType: { type: ['line', 'bar'], title: { line: 'Line', bar: 'Bar' } },
                            restore: { title: 'Restore' }
                        }
                    }
                });
            }
        });
    </script>
@endsection

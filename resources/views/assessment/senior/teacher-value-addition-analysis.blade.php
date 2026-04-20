@extends('layouts.master')

@section('title')
    Teacher Value Addition Analysis
@endsection

@section('css')
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: start;
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: start;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .va-positive {
            color: #059669;
            font-weight: bold;
        }

        .va-negative {
            color: #dc2626;
            font-weight: bold;
        }

        .subject-card {
            margin-bottom: 32px;
        }

        .subject-card .card-header {
            background-color: #e6e6e6;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
        }

        .subject-card .card-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
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

            .subject-card {
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
            Teacher Value Addition Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
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
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address ?? 'Physical Address' }}</span>
                                <br>
                                <span>{{ $school_data->postal_address ?? 'Postal Address' }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone ?? 'N/A' }} Fax:
                                    {{ $school_data->fax ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @else
                                <span>Logo Not Available</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pb-2">
                    @if ($test)
                        @if ($test->type == 'CA')
                            <h5 class="mb-0">TEACHER BY TEACHER {{ strtoupper($test->grade->name ?? 'GRADE') }}, {{ strtoupper($test->name ?? 'MONTH') }} {{ $test->year ?? date('Y') }}</h5>
                        @else
                            <h5 class="mb-0">TEACHER BY TEACHER {{ strtoupper($test->grade->name ?? 'GRADE') }}, END OF TERM (EXAM) {{ $test->year ?? date('Y') }}</h5>
                        @endif
                    @else
                        <h5 class="mb-0">Teacher Value Addition Analysis</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Per-subject tables + charts --}}
    @forelse ($subjectGroups as $groupIndex => $group)
        <div class="row">
            <div class="col-12">
                <div class="card subject-card">
                    <div class="card-header">
                        <h5>{{ $group['name'] }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Teacher</th>
                                        <th>Class</th>
                                        <th>A*</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>F</th>
                                        <th>G</th>
                                        <th>U</th>
                                        <th>X</th>
                                        <th>Total</th>
                                        <th>ABC%</th>
                                        <th>%(A-E)</th>
                                        <th>% JC [ABC]</th>
                                        <th>VA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['teacher'] }}</td>
                                            <td>{{ $row['class'] }}</td>
                                            <td>{{ $row['A*'] }}</td>
                                            <td>{{ $row['A'] }}</td>
                                            <td>{{ $row['B'] }}</td>
                                            <td>{{ $row['C'] }}</td>
                                            <td>{{ $row['D'] }}</td>
                                            <td>{{ $row['E'] }}</td>
                                            <td>{{ $row['F'] }}</td>
                                            <td>{{ $row['G'] }}</td>
                                            <td>{{ $row['U'] }}</td>
                                            <td>{{ $row['X'] }}</td>
                                            <td>{{ $row['total'] }}</td>
                                            <td>{{ $row['abcPercent'] }}</td>
                                            <td>{{ $row['aePercent'] }}</td>
                                            <td>{{ $row['jcAbcPercent'] }}</td>
                                            <td class="{{ $row['va'] >= 0 ? 'va-positive' : 'va-negative' }}">{{ $row['va'] > 0 ? '+' : '' }}{{ $row['va'] }}</td>
                                        </tr>
                                    @endforeach
                                    {{-- Department Overall --}}
                                    @php $tot = $group['total']; @endphp
                                    <tr class="total-row">
                                        <td>Department Overall</td>
                                        <td></td>
                                        <td>{{ $tot['A*'] }}</td>
                                        <td>{{ $tot['A'] }}</td>
                                        <td>{{ $tot['B'] }}</td>
                                        <td>{{ $tot['C'] }}</td>
                                        <td>{{ $tot['D'] }}</td>
                                        <td>{{ $tot['E'] }}</td>
                                        <td>{{ $tot['F'] }}</td>
                                        <td>{{ $tot['G'] }}</td>
                                        <td>{{ $tot['U'] }}</td>
                                        <td>{{ $tot['X'] }}</td>
                                        <td>{{ $tot['total'] }}</td>
                                        <td>{{ $tot['abcPercent'] }}</td>
                                        <td>{{ $tot['aePercent'] }}</td>
                                        <td>{{ $tot['jcAbcPercent'] }}</td>
                                        <td class="{{ $tot['va'] >= 0 ? 'va-positive' : 'va-negative' }}">{{ $tot['va'] > 0 ? '+' : '' }}{{ $tot['va'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="chart-{{ $groupIndex }}" class="chart-container no-print"></div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i> No data available for this test.
                </div>
            </div>
        </div>
    @endforelse
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const subjectGroups = @json($subjectGroups);

            const gradeColors = {
                'A*': '#1e3a5f', 'A': '#2563eb', 'B': '#3b82f6', 'C': '#60a5fa',
                'D': '#f59e0b', 'E': '#f97316', 'F': '#ef4444', 'G': '#dc2626',
                'U': '#991b1b', 'X': '#9ca3af'
            };
            const grades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X'];

            subjectGroups.forEach(function(group, idx) {
                const chartDom = document.getElementById('chart-' + idx);
                if (!chartDom || typeof echarts === 'undefined') return;

                const existingInstance = echarts.getInstanceByDom(chartDom);
                if (existingInstance) existingInstance.dispose();

                const chart = echarts.init(chartDom);
                const rows = group.rows;

                // X-axis: "Teacher (Class)"
                const labels = rows.map(function(r) {
                    return r.teacher + ' (' + r['class'] + ')';
                });

                // Stacked bar series per grade
                const barSeries = grades.map(function(grade) {
                    return {
                        name: grade,
                        type: 'bar',
                        stack: 'grades',
                        emphasis: { focus: 'series' },
                        itemStyle: { color: gradeColors[grade] },
                        data: rows.map(function(r) { return r[grade]; })
                    };
                });

                // Line series on secondary axis
                const lineSeries = [
                    {
                        name: 'ABC%',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 6,
                        lineStyle: { width: 2, color: '#059669' },
                        itemStyle: { color: '#059669' },
                        data: rows.map(function(r) { return r.abcPercent; })
                    },
                    {
                        name: '% JC [ABC]',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        symbol: 'diamond',
                        symbolSize: 6,
                        lineStyle: { width: 2, color: '#7c3aed', type: 'dashed' },
                        itemStyle: { color: '#7c3aed' },
                        data: rows.map(function(r) { return r.jcAbcPercent; })
                    },
                    {
                        name: 'VA',
                        type: 'bar',
                        yAxisIndex: 1,
                        barWidth: 14,
                        barGap: '30%',
                        itemStyle: {
                            color: function(params) {
                                return params.value >= 0 ? '#059669' : '#dc2626';
                            },
                            borderRadius: [2, 2, 0, 0]
                        },
                        data: rows.map(function(r) { return r.va; })
                    }
                ];

                const option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'cross' },
                        valueFormatter: function(value) {
                            return typeof value === 'number' ? value.toFixed(1) : value;
                        }
                    },
                    legend: {
                        data: grades.concat(['ABC%', '% JC [ABC]', 'VA']),
                        top: 0,
                        type: 'scroll',
                        textStyle: { fontSize: 11 }
                    },
                    grid: {
                        top: 50,
                        bottom: 10,
                        left: '3%',
                        right: '4%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            interval: 0,
                            rotate: labels.length > 4 ? 25 : 0,
                            fontSize: 11
                        }
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
                            min: function(value) { return Math.min(0, Math.floor(value.min / 10) * 10); },
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
                            magicType: {
                                type: ['line', 'bar', 'stack'],
                                title: { line: 'Line', bar: 'Bar', stack: 'Stack' }
                            },
                            restore: { title: 'Restore' }
                        }
                    }
                };

                chart.setOption(option);

                var resizeObserver = new ResizeObserver(function() {
                    if (chart && !chart.isDisposed()) chart.resize();
                });
                resizeObserver.observe(chartDom);
                window.addEventListener('resize', function() {
                    if (chart && !chart.isDisposed()) chart.resize();
                });
            });
        });
    </script>
@endsection

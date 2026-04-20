@extends('layouts.master')
@section('title', 'House Performance Analysis')
@section('css')
    <style>
        .table td,
        .table th {
            font-size: 12px;
            padding: 0.2rem;
            vertical-align: middle;
            text-align: center;
            white-space: nowrap;
        }

        .table thead th {
            font-weight: 600;
            font-size: 12px;
            background-color: #f8f9fa;
        }

        .house-name {
            text-align: left !important;
            font-weight: 600;
        }

        .totals-row {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .chart-container {
            height: 400px;
            margin-bottom: 30px;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        @media print {

            .card-tools,
            .btn-group,
            .charts-section {
                display: none;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
            }

            .table th,
            .table td {
                border: 1px solid #dee2e6;
                padding: 4px;
                font-size: 9px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('finals.houses.index') }}">Back</a>
        @endslot
        @slot('title')
            House Performance Analysis
        @endslot
    @endcomponent
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-end">
                <a href="javascript:void(0)" onclick="window.print()">
                    <i class="bx bx-printer font-size-18 text-muted me-2"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border-1">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6 align-items-start">
                                <div class="form-group" style="font-size: 13px;">
                                    <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                                    <span>{{ $school_data->physical_address ?? '' }}</span><br>
                                    <span>{{ $school_data->postal_address ?? '' }}</span><br>
                                    <span>Tel: {{ $school_data->telephone ?? '' }} Fax: {{ $school_data->fax ?? '' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                @if (isset($school_data->logo_path))
                                    <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">House Performance Analysis - {{ $graduation_term }}, {{ $graduation_year }}</h5>

                        @if (count($houses) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">House</th>
                                            <th colspan="21" class="text-center">Grade Counts</th>
                                            <th colspan="12" class="text-center">Percentages</th>
                                            <th colspan="3" class="text-center">Total Students</th>
                                        </tr>
                                        <tr>
                                            <!-- Grade headers -->
                                            <th colspan="3">Merit</th>
                                            <th colspan="3">A</th>
                                            <th colspan="3">B</th>
                                            <th colspan="3">C</th>
                                            <th colspan="3">D</th>
                                            <th colspan="3">E</th>
                                            <th colspan="3">U</th>
                                            <!-- Percentage headers -->
                                            <th colspan="3">MAB%</th>
                                            <th colspan="3">MABC%</th>
                                            <th colspan="3">MABCD%</th>
                                            <th colspan="3">DEU%</th>
                                            <!-- Total Students -->
                                            <th>M</th>
                                            <th>F</th>
                                            <th>T</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <!-- Sub-headers for grades (7 grades x 3 = 21) -->
                                            @for ($i = 0; $i < 7; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor
                                            <!-- Sub-headers for percentages (4 categories x 3 = 12) -->
                                            @for ($i = 0; $i < 4; $i++)
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                            @endfor
                                            <!-- Total students already has headers -->
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($houses as $house)
                                            <tr>
                                                <td class="house-name">{{ $house['house_name'] }}</td>

                                                <!-- Grade counts -->
                                                @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                    <td>{{ $house['grade_analysis'][$grade]['M'] }}</td>
                                                    <td>{{ $house['grade_analysis'][$grade]['F'] }}</td>
                                                    <td>{{ $house['grade_analysis'][$grade]['T'] }}</td>
                                                @endforeach

                                                <!-- Percentages -->
                                                @foreach (['MAB', 'MABC', 'MABCD', 'DEU'] as $category)
                                                    <td>{{ $house['percentages'][$category]['M'] }}%</td>
                                                    <td>{{ $house['percentages'][$category]['F'] }}%</td>
                                                    <td>{{ $house['percentages'][$category]['T'] }}%</td>
                                                @endforeach

                                                <!-- Total students -->
                                                <td>{{ $house['totals']['M'] }}</td>
                                                <td>{{ $house['totals']['F'] }}</td>
                                                <td>{{ $house['totals']['T'] }}</td>
                                            </tr>
                                        @endforeach

                                        <!-- Totals row -->
                                        <tr class="totals-row">
                                            <td class="house-name">Totals</td>

                                            <!-- Grade totals -->
                                            @foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <td>{{ $overall_summary[$grade]['M'] }}</td>
                                                <td>{{ $overall_summary[$grade]['F'] }}</td>
                                                <td>{{ $overall_summary[$grade]['T'] }}</td>
                                            @endforeach

                                            <!-- Percentage totals -->
                                            @foreach (['MAB', 'MABC', 'MABCD', 'DEU'] as $category)
                                                <td>{{ $overall_summary['percentages'][$category]['M'] }}%</td>
                                                <td>{{ $overall_summary['percentages'][$category]['F'] }}%</td>
                                                <td>{{ $overall_summary['percentages'][$category]['T'] }}%</td>
                                            @endforeach

                                            <!-- Total students -->
                                            <td>{{ $overall_summary['totals']['M'] }}</td>
                                            <td>{{ $overall_summary['totals']['F'] }}</td>
                                            <td>{{ $overall_summary['totals']['T'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Charts Section -->
                            <div class="charts-section mt-5">
                                <h5 class="text-center mb-4">Overall Grade Distribution by Gender</h5>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="chart-title">Overall Grade Distribution by House and Gender</div>
                                        <div id="houseGradeChart" class="chart-container"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                No house data with results found for {{ $graduation_year }}.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const houses = @json($houses);
            const overallSummary = @json($overall_summary);

            if (houses.length > 0) {
                initHouseGradeChart(houses, overallSummary);
            }
        });

        function initHouseGradeChart(houses, summary) {
            const chartDom = document.getElementById('houseGradeChart');
            if (!chartDom) return;

            const chart = echarts.init(chartDom);

            const houseNames = houses.map(h => h.house_name);
            const grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
            const colors = {
                'Merit (M)': '#8B5CF6',
                'Merit (F)': '#A78BFA',
                'A (M)': '#10B981',
                'A (F)': '#34D399',
                'B (M)': '#F59E0B',
                'B (F)': '#FBBF24',
                'C (M)': '#EF4444',
                'C (F)': '#F87171',
                'D (M)': '#6366F1',
                'D (F)': '#818CF8',
                'E (M)': '#EC4899',
                'E (F)': '#F472B6',
                'U (M)': '#6B7280',
                'U (F)': '#9CA3AF'
            };

            const series = [];
            grades.forEach(grade => {
                // Male
                series.push({
                    name: `${grade} (M)`,
                    type: 'line',
                    data: houses.map(h => h.grade_analysis[grade].M),
                    lineStyle: {
                        type: 'solid'
                    },
                    symbol: 'circle',
                    symbolSize: 6
                });
                // Female
                series.push({
                    name: `${grade} (F)`,
                    type: 'line',
                    data: houses.map(h => h.grade_analysis[grade].F),
                    lineStyle: {
                        type: 'dashed'
                    },
                    symbol: 'circle',
                    symbolSize: 6
                });
            });

            const option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: series.map(s => s.name),
                    top: 0,
                    type: 'scroll'
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    top: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: houseNames,
                    name: 'House',
                    nameLocation: 'end'
                },
                yAxis: {
                    type: 'value',
                    name: 'Students'
                },
                series: series
            };

            chart.setOption(option);

            window.addEventListener('resize', function() {
                chart.resize();
            });
        }
    </script>
@endsection

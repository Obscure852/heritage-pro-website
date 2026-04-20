@extends('layouts.master')

@section('title')
    Classes Overall Analysis
@endsection

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

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 0;
            box-shadow: none;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: center;
            font-size: 12px;
        }

        .table th {
            background-color: #fff;
            font-weight: 600;
        }

        .class-section {
            margin-bottom: 24px;
        }

        .class-info {
            margin-bottom: 8px;
        }

        .class-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
        }

        .class-info small {
            font-size: 12px;
            color: #6b7280;
        }

        .class-table-container {
            border: 1px solid #e5e7eb;
            border-radius: 0;
            overflow: hidden;
        }

        .totals-row {
            font-weight: 600;
            background: #f3f3f3;
        }

        .overall-section {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #e5e7eb;
        }

        .overall-section h5 {
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 16px;
        }

        @media screen {
            body {
                font-size: 14px;
            }
        }

        @media print {
            @page {
                size: portrait;
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
                border: none;
            }

            .table {
                font-size: 9pt;
            }

            .class-section {
                page-break-inside: avoid;
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
            Classes Overall Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
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
                                <span>{{ $school_data->physical_address }}</span>
                                <br>
                                <span>{{ $school_data->postal_address }}</span>
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
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>
                                    {{ $grade->name ?? 'Grade' }} -
                                    @if ($reportType === 'Exam')
                                        End Of Term Exam
                                    @else
                                        End Of {{ $test->name ?? '' }}
                                    @endif
                                    Term {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }} Classes Performance Analysis
                                </h5>
                            </div>
                        </div>

                        {{-- Individual Class Tables --}}
                        @foreach ($classPerformance as $className => $data)
                            <div class="class-section">
                                <div class="class-info">
                                    <h6>{{ $data['className'] }}</h6>
                                    <small>Class Teacher: {{ $data['classTeacher'] }}</small>
                                </div>
                                <div class="class-table-container">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Merit</th>
                                                <th>A</th>
                                                <th>B</th>
                                                <th>C</th>
                                                <th>D</th>
                                                <th>E</th>
                                                <th>U</th>
                                                <th>Total</th>
                                                <th>MAB%</th>
                                                <th>MABC%</th>
                                                <th>MABCD%</th>
                                                <th>DEU%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $data['grades']['Merit'] }}</td>
                                                <td>{{ $data['grades']['A'] }}</td>
                                                <td>{{ $data['grades']['B'] }}</td>
                                                <td>{{ $data['grades']['C'] }}</td>
                                                <td>{{ $data['grades']['D'] }}</td>
                                                <td>{{ $data['grades']['E'] }}</td>
                                                <td>{{ $data['grades']['U'] }}</td>
                                                <td><strong>{{ $data['total'] }}</strong></td>
                                                <td>{{ $data['MAB%'] }}%</td>
                                                <td>{{ $data['MABC%'] }}%</td>
                                                <td>{{ $data['MABCD%'] }}%</td>
                                                <td>{{ $data['DEU%'] }}%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                        {{-- Overall Totals Section --}}
                        <div class="overall-section">
                            <h5>Overall Totals (All Classes)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Merit</th>
                                            <th>A</th>
                                            <th>B</th>
                                            <th>C</th>
                                            <th>D</th>
                                            <th>E</th>
                                            <th>U</th>
                                            <th>Total</th>
                                            <th>MAB%</th>
                                            <th>MABC%</th>
                                            <th>MABCD%</th>
                                            <th>DEU%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="totals-row">
                                            <td>{{ $overallTotals['grades']['Merit'] }}</td>
                                            <td>{{ $overallTotals['grades']['A'] }}</td>
                                            <td>{{ $overallTotals['grades']['B'] }}</td>
                                            <td>{{ $overallTotals['grades']['C'] }}</td>
                                            <td>{{ $overallTotals['grades']['D'] }}</td>
                                            <td>{{ $overallTotals['grades']['E'] }}</td>
                                            <td>{{ $overallTotals['grades']['U'] }}</td>
                                            <td><strong>{{ $overallTotals['total'] }}</strong></td>
                                            <td>{{ $overallTotals['MAB%'] }}%</td>
                                            <td>{{ $overallTotals['MABC%'] }}%</td>
                                            <td>{{ $overallTotals['MABCD%'] }}%</td>
                                            <td>{{ $overallTotals['DEU%'] }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Charts Section --}}
                        <div class="row no-print mt-4">
                            <div class="col-12 mb-4">
                                <div id="gradeDistributionChart" style="width: 100%; height: 400px;"></div>
                            </div>
                            <div class="col-12">
                                <div id="percentageChart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function printContent() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const classPerformance = @json($classPerformance ?? []);
        const classNames = Object.keys(classPerformance);
        const grades = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];
        const percentages = ['MAB%', 'MABC%', 'MABCD%', 'DEU%'];

        if (!classPerformance || classNames.length === 0) {
            document.getElementById('gradeDistributionChart').innerHTML =
                '<p style="text-align:center; padding:20px;">No data available for charts.</p>';
            document.getElementById('percentageChart').innerHTML =
                '<p style="text-align:center; padding:20px;">No data available for charts.</p>';
            return;
        }

        // Prepare data for charts
        const gradeData = {};
        const percentageData = {};

        grades.forEach(g => gradeData[g] = []);
        percentages.forEach(p => percentageData[p] = []);

        classNames.forEach(className => {
            const data = classPerformance[className];
            grades.forEach(g => gradeData[g].push(data.grades[g] || 0));
            percentages.forEach(p => percentageData[p].push(data[p] || 0));
        });

        const colors = {
            Merit: '#9a60b4',
            A: '#91cc75',
            B: '#5470c6',
            C: '#fac858',
            D: '#fc8452',
            E: '#ee6666',
            U: '#9e9e9e',
            'MAB%': '#5470c6',
            'MABC%': '#91cc75',
            'MABCD%': '#fac858',
            'DEU%': '#ee6666'
        };

        // Grade Distribution Chart
        const gradeChartDom = document.getElementById('gradeDistributionChart');
        const gradeChart = echarts.init(gradeChartDom);

        const gradeSeries = grades.map(grade => ({
            name: grade,
            type: 'bar',
            stack: 'total',
            emphasis: { focus: 'series' },
            data: gradeData[grade],
            itemStyle: { color: colors[grade] }
        }));

        gradeChart.setOption({
            title: {
                text: 'Grade Distribution by Class',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' }
            },
            legend: {
                top: 30,
                data: grades
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
                data: classNames,
                axisLabel: { rotate: classNames.length > 5 ? 45 : 0 }
            },
            yAxis: {
                type: 'value',
                name: 'Number of Students'
            },
            series: gradeSeries,
            toolbox: {
                right: 20,
                feature: {
                    saveAsImage: {},
                    magicType: { type: ['line', 'bar', 'stack'] },
                    restore: {}
                }
            }
        });

        // Percentage Chart
        const percChartDom = document.getElementById('percentageChart');
        const percChart = echarts.init(percChartDom);

        const percSeries = percentages.map(p => ({
            name: p,
            type: 'line',
            smooth: true,
            data: percentageData[p],
            itemStyle: { color: colors[p] }
        }));

        percChart.setOption({
            title: {
                text: 'Performance Percentages by Class',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                valueFormatter: val => val + '%'
            },
            legend: {
                top: 30,
                data: percentages
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
                data: classNames,
                axisLabel: { rotate: classNames.length > 5 ? 45 : 0 }
            },
            yAxis: {
                type: 'value',
                name: 'Percentage',
                min: 0,
                max: 100,
                axisLabel: { formatter: '{value}%' }
            },
            series: percSeries,
            toolbox: {
                right: 20,
                feature: {
                    saveAsImage: {},
                    magicType: { type: ['line', 'bar'] },
                    restore: {}
                }
            }
        });

        // Resize handling
        window.addEventListener('resize', function() {
            gradeChart.resize();
            percChart.resize();
        });
    });
</script>
@endsection

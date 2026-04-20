@extends('layouts.master')
@section('title')
    Class Subjects List Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Grade Subjects Analysis Report
        @endslot
    @endcomponent
@section('css')
    <style>
        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 14px;
        }

        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 12px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: auto;
                margin-left: 0;
                margin-top: 0;
                padding: 20mm;
                page-break-after: always;
            }

            .card {
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }
    </style>
@endsection
<div class="row no-print">
    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
            <i style="font-size:20px;" class="bx bx-download text-muted me-2"></i>
        </a>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
            class="bx bx-printer text-muted"></i>
    </div>
</div>
<div class="container-fluid printable">
    <div class="card">
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
        <div class="card-body">
            <div class="table-responsive">
                @if ($test->type == 'CA')
                    <h6> {{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Subjects Analysis
                    </h6>
                @else
                    <h6>{{ $test->grade->name ?? 'Grade' }} - End of Term {{ $test->grade->name ?? '' }} Subjects
                        Analysis</h6>
                @endif
                <table class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="text-align:left;" rowspan="2">Subject</th>
                            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                <th style="text-align: center" colspan="3">{{ $grade }}</th>
                            @endforeach
                            <th rowspan="2">Credit %</th>
                            <th rowspan="2">Pass %</th>
                            <th style="text-align: center;" colspan="3">Total</th>
                            <th rowspan="2">Position</th>
                        </tr>
                        <tr style="text-align: center;">
                            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                <th>M</th>
                                <th>F</th>
                                <th>T</th>
                            @endforeach
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $subject)
                            <tr>
                                <td style="text-align: left;">{{ $subject['SUBJECT'] }}</td>
                                @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                    <td>{{ $subject[$grade]['M'] }}</td>
                                    <td>{{ $subject[$grade]['F'] }}</td>
                                    <td>{{ ($subject[$grade]['M'] ?? 0) + ($subject[$grade]['F'] ?? 0) }}</td>
                                @endforeach
                                <td>{{ $subject['CREDIT %'] }}</td>
                                <td>{{ $subject['PASS %'] }}</td>
                                <td>{{ $subject['TOTAL']['M'] }}</td>
                                <td>{{ $subject['TOTAL']['F'] }}</td>
                                <td>{{ ($subject['TOTAL']['M'] ?? 0) + ($subject['TOTAL']['F'] ?? 0) }}</td>
                                <td>{{ $subject['POSITION'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td>TOTAL</td>
                            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade)
                                <td>{{ $totals[$grade]['M'] }}</td>
                                <td>{{ $totals[$grade]['F'] }}</td>
                                <td>{{ ($totals[$grade]['M'] ?? 0) + ($totals[$grade]['F'] ?? 0) }}</td>
                            @endforeach
                            <td>{{ $totals['CREDIT %'] }}</td>
                            <td>{{ $totals['PASS %'] }}</td>
                            <td>{{ $totals['TOTAL']['M'] }}</td>
                            <td>{{ $totals['TOTAL']['F'] }}</td>
                            <td>{{ ($totals['TOTAL']['M'] ?? 0) + ($totals['TOTAL']['F'] ?? 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Charts -->
            <div class="chart-container no-print">
                <h6>Grade Distribution by Gender for Each Subject</h6>
                <canvas id="gradeDistributionChart"></canvas>
            </div>
            <div class="chart-container no-print" style="max-width: 400px; margin: 20px auto;">
                <h6>Overall Credit and Pass Percentages</h6>
                <canvas id="creditPassChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportData = @json($report);
        const totalsData = @json($totals);

        const grades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'];
        const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        const subjects = reportData.map(item => item.SUBJECT);
        const datasets = grades.flatMap(grade => [{
                label: `${grade} (Male)`,
                data: reportData.map(item => item[grade]?.M || 0),
                backgroundColor: getColorForGrade(grade, 0.7),
                stack: grade
            },
            {
                label: `${grade} (Female)`,
                data: reportData.map(item => item[grade]?.F || 0),
                backgroundColor: getColorForGrade(grade, 0.4),
                stack: grade
            }
        ]);

        new Chart(gradeDistributionCtx, {
            type: 'bar',
            data: {
                labels: subjects,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // To reduce clutter
                    },
                    title: {
                        display: true,
                        text: 'Grade Distribution by Gender for Each Subject'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });

        // Credit and Pass Percentages Chart
        const creditPassCtx = document.getElementById('creditPassChart').getContext('2d');
        new Chart(creditPassCtx, {
            type: 'pie',
            data: {
                labels: ['Credit %', 'Pass %', 'Other'],
                datasets: [{
                    data: [
                        totalsData['CREDIT %'],
                        totalsData['PASS %'] - totalsData['CREDIT %'],
                        100 - totalsData['PASS %']
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Overall Credit and Pass Percentages'
                    }
                }
            }
        });
    });

    function getColorForGrade(grade, alpha) {
        const colors = {
            'A*': `rgba(75, 192, 192, ${alpha})`,
            'A': `rgba(54, 162, 235, ${alpha})`,
            'B': `rgba(255, 206, 86, ${alpha})`,
            'C': `rgba(153, 102, 255, ${alpha})`,
            'D': `rgba(255, 159, 64, ${alpha})`,
            'E': `rgba(255, 99, 132, ${alpha})`,
            'F': `rgba(199, 199, 199, ${alpha})`,
            'G': `rgba(83, 102, 255, ${alpha})`,
            'U': `rgba(255, 99, 71, ${alpha})`
        };
        return colors[grade] || `rgba(0, 0, 0, ${alpha})`;
    }

    function printContent() {
        window.print();
    }
</script>
@endsection

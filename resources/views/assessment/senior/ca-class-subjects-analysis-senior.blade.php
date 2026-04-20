@extends('layouts.master')

@section('title')
    Class Subjects List Analysis
@endsection

@section('css')
    <style>
        .equal-width-table th,
        .equal-width-table td {
            width: 1%;
            white-space: nowrap;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle !important;
        }

        .table thead th {
            text-align: center;
        }

        .gender-row {
            font-size: 0.9em;
            color: #555;
        }

        .chart-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
        }

        .printable {
            font-size: 10pt;
        }

        .printable table {
            font-size: 12px;
        }

        @media print {
            @page {
                size: landscape;
                margin-top: 10px;
                margin-left: 10px;
                margin-right: 10px;
            }

            .no-print {
                display: none !important;
            }


            .printable table {
                font-size: 10px;
            }

            .table-responsive {
                overflow-x: visible !important;
            }

            .container-fluid {
                width: 100%;
                margin: 0;
                padding: 0;
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
            @if ($type === 'CA')
                End of {{ $test->name ?? 'Month' }} Analysis Report
            @else
                End of Term Analysis Report
            @endif
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 col-lg-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card border-1">
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
                    @if ($type === 'CA')
                        <h6>{{ $className }} - End of {{ $test->name ?? 'Month' }} Subjects Analysis Report by Grade &
                            Gender</h6>
                    @else
                        <h6>{{ $className }} - End of Term Subjects Analysis Report by Grade & Gender</h6>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="text-align:left;" rowspan="2">SUBJECT</th>
                                    <th colspan="2">A*</th>
                                    <th colspan="2">A</th>
                                    <th colspan="2">B</th>
                                    <th colspan="2">C</th>
                                    <th rowspan="2">CREDIT %</th>
                                    <th colspan="2">D</th>
                                    <th colspan="2">E</th>
                                    <th rowspan="2">PASS %</th>
                                    <th colspan="2">F</th>
                                    <th colspan="2">G</th>
                                    <th colspan="2">U</th>
                                    <th colspan="2">TOTAL</th>
                                    <th rowspan="2">POSITION</th>
                                </tr>
                                <tr class="gender-row">
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>M</th>
                                    <th>F</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report as $subject)
                                    <tr>
                                        <td style="text-align: left;">{{ $subject['SUBJECT'] }}</td>
                                        <td>{{ $subject['A*']['M'] }}</td>
                                        <td>{{ $subject['A*']['F'] }}</td>
                                        <td>{{ $subject['A']['M'] }}</td>
                                        <td>{{ $subject['A']['F'] }}</td>
                                        <td>{{ $subject['B']['M'] }}</td>
                                        <td>{{ $subject['B']['F'] }}</td>
                                        <td>{{ $subject['C']['M'] }}</td>
                                        <td>{{ $subject['C']['F'] }}</td>
                                        <td>{{ $subject['CREDIT %'] }}</td>
                                        <td>{{ $subject['D']['M'] }}</td>
                                        <td>{{ $subject['D']['F'] }}</td>
                                        <td>{{ $subject['E']['M'] }}</td>
                                        <td>{{ $subject['E']['F'] }}</td>
                                        <td>{{ $subject['PASS %'] }}</td>
                                        <td>{{ $subject['F']['M'] }}</td>
                                        <td>{{ $subject['F']['F'] }}</td>
                                        <td>{{ $subject['G']['M'] }}</td>
                                        <td>{{ $subject['G']['F'] }}</td>
                                        <td>{{ $subject['U']['M'] }}</td>
                                        <td>{{ $subject['U']['F'] }}</td>
                                        <td>{{ $subject['TOTAL']['M'] }}</td>
                                        <td>{{ $subject['TOTAL']['F'] }}</td>
                                        <td>{{ $subject['POSITION'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td>TOTAL</td>
                                    <td>{{ $totals['A*']['M'] }}</td>
                                    <td>{{ $totals['A*']['F'] }}</td>
                                    <td>{{ $totals['A']['M'] }}</td>
                                    <td>{{ $totals['A']['F'] }}</td>
                                    <td>{{ $totals['B']['M'] }}</td>
                                    <td>{{ $totals['B']['F'] }}</td>
                                    <td>{{ $totals['C']['M'] }}</td>
                                    <td>{{ $totals['C']['F'] }}</td>
                                    <td>{{ $totals['CREDIT %'] }}</td>
                                    <td>{{ $totals['D']['M'] }}</td>
                                    <td>{{ $totals['D']['F'] }}</td>
                                    <td>{{ $totals['E']['M'] }}</td>
                                    <td>{{ $totals['E']['F'] }}</td>
                                    <td>{{ $totals['PASS %'] }}</td>
                                    <td>{{ $totals['F']['M'] }}</td>
                                    <td>{{ $totals['F']['F'] }}</td>
                                    <td>{{ $totals['G']['M'] }}</td>
                                    <td>{{ $totals['G']['F'] }}</td>
                                    <td>{{ $totals['U']['M'] }}</td>
                                    <td>{{ $totals['U']['F'] }}</td>
                                    <td>{{ $totals['TOTAL']['M'] }}</td>
                                    <td>{{ $totals['TOTAL']['F'] }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="chart-container no-print">
                        <h6>Grade Distribution by Gender for Each Subject</h6>
                        <canvas id="gradeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const reportData = @json($report);
                const totalsData = @json($totals);

                const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
                const subjects = reportData.map(item => item.SUBJECT);
                const grades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'];
                const datasets = grades.flatMap(grade => [{
                        label: `${grade} (Male)`,
                        data: reportData.map(item => item[grade].M),
                        backgroundColor: getColorForGrade(grade, 0.7),
                        stack: grade
                    },
                    {
                        label: `${grade} (Female)`,
                        data: reportData.map(item => item[grade].F),
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
                                stacked: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Grade Distribution by Gender for Each Subject'
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

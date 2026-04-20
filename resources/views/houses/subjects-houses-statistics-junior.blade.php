@extends('layouts.master')
@section('title')
    Houses Subjects Analysis
@endsection
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
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

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Houses Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" title="Export to Excel">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
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
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>All Grades House Performance Analysis -
                                    {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                                    - Term
                                    {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">House</th>

                                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                    <th colspan="2">{{ $g }}</th>
                                                @endforeach

                                                @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $p)
                                                    <th colspan="2">{{ $p }}</th>
                                                @endforeach

                                                <th colspan="2">Total</th>
                                            </tr>
                                            <tr>
                                                @for ($i = 0; $i < 10; $i++)
                                                    <th>M</th>
                                                    <th>F</th>
                                                @endfor
                                                <th>M</th>
                                                <th>F</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($housePerformance as $houseName => $data)
                                                <tr>
                                                    <td>{{ $houseName }}</td>

                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                        <td>{{ $data['grades'][$g]['M'] }}</td>
                                                        <td>{{ $data['grades'][$g]['F'] }}</td>
                                                    @endforeach

                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $p)
                                                        <td>{{ $data[$p]['M'] }}%</td>
                                                        <td>{{ $data[$p]['F'] }}%</td>
                                                    @endforeach

                                                    <td>{{ $data['totalMale'] }}</td>
                                                    <td>{{ $data['totalFemale'] }}</td>
                                                </tr>
                                            @endforeach

                                            <tr style="font-weight:600;background:#f3f3f3">
                                                <td>Totals</td>

                                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g)
                                                    <td>{{ $overallTotals['grades'][$g]['M'] }}</td>
                                                    <td>{{ $overallTotals['grades'][$g]['F'] }}</td>
                                                @endforeach

                                                @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%'] as $p)
                                                    <td>{{ $overallTotals[$p]['M'] }}%</td>
                                                    <td>{{ $overallTotals[$p]['F'] }}%</td>
                                                @endforeach

                                                <td>{{ $overallTotals['totalMale'] }}</td>
                                                <td>{{ $overallTotals['totalFemale'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Graphs Section -->
                        <div class="row no-print mt-4">
                            <div class="col-md-12">
                                <h5 class="text-center">Percentage Performance by House and Gender</h5>
                                <canvas id="percentageChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                        <div class="row no-print mt-4">
                            <div class="col-md-12">
                                <h5 class="text-center">Grade Distribution by House and Gender</h5>
                                <canvas id="gradeDistributionChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var housePerformance = @json($housePerformance);
        var houseNames = Object.keys(housePerformance);

        var grades = ['A', 'B', 'C', 'D', 'E', 'U'];
        var maleGradeCounts = {};
        var femaleGradeCounts = {};

        grades.forEach(function(grade) {
            maleGradeCounts[grade] = houseNames.map(function(house) {
                return housePerformance[house]['grades'][grade]['M'];
            });
            femaleGradeCounts[grade] = houseNames.map(function(house) {
                return housePerformance[house]['grades'][grade]['F'];
            });
        });

        var ctx = document.getElementById('gradeDistributionChart').getContext('2d');
        var gradeDistributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: houseNames,
                datasets: grades.flatMap(function(grade, index) {
                    var backgroundColors = ['#4caf50', '#2196f3', '#ffc107', '#ff5722', '#9c27b0',
                        '#e91e63'
                    ];
                    return [{
                            label: grade + ' (M)',
                            data: maleGradeCounts[grade],
                            backgroundColor: backgroundColors[index],
                            stack: 'Male'
                        },
                        {
                            label: grade + ' (F)',
                            data: femaleGradeCounts[grade],
                            backgroundColor: backgroundColors[index],
                            borderColor: '#000',
                            borderWidth: 1,
                            stack: 'Female'
                        }
                    ];
                })
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Grades'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Grade Distribution by House and Gender'
                    }
                }
            }
        });

        // Prepare data for Percentage Performance by House and Gender
        var percentages = ['AB%', 'ABC%', 'ABCD%', 'DEU%'];
        var malePercentageData = {};
        var femalePercentageData = {};

        percentages.forEach(function(percentage) {
            malePercentageData[percentage] = houseNames.map(function(house) {
                return housePerformance[house][percentage]['M'];
            });
            femalePercentageData[percentage] = houseNames.map(function(house) {
                return housePerformance[house][percentage]['F'];
            });
        });

        var ctx2 = document.getElementById('percentageChart').getContext('2d');
        var percentageChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: houseNames,
                datasets: percentages.flatMap(function(percentage, index) {
                    var borderColors = ['#42a5f5', '#66bb6a', '#ffa726', '#ab47bc'];
                    return [{
                            label: percentage + ' (M)',
                            data: malePercentageData[percentage],
                            borderColor: borderColors[index],
                            backgroundColor: borderColors[index],
                            fill: false,
                            tension: 0.1
                        },
                        {
                            label: percentage + ' (F)',
                            data: femalePercentageData[percentage],
                            borderColor: borderColors[index],
                            backgroundColor: borderColors[index],
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.1
                        }
                    ];
                })
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Percentage'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Percentage Performance by House and Gender'
                    }
                }
            }
        });

        function printContent() {
            window.print();
        }
    </script>
@endsection

@extends('layouts.master')
@section('title')
    ({{ $grade->name ?? '' }}) Special Needs Analysis
@endsection
@section('css')
    <style>
        .performance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .performance-table th,
        .performance-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .performance-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .performance-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .performance-table tr:hover {
            background-color: #f5f5f5;
        }

        .category-header {
            background-color: #e0e0e0 !important;
            font-weight: bold;
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2 !important;
        }

        .highlighted-cell {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .chart-container {
            width: 100%;
            height: 400px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
        }

        .student-type-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .student-type-section h6 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .performance-table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
            }

            .category-header {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
            }

            .total-row {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
            }

            .highlighted-cell {
                background-color: #d4edda !important;
                -webkit-print-color-adjust: exact;
            }

            .type-badge {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
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
            Special Needs Analysis
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row no-print mb-3">
            <div class="col-12 d-flex justify-content-end">
                <i onclick="printContent()" class="bx bx-printer text-muted" style="font-size:20px; cursor:pointer;"></i>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <strong>{{ $school_data->school_name }}</strong><br>
                    {{ $school_data->physical_address }}<br>
                    {{ $school_data->postal_address }}<br>
                    Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}
                </div>
                <div>
                    @if ($school_data->logo_path)
                        <img src="{{ asset($school_data->logo_path) }}" height="80" alt="Logo">
                    @endif
                </div>
            </div>
            <div class="card-body">
                <h6>{{ $grade->name ?? '' }}
                    {{ strtolower($test->type ?? '') === 'exam' ? 'End of Term' : 'End of ' . ($test->name ?? '') }}
                    Special Needs Analysis - Term {{ $term->term }},
                    {{ $term->year }}</h6>

                @if($totalStudents > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped performance-table">
                            <thead>
                                <tr>
                                    <th colspan="2"></th>
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
                                <!-- Male -->
                                <tr>
                                    <td rowspan="2" class="category-header">Male</td>
                                    <td>Output</td>
                                    <td>{{ $m_M }}</td>
                                    <td>{{ $a_M }}</td>
                                    <td>{{ $b_M }}</td>
                                    <td>{{ $c_M }}</td>
                                    <td>{{ $d_M }}</td>
                                    <td>{{ $e_M }}</td>
                                    <td>{{ $u_M }}</td>
                                    <td>{{ $mab_M_Percentage }}</td>
                                    <td>{{ $mabc_M_Percentage }}</td>
                                    <td>{{ $mabcd_M_Percentage }}</td>
                                    <td>{{ $maleCount }}</td>
                                </tr>
                                <tr>
                                    <td>PSLE</td>
                                    <td></td>
                                    <td class="highlighted-cell">{{ $psleA_M }}</td>
                                    <td>{{ $psleB_M }}</td>
                                    <td>{{ $psleC_M }}</td>
                                    <td>{{ $psleD_M }}</td>
                                    <td>{{ $psleE_M }}</td>
                                    <td>{{ $psleU_M }}</td>
                                    <td>{{ $psleAB_M_Percentage }}</td>
                                    <td>{{ $psleABC_M_Percentage }}</td>
                                    <td>{{ $psleABCD_M_Percentage }}</td>
                                    <td>{{ $psleTotalM }}</td>
                                </tr>
                                <!-- Female -->
                                <tr>
                                    <td rowspan="2" class="category-header">Female</td>
                                    <td>Output</td>
                                    <td>{{ $m_F }}</td>
                                    <td>{{ $a_F }}</td>
                                    <td>{{ $b_F }}</td>
                                    <td>{{ $c_F }}</td>
                                    <td>{{ $d_F }}</td>
                                    <td>{{ $e_F }}</td>
                                    <td>{{ $u_F }}</td>
                                    <td>{{ $mab_F_Percentage }}</td>
                                    <td>{{ $mabc_F_Percentage }}</td>
                                    <td>{{ $mabcd_F_Percentage }}</td>
                                    <td>{{ $femaleCount }}</td>
                                </tr>
                                <tr>
                                    <td>PSLE</td>
                                    <td></td>
                                    <td class="highlighted-cell">{{ $psleA_F }}</td>
                                    <td>{{ $psleB_F }}</td>
                                    <td>{{ $psleC_F }}</td>
                                    <td>{{ $psleD_F }}</td>
                                    <td>{{ $psleE_F }}</td>
                                    <td>{{ $psleU_F }}</td>
                                    <td>{{ $psleAB_F_Percentage }}</td>
                                    <td>{{ $psleABC_F_Percentage }}</td>
                                    <td>{{ $psleABCD_F_Percentage }}</td>
                                    <td>{{ $psleTotalF }}</td>
                                </tr>
                                <!-- Total -->
                                <tr>
                                    <td rowspan="2" class="total-row">Total</td>
                                    <td class="total-row">Output</td>
                                    <td class="total-row">{{ $sumM }}</td>
                                    <td class="total-row">{{ $sumA }}</td>
                                    <td class="total-row">{{ $sumB }}</td>
                                    <td class="total-row">{{ $sumC }}</td>
                                    <td class="total-row">{{ $sumD }}</td>
                                    <td class="total-row">{{ $sumE }}</td>
                                    <td class="total-row">{{ $sumU }}</td>
                                    <td class="total-row">{{ $mab_T_percentage }}</td>
                                    <td class="total-row">{{ $mabc_T_percentage }}</td>
                                    <td class="total-row">{{ $mabcd_T_percentage }}</td>
                                    <td class="total-row">{{ $totalStudents }}</td>
                                </tr>
                                <tr class="total-row">
                                    <td>PSLE</td>
                                    <td></td>
                                    <td class="highlighted-cell">{{ $psleA_M + $psleA_F }}</td>
                                    <td>{{ $psleB_M + $psleB_F }}</td>
                                    <td>{{ $psleC_M + $psleC_F }}</td>
                                    <td>{{ $psleD_M + $psleD_F }}</td>
                                    <td>{{ $psleE_M + $psleE_F }}</td>
                                    <td>{{ $psleU_M + $psleU_F }}</td>
                                    <td>{{ $psleAB_T_Percentage }}</td>
                                    <td>{{ $psleABC_T_Percentage }}</td>
                                    <td>{{ $psleABCD_T_Percentage }}</td>
                                    <td>{{ $totalPsleStudents }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Breakdown by Student Type -->
                    @if(count($typeGradeCounts) > 0)
                        <div class="student-type-section">
                            <h6>Breakdown by Student Type</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped performance-table">
                                    <thead>
                                        <tr>
                                            <th>Student Type</th>
                                            <th>Merit</th>
                                            <th>A</th>
                                            <th>B</th>
                                            <th>C</th>
                                            <th>D</th>
                                            <th>E</th>
                                            <th>U</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($typeGradeCounts as $typeId => $typeData)
                                            <tr>
                                                <td class="text-start">
                                                    <span class="type-badge" style="background-color: {{ $typeData['color'] }}">
                                                        {{ $typeData['name'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $typeData['grades']['M']['M'] + $typeData['grades']['M']['F'] }}</td>
                                                <td>{{ $typeData['grades']['A']['M'] + $typeData['grades']['A']['F'] }}</td>
                                                <td>{{ $typeData['grades']['B']['M'] + $typeData['grades']['B']['F'] }}</td>
                                                <td>{{ $typeData['grades']['C']['M'] + $typeData['grades']['C']['F'] }}</td>
                                                <td>{{ $typeData['grades']['D']['M'] + $typeData['grades']['D']['F'] }}</td>
                                                <td>{{ $typeData['grades']['E']['M'] + $typeData['grades']['E']['F'] }}</td>
                                                <td>{{ $typeData['grades']['U']['M'] + $typeData['grades']['U']['F'] }}</td>
                                                <td>{{ $typeData['male_count'] }}</td>
                                                <td>{{ $typeData['female_count'] }}</td>
                                                <td><strong>{{ $typeData['total'] }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>

                    <div class="chart-container">
                        <canvas id="comparisonChart"></canvas>
                    </div>

                    @if(count($typeGradeCounts) > 0)
                        <div class="chart-container">
                            <canvas id="typeDistributionChart"></canvas>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No students with special needs (student types) found in this grade for the selected assessment.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        function printContent() {
            window.print();
        }

        @if($totalStudents > 0)
        document.addEventListener('DOMContentLoaded', function() {
            const mabPercentage = parseFloat('{{ $mab_T_percentage }}'.replace('%', ''));
            const mabcPercentage = parseFloat('{{ $mabc_T_percentage }}'.replace('%', ''));
            const mabcdPercentage = parseFloat('{{ $mabcd_T_percentage }}'.replace('%', ''));
            const psleMabPercentage = parseFloat('{{ $psleAB_T_Percentage }}'.replace('%', ''));
            const psleAbcPercentage = parseFloat('{{ $psleABC_T_Percentage }}'.replace('%', ''));

            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: ['MERIT', 'A', 'B', 'C', 'D', 'E', 'U'],
                    datasets: [{
                            label: 'Male',
                            data: [{{ $m_M }}, {{ $a_M }}, {{ $b_M }},
                                {{ $c_M }}, {{ $d_M }}, {{ $e_M }},
                                {{ $u_M }}
                            ],
                            backgroundColor: 'rgba(54,162,235,0.6)',
                            borderColor: 'rgba(54,162,235,1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Female',
                            data: [{{ $m_F }}, {{ $a_F }}, {{ $b_F }},
                                {{ $c_F }}, {{ $d_F }}, {{ $e_F }},
                                {{ $u_F }}
                            ],
                            backgroundColor: 'rgba(255,99,132,0.6)',
                            borderColor: 'rgba(255,99,132,1)',
                            borderWidth: 1
                        },
                        {
                            label: 'MAB%',
                            data: [null, null, null, mabPercentage, mabPercentage, mabPercentage,
                                mabPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(255,206,86,1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'MABC%',
                            data: [null, null, null, mabcPercentage, mabcPercentage, mabcPercentage,
                                mabcPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(75,192,192,1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'MABCD%',
                            data: [null, null, null, null, mabcdPercentage, mabcdPercentage,
                                mabcdPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(153,102,255,1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Special Needs Students - {{ $type }} Performance Distribution by Gender'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Count'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: v => v + '%'
                            },
                            title: {
                                display: true,
                                text: 'Percent'
                            }
                        }
                    }
                }
            });

            // PSLE vs Current Comparison Chart
            const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
            new Chart(comparisonCtx, {
                type: 'bar',
                data: {
                    labels: ['A', 'B', 'C', 'D', 'E', 'U'],
                    datasets: [{
                            label: 'Current',
                            data: [{{ $sumA }}, {{ $sumB }}, {{ $sumC }},
                                {{ $sumD }}, {{ $sumE }}, {{ $sumU }}
                            ],
                            backgroundColor: 'rgba(54,162,235,0.6)',
                            borderColor: 'rgba(54,162,235,1)',
                            borderWidth: 1
                        },
                        {
                            label: 'PSLE',
                            data: [
                                {{ $psleA_M + $psleA_F }},
                                {{ $psleB_M + $psleB_F }},
                                {{ $psleC_M + $psleC_F }},
                                {{ $psleD_M + $psleD_F }},
                                {{ $psleE_M + $psleE_F }},
                                {{ $psleU_M + $psleU_F }}
                            ],
                            backgroundColor: 'rgba(255,159,64,0.6)',
                            borderColor: 'rgba(255,159,64,1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Current MAB%',
                            data: [null, null, mabPercentage, mabPercentage, mabPercentage,
                                mabPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(255,206,86,1)',
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'PSLE MAB%',
                            data: [null, null, psleMabPercentage, psleMabPercentage, psleMabPercentage,
                                psleMabPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(255,159,64,1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Current ABC%',
                            data: [null, null, mabcPercentage, mabcPercentage, mabcPercentage,
                                mabcPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(75,192,192,1)',
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'PSLE ABC%',
                            data: [null, null, psleAbcPercentage, psleAbcPercentage, psleAbcPercentage,
                                psleAbcPercentage
                            ],
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(153,102,255,1)',
                            borderDash: [5, 5],
                            tension: 0.1,
                            pointRadius: 0,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'PSLE vs Current {{ $type }} Comparison - Special Needs Students'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Count'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            max: 100,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: v => v + '%'
                            },
                            title: {
                                display: true,
                                text: 'Percent'
                            }
                        }
                    }
                }
            });

            @if(count($typeGradeCounts) > 0)
            // Student Type Distribution Chart
            const typeLabels = @json(array_column($typeGradeCounts, 'name'));
            const typeColors = @json(array_column($typeGradeCounts, 'color'));
            const typeTotals = @json(array_column($typeGradeCounts, 'total'));

            const typeCtx = document.getElementById('typeDistributionChart').getContext('2d');
            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeTotals,
                        backgroundColor: typeColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribution by Student Type'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            @endif
        });
        @endif
    </script>
@endsection

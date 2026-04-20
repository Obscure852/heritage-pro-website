@extends('layouts.master')
@section('title')
    Teacher by Teacher Analysis Report
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .table {
                width: 100%;
                margin-bottom: 3mm;
                margin-top: 10px;
                page-break-inside: avoid;
                font-size: 13px;
            }

            .table th,
            .table td {
                padding: 1mm;
                white-space: nowrap;
            }

            .table-sm td,
            .table-sm th {
                padding: 0.5mm 1mm;
            }
        .percent-high {
            background-color: #d5f5d5;
        }

        .percent-medium {
            background-color: #ffffd5;
        }

        .percent-low {
            background-color: #ffd5d5;
        }
        
        .house-tooltip {
            position: relative;
            cursor: pointer;
        }
        
        .house-tooltip .tooltip-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
            z-index: 100;
            width: 150px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
        }
        
        .house-tooltip:hover .tooltip-content {
            display: block;
        }
        
        .section-divider {
            margin-top: 30px;
            margin-bottom: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .chart-container {
            height: 350px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
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

            .graph-container {
                page-break-before: always;
            }
            
            .house-tooltip .tooltip-content {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
        <a href="#" onclick="event.preventDefault(); 
            if (document.referrer) {
            history.back();
            } else {
            window.location = '{{ $gradebookBackUrl }}';
            }
     ">Back</a>
     
        @endslot
        @slot('title')
            Class & House Analysis {{ ucfirst($type) }}
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-download"></i>
            </a>

            <a href="#" onclick="printContent()" class="me-2 text-muted">
                <i style="font-size: 20px;" class="bx bx-printer me-1"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div style="font-size:14px;" class="col-md-6">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $school_data->school_name }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address }}</p>
                                <p class="mb-0">{{ $school_data->postal_address }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-start mb-3">Class & House Analysis {{ ucfirst($type) }} Term {{ $currentTerm->term }}, {{ $currentTerm->year }} </h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: left">Teacher</th>
                                    <th style="text-align: left">Class</th>
                                    <th>Gender</th>
                                    <th>House</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th>D</th>
                                    <th>E</th>
                                    <th>U</th>
                                    <th>Total</th>
                                    <th>Grand Total</th>
                                    <th>A-B%</th>
                                    <th>A-C%</th>
                                    <th>A-D%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $index => $class)
                                    <tr @if ($index % 2 == 0) class="table-light" @endif>
                                        <td style="text-align: left" rowspan="2">{{ $class['teacher'] }}</td>
                                        <td style="text-align: left" rowspan="2">{{ $class['class'] }}</td>
                                        <td>Male</td>
                                        <td rowspan="2" class="house-tooltip">
                                            {{ $class['house'] }}
                                            @if (!empty($class['house_distribution']))
                                                <div class="tooltip-content">
                                                    <strong>House Distribution:</strong>
                                                    <ul style="padding-left: 15px; margin-bottom: 0;">
                                                        @foreach($class['house_distribution'] as $houseName => $count)
                                                            <li>{{ $houseName }}: {{ $count }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $class['grades']['A']['M'] }}</td>
                                        <td>{{ $class['grades']['B']['M'] }}</td>
                                        <td>{{ $class['grades']['C']['M'] }}</td>
                                        <td>{{ $class['grades']['D']['M'] }}</td>
                                        <td>{{ $class['grades']['E']['M'] }}</td>
                                        <td>{{ $class['grades']['U']['M'] }}</td>
                                        <td>{{ $class['male_count'] }}</td>
                                        <td rowspan="2">{{ $class['total'] }}</td>
                                        <td rowspan="2"
                                            class="{{ $class['a_b_percentage'] >= 40 ? 'percent-high' : ($class['a_b_percentage'] >= 25 ? 'percent-medium' : 'percent-low') }}">
                                            {{ $class['a_b_percentage'] }}%
                                        </td>
                                        <td rowspan="2"
                                            class="{{ $class['a_c_percentage'] >= 50 ? 'percent-high' : ($class['a_c_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                            {{ $class['a_c_percentage'] }}%
                                        </td>
                                        <td rowspan="2"
                                            class="{{ $class['a_d_percentage'] >= 70 ? 'percent-high' : ($class['a_d_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                            {{ $class['a_d_percentage'] }}%
                                        </td>
                                    </tr>
                                    <tr @if ($index % 2 == 0) class="table-light" @endif>
                                        <td>Female</td>
                                        <td>{{ $class['grades']['A']['F'] }}</td>
                                        <td>{{ $class['grades']['B']['F'] }}</td>
                                        <td>{{ $class['grades']['C']['F'] }}</td>
                                        <td>{{ $class['grades']['D']['F'] }}</td>
                                        <td>{{ $class['grades']['E']['F'] }}</td>
                                        <td>{{ $class['grades']['U']['F'] }}</td>
                                        <td>{{ $class['female_count'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-warning">
                                    <td colspan="3" style="text-align: right"><strong>Total</strong></td>
                                    <td></td>
                                    <td>{{ $totalGrades['A'] }}</td>
                                    <td>{{ $totalGrades['B'] }}</td>
                                    <td>{{ $totalGrades['C'] }}</td>
                                    <td>{{ $totalGrades['D'] }}</td>
                                    <td>{{ $totalGrades['E'] }}</td>
                                    <td>{{ $totalGrades['U'] }}</td>
                                    <td>{{ $totalStudents }}</td>
                                    <td>{{ $totalStudents }}</td>
                                    <td
                                        class="{{ $overallABPercentage >= 40 ? 'percent-high' : ($overallABPercentage >= 25 ? 'percent-medium' : 'percent-low') }}">
                                        {{ $overallABPercentage }}%
                                    </td>
                                    <td
                                        class="{{ $overallABCPercentage >= 50 ? 'percent-high' : ($overallABCPercentage >= 30 ? 'percent-medium' : 'percent-low') }}">
                                        {{ $overallABCPercentage }}%
                                    </td>
                                    <td
                                        class="{{ $overallADPercentage >= 70 ? 'percent-high' : ($overallADPercentage >= 50 ? 'percent-medium' : 'percent-low') }}">
                                        {{ $overallADPercentage }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Performance Visualizations -->
                    <div class="section-divider"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-center">Grade Distribution by Class</h6>
                            <div class="chart-container">
                                <canvas id="gradeDistributionChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center">Performance Metrics by Class</h6>
                            <div class="chart-container">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const rawClasses = @json(array_column($classes, 'class'));
        const classData = @json($classes);

        // Helper to build a vertical gradient
        function buildGradient(ctx, colorStart, colorEnd) {
            const grad = ctx.createLinearGradient(0, 0, 0, 350);
            grad.addColorStop(0, colorStart);
            grad.addColorStop(1, colorEnd);
            return grad;
        }

        // —— Grade Distribution Chart —— 
        const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        const gradeColors = {
            A: ['rgba(52,152,219,0.8)', 'rgba(52,152,219,0.3)'],
            B: ['rgba(46,204,113,0.8)', 'rgba(46,204,113,0.3)'],
            C: ['rgba(155,89,182,0.8)', 'rgba(155,89,182,0.3)'],
            D: ['rgba(241,196,15,0.8)', 'rgba(241,196,15,0.3)'],
            E: ['rgba(230,126,34,0.8)', 'rgba(230,126,34,0.3)'],
            U: ['rgba(231,76,60,0.8)', 'rgba(231,76,60,0.3)'],
        };

        const gradeDatasets = Object.entries(gradeColors).map(([grade, [start, end]]) => ({
            label: grade,
            data: classData.map(c => c.grades[grade].M + c.grades[grade].F),
            backgroundColor: buildGradient(gradeCtx, start, end),
            borderRadius: 4,
            barPercentage: 0.6,
            categoryPercentage: 0.8,
        }));

        new Chart(gradeCtx, {
            type: 'bar',
            data: {
                labels: rawClasses,
                datasets: gradeDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 10, bottom: 10, left: 10, right: 10 }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 16,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: { font: { size: 11 }, maxRotation: 0, autoSkip: false }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'Students', font: { size: 12 } },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });

        // —— Performance Metrics Chart —— 
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        const perfLabels = rawClasses;
        const perfValues = {
            'A-B %': classData.map(c => c.a_b_percentage),
            'A-C %': classData.map(c => c.a_c_percentage),
            'A-D %': classData.map(c => c.a_d_percentage)
        };

        const perfDatasets = Object.entries(perfValues).map(([label, data], i) => {
            const colors = Object.values(gradeColors)[i];
            return {
                label,
                data,
                borderColor: colors[0],
                backgroundColor: colors[0].replace(/0\.8/, '0.3'),
                fill: false,
                tension: 0.4,            // smooth curves
                pointRadius: 5,
                borderWidth: 2
            };
        });

        new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: perfLabels,
                datasets: perfDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 }, padding: 12 }
                    },
                    tooltip: {
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}%`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { font: { size: 11 }, maxRotation: 0, autoSkip: false }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Percentage (%)', font: { size: 12 } },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    });
    </script>
@endsection

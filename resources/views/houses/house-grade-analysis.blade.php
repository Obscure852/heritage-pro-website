@extends('layouts.master')
@section('title')
    Grade Credits by House Analysis
@endsection
@section('css')
    <style>
        .house-info {
            font-size: 0.9em;
            color: #555;
        }

        .table-summary {
            margin-bottom: 30px;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        @media print {
            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
            }

            .table th,
            .table td {
                border: 1px solid #ddd;
                padding: 4px;
            }

            .table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .total-row {
                background-color: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .house-info {
                font-size: 0.8em;
            }

            h4 {
                font-size: 14pt;
                margin-bottom: 10px;
            }

            .card-title-desc {
                font-size: 10pt;
                margin-bottom: 20px;
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
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Grade Credits by House Analysis
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
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
                    @if ($test->type == 'CA')
                        <h6>End of {{ $test->name ?? 'Month' }} House Credits Performance Analysis</h6>
                    @else
                        <h6>End of Term House Credits Performance Analysis</h6>
                    @endif

                    <p class="card-title-desc">
                        Overview of credits and JCE grades for each house
                    </p>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">House Name</th>
                                    <th rowspan="2">Total Students</th>
                                    <th rowspan="2">Class Name</th>
                                    <th rowspan="2">No. of Students</th>
                                    <th style="text-align: center;" colspan="3">>=6 Credits</th>
                                    <th rowspan="2">% with >= 6 Credits</th>
                                    <th rowspan="2">Students with >=6 JCE Credits</th>
                                    <th rowspan="2">% with >=6 JCE Credits</th>
                                    <th rowspan="2">Value Addition</th>
                                </tr>
                                <tr>
                                    <th>M</th>
                                    <th>F</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedData as $data)
                                    @php
                                        $classCount = $data['classes']->count();
                                        $rowspan = $classCount + 1;
                                        $firstRow = true;
                                    @endphp

                                    @if ($classCount > 0)
                                        @foreach ($data['classes'] as $class)
                                            <tr>
                                                @if ($firstRow)
                                                    <td rowspan="{{ $rowspan }}">
                                                        {{ $data['house']->name }}
                                                        <div class="house-info">
                                                            <strong>Head:</strong>
                                                            {{ $data['house']->houseHead->fullName ?? 'Not assigned' }}<br>
                                                            <strong>Assistant:</strong>
                                                            {{ $data['house']->houseAssistant->fullName ?? 'Not assigned' }}
                                                        </div>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" class="text-center">
                                                        {{ $data['total_students'] }}</td>
                                                    @php $firstRow = false; @endphp
                                                @endif
                                                <td>{{ $class['name'] }}
                                                </td>
                                                <td class="text-center">{{ $class['count'] }}</td>
                                                <td class="text-center">{{ $class['male_with_more_than_6_credits'] }}</td>
                                                <td class="text-center">{{ $class['female_with_more_than_6_credits'] }}
                                                </td>
                                                <td class="text-center">{{ $class['students_with_more_than_6_credits'] }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $class['percentage_with_more_than_6_credits'] }}%</td>
                                                <td class="text-center">
                                                    {{ $class['students_with_more_than_6_jce_credits'] }}</td>
                                                <td class="text-center">{{ $class['percentage_of_jce_grades'] }}%</td>
                                                <td class="text-center">{{ $class['value_addition'] }}%</td>
                                            </tr>
                                        @endforeach
                                        <!-- House Total Row -->
                                        <tr class="total-row">
                                            <td colspan="3">Total for {{ $data['house']->name }}</td>
                                            <td class="text-center">{{ $data['male_with_more_than_6_credits'] }}</td>
                                            <td class="text-center">{{ $data['female_with_more_than_6_credits'] }}</td>
                                            <td class="text-center">{{ $data['students_with_more_than_6_credits'] }}</td>
                                            <td class="text-center">{{ $data['percentage_with_more_than_6_credits'] }}%
                                            </td>
                                            <td class="text-center">{{ $data['students_with_more_than_6_jce_credits'] }}
                                            </td>
                                            <td class="text-center">{{ $data['percentage_of_jce_grades'] }}%</td>
                                            <td class="text-center">{{ $data['value_addition'] }}%</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>
                                                {{ $data['house']->name }}
                                                <div class="house-info">
                                                    <strong>Head:</strong>
                                                    {{ $data['house']->houseHead->fullName ?? 'Not assigned' }}<br>
                                                    <strong>Assistant:</strong>
                                                    {{ $data['house']->houseAssistant->fullName ?? 'Not assigned' }}
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $data['total_students'] }}</td>
                                            <td colspan="9">No classes assigned</td>
                                        </tr>
                                    @endif
                                @endforeach
                                <!-- Overall Total Row -->
                                <tr class="total-row">
                                    <td colspan="3">Overall Total</td>
                                    <td class="text-center">{{ $overallMaleWithMoreThan6Credits }}</td>
                                    <td class="text-center">{{ $overallFemaleWithMoreThan6Credits }}</td>
                                    <td class="text-center">{{ $overallStudentsWithMoreThan6Credits }}</td>
                                    <td class="text-center">{{ $overallPercentageWithMoreThan6Credits }}%</td>
                                    <td class="text-center">{{ $overallStudentsWithMoreThan6JceCredits }}</td>
                                    <td class="text-center">{{ $overallPercentageOfJceGrades }}%</td>
                                    <td class="text-center">{{ $overallValueAddition }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4 no-print">
                        <div class="col-12">
                            <h4 class="card-title">House Grade Analysis Chart</h4>
                            <div class="chart-container">
                                <canvas id="houseGradeChart"></canvas>
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
        $(document).ready(function() {
            var ctx = document.getElementById('houseGradeChart').getContext('2d');
            var chartData = {
                labels: [
                    @foreach ($groupedData as $data)
                        '{{ $data['house']->name }}',
                    @endforeach
                ],
                datasets: [{
                        label: '% with >= 6 Credits',
                        data: [
                            @foreach ($groupedData as $data)
                                {{ $data['percentage_with_more_than_6_credits'] }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: '% with >=6 JCE Credits',
                        data: [
                            @foreach ($groupedData as $data)
                                {{ $data['percentage_of_jce_grades'] }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Value Addition',
                        data: [
                            @foreach ($groupedData as $data)
                                {{ $data['value_addition'] }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            };

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'House Grade Analysis Comparison'
                        }
                    }
                }
            });
        });


        function printContent() {
            window.print();
        }
    </script>
@endsection

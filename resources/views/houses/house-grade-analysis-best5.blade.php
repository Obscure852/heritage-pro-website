@extends('layouts.master')

@section('title', 'Grade Credits by House Analysis')

@section('css')
    <style>
        .house-info {
            font-size: .9em;
            color: #555
        }

        .table-summary {
            margin-bottom: 30px
        }

        .text-center {
            text-align: center
        }

        .total-row {
            font-weight: 700;
            background-color: #f9f9f9
        }

        @media print {
            body {
                font-size: 10pt
            }

            .no-print {
                display: none !important
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt
            }

            .table th,
            .table td {
                border: 1px solid #ddd;
                padding: 4px
            }

            .table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact
            }

            .total-row {
                background-color: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact
            }

            .house-info {
                font-size: .8em
            }

            h4 {
                font-size: 14pt;
                margin-bottom: 10px
            }

            .card-title-desc {
                font-size: 10pt;
                margin-bottom: 20px
            }

            @page {
                size: landscape;
                margin: .5cm
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
            Grade Credits by House Analysis — Best 5
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" title="Download Excel">
                <i class="bx bx-download text-muted me-2" style="font-size:20px;"></i>
            </a>
            <i onclick="printContent()" class="bx bx-printer text-muted"
                style="font-size:20px;margin-bottom:10px;cursor:pointer;" title="Print"></i>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-start">
                        <div class="col-6">
                            <div class="form-group" style="font-size:14px;">
                                <strong>{{ $school_data->school_name }}</strong><br>
                                <span>{{ $school_data->physical_address }}</span><br>
                                <span>{{ $school_data->postal_address }}</span><br>
                                <span>Tel: {{ $school_data->telephone }}
                                    {{ $school_data->fax ? ' Fax: ' . $school_data->fax : '' }}</span>
                            </div>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($test && $test->type == 'CA')
                        <h6>End of {{ $test->name ?? 'Month' }} — House Credits Performance Analysis (Best 5)</h6>
                    @else
                        <h6>End of Term — House Credits Performance Analysis (Best 5)</h6>
                    @endif

                    <p class="card-title-desc">Overview of internal credits (Best 5) vs JCE credits, per house and class.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th rowspan="2" scope="col">House Name</th>
                                    <th rowspan="2" scope="col">Total Students</th>
                                    <th rowspan="2" scope="col">Class Name</th>
                                    <th rowspan="2" scope="col">No. of Students</th>
                                    <th colspan="3" class="text-center" scope="colgroup">&ge; 5 Credits</th>
                                    <th rowspan="2" scope="col">% with &ge; 5 Credits</th>
                                    <th rowspan="2" scope="col">Students with &ge; 5 JCE Credits</th>
                                    <th rowspan="2" scope="col">% with &ge; 5 JCE Credits</th>
                                    <th rowspan="2" scope="col">Value Addition</th>
                                </tr>
                                <tr>
                                    <th scope="col">M</th>
                                    <th scope="col">F</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedData as $data)
                                    @php
                                        $classCount = $data['classes']->count();
                                        $rowspan = $classCount + 1; // + totals row
                                        $firstRow = true;
                                    @endphp

                                    @if ($classCount > 0)
                                        @foreach ($data['classes'] as $class)
                                            <tr>
                                                @if ($firstRow)
                                                    {{-- These two cells span all class rows + the house total row --}}
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

                                                <td>{{ $class['name'] }}</td>
                                                <td class="text-center">{{ $class['count'] }}</td>
                                                <td class="text-center">{{ $class['male_with_more_than_5_credits'] }}</td>
                                                <td class="text-center">{{ $class['female_with_more_than_5_credits'] }}
                                                </td>
                                                <td class="text-center">{{ $class['students_with_more_than_5_credits'] }}
                                                </td>
                                                <td class="text-center">
                                                    {{ number_format($class['percentage_with_more_than_5_credits'], 2) }}%
                                                </td>
                                                <td class="text-center">
                                                    {{ $class['students_with_more_than_5_jce_credits'] }}</td>
                                                <td class="text-center">
                                                    {{ number_format($class['percentage_of_jce_grades'], 2) }}%</td>
                                                <td class="text-center">{{ number_format($class['value_addition'], 2) }}%
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- House Total Row --}}
                                        <tr class="total-row">
                                            {{-- Reminder: first 2 columns are occupied by the rowspans above --}}
                                            <td colspan="2">Total for {{ $data['house']->name }}</td>
                                            {{-- covers Class Name + No. of Students --}}
                                            <td class="text-center">{{ $data['male_with_more_than_5_credits'] }}</td>
                                            <td class="text-center">{{ $data['female_with_more_than_5_credits'] }}</td>
                                            <td class="text-center">{{ $data['students_with_more_than_5_credits'] }}</td>
                                            <td class="text-center">
                                                {{ number_format($data['percentage_with_more_than_5_credits'], 2) }}%</td>
                                            <td class="text-center">{{ $data['students_with_more_than_5_jce_credits'] }}
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($data['percentage_of_jce_grades'], 2) }}%</td>
                                            <td class="text-center">{{ number_format($data['value_addition'], 2) }}%</td>
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

                                {{-- Overall Total Row --}}
                                <tr class="total-row">
                                    <td colspan="4">Overall Total</td> {{-- covers House Name + Total Students + Class Name + No. of Students --}}
                                    <td class="text-center">{{ $overallMaleWithMoreThan5Credits }}</td>
                                    <td class="text-center">{{ $overallFemaleWithMoreThan5Credits }}</td>
                                    <td class="text-center">{{ $overallStudentsWithMoreThan5Credits }}</td>
                                    <td class="text-center">{{ number_format($overallPercentageWithMoreThan5Credits, 2) }}%
                                    </td>
                                    <td class="text-center">{{ $overallStudentsWithMoreThan5JceCredits }}</td>
                                    <td class="text-center">{{ number_format($overallPercentageOfJceGrades, 2) }}%</td>
                                    <td class="text-center">{{ number_format($overallValueAddition, 2) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4 no-print">
                        <div class="col-12">
                            <h4 class="card-title">House Grade Analysis Chart (Best 5)</h4>
                            <div class="chart-container">
                                <canvas id="houseGradeChart" aria-label="House Grade Analysis Chart"
                                    role="img"></canvas>
                            </div>
                        </div>
                    </div>
                </div> {{-- card-body --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            const ctx = document.getElementById('houseGradeChart')?.getContext('2d');
            if (!ctx || typeof Chart === 'undefined') return;

            const labels = [
                @foreach ($groupedData as $data)
                    @if ($data['house']?->name)
                        '{{ $data['house']->name }}',
                    @endif
                @endforeach
            ];

            const pctInternal = [
                @foreach ($groupedData as $data)
                    {{ round($data['percentage_with_more_than_5_credits'], 2) }},
                @endforeach
            ];

            const pctJce = [
                @foreach ($groupedData as $data)
                    {{ round($data['percentage_of_jce_grades'], 2) }},
                @endforeach
            ];

            const valueAdd = [
                @foreach ($groupedData as $data)
                    {{ round($data['value_addition'], 2) }},
                @endforeach
            ];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                            label: '% with ≥ 5 Credits',
                            data: pctInternal,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '% with ≥ 5 JCE Credits',
                            data: pctJce,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Value Addition',
                            data: valueAdd,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'House Grade Analysis Comparison — Best 5'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed.y;
                                    return `${ctx.dataset.label}: ${typeof v === 'number' ? v.toFixed(2) : v}%`;
                                }
                            }
                        }
                    }
                }
            });
        })();

        function printContent() {
            window.print();
        }
    </script>
@endsection

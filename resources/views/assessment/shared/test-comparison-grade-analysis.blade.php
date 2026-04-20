@extends('layouts.master')
@section('title')
    Subject Analysis
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
            Subjects Comparison Analysis
        @endslot
    @endcomponent

    <style>
        body {
            font-size: 12px;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        @media print {

            html,
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                font-size: 10px;
                line-height: normal;
                overflow: visible;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: static;
                width: 100% !important;
                margin: 0;
                padding: 0;
                page-break-after: avoid;
            }

            .card-header img {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }

            .card-body {
                width: 100% !important;
                margin: 0;
                padding: 0;
            }

            h5 {
                margin-top: 10px;
                margin-bottom: 5px;
            }

            .table-responsive {
                width: 100% !important;
                margin: 0;
                padding: 0;
                page-break-inside: avoid;
            }

            .table,
            .table th,
            .table td {
                border-collapse: collapse;
                overflow: visible;
                word-wrap: break-word;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000 !important;
            }

            textarea {
                border: none;
            }
        }
    </style>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <i onclick="window.location.href='{{ request()->fullUrlWithQuery(['export' => 'true']) }}'"
                class="bx bx-download me-2 text-muted" style="font-size:20px; cursor:pointer;"></i>
            <i onclick="printContent()" class="bx bx-printer text-muted" style="font-size:20px; cursor:pointer;"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6" style="font-size:14px;">
                            <strong>{{ $school_data->school_name }}</strong><br>
                            {{ $school_data->physical_address }}<br>
                            {{ $school_data->postal_address }}<br>
                            Tel: {{ $school_data->telephone }} | Fax: {{ $school_data->fax }}
                        </div>
                        <div class="col-md-6 text-end">
                            <img src="{{ URL::asset($school_data->logo_path) }}" height="80" alt="School Logo">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h6>{{ $grade->name }}
                        {{ strtolower($test->type) === 'exam' ? 'End of Term' : 'End of ' . $test->name }}
                        Subjects Comparison Analysis - Term {{ $term->term ?? '' }}, {{ $term->year ?? '' }}</h6>

                    {{-- Per-Subject Comparison --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    @foreach ($subjects as $subject)
                                        <th colspan="2" class="text-center">{{ $subject }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th></th>
                                    @foreach ($subjects as $subject)
                                        <th class="text-center">Prev</th>
                                        <th class="text-center">Curr</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                    <tr>
                                        <td>{{ $grade }}</td>
                                        @foreach ($subjects as $subject)
                                            <td class="text-center">{{ $gradeCounts[$subject]['prev'][$grade] ?? 0 }}
                                            </td>
                                            <td class="text-center">{{ $gradeCounts[$subject]['curr'][$grade] ?? 0 }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Quality</td>
                                    @foreach ($subjects as $subject)
                                        <td class="text-center">{{ $gradeCounts[$subject]['qualityPrev'] }}%</td>
                                        <td class="text-center">{{ $gradeCounts[$subject]['qualityCurr'] }}%</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Quantity</td>
                                    @foreach ($subjects as $subject)
                                        <td class="text-center">{{ $gradeCounts[$subject]['quantityPrev'] }}%</td>
                                        <td class="text-center">{{ $gradeCounts[$subject]['quantityCurr'] }}%</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Value Addition</td>
                                    @foreach ($subjects as $subject)
                                        <td colspan="2" class="text-center">
                                            {{ $gradeCounts[$subject]['valueAddition'] }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Rank</td>
                                    @foreach ($subjects as $subject)
                                        <td colspan="2" class="text-center">
                                            @php
                                                $rank = array_search($subject, $rankedSubjects);
                                            @endphp
                                            {{ $rank !== false ? $rank + 1 : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Previous Test Overall Distribution --}}
                    <h5>Previous Test Overall Grade Distribution</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                        <th class="text-center">{{ $grade }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                    <th class="text-center">AB%</th>
                                    <th class="text-center">ABC%</th>
                                    <th class="text-center">DEU%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                        <td class="text-center">{{ $prevGradeCounts[$grade] ?? 0 }}</td>
                                    @endforeach
                                    @php
                                        $totalPrev = array_sum($prevGradeCounts);
                                        $prevAB =
                                            ((($prevGradeCounts['A'] ?? 0) + ($prevGradeCounts['B'] ?? 0)) /
                                                max($totalPrev, 1)) *
                                            100;
                                        $prevABC =
                                            ((($prevGradeCounts['A'] ?? 0) +
                                                ($prevGradeCounts['B'] ?? 0) +
                                                ($prevGradeCounts['C'] ?? 0)) /
                                                max($totalPrev, 1)) *
                                            100;
                                        $prevDEU =
                                            ((($prevGradeCounts['D'] ?? 0) +
                                                ($prevGradeCounts['E'] ?? 0) +
                                                ($prevGradeCounts['U'] ?? 0)) /
                                                max($totalPrev, 1)) *
                                            100;
                                    @endphp
                                    <td class="text-center">{{ $totalPrev }}</td>
                                    <td class="text-center">{{ round($prevAB, 2) }}%</td>
                                    <td class="text-center">{{ round($prevABC, 2) }}%</td>
                                    <td class="text-center">{{ round($prevDEU, 2) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Current Test Overall Distribution --}}
                    <h5>Current Test Overall Grade Distribution</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                        <th class="text-center">{{ $grade }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                    <th class="text-center">AB%</th>
                                    <th class="text-center">ABC%</th>
                                    <th class="text-center">DEU%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                        <td class="text-center">{{ $currGradeCounts[$grade] ?? 0 }}</td>
                                    @endforeach
                                    @php
                                        $totalCurr = array_sum($currGradeCounts);
                                        $currAB =
                                            ((($currGradeCounts['A'] ?? 0) + ($currGradeCounts['B'] ?? 0)) /
                                                max($totalCurr, 1)) *
                                            100;
                                        $currABC =
                                            ((($currGradeCounts['A'] ?? 0) +
                                                ($currGradeCounts['B'] ?? 0) +
                                                ($currGradeCounts['C'] ?? 0)) /
                                                max($totalCurr, 1)) *
                                            100;
                                        $currDEU =
                                            ((($currGradeCounts['D'] ?? 0) +
                                                ($currGradeCounts['E'] ?? 0) +
                                                ($currGradeCounts['U'] ?? 0)) /
                                                max($totalCurr, 1)) *
                                            100;
                                    @endphp
                                    <td class="text-center">{{ $totalCurr }}</td>
                                    <td class="text-center">{{ round($currAB, 2) }}%</td>
                                    <td class="text-center">{{ round($currABC, 2) }}%</td>
                                    <td class="text-center">{{ round($currDEU, 2) }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary --}}
                    <h5>Comparison Value Addition Summary</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <td>Overall Value Addition:</td>
                                <td>{{ round($valueAdditions['overall'], 2) }}%</td>
                            </tr>
                        </table>
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
    </script>
@endsection

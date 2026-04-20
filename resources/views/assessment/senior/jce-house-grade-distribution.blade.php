@extends('layouts.master')
@section('title')
    JCE House Grade Distribution
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .house-total-row {
            background-color: #d4edda !important;
            font-weight: bold;
        }

        .school-total-row {
            background-color: #cce5ff !important;
            font-weight: bold;
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
                page-break-inside: avoid;
            }

            .table {
                font-size: 9pt;
            }

            .house-total-row {
                background-color: #d4edda !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .school-total-row {
                background-color: #cce5ff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
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
            JCE House Grade Distribution
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
            <div class="card mb-4">
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
                    <h6 class="text-center mb-3">{{ $year }} {{ $gradeName }} JC GRADES DISTRIBUTION TABLE</h6>

                    @if (collect($houseData)->sum(fn($h) => $h['totals']['total']) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name of House</th>
                                        <th>Class</th>
                                        @foreach ($gradeCategories as $grade)
                                            <th>{{ $grade }}</th>
                                        @endforeach
                                        <th>Total</th>
                                        <th>%[A]</th>
                                        <th>%[A-B]</th>
                                        <th>%[A-C]</th>
                                        <th>Class Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($houseData as $houseName => $data)
                                        @php
                                            $classCount = count($data['classes']);
                                            $rowSpan = $classCount + 1; // classes + house total row
                                        @endphp

                                        @if ($classCount > 0)
                                            @foreach ($data['classes'] as $className => $classStats)
                                                <tr>
                                                    @if ($loop->first)
                                                        <td rowspan="{{ $rowSpan }}" style="vertical-align: middle; font-weight: bold;">
                                                            {{ strtoupper($houseName) }}
                                                        </td>
                                                    @endif
                                                    <td>{{ $className }}</td>
                                                    @foreach ($gradeCategories as $grade)
                                                        <td>{{ $classStats['grades'][$grade] }}</td>
                                                    @endforeach
                                                    <td>{{ $classStats['total'] }}</td>
                                                    @php
                                                        $classTotal = $classStats['total'];
                                                        $merit = $classStats['grades']['Merit'];
                                                        $a = $classStats['grades']['A'];
                                                        $b = $classStats['grades']['B'];
                                                        $c = $classStats['grades']['C'];

                                                        $pctA = $classTotal > 0 ? round(($merit + $a) / $classTotal * 100, 2) : 0;
                                                        $pctAB = $classTotal > 0 ? round(($merit + $a + $b) / $classTotal * 100, 2) : 0;
                                                        $pctAC = $classTotal > 0 ? round(($merit + $a + $b + $c) / $classTotal * 100, 2) : 0;
                                                    @endphp
                                                    <td>{{ $pctA }}</td>
                                                    <td>{{ $pctAB }}</td>
                                                    <td>{{ $pctAC }}</td>
                                                    <td>{{ $classTotal }}</td>
                                                </tr>
                                            @endforeach

                                            {{-- House Total Row --}}
                                            <tr class="house-total-row">
                                                <td>Total</td>
                                                @foreach ($gradeCategories as $grade)
                                                    <td>{{ $data['totals']['grades'][$grade] }}</td>
                                                @endforeach
                                                <td>{{ $data['totals']['total'] }}</td>
                                                @php
                                                    $houseTotal = $data['totals']['total'];
                                                    $hMerit = $data['totals']['grades']['Merit'];
                                                    $hA = $data['totals']['grades']['A'];
                                                    $hB = $data['totals']['grades']['B'];
                                                    $hC = $data['totals']['grades']['C'];

                                                    $hPctA = $houseTotal > 0 ? round(($hMerit + $hA) / $houseTotal * 100, 2) : 0;
                                                    $hPctAB = $houseTotal > 0 ? round(($hMerit + $hA + $hB) / $houseTotal * 100, 2) : 0;
                                                    $hPctAC = $houseTotal > 0 ? round(($hMerit + $hA + $hB + $hC) / $houseTotal * 100, 2) : 0;
                                                @endphp
                                                <td>{{ $hPctA }}</td>
                                                <td>{{ $hPctAB }}</td>
                                                <td>{{ $hPctAC }}</td>
                                                <td>{{ $houseTotal }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td style="vertical-align: middle; font-weight: bold;">
                                                    {{ strtoupper($houseName) }}
                                                </td>
                                                <td colspan="{{ count($gradeCategories) + 5 }}" class="text-muted">
                                                    No students in this grade
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    {{-- School Total Row --}}
                                    <tr class="school-total-row">
                                        <td colspan="2"><strong>SCHOOL Total</strong></td>
                                        @foreach ($gradeCategories as $grade)
                                            <td>{{ $schoolTotals['grades'][$grade] }}</td>
                                        @endforeach
                                        <td>{{ $schoolTotals['total'] }}</td>
                                        @php
                                            $sTotal = $schoolTotals['total'];
                                            $sMerit = $schoolTotals['grades']['Merit'];
                                            $sA = $schoolTotals['grades']['A'];
                                            $sB = $schoolTotals['grades']['B'];
                                            $sC = $schoolTotals['grades']['C'];

                                            $sPctA = $sTotal > 0 ? round(($sMerit + $sA) / $sTotal * 100, 2) : 0;
                                            $sPctAB = $sTotal > 0 ? round(($sMerit + $sA + $sB) / $sTotal * 100, 2) : 0;
                                            $sPctAC = $sTotal > 0 ? round(($sMerit + $sA + $sB + $sC) / $sTotal * 100, 2) : 0;
                                        @endphp
                                        <td>{{ $sPctA }}</td>
                                        <td>{{ $sPctAB }}</td>
                                        <td>{{ $sPctAC }}</td>
                                        <td>{{ $sTotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">No JCE grade data available for this grade.</div>
                    @endif
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

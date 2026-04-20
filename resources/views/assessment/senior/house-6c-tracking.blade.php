@extends('layouts.master')
@section('title')
    House 6C's Tracking Report
@endsection

@section('css')
    <style>
        .tracking-table {
            font-size: 11px;
            white-space: nowrap;
        }

        .tracking-table th,
        .tracking-table td {
            padding: 4px 6px !important;
            text-align: center;
            vertical-align: middle;
        }

        .tracking-table .house-label {
            text-align: left;
            font-weight: 700;
            background: #f0f0f0;
        }

        .tracking-table .class-label {
            text-align: left;
        }

        .tracking-table .total-row td {
            font-weight: 700;
            background: #f0f0f0 !important;
        }

        .tracking-table .grand-total-row td {
            font-weight: 700;
            background: #f0f0f0 !important;
        }

        .period-header {
            background: #f0f0f0 !important;
            font-weight: 600;
        }

        .sub-header {
            background: #f0f0f0 !important;
            font-weight: 600;
            font-size: 10px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10px;
            }

            .no-print {
                display: none !important;
            }

            .tracking-table {
                font-size: 9px;
            }

            .tracking-table th,
            .tracking-table td {
                padding: 2px 4px !important;
            }

            .tracking-table .total-row td,
            .tracking-table .grand-total-row td,
            .period-header,
            .sub-header,
            .house-label {
                background: #f0f0f0 !important;
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
            House 6C's Tracking Report
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a class="text-muted" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px; margin-bottom:10px; margin-right:10px; cursor:pointer;"
                    class="bx bx-download text-muted me-2" title="Export to Excel"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted" title="Print"></i>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {{-- School header --}}
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
                    <h5>{{ $school_data->school_name ?? '' }} - YEAR {{ $startYear }} - {{ $gradeName }} ANALYSIS</h5>
                    <p class="text-muted mb-0">
                        <strong>Number (No.) and Percentage (%) of Students With 6C's or Better</strong>
                    </p>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered tracking-table mb-0">
                            <thead>
                                {{-- Row 1: Test period labels spanning their column groups --}}
                                <tr>
                                    <th class="period-header" rowspan="2">HOUSE</th>
                                    @foreach ($testPeriods as $tp)
                                        <th class="period-header" colspan="7">{{ $tp['label'] }}</th>
                                    @endforeach
                                </tr>
                                {{-- Row 2: Sub-headers for each test period --}}
                                <tr>
                                    @foreach ($testPeriods as $tp)
                                        <th class="sub-header">CLASS</th>
                                        <th class="sub-header">Size</th>
                                        <th class="sub-header">No.Sat</th>
                                        <th class="sub-header">No.</th>
                                        <th class="sub-header">%</th>
                                        <th class="sub-header">JCE%</th>
                                        <th class="sub-header">VA%</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($housesData as $houseName => $periodData)
                                    @php
                                        // Determine max rows (classes) across all test periods for this house
                                        $maxClasses = 0;
                                        foreach ($testPeriods as $tpIdx => $tp) {
                                            $classCount = isset($periodData[$tpIdx]) ? count($periodData[$tpIdx]['classes']) : 0;
                                            $maxClasses = max($maxClasses, $classCount);
                                        }
                                        if ($maxClasses === 0) $maxClasses = 1;
                                    @endphp

                                    {{-- Data rows for each class --}}
                                    @for ($row = 0; $row < $maxClasses; $row++)
                                        <tr>
                                            @if ($row === 0)
                                                <td class="house-label" rowspan="{{ $maxClasses + 1 }}">
                                                    {{ strtoupper($houseName) }}
                                                </td>
                                            @endif
                                            @foreach ($testPeriods as $tpIdx => $tp)
                                                @php
                                                    $cls = isset($periodData[$tpIdx]['classes'][$row])
                                                        ? $periodData[$tpIdx]['classes'][$row]
                                                        : null;
                                                @endphp
                                                @if ($cls)
                                                    <td class="class-label">{{ $cls['name'] }}</td>
                                                    <td>{{ $cls['size'] }}</td>
                                                    <td>{{ $cls['noSat'] }}</td>
                                                    <td>{{ $cls['no6c'] }}</td>
                                                    <td>{{ number_format($cls['pct'], 2) }}</td>
                                                    <td>{{ number_format($cls['jcePct'], 2) }}</td>
                                                    <td>{{ number_format($cls['vaPct'], 2) }}</td>
                                                @else
                                                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endfor

                                    {{-- House total row --}}
                                    <tr class="total-row">
                                        @foreach ($testPeriods as $tpIdx => $tp)
                                            @php
                                                $ht = isset($periodData[$tpIdx]['total'])
                                                    ? $periodData[$tpIdx]['total']
                                                    : null;
                                            @endphp
                                            @if ($ht)
                                                <td><strong>TOTAL</strong></td>
                                                <td>{{ $ht['size'] }}</td>
                                                <td>{{ $ht['noSat'] }}</td>
                                                <td>{{ $ht['no6c'] }}</td>
                                                <td>{{ number_format($ht['pct'], 2) }}</td>
                                                <td>{{ number_format($ht['jcePct'], 2) }}</td>
                                                <td>{{ number_format($ht['vaPct'], 2) }}</td>
                                            @else
                                                <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach

                                {{-- Grand total row --}}
                                <tr class="grand-total-row">
                                    <td><strong>TOTAL</strong></td>
                                    @foreach ($testPeriods as $tpIdx => $tp)
                                        @php
                                            $gt = $grandTotal[$tpIdx] ?? null;
                                        @endphp
                                        @if ($gt)
                                            <td></td>
                                            <td>{{ $gt['size'] }}</td>
                                            <td>{{ $gt['noSat'] }}</td>
                                            <td>{{ $gt['no6c'] }}</td>
                                            <td>{{ number_format($gt['pct'], 2) }}</td>
                                            <td>{{ number_format($gt['jcePct'], 2) }}</td>
                                            <td>{{ number_format($gt['vaPct'], 2) }}</td>
                                        @else
                                            <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tbody>
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

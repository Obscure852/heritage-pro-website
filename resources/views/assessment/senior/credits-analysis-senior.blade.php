@extends('layouts.master')
@section('title')
    Credits Analysis By Class
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

        .card {
            width: 100%;
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
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
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
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Credits Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-download text-muted me-2"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
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
                        <div class="col-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row">
                            <div class="col-12">
                                @if ($test->type == 'CA')
                                    <h5>{{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Class Credits Performance Analysis</h5>
                                @else
                                    <h5>{{ $test->grade->name ?? 'Grade' }} - End of Term Class Credits Performance Analysis</h5>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle;">Class</th>
                                                <th rowspan="2" style="vertical-align: middle;">Class Teacher</th>
                                                <th rowspan="2" style="vertical-align: middle;">Total</th>
                                                <th colspan="{{ count($creditCategories) }}" class="text-center">Number of
                                                    Credits (A*-A-B-C)</th>
                                                <th colspan="2" class="text-center">Best 6 Points</th>
                                            </tr>
                                            <tr>
                                                @foreach ($creditCategories as $credits)
                                                    <th class="text-center">{{ $credits }}</th>
                                                @endforeach
                                                <th class="text-center">≥34</th>
                                                <th class="text-center">≥46</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($classStats as $className => $stats)
                                                <tr>
                                                    <td>{{ $className }}</td>
                                                    <td>{{ $classTeachers[$className] ?? 'Class Teacher' }}</td>
                                                    <td class="text-center">{{ $stats['total'] }}</td>

                                                    @foreach ($creditCategories as $credits)
                                                        <td class="text-center">{{ $stats['credits'][$credits] }}</td>
                                                    @endforeach

                                                    <td class="text-center">{{ $stats['pointsGte34'] }}</td>
                                                    <td class="text-center">{{ $stats['pointsGte46'] }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="font-weight-bold">
                                                <td>House / Grade Totals</td>
                                                <td></td>
                                                <td class="text-center">{{ $gradeTotals['total'] }}</td>

                                                @foreach ($creditCategories as $credits)
                                                    <td class="text-center">{{ $gradeTotals['credits'][$credits] }}</td>
                                                @endforeach

                                                <td class="text-center">{{ $gradeTotals['pointsGte34'] }}</td>
                                                <td class="text-center">{{ $gradeTotals['pointsGte46'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

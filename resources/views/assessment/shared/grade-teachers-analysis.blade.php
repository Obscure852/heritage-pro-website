@extends('layouts.master')

@section('title')
    Credits by House Analysis
@endsection

@section('css')
    <style>
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
            padding: 8px;
            text-align: start;
            font-size: 12px;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: start;
        }

        .credit-column,
        .pass-column {
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10px;
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

            .total-row {
                background-color: #e6e6e6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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
            Teachers Class Grades Analysis
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
                    @if ($test->type == 'CA')
                        <h5>{{ $test->grade->name ?? 'Grade' }} - End of {{ $test->name ?? 'Month' }} Teachers Analysis
                        </h5>
                    @else
                        <h5>{{ $test->grade->name ?? 'Grade' }} - End of Term Teachers Analysis</h5>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>A*</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th class="credit-column"> Credits(%)</th>
                                    <th>D</th>
                                    <th>E</th>
                                    <th class="pass-column">Pass(%)</th>
                                    <th>F</th>
                                    <th>G</th>
                                    <th>U</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData as $index => $row)
                                    <tr
                                        class="{{ !empty($row['_grand_total']) || !empty($row['_group_total']) ? 'total-row' : '' }}">
                                        <td>{{ $row['TEACHER'] }}</td>
                                        <td>{{ $row['CLASS'] }}</td>
                                        <td>{{ $row['SUBJECT'] }}</td>
                                        <td>{{ $row['A*'] }}</td>
                                        <td>{{ $row['A'] }}</td>
                                        <td>{{ $row['B'] }}</td>
                                        <td>{{ $row['C'] }}</td>
                                        <td class="credit-column">{{ $row['% CREDIT'] }}</td>
                                        <td>{{ $row['D'] }}</td>
                                        <td>{{ $row['E'] }}</td>
                                        <td class="pass-column">{{ $row['% PASS'] }}</td>
                                        <td>{{ $row['F'] }}</td>
                                        <td>{{ $row['G'] }}</td>
                                        <td>{{ $row['U'] }}</td>
                                        <td>{{ $row['TOTAL'] }}</td>
                                    </tr>
                                @endforeach
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

        function refreshData() {
            location.reload();
        }

        $(document).ready(function() {
            $('.table-house-analysis').DataTable({
                "paging": false,
                "ordering": true,
                "info": false,
                "searching": false
            });
        });
    </script>
@endsection

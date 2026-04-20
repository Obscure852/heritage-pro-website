@extends('layouts.master')
@section('title')
    Students Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Students Statistical Analysis List
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .card {
                box-shadow: none;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: relative;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                /* Allow horizontal scrolling for wide content */
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-body {
                margin: 0 auto;
                padding: 0;
            }

            .printable .table {
                width: 750px;
                overflow-x: auto;
                /* Allow horizontal scrolling for wide tables */
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <strong>{{ $school_data->school_name }}</strong><br>
                                    <span>{{ $school_data->physical_address }}</span><br>
                                    <span>{{ $school_data->postal_address }}</span><br>
                                    <span>Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 class="text-end">
                                    Student Departures Report for Year
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Reason</th>
                                    <th>Total</th>
                                    @foreach ($yearTerms as $term)
                                        <th>Term {{ $term->term }}</th>
                                    @endforeach
                                    <th>Property Not Returned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allReasons as $reason)
                                    <tr>
                                        <td>{{ $reason }}</td>
                                        <td>{{ $summary[$reason]['total'] }}</td>
                                        @foreach ($yearTerms as $term)
                                            <td>{{ $summary[$reason]['terms'][$term->term] ?? 0 }}</td>
                                        @endforeach
                                        <td>{{ $summary[$reason]['property_not_returned'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td><strong>{{ $totalStats['total'] }}</strong></td>
                                    @foreach ($yearTerms as $term)
                                        <td><strong>{{ $totalStats['by_term'][$term->term] ?? 0 }}</strong></td>
                                    @endforeach
                                    <td><strong>{{ $totalStats['property_not_returned'] }}</strong></td>
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

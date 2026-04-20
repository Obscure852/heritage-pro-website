@extends('layouts.master')
@section('title')
    Credits by House Analysis
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

        .table-responsive {
            overflow-x: auto;
        }

        .table-house-analysis {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        .table-house-analysis th,
        .table-house-analysis td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .table-house-analysis th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table-house-analysis tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table-house-analysis tr:hover {
            background-color: #f5f5f5;
        }

        .credit-column {
            background-color: #e6f3ff;
        }

        .pass-column {
            background-color: #e6ffe6;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            House Grade Analysis
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;" class="bx bx-sync text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="card-title">Subjects Grade Analysis</h4>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>A*</th>
                            <th>A</th>
                            <th>B</th>
                            <th>C</th>
                            <th>% CREDIT</th>
                            <th>D</th>
                            <th>E</th>
                            <th>% PASS</th>
                            <th>F</th>
                            <th>G</th>
                            <th>U</th>
                            <th>TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subjectData as $subject => $data)
                            <tr>
                                <td>{{ $subject }}</td>
                                <td>{{ $data['A*'] }}</td>
                                <td>{{ $data['A'] }}</td>
                                <td>{{ $data['B'] }}</td>
                                <td>{{ $data['C'] }}</td>
                                <td>{{ $data['% CREDIT'] }}</td>
                                <td>{{ $data['D'] }}</td>
                                <td>{{ $data['E'] }}</td>
                                <td>{{ $data['% PASS'] }}</td>
                                <td>{{ $data['F'] }}</td>
                                <td>{{ $data['G'] }}</td>
                                <td>{{ $data['U'] }}</td>
                                <td>{{ $data['TOTAL'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    </script>
@endsection

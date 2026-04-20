@extends('layouts.master')

@section('title')
    Teacher Commitment Report
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('academic.index') }}">Back </a>
        @endslot
        @slot('title')
            Teacher Commitment Report
        @endslot
    @endcomponent

    <style>
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
            }

            .printable #main {
                display: none;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }
        }
    </style>

    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
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
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h5 class="text-muted">Teacher Commitments - {{ $currentTerm->name }}</h5>

                    @foreach ($teacherCommitments as $commitment)
                        <div class="mt-4">
                            <h6><strong>Teacher:</strong> {{ $commitment['teacher_name'] }}</h6>
                            <table class="table table-sm table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Class Name</th>
                                        <th>Subject Name</th>
                                        <th>Student Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($commitment['commitments'] as $item)
                                        <tr>
                                            <td>{{ $item['type'] }}</td>
                                            <td>{{ $item['class_name'] }}</td>
                                            <td>{{ $item['subject_name'] }}</td>
                                            <td>{{ $item['student_count'] }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total Students:</strong></td>
                                        <td><strong>{{ $commitment['total_students'] }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endforeach
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

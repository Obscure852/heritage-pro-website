@extends('layouts.master')
@section('title')
    Class List Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.class-list-report') }}"> Back </a>
        @endslot
        @slot('title')
            Class List Report
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
                width: 100%;
                margin: 0;
            }

            .printable .table th,
            .printable .table td {
                padding: 6px;
                text-align: left;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable d-flex justify-content-start">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0; padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0; padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Generated: {{ now()->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>{{ $grade_name }} - {{ $list_name }}</h5>
                        <small class="text-muted">
                            Total: <strong>{{ $statistics['total'] }}</strong> |
                            Male: <strong>{{ $statistics['male'] }}</strong> |
                            Female: <strong>{{ $statistics['female'] }}</strong>
                        </small>
                    </div>

                    @if ($students->isEmpty())
                        <div class="alert alert-info">
                            No students found for the selected class or optional subject.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm">
                                <thead style="background-color: #6b7280; color: white;">
                                    <tr>
                                        <th>#</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Gender</th>
                                        <th>PSLE</th>
                                        <th style="width:150px"></th>
                                        <th style="width:150px"></th>
                                        <th style="width:150px"></th>
                                        <th style="width:150px"></th>
                                        <th style="width:150px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->first_name }}</td>
                                            <td>{{ $student->last_name }}</td>
                                            <td>{{ $student->gender }}</td>
                                            <td>{{ optional($student->psle)->overall_grade ?? '-' }}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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

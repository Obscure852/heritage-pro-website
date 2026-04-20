@extends('layouts.master')
@section('title')
    Staff Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.index') }}"> Back </a>
        @endslot
        @slot('title')
            Staff Department List
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
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
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('staff.export-analysis-department') }}">
                <i style="font-size:18px;margin-bottom:10px;margin-right:5px;cursor:pointer;"
                    class="bx bx-export text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size:18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    @if (!empty($usersByDepartment))
                        <h6>Analysis By Department</h6>
                        @foreach ($usersByDepartment as $department => $users)
                            <h6 class="text-muted">{{ $department }}</h6>
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr class="text-muted">
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Date of Birth</th>
                                        <th>Gender</th>
                                        <th>Area of Work</th>
                                        <th>ID Number</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->firstname . ' ' . $user->middlename . ' ' . $user->lastname }}
                                            </td>
                                            <td>{{ $user->formatted_date_of_birth }}</td>
                                            <td>{{ $user->gender }}</td>
                                            <td>{{ $user->area_of_work }}</td>
                                            <td>{{ $user->id_number }}</td>
                                            <td>{{ $user->phone }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    @endif
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection

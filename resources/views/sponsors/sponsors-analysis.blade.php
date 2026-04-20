@extends('layouts.master')
@section('title')
    Sponsors Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('sponsors.index') }}">Back</a>
        @endslot
        @slot('title')
            Sponsors Analysis List
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
                width: 100%;
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
            <a href="{{ route('sponsors.sponsors-export-list') }}"><i
                    style="font-size: 18px;margin-bottom:10px;margin-right:5px;cursor:pointer;"
                    class="bx bx-export text-muted"></i></a>
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
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
                    <h5 class="text-muted">Parents List</h5>
                    <table class="table table-bordered table-sm table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Nationality</th>
                                <th>Relation</th>
                                <th>Profession</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($sponsors->isNotEmpty())
                                @foreach ($sponsors as $sponsor)
                                    <tr>
                                        <td>{{ $sponsor->id }}</td>
                                        <td>{{ $sponsor->first_name }}</td>
                                        <td>{{ $sponsor->last_name }}</td>
                                        <td>{{ $sponsor->gender }}</td>
                                        <td>{{ $sponsor->formatted_date_of_birth }}</td>
                                        <td>{{ $sponsor->nationality }}</td>
                                        <td>{{ $sponsor->relation }}</td>
                                        <td>{{ $sponsor->profession }}</td>
                                        <td>{{ $sponsor->phone }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
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

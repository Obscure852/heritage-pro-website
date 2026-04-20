@extends('layouts.master')
@section('title')
    Students Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.staff-custom-analysis') }}"> Back </a>
        @endslot
        @slot('title')
            Students Custom Analysis Report
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
                width: 1000px;
                margin: 0;
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
            <i onclick="printContent()" style="font-size: 18px; margin-bottom:10px; cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>
    <div class="row printable d-flex justify-content-start">
        <div class="col-md-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
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
                    </div>
                </div>
                <div class="card-body">
                    <table style="font-size: 13px;" class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                @foreach ($fields as $field)
                                    <th>{{ $field_headers[$field] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    @foreach ($fields as $field)
                                        <td>
                                            @if ($field == 'roles')
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            @elseif ($field == 'klasses')
                                                @foreach ($user->klasses as $klass)
                                                    <span>{{ $klass->klass->name ?? '' }}
                                                        {{ $klass->subject->subject->name ?? '' }}</span>
                                                    <br>
                                                @endforeach
                                            @elseif ($field == 'klassSubjects')
                                                {{ $user->klassSubjects->pluck('name')->join(', ') }}
                                            @elseif ($field == 'qualifications')
                                                @foreach ($user->qualifications as $qualification)
                                                    <span>
                                                        {{ app\models\Qualification::find($qualification->qualification_id)->qualification ?? '' }}
                                                        {{ $qualification->level }} from
                                                        {{ $qualification->college }}</span>
                                                @endforeach
                                            @else
                                                {{ $user->$field }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
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

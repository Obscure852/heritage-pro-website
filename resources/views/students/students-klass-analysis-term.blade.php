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
            Students Analysis List
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
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row printable table-responsive">
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
                    <h5>Students Class Lists</h5>
                    <table class="table table-bordered table-sm table-striped table-responsive">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Class</td>
                                <td>Subject</td>
                                <td>Teacher</td>
                                <td>Grade</td>
                                <td>Venue/Classroom</td>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($klass_subjects->isNotEmpty())
                                @foreach ($klass_subjects as $index => $klass_subject)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ optional($klass_subject->klass)->name }}</td>
                                        <td>{{ optional($klass_subject->subject->subject)->name }}</td>
                                        <td>{{ optional($klass_subject->teacher)->full_name }}</td>
                                        <td>{{ optional($klass_subject->grade)->name }}</td>
                                        <td>{{ optional($klass_subject->venue)->name }}</td>
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

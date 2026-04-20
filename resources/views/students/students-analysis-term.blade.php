@extends('layouts.master')
@section('title') Students Analysis @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('students.index') }}"> Back </a> @endslot
        @slot('title') Students Analysis List @endslot
    @endcomponent

    <style>
        .card{
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); /* Shadow effect */
            }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                line-height: normal;
            }
            body * {
                visibility: hidden;
            }
            .printable, .printable * {
                visibility: visible;
            }
            .printable {
                position: relative;
                margin: 0 auto;
                width: 80%; /* Adjust the width as needed */
                max-width: 1000px; /* Adjust max-width as needed */
            }
        }

    </style>
    <div class="row">
        <div class="col-md-10 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 15px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-10">
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
                        <span>Tel: {{ $school_data->telephone .' Fax: '. $school_data->fax }}</span>
                        </div>
                        </div>
                        <div  class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    @if ($students->isNotEmpty())
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Gender</th>
                                <th>Class</th>
                                <th>Grade</th>
                                <th>House</th>
                                <th>Date of Birth</th>
                                <th>ID Number</th>
                                <th>Nationality</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                <tr>
                                    <td>{{ $index }}</td>
                                    <td>{{ $student->first_name }}</td>
                                    <td>{{ $student->last_name }}</td>
                                    <td>{{ $student->gender }}</td>
                                    <td>{{ $student->class->name ?? '' }}</td>
                                    <td>{{ $student->class->grade->name ?? '' }}</td>
                                    <td>{{ $student->house->name ?? '' }}</td>
                                    <td>{{ $student->formatted_date_of_birth }}</td>
                                    <td>{{ $student->id_number }}</td>
                                    <td>{{ $student->nationality }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                   {{-- <div class="row">
                    <div class="col-md-12 d-flex justify-content-center">
                        {{ $students->links() }}
                    </div>
                   </div> --}}
                @endif
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <!-- pristine js -->
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <!-- form validation -->
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection

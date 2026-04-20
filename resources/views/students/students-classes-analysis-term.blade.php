@extends('layouts.master')
@section('title') Students Analysis @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('students.index') }}"> Back </a> @endslot
        @slot('title') Students Class Teacher's List @endslot
    @endcomponent
    <style>
            .card {
                border: 1px solid #e5e7eb;
                border-radius: 3px;
                box-shadow: none;
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
        <div class="col-md-8 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 15px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-8">
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
                    @if ($klasses->isNotEmpty())
                    @php
                        $boys =0;
                        $girls = 0;
                    @endphp
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Class</th>
                                    <th rowspan="2">Class Teacher</th>
                                    <th colspan="3">Totals</th>
                                </tr>
                                <tr>
                                    <th>B</th>
                                    <th>G</th>
                                    <th>T</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($klasses as $index => $klass)
                                    <tr>
                                        <td>{{ $index }}</td>
                                        <td>{{ $klass->name }}</td>
                                        <td>{{ $klass->teacher->full_name }}</td>
    
                                        @php
                                            $boys += $klass->boys_count;
                                            $girls += $klass->girls_count;
                                        @endphp
    
                                        <td>{{ $klass->boys_count }}</td>
                                        <td>{{ $klass->girls_count }}</td>
                                        <td>{{ intval($klass->boys_count) + intval($klass->girls_count) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    {{-- <td></td>
                                    <td></td> --}}
                                    <td colspan="3" style="text-align: end"><strong>Totals: </strong></td>
                                    <td>{{ intval($boys) }}</td>
                                    <td>{{ intval($girls) }}</td>
                                    <td>{{ intval($boys) + intval($girls)  }}</td>
                                </tr>
                            </tbody>
                        </table>
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

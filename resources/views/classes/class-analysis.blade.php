@extends('layouts.master')
@section('title') Academic Analysis @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('academic.index') }}"> Back </a> @endslot
        @slot('title') Class Analysis @endslot
    @endcomponent
    <style>
        .card{
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        }

        .report-card {
                margin-top: 0mm;
                margin-bottom: 20mm;
        }

        body{
            font-size: 14px;
        }

        textarea {
            width: 100%; 
            box-sizing: border-box; 
            border: 1px solid #333; 
            padding: 5px; 
            margin: 10px 0; 
        }

        @media print {

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }
            
            body * {
                visibility: hidden;
            }
            .printable, .printable * {
                visibility: visible;
            }
            body * {
                visibility: hidden;
            }

            .printable, .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%); 
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }

            .printable img{
                height:80px;
                width: 10px;
            }


            .card-header {
                display: flex;
                justify-content: space-between; 
                align-items: center;
                padding: 0 10mm; 
            }

            .card-header img {
                width: 300px; 
                height: 120px; 
            }

            .table { 
                width: 100%;
                table-layout: fixed; 
            }

            .table th, .table td {
                width: auto; 
                overflow: visible; 
                word-wrap: break-word; 
            }
            
            textarea {
                border: none; 
            }

            .card{
                box-shadow: none;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-10 col-lg-10 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;" class="bx bx-sync"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-10 col-lg-10">
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
                        <span>Tel: {{ $school_data->telephone .' Fax: '. $school_data->fax }}</span>
                        </div>
                        </div>
                        <div  class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row">
                            <h5> {{ $klass->name ?? '' }} Allocations ({{ $klass->teacher->fullName ?? '' }}) ({{ $klass->students->count() ?? 0 }})</h5>
                            <div class="col-md-12 col-lg-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Names</th>
                                            <th>Gender</th>
                                            <th>DOB</th>
                                            <th>Nationality</th> 
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($klass->students as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $student->fullname ?? '' }}</td>
                                                <td>{{ $student->gender ?? '' }}</td>
                                                <td>{{ $student->date_of_birth ?? '' }}</td>
                                                <td>{{ $student->nationality ?? ''  }}</td>
                                                <td>{{ $student->type ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 col-lg-10">
                                <div id="main" style="width: 100%;height:400px;"></div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>

    <script>

        function printContent() {
            window.print();
        }

    </script>
    
@endsection

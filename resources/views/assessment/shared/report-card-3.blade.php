@extends('layouts.master')
@section('title') Student Report Card @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ $gradebookBackUrl }}">Student Report Card</a> @endslot
        @slot('title') {{ $student->fullname ?? ''  }} @endslot
    @endcomponent
    <style>
        .card{
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        }

        .report-card {
                margin-top: 0mm;
                margin-bottom: 20mm;
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
        <div class="col-md-8 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 15px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
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
                    <div class="report-card">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-4">
                                        <span><strong>Student:</strong> {{ $reportCard['studentName'] }}</span>
                                        <br>
                                        <span><strong>Total in Class: </strong> {{ $reportCard['totalStudents'] ?? 'NULL' }}</span>
                                        <br>

                                    </div>
                                    <div class="col-md-4">
                                        <span><strong>Class:</strong> {{ $reportCard['className'] }}</span>
                                        <br>
                                        <span><strong>Position:</strong> {{ $reportCard['classPosition'] ?? 0 }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <span><strong>Absent Days:</strong> {{ $reportCard['absentDays'] ?? '0' }}</span>
                                        <br>
                                        <span><strong>Class Average Score:</strong> {{ number_format($reportCard['classAverage']['score'],1).' % ' .$reportCard['classAverage']['grade']->grade  ?? 0 }}</span>
                                    </div>
                                </div>

                                <table class="table table-bordered mt-2">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Possible Marks</th>
                                            <th>Actual Marks</th>
                                            <th>%</th>
                                            <th>Grade</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reportCard['subjects'] as $subject)
                                        <tr>
                                            <td>{{ $subject['name'] }}</td>
                                            <td>{{ $subject['possibleMarks'] }}</td>
                                            <td>{{ $subject['actualMarks'] }}</td>
                                            <td>{{ $subject['percentage'] }}%</td>
                                            <td>{{ $subject['grade'] }}</td>
                                            <td>{{ $subject['comments'] }}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <th>Total:</th>
                                            <th>{{ $reportCard['total']['possibleMarks'] }}</th>
                                            <th>{{ $reportCard['total']['actualMarks'] }}</th>
                                            <th>{{ $reportCard['total']['percentage'] }}%</th>
                                            <th colspan="2">{{ $reportCard['total']['grade']->grade }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                    
                                <div class="remarks-section">
                                    <h5>Class Teacher's Remarks</h5>
                                    <p>{{ $reportCard['classTeacherRemarks'] }}</p>
                                    <p><strong>{{ $reportCard['classTeacherName'] }}</strong></p>
                    
                                    <h5>Head Teacher's Remarks</h5>
                                    <p>{{ $reportCard['headTeacherRemarks'] }}</p>
                                    <p><strong>{{ $reportCard['headTeacherName'] }}</strong></p>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <label for="class_teacher_signature">Class Teacher's signature:</label>
                                        <br>
                                        @if (!empty($reportCard['classTeacherSignaturePath']))
                                            <img height="60px;" src="{{ URL::asset($reportCard['classTeacherSignaturePath']) }}" alt="Class Teacher Signature">
                                        @else
                                            <p>.....................</p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        Parent's signature: <br><br>
                                         .....................................
                                    </div>
                                    <div class="col-md-4">
                                        <label for="head_teacher_signature">Head Teacher's signature:</label>
                                        <br>
                                        @if (!empty($reportCard['headTeacherSignaturePath']))
                                            <img height="60px;" src="{{ URL::asset($reportCard['headTeacherSignaturePath']) }}" alt="Head Teacher Signature">
                                        @else
                                            <p>.....................</p>
                                        @endif
                                    </div>
                                </div>
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
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection

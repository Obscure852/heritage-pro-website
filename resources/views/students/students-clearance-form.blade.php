@extends('layouts.master')
@section('title')
    Student Clearance Form
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.show', $student->id) }}">Back</a>
        @endslot
        @slot('title')
            Clearance Form
        @endslot
    @endcomponent

    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .returned {
            color: green;
            font-weight: bold;
        }

        .not-returned {
            color: red;
            font-weight: bold;
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
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5>Student Clearance Form</h5>
                    <p>
                        <strong>Student Name:</strong> {{ $student->fullName ?? '' }}<br>
                        <strong>Student ID:</strong> {{ $student->id_number ?? '' }}<br>
                        <strong>Class :</strong> {{ $student->currentClass()->name ?? '' }}<br>
                        <strong>Date:</strong> {{ date('Y-m-d') }}
                    </p>

                    @php
                        $allocations = $student->bookAllocations->groupBy('grade_id');
                    @endphp

                    @foreach ($allocations as $gradeId => $gradeAllocations)
                        <div class="grade-section">
                            <h6>Grade: {{ $gradeAllocations->first()->grade->name }}</h6>
                            <table class="table table-sm table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>ISBN</th>
                                        <th>Accession Number</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gradeAllocations as $allocation)
                                        <tr>
                                            <td>{{ $allocation->copy->book->title }}</td>
                                            <td>{{ $allocation->copy->book->isbn }}</td>
                                            <td>{{ $allocation->accession_number }}</td>
                                            <td>{{ $allocation->return_date ?? 'Not returned' }}</td>
                                            <td>
                                                @if ($allocation->return_date)
                                                    <span class="returned">✓ Returned</span>
                                                @else
                                                    <span class="not-returned">✗ Not Returned</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-12" style="margin-right: 20px;margin-left:20px;">
                        <table class="table table-borderless">
                            <thead>
                                <th>Teacher Signature</th>
                                <th>Student Signature</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        .......................................
                                    </td>
                                    <td>......................................</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

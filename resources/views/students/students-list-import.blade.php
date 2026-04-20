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
            Students List For Import Report
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
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
                width: 100%;
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
            <a href="{{ route('students.term-import-list', ['export' => 'excel']) }}"
                style="font-size: 18px; margin-bottom:10px; cursor:pointer;">
                <i class="bx bx-download me-2 text-muted"></i>
            </a>
            <i onclick="printContent()" style="font-size: 18px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted me-2"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address }}</span>
                                <br>
                                <span>{{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Students List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Connect ID</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                    <th>Nationality</th>
                                    <th>ID Number</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Grade</th>
                                    <th>Class</th>
                                    <th>Year</th>
                                    <!-- Parent (Sponsor) Columns -->
                                    <th>Parent First Name</th>
                                    <th>Parent Last Name</th>
                                    <th>Parent Gender</th>
                                    <th>Parent Date of Birth</th>
                                    <th>Parent ID Number</th>
                                    <th>Parent Relation</th>
                                    <th>Parent Status</th>
                                    <th>Parent Phone</th>
                                    <th>Parent Profession</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reportData as $index => $student)
                                    <tr>
                                        <td>{{ $student['connect_id'] }}</td>
                                        <td>{{ $student['first_name'] }} {{ $student['last_name'] }}</td>
                                        <td>{{ $student['gender'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($student['date_of_birth'])->format('d/m/Y') }}</td>
                                        <td>{{ $student['nationality'] }}</td>
                                        <td>{{ $student['id_number'] }}</td>
                                        <td>{{ $student['status'] }}</td>
                                        <td>{{ $student['type'] }}</td>
                                        <td>{{ $student['grade'] }}</td>
                                        <td>{{ $student['class'] }}</td>
                                        <td>{{ $student['year'] }}</td>
                                        <td>{{ $student['parent_first_name'] }}</td>
                                        <td>{{ $student['parent_last_name'] }}</td>
                                        <td>{{ $student['parent_gender'] }}</td>
                                        <td>
                                            @if ($student['parent_date_of_birth'])
                                                {{ \Carbon\Carbon::parse($student['parent_date_of_birth'])->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td>{{ $student['parent_id_number'] }}</td>
                                        <td>{{ $student['parent_relation'] }}</td>
                                        <td>{{ $student['parent_status'] }}</td>
                                        <td>{{ $student['parent_phone'] }}</td>
                                        <td>{{ $student['parent_profession'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="20" class="text-center">No students found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

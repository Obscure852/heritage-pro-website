@extends('layouts.master')
@section('title')
    Students Filter Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}">Back</a>
        @endslot
        @slot('title')
            Students Filter Analysis Report
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
            }

            .no-print {
                display: none;
            }
        }
    </style>

    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" class="bx bx-printer text-muted" style="font-size: 18px; cursor: pointer;"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong><br>
                                <span>{{ $school_data->physical_address }}</span><br>
                                <span>{{ $school_data->postal_address }}</span><br>
                                <span>Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @forelse($filters as $filter)
                        <div class="mb-4">
                            <h5 class="text-primary">{{ $filter->name }} Students ({{ $filter->students->count() }})</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Student Name</th>
                                            <th style="width: 100px;">Gender</th>
                                            <th>Class</th>
                                            <th>House</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($filter->students as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $student->fullName }}</td>
                                                <td>{{ $student->gender === 'M' ? 'Male' : 'Female' }}</td>
                                                <td>{{ optional($student->currentClassRelation)->name ?? 'Not Assigned' }}
                                                </td>
                                                <td>{{ optional($student->house)->name ?? 'Not Assigned' }}</td>
                                                <td>{{ $student->type ?? 'Not Specified' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No students found in this filter</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">
                                                <strong>Total: {{ $filter->students->count() }}</strong>
                                            </td>
                                            <td colspan="2">
                                                <strong>Males:
                                                    {{ $filter->students->where('gender', 'M')->count() }}</strong>
                                            </td>
                                            <td colspan="2">
                                                <strong>Females:
                                                    {{ $filter->students->where('gender', 'F')->count() }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">No filters found</div>
                    @endforelse

                    <!-- Summary Section -->
                    <div class="mt-4">
                        <h5>Summary</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Filter</th>
                                        <th>Total Students</th>
                                        <th>Males</th>
                                        <th>Females</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($filters as $filter)
                                        <tr>
                                            <td>{{ $filter->name }}</td>
                                            <td>{{ $filter->students->count() }}</td>
                                            <td>{{ $filter->students->where('gender', 'M')->count() }}</td>
                                            <td>{{ $filter->students->where('gender', 'F')->count() }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-primary">
                                        <td><strong>Total</strong></td>
                                        <td><strong>{{ $filters->sum(function ($filter) {return $filter->students->count();}) }}</strong>
                                        </td>
                                        <td><strong>{{ $filters->sum(function ($filter) {return $filter->students->where('gender', 'M')->count();}) }}</strong>
                                        </td>
                                        <td><strong>{{ $filters->sum(function ($filter) {return $filter->students->where('gender', 'F')->count();}) }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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

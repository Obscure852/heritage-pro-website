@extends('layouts.master')
@section('title')
    Allocated Books
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Students Textbook Allocations
        @endslot
    @endcomponent

@section('css')
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
            font-size: 14px;
        }

        .card table {
            font-size: 14px;
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

            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection
<div class="row no-print">
    <div class="col-12 d-flex justify-content-end">
        <i onclick="printContent()" class="bx bx-printer text-muted"
            style="font-size: 18px; margin-bottom:10px; cursor:pointer;"></i>
    </div>
</div>
<div class="row printable">
    <div class="col-12">
        <div class="card">
            <div class="row" style="margin: 10px;">
                <div class="col-12">
                    <form method="GET" action="{{ route('students.students-book-query') }}"
                        class="form-inline mb-3 no-print">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date" class="sr-only">Start Date</label>
                                    <input type="date" id="start_date" name="start_date"
                                        class="form-control form-control-sm mx-sm-2"
                                        value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date" class="sr-only">End Date</label>
                                    <input type="date" id="end_date" name="end_date"
                                        class="form-control form-control-sm mx-sm-2" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <button type="button" class="btn btn-sm btn-info" onclick="resetForm()">Reset</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                </div>
            </div>

            <div class="card-header" style="height: 120px;">
                <div class="row">
                    <div class="col-md-6">
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
                @if ($allocations->isNotEmpty())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Book Title</th>
                                <th>Checkout Date</th>
                                <th>Due Date</th>
                                <th>Copy Accession Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allocations as $allocation)
                                @php
                                    $isOverdue = $allocation->due_date->isPast() && is_null($allocation->return_date);
                                @endphp
                                <tr @if ($isOverdue) style="background-color: lightcoral;" @endif>
                                    <td>{{ optional($allocation->student)->full_name }}</td>
                                    <td>{{ optional($allocation->student->currentClassRelation->first())->name }}</td>
                                    <td>{{ optional($allocation->copy->book)->title }}</td>
                                    <td>{{ $allocation->allocation_date->format('d/m/Y') }}</td>
                                    <td>{{ $allocation->due_date->format('d/m/Y') }}</td>
                                    <td>{{ $allocation->copy->accession_number }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Additional content such as charts can be placed here -->
                @else
                    <p>No data available</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    function resetForm() {
        window.location.href = '{{ route('students.students-book-query') }}';
    }

    function printContent() {
        window.print();
    }
</script>

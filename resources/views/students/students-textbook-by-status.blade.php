@extends('layouts.master')
@section('title')
    Textbooks By Status
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}"> Back </a>
        @endslot
        @slot('title')
            Textbooks
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
                margin: 10px;
                padding: 0;
                line-height: normal;
                font-size: 12px;
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
                margin: 0;
                width: 100%;
                overflow-x: auto;
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-header,
            .printable .card-body {
                margin: 0;
                padding: 0;
            }

            .printable .row,
            .printable .col-12,
            .printable .col-md-6 {
                margin: 0;
                padding: 0;
            }

            .printable .table {
                width: 100%;
                margin: 0;
                padding: 0;
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
                @if ($books->isNotEmpty())
                    @foreach ($books as $book)
                        <h6>{{ $book->title }} ({{ $book->copies->count() }} copies)</h6>
                        @if ($book->copies->isNotEmpty())
                            <table class="table table-sm table-striped table-bordered nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Accession Number</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($book->copies as $copy)
                                        <tr>
                                            <td>{{ $copy->accession_number }}</td>
                                            <td>{{ ucfirst($copy->status) == 'Checked_out' ? 'Checked Out' : ucfirst($copy->status) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No copies available for this book.</p>
                        @endif
                    @endforeach
                @else
                    <p>No books available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    function printContent() {
        window.print();
    }
</script>

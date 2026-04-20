@extends('layouts.master')
@section('title')
    Sponsor Import Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('sponsors.index') }}">Back</a>
        @endslot
        @slot('title')
            Sponsor List
        @endslot
    @endcomponent

    <style>
        .card {
            box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
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

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('sponsors.import-list-report', ['export' => 'excel']) }}" style="font-size: 18px;"
                class="mr-2 text-muted">
                <i class="bx bx-sync"></i>
            </a>
            <a href="#" onclick="printContent()" class="text-muted" style="font-size: 18px;">
                <i class="bx bx-printer"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="height: 120px;">
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
                            <img src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo" height="80">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Sponsor Import Report</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Connect ID</th>
                                    <th>Title</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                    <th>Nationality</th>
                                    <th>Relation</th>
                                    <th>Status</th>
                                    <th>ID Number</th>
                                    <th>Phone</th>
                                    <th>Profession</th>
                                    <th>Work Place</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reportData as $sponsor)
                                    <tr>
                                        <td>{{ $sponsor['connect_id'] }}</td>
                                        <td>{{ $sponsor['title'] }}</td>
                                        <td>{{ $sponsor['first_name'] }}</td>
                                        <td>{{ $sponsor['last_name'] }}</td>
                                        <td>{{ $sponsor['email'] }}</td>
                                        <td>{{ $sponsor['gender'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sponsor['date_of_birth'])->format('d/m/Y') }}</td>
                                        <td>{{ $sponsor['nationality'] }}</td>
                                        <td>{{ $sponsor['relation'] }}</td>
                                        <td>{{ $sponsor['status'] }}</td>
                                        <td>{{ $sponsor['id_number'] }}</td>
                                        <td>{{ $sponsor['phone'] }}</td>
                                        <td>{{ $sponsor['profession'] }}</td>
                                        <td>{{ $sponsor['work_place'] }}</td>
                                        <td>{{ $sponsor['year'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">No sponsors found</td>
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

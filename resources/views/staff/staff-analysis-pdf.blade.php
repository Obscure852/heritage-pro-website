@extends('layouts.master')
@section('title')
    Staff Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.index') }}"> Back </a>
        @endslot
        @slot('title')
            Staff Analysis List
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            /* Shadow effect */
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

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: relative;
                margin: 0 auto;
                width: 80%;
                /* Adjust the width as needed */
                max-width: 1000px;
                /* Adjust max-width as needed */
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
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    @if (!empty($users))
                        <h4>User Report</h4>
                        @foreach ($groupedUsers as $areaOfWork => $users)
                            <h2>{{ $areaOfWork }}</h2>
                            <ul>
                                @foreach ($users as $user)
                                    <li>{{ $user->name }} - {{ $user->email }}</li>
                                @endforeach
                            </ul>
                        @endforeach
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

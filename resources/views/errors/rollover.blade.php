@extends('layouts.master-without-nav')
@section('title')
    Year Rollover Error
@endsection
@section('content')
    <div class="account-pages my-5 pt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="card-body pt-0">
                            <div class="ex-page-content text-center">
                                <h1 class="text-danger mt-5">500</h1>
                                <h3 class="mb-4">Year Rollover Error</h3>
                                <p class="mb-4">{{ $errorMessage }}</p>
                                <p class="mb-4">Error Code: {{ $errorCode }}</p>
                                <p class="mb-4">We apologize for the inconvenience. Our team has been notified and will
                                    address this issue as soon as possible.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 text-center">
                        <a class="btn btn-sm btn-danger waves-effect waves-light" href="{{ route('dashboard') }}">Back to
                            Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

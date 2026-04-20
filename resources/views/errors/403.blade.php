@extends('layouts.master-without-nav')
@section('title')
    Access Denied
@endsection
@section('css')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .error-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            max-width: 500px;
            width: 90%;
            transition: all 0.3s ease;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #f3f3f3;
            position: relative;
            line-height: 1;
        }

        .error-title {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            color: #333;
            width: 100%;
            text-align: center;
        }

        .error-icon {
            font-size: 4rem;
            position: relative;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-icon .fa-globe {
            color: #e9ecef;
        }

        .error-icon .fa-ban {
            color: #dc3545;
            position: absolute;
        }

        .btn-back {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
    </style>
@endsection

@section('body')

    <body>
    @endsection

    @section('content')
        <div class="error-container">
            <div class="card error-card shadow">
                <div class="card-body p-5">
                    <div class="text-center">
                        <!-- Error Code & Title -->
                        <div class="position-relative mb-4">
                            <div class="error-code">403</div>
                            <h2 class="error-title fw-bold">Access Denied</h2>
                        </div>

                        <!-- Error Icon -->
                        <div class="error-icon mb-4">
                            <i class="fas fa-globe"></i>
                            <i class="fas fa-ban"></i>
                        </div>

                        <!-- Error Message -->
                        <div class="mb-4">
                            <p class="text-muted mb-2">
                                The application is not available in your region.
                            </p>
                            <p class="text-muted small">
                                Please contact support if you believe this is an error.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-3">
                            <button onclick="history.back()" class="btn btn-secondary btn-back">
                                <i class="fas fa-arrow-left me-2"></i>
                                Go Back
                            </button>
                            <a href="mailto:support@heritagepro.co" class="btn btn-primary btn-back">
                                <i class="fas fa-envelope me-2"></i>
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

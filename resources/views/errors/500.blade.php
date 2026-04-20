@extends('layouts.master-without-nav')
@section('title')
    Server Error
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
            color: #dc3545;
        }

        .btn-back {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .rotate {
            animation: rotate 4s linear infinite;
        }

        .error-icon i {
            filter: drop-shadow(0 0 2px rgba(220, 53, 69, 0.3));
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
                            <div class="error-code">500</div>
                            <h2 class="error-title fw-bold">Server Error</h2>
                        </div>

                        <!-- Error Icon -->
                        <div class="error-icon mb-4">
                            <i class="fas fa-cog rotate"></i>
                        </div>

                        <!-- Error Message -->
                        <div class="mb-4">
                            <p class="text-muted mb-2">
                                Oops! Something went wrong on our servers.
                            </p>
                            <p class="text-muted small">
                                We're working to fix this issue. Please try again later or contact support if the problem
                                persists.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-3">
                            <button onclick="location.reload()" class="btn btn-secondary btn-back">
                                <i class="fas fa-redo me-2"></i>
                                Try Again
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-back">
                                <i class="fas fa-home me-2"></i>
                                Home Page
                            </a>
                        </div>

                        <!-- Support Link -->
                        <div class="mt-4">
                            <a href="mailto:support@heritagepro.co" class="text-muted small">
                                <i class="fas fa-envelope me-1"></i>
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

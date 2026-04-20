@extends('layouts.master-without-nav')
@section('title')
    Thank You!
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
        }

        .success-container {
            background: white;
            border-radius: 3px;
            padding: 48px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success-icon i {
            font-size: 52px;
            color: white;
        }

        .success-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .success-message {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 12px 24px;
            border-radius: 3px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 32px 24px;
                margin: 16px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="success-container">
            <div class="success-icon">
                <i class="bx bx-check"></i>
            </div>

            <h1 class="success-title">Thank You, {{ $admission->full_name ?? '' }}</h1>
            <p class="success-message">
                Your application has been submitted successfully. We will be in touch with you soon.
            </p>

            <a class="btn btn-primary" href="{{ route('admissions.online-applications') }}">
                <i class="bx bx-plus me-2"></i> Apply for another child
            </a>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/js/pages/pass-addon.init.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
@endsection

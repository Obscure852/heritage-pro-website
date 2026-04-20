@extends('layouts.master-without-nav')
@section('title')
    Student Reset Password | Heritage Pro School Management System
@endsection

@section('css')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .auth-page {
            background: linear-gradient(135deg, #e8f4fc 0%, #f0f4f8 50%, #e3edf5 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .reset-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
            max-width: 480px;
            width: 100%;
            padding: 48px;
        }

        .reset-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #e8f4fc 0%, #dbeafe 100%);
            color: #2563eb;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        .reset-badge i {
            font-size: 12px;
        }

        .reset-title {
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .reset-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
            font-weight: 400;
        }

        .info-alert {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-alert i {
            color: #2563eb;
            font-size: 16px;
            margin-top: 2px;
        }

        .info-alert p {
            color: #1e40af;
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
        }

        .success-alert {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-alert i {
            color: #16a34a;
            font-size: 16px;
        }

        .success-alert p {
            color: #166534;
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
            flex: 1;
        }

        .success-alert .btn-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #166534;
            opacity: 0.5;
            padding: 0;
        }

        .error-alert {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-alert i {
            color: #dc2626;
            font-size: 16px;
        }

        .error-alert p {
            color: #991b1b;
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.2s ease;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            color: #1e293b;
            background: #f9fafb;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-control:focus + i.input-icon,
        .input-wrapper:focus-within i.input-icon {
            color: #2563eb;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset i {
            font-size: 14px;
        }

        .btn-reset.loading .btn-text {
            display: none;
        }

        .btn-reset.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-reset:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .back-to-login {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #64748b;
        }

        .back-to-login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .back-to-login a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            color: #94a3b8;
            font-size: 13px;
            width: 100%;
        }

        @media (max-width: 520px) {
            .reset-card {
                padding: 32px 24px;
            }

            .reset-title {
                font-size: 22px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="auth-page">
        <div class="reset-card">
            <div class="text-center">
                <div class="reset-badge">
                    <i class="fas fa-key"></i>
                    STUDENT PASSWORD RESET
                </div>

                <h1 class="reset-title">Forgot your password?</h1>
                <p class="reset-subtitle">Enter your email and we'll send you a reset link</p>
            </div>

            <div class="info-alert">
                <i class="fas fa-info-circle"></i>
                <p>A password reset link will be sent to your registered student email address.</p>
            </div>

            @if (session('status'))
                <div class="success-alert">
                    <i class="fas fa-check-circle"></i>
                    <p>{{ session('status') }}</p>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @error('email')
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>{{ $message }}</p>
                </div>
            @enderror

            <form method="POST" action="{{ route('student.password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Student email address</label>
                    <div class="input-wrapper">
                        <input type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                            placeholder="student@example.com">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="btn-reset">
                    <span class="btn-text">Send reset link <i class="fas fa-paper-plane"></i></span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Sending...
                    </span>
                </button>
            </form>

            <div class="back-to-login">
                Remember your password? <a href="{{ route('student.login') }}">Sign in</a>
            </div>
        </div>

        <div class="footer-text">
            &copy; <script>document.write(new Date().getFullYear())</script> Heritage Pro
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-reset');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection

@extends('layouts.master-without-nav')

@section('title')
    Student Recover Password | Heritage School Management System
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .auth-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.5s ease;
        }

        .recovery-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: 2rem auto;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .recovery-form {
            padding: 3rem;
        }

        .brand-logo {
            max-width: 180px;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.50rem 1.2rem;
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 0.50rem 2rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .text-muted a {
            color: #4e73df;
            transition: color 0.3s ease;
        }

        .text-muted a:hover {
            color: #2e59d9;
            text-decoration: underline;
        }

        #password-criteria {
            padding-left: 0;
        }

        #password-criteria li {
            list-style: none;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        #password-criteria i {
            margin-right: 0.5rem;
            transition: color 0.2s ease;
        }

        .text-success i {
            color: green;
        }

        .text-danger i {
            color: red;
        }

        /* Password toggle styles */
        .password-field-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #4e73df;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-loading:disabled:hover {
            transform: none;
            box-shadow: none;
        }
    </style>
@endsection

@php
    $school_data = \App\Models\SchoolSetup::first();
    $name = $school_data->school_name ?? 'No School Name Found';
@endphp

@section('content')
    <div class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="recovery-card animate__animated animate__fadeInUp">
                        <div class="recovery-form">
                            <div class="text-center">
                                <h5 class="mb-0">{{ $name }}</h5>
                                <p class="text-muted mt-2">Student Password Reset</p>
                            </div>

                            @if (session('errors'))
                                <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>{{ session('errors')->first('email') }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>{{ session('message') }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- IMPORTANT: Use the sponsor password reset update route here --}}
                            <form class="mt-4" method="POST" action="{{ route('student.password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                {{-- Email is typically read-only for a password reset --}}
                                <div class="mb-3">
                                    <label for="useremail" class="form-label">Student Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="useremail" name="email" value="{{ $email ?? old('email') }}" readonly>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- New Password --}}
                                <div class="mb-3">
                                    <label for="userpassword" class="form-label">New Password</label>
                                    <div class="password-field-wrapper">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            name="password" id="userpassword" placeholder="Enter new password">
                                        <span class="password-toggle" id="toggle-password">
                                            <i class="fas fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Confirm New Password --}}
                                <div class="mb-3">
                                    <label for="password-confirm" class="form-label">Confirm New Password</label>
                                    <div class="password-field-wrapper">
                                        <input id="password-confirm" type="password" name="password_confirmation"
                                            class="form-control" placeholder="Confirm new password">
                                        <span class="password-toggle" id="toggle-confirm-password">
                                            <i class="fas fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>

                                {{-- Password Criteria List --}}
                                <ul id="password-criteria" class="mb-4">
                                    <li id="length"><i class="fas fa-circle"></i>At least 8 characters</li>
                                    <li id="special"><i class="fas fa-circle"></i>At least 1 special character</li>
                                    <li id="uppercase"><i class="fas fa-circle"></i>At least 1 uppercase letter</li>
                                    <li id="lowercase"><i class="fas fa-circle"></i>At least 1 lowercase letter</li>
                                    <li id="match"><i class="fas fa-circle"></i>Passwords must match</li>
                                </ul>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text">Reset Password</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Resetting...
                                        </span>
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                {{-- Link back to Sponsor login instead of general user login --}}
                                <p class="text-muted mb-0">
                                    Remember your password?
                                    <a href="{{ route('student.login') }}" class="fw-semibold">Sign In</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-muted">
                            &copy;
                            <script>
                                document.write(new Date().getFullYear())
                            </script> Heritage School.
                            Crafted with <i class="fas fa-heart text-danger"></i> by Platinum Developers
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('userpassword');
            const confirmPasswordInput = document.getElementById('password-confirm');
            const criteriaList = {
                length: document.getElementById('length'),
                special: document.getElementById('special'),
                uppercase: document.getElementById('uppercase'),
                lowercase: document.getElementById('lowercase'),
                match: document.getElementById('match')
            };

            // Password toggle functionality
            const togglePassword = document.getElementById('toggle-password');
            const toggleConfirmPassword = document.getElementById('toggle-confirm-password');

            togglePassword.addEventListener('click', function() {
                togglePasswordVisibility(passwordInput, this);
            });

            toggleConfirmPassword.addEventListener('click', function() {
                togglePasswordVisibility(confirmPasswordInput, this);
            });

            function togglePasswordVisibility(inputField, toggleIcon) {
                const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                inputField.setAttribute('type', type);

                // Toggle the eye icon
                const icon = toggleIcon.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            function validatePassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                const lengthValid = password.length >= 8;
                const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                const uppercaseValid = /[A-Z]/.test(password);
                const lowercaseValid = /[a-z]/.test(password);
                const matchValid = password === confirmPassword && password !== '';

                updateCriteria(criteriaList.length, lengthValid);
                updateCriteria(criteriaList.special, specialValid);
                updateCriteria(criteriaList.uppercase, uppercaseValid);
                updateCriteria(criteriaList.lowercase, lowercaseValid);
                updateCriteria(criteriaList.match, matchValid);
            }

            function updateCriteria(element, isValid) {
                const icon = element.querySelector('i');
                if (isValid) {
                    icon.classList.remove('fa-times-circle');
                    icon.classList.add('fa-check-circle');
                    element.classList.remove('text-danger');
                    element.classList.add('text-success');
                } else {
                    icon.classList.remove('fa-check-circle');
                    icon.classList.add('fa-times-circle');
                    element.classList.remove('text-success');
                    element.classList.add('text-danger');
                }
            }

            passwordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validatePassword);

            // Form submit loading state
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection

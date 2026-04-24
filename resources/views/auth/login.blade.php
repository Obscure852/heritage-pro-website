@extends('layouts.auth')

@section('title', 'Sign In')
@section('auth_heading', 'Sign in to the CRM')
@section('auth_copy', 'Use your staff email address and password to access the CRM workspace.')
@section('auth_media_heading', 'Secure CRM access')
@section('auth_media_copy', 'One staff sign-in for the CRM dashboard, commercial workspace, team directory, requests, and customer activity.')

@section('auth_inline_email_errors', '1')

@section('auth_content')
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">Email address <span class="auth-required">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your staff email address" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Password <span class="auth-required">*</span></label>
            <div class="auth-input-shell">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your password" required autocomplete="current-password">
                <button
                    type="button"
                    class="auth-input-action"
                    data-password-toggle
                    data-password-target="password"
                    aria-label="Show password"
                    title="Show password"
                >
                    <svg class="icon-eye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M2 12C3.9 8.2 7.4 6 12 6s8.1 2.2 10 6c-1.9 3.8-5.4 6-10 6s-8.1-2.2-10-6Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                    </svg>
                    <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 3L21 21" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10.6 10.7A3 3 0 0 0 12 15a3 3 0 0 0 2.3-5.1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.9 5.1A11 11 0 0 1 12 5c4.6 0 8.1 2.2 10 6a12.6 12.6 0 0 1-4 4.7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.2 6.3A12.5 12.5 0 0 0 2 12c1.9 3.8 5.4 6 10 6 1.4 0 2.8-.2 4-.7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <label class="auth-check" for="remember">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <span>Keep me signed in on this device</span>
        </label>

        <div class="auth-link-stack auth-link-stack-centered">
            <button type="submit" class="auth-submit btn-loading">
                <span class="btn-text">Sign in</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Signing in...
                </span>
            </button>

            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">Reset password</a>
            @endif

            <p class="auth-meta">Password reset is the self-service path for first-time password creation and password recovery.</p>
        </div>
    </form>
@endsection
